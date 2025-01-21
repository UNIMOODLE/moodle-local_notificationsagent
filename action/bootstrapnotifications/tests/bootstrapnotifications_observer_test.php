<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

// Project implemented by the "Recovery, Transformation and Resilience Plan.
// Funded by the European Union - Next GenerationEU".
//
// Produced by the UNIMOODLE University Group: Universities of
// Valladolid, Complutense de Madrid, UPV/EHU, León, Salamanca,
// Illes Balears, Valencia, Rey Juan Carlos, La Laguna, Zaragoza, Málaga,
// Córdoba, Extremadura, Vigo, Las Palmas de Gran Canaria y Burgos.

/**
 * Version details
 *
 * @package    notificationsaction_bootstrapnotifications
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace notificationsaction_bootstrapnotifications;

use local_notificationsagent\evaluationcontext;
use local_notificationsagent\form\editrule_form;
use local_notificationsagent\notificationplugin;
use local_notificationsagent\helper\test\phpunitutil;
use local_notificationsagent\rule;
use notificationsaction_bootstrapnotifications\bootstrapmessages;
use notificationsaction_bootstrapnotifications\bootstrapnotifications;

/**
 * Test boostrapnotificationss
 *
 * @group notificationsagent
 */
final class bootstrapnotifications_observer_test extends \advanced_testcase {
    /**
     * @var rule
     */
    private static $rule;

    /**
     * @var bootstrapnotifications
     */
    private static $subplugin;

    /**
     * @var \stdClass
     */
    private static $coursetest;

    /**
     * @var string
     */
    private static $subtype;
    /**
     * @var \stdClass
     */
    private static $user;
    /**
     * @var evaluationcontext
     */
    private static $context;
    /**
     * @var bool|\context|\context_course
     */
    private static $coursecontext;
    /**
     * @var array|string[]
     */
    private static $elements;
    /**
     * id for condition
     */
    public const CONDITIONID = 1;
    /**
     * Date start for the course
     */
    public const COURSE_DATESTART = 1704099600; // 01/01/2024 10:00:00.
    /**
     * Date end for the course
     */
    public const COURSE_DATEEND = 1706605200; // 30/01/2024 10:00:00,

    /**
     * Set up the test fixture.
     * This method is called before a test is executed.
     */
    public function setUp(): void {
        parent::setUp();
        $this->resetAfterTest(true);
        self::$rule = new rule();

        self::$subplugin = new bootstrapnotifications(self::$rule->to_record());
        self::$subplugin->set_id(5);
        self::$coursetest = self::getDataGenerator()->create_course(
            ['startdate' => self::COURSE_DATESTART, 'enddate' => self::COURSE_DATEEND]
        );
        self::$coursecontext = \context_course::instance(self::$coursetest->id);
        self::$user = self::getDataGenerator()->create_user();
        self::$context = new evaluationcontext();
        self::$context->set_userid(self::$user->id);
        self::$context->set_courseid(self::$coursetest->id);
        self::$subtype = 'bootstrapnotifications';
        self::$elements = ['[TTTT]'];
    }

    /**
     * Test case for the course_viewed observer event.
     *
     * @dataProvider dataprovider
     *
     * @param string $message Message to create a bootstrap notification.
     * @param int $course Course ID, SITEID for front page.
     *
     * @covers       \notificationsaction_bootstrapnotifications_observer::course_viewed
     * @covers       \notificationsaction_bootstrapnotifications\bootstrapmessages::get_records
     *
     */
    public function test_course_viewed($message, $course): void {
        global $SESSION;

        $courseid = $course == SITEID ? SITEID : self::$coursetest->id;
        $bootstrap = new bootstrapmessages();
        $bootstrap->set('userid', self::$user->id);
        $bootstrap->set('courseid', self::$coursetest->id);
        $bootstrap->set('message', $message);
        $bootstrap->save();

        $messages = bootstrapmessages::get_records(['userid' => self::$user->id, 'courseid' => self::$coursetest->id]);

        $this->assertNotEmpty($messages);

        $event = \core\event\course_viewed::create([
                'context' => \context_course::instance($courseid),
                'userid' => self::$user->id,
                'courseid' => $courseid,
        ]);
        $event->trigger();

        if ($courseid == SITEID) {
            $this->assertFalse(\notificationsaction_bootstrapnotifications_observer::course_viewed($event));
        } else {
            $deleted = bootstrapmessages::get_records(['userid' => self::$user->id, 'courseid' => $courseid]);

            $this->assertEmpty($deleted);

            $this->assertEquals($message, $SESSION->notifications[0]->message);
            $this->assertEquals('success', $SESSION->notifications[0]->type);
        }
    }

    /**
     * Data provider for the test_course_viewed test.
     *
     */
    public static function dataprovider(): array {
        return [
                'siteid' => ['{"message":"TEST"}', 1],
                'message 1' => ['{"message":"TEST"}', 0],
                'message 2' => ['{"message":"Message content"}', 0],
        ];
    }
}
