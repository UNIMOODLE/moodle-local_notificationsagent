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
 * @package    local_notificationsagent
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace notificationscondition_activityend;

use local_notificationsagent\rule;
use notificationscondition_activityend\task\activityend_crontask;

defined('MOODLE_INTERNAL') || die();
require_once(__DIR__ . '/../../../../../lib/cronlib.php');

/**
 * @group notificationsagent
 */
class activityend_crontask_test extends \advanced_testcase {

    private static $rule;
    private static $user;
    private static $course;
    public const COURSE_DATESTART = 1704099600; // 01/01/2024 10:00:00.
    public const COURSE_DATEEND = 1706605200; // 30/01/2024 10:00:00,
    public const CM_DATESTART = 1704099600; // 01/01/2024 10:00:00,
    public const CM_DATEEND = 1705741200; // 20/01/2024 10:00:00,
    public const USER_FIRSTACCESS = 1704099600;
    public const USER_LASTACCESS = 1704099600;

    final public function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
        $rule = new rule();
        self::$rule = $rule;
        self::$user = self::getDataGenerator()->create_user();
        self::$course = self::getDataGenerator()->create_course(
            ([
                'startdate' => self::COURSE_DATESTART,
                'enddate' => self::COURSE_DATEEND,
            ])
        );
        self::getDataGenerator()->enrol_user(self::$user->id, self::$course->id);

    }

    /**
     * @covers       \notificationscondition_activityend\task\activityend_crontask::execute
     * @dataProvider dataprovider
     */
    public function test_execute($date) {
        global $DB, $USER;
        $pluginname = 'activityend';

        $quizgen = self::getDataGenerator()->get_plugin_generator('mod_quiz');
        $cmtestacct = $quizgen->create_instance([
            'course' => self::$course->id,
            'timeopen' => self::CM_DATESTART,
            'timeclose' => self::CM_DATEEND,
        ]);

        $dataform = new \StdClass();
        $dataform->title = "Rule Test";
        $dataform->type = 1;
        $dataform->courseid = self::$course->id;
        $dataform->timesfired = 2;
        $dataform->runtime_group = ['runtime_days' => 5, 'runtime_hours' => 0, 'runtime_minutes' => 0];
        $USER->id = self::$user->id;
        $ruleid = self::$rule->create($dataform);
        self::$rule->set_id($ruleid);

        $objdb = new \stdClass();
        $objdb->ruleid = self::$rule->get_id();
        $objdb->courseid = self::$course->id;
        $objdb->type = 'condition';
        $objdb->pluginname = $pluginname;
        $objdb->parameters = '{"time":"' . $date . '", "cmid":"' . $cmtestacct->cmid . '"}';
        $objdb->cmid = $cmtestacct->id;

        // Insert.
        $conditionid = $DB->insert_record('notificationsagent_condition', $objdb);
        $this->assertIsInt($conditionid);
        self::$rule::create_instance($ruleid);

        $task = \core\task\manager::get_scheduled_task(activityend_crontask::class);
        $task->execute();

        $cache = $DB->get_record('notificationsagent_cache', ['conditionid' => $conditionid]);

        $this->assertEquals($pluginname, $cache->pluginname);
        $this->assertEquals(self::$course->id, $cache->courseid);
        $this->assertEquals(self::CM_DATEEND - $date, $cache->timestart);
        $this->assertEquals(self::$user->id, $cache->userid);

    }

    public static function dataprovider(): array {
        return [
            [86400],
            [86400 * 3],
        ];
    }
}


