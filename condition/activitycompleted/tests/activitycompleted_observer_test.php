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
// Funded by the European Union - Next GenerationEU\".
//
// Produced by the UNIMOODLE University Group: Universities of
// Valladolid, Complutense de Madrid, UPV/EHU, León, Salamanca,
// Illes Balears, Valencia, Rey Juan Carlos, La Laguna, Zaragoza, Málaga,
// Córdoba, Extremadura, Vigo, Las Palmas de Gran Canaria y Burgos.

/**
 * Version details
 *
 * @package    notificationscondition_activitycompleted
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace notificationscondition_activitycompleted;

use local_notificationsagent\evaluationcontext;
use local_notificationsagent\form\editrule_form;
use local_notificationsagent\helper\test\mock_base_logger;
use local_notificationsagent\helper\test\phpunitutil;
use local_notificationsagent\notificationplugin;
use local_notificationsagent\notificationsagent;
use local_notificationsagent\rule;
use notificationscondition_activitycompleted\activitycompleted;

/**
 * Tests for the activitycompleted observer condition.
 *
 * @group notificationsagent
 */
class activitycompleted_observer_test extends \advanced_testcase {
    /**
     * @var rule
     */
    private static $rule;
    /**
     * @var activitycompleted
     */
    private static $subplugin;
    /**
     * @var \stdClass
     */
    private static $course;
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
     * Date start for the course
     */
    public const COURSE_DATESTART = 1704099600; // 01/01/2024 10:00:00.
    /**
     * Date end for the course
     */
    public const COURSE_DATEEND = 1706605200; // 30/01/2024 10:00:00,
    /**
     * Activity date start
     */
    public const CM_DATESTART = 1704099600; // 01/01/2024 10:00:00,
    /**
     * Activity date end
     */
    public const CM_DATEEND = 1705741200; // 20/01/2024 10:00:00,

    public function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
        $rule = new rule();
        self::$rule = $rule;
        self::$course = self::getDataGenerator()->create_course(
            ([
                        'startdate' => self::COURSE_DATESTART,
                        'enddate' => self::COURSE_DATEEND,
                        'enablecompletion' => true,
                ])
        );
        self::$user = self::getDataGenerator()->create_user();
        self::setUser(self::$user);
        self::$context = new evaluationcontext();
        self::$context->set_userid(self::$user->id);
        self::$context->set_courseid(self::$course->id);
        self::$subtype = 'activitycompleted';
        self::$elements = ['[AAAA]'];
    }

    /**
     * Testing course_module_viewed event
     *
     * @return void
     * @covers       \local_notificationsagent\notificationsagent::bulk_delete_conditions_by_userid
     * @covers       \notificationscondition_activitycompleted_observer::course_module_completion_updated
     *
     * @dataProvider dataprovider
     *
     */

    public function test_course_module_completion_updated($status) {
        global $DB, $USER;

        $pluginname = activitycompleted::NAME;

        $modinstance = self::getDataGenerator()->create_module('quiz', [
                'course' => self::$course,
                'completion' => COMPLETION_TRACKING_AUTOMATIC,
        ]);

        $cmtestac = get_coursemodule_from_instance('quiz', $modinstance->id, self::$course->id, false, MUST_EXIST);

        $dataform = new \StdClass();
        $dataform->title = "Rule Test";
        $dataform->type = 1;
        $dataform->courseid = self::$course->id;
        $dataform->timesfired = 2;
        $dataform->runtime_group = ['runtime_days' => 5, 'runtime_hours' => 0, 'runtime_minutes' => 0];
        $USER->id = 2;
        $ruleid = self::$rule->create($dataform);
        self::$rule->set_id($ruleid);
        self::setUser(2);

        $objdb = new \stdClass();
        $objdb->ruleid = self::$rule->get_id();
        $objdb->courseid = self::$course->id;
        $objdb->type = 'condition';
        $objdb->pluginname = $pluginname;
        $objdb->parameters = '{"cmid":"' . $modinstance->cmid . '"}';
        $objdb->cmid = $modinstance->cmid;
        // Insert.
        $conditionid = $DB->insert_record('notificationsagent_condition', $objdb);
        $this->assertIsInt($conditionid);
        self::$rule::create_instance($ruleid);

        $completion = new \completion_info(self::$course);
        if ($status == COMPLETION_INCOMPLETE) {
            $completion->update_state($cmtestac, COMPLETION_COMPLETE, self::$user->id, false);
        }

        $completion->update_state($cmtestac, $status, self::$user->id, true);

        $cache = $DB->get_record('notificationsagent_cache', ['conditionid' => $conditionid]);
        $trigger = $DB->get_record('notificationsagent_triggers', ['conditionid' => $conditionid]);

        if ($status == COMPLETION_COMPLETE) {
            $this->assertEquals($pluginname, $cache->pluginname);
            $this->assertEquals(self::$course->id, $cache->courseid);
            $this->assertEquals(self::$user->id, $cache->userid);
            $this->assertEquals(self::$course->id, $trigger->courseid);
            $this->assertEquals(self::$rule->get_id(), $trigger->ruleid);
            $this->assertEquals(self::$user->id, $trigger->userid);
        }

        if ($status == COMPLETION_INCOMPLETE) {
            $this->assertEmpty($cache);
            $this->assertEmpty($trigger);
        }
    }

    /**
     * Data provider for course module completion updated
     *
     * @return array[]
     */
    public static function dataprovider(): array {
        return [
                [COMPLETION_COMPLETE],
                [COMPLETION_INCOMPLETE],
        ];
    }
}
