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
 * @package    notificationscondition_forumnoreply
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace notificationscondition_forumnoreply\task;

use local_notificationsagent\notificationplugin;
use local_notificationsagent\notificationsagent;
use local_notificationsagent\rule;
use notificationscondition_forumnoreply\forumnoreply;
use notificationscondition_forumnoreply\task\forumnoreply_crontask;

/**
 * Test scheduled task for forumnoreply subplugin
 *
 * @group notificationsagent
 */
final class forumnoreply_crontask_test extends \advanced_testcase {
    /**
     * @var rule
     */
    private static $rule;
    /**
     * @var \stdClass
     */
    private static $user;
    /**
     * @var \stdClass
     */
    private static $course;
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
    /**
     * User first access to a course
     */
    public const USER_FIRSTACCESS = 1704099600;
    /**
     * User last access to a course
     */
    public const USER_LASTACCESS = 1704099600;

    public function setUp(): void {
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
     * Testing execute
     *
     * @param int $date
     *
     * @covers       \notificationscondition_forumnoreply\task\forumnoreply_crontask::execute
     * @covers       \local_notificationsagent\helper\helper::custom_mtrace
     * @dataProvider dataprovider
     */
    public function test_execute($date): void {
        global $DB, $USER;
        $pluginname = forumnoreply::NAME;

        $quizgen = self::getDataGenerator()->get_plugin_generator('mod_forum');
        $cmtestfct = $quizgen->create_instance([
            'course' => self::$course->id,
        ]);

        $record = new \stdClass();
        $record->course = self::$course->id;
        $record->userid = self::$user->id;
        $record->usermodified = self::$user->id;
        $record->forum = $cmtestfct->id;
        $record->timeend = 0;
        // Meke sure we have a discussion.
        $record->timemodified = self::COURSE_DATESTART - $date;
        $record->timestart = 1;

        $discussion = $quizgen->create_discussion($record);
        $this->assertIsInt($discussion->id);

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
        $objdb->parameters = '{"time":"' . $date . '", "cmid":"' . $cmtestfct->cmid . '"}';
        $objdb->cmid = $cmtestfct->id;

        // Insert.
        $conditionid = $DB->insert_record('notificationsagent_condition', $objdb);
        $this->assertIsInt($conditionid);
        self::$rule::create_instance($ruleid);

        $task = \core\task\manager::get_scheduled_task(forumnoreply_crontask::class);
        $task->set_timestarted(self::COURSE_DATESTART);
        $task->execute();

        $cache = $DB->get_record('notificationsagent_cache', ['conditionid' => $conditionid]);
        $trigger = $DB->get_record('notificationsagent_triggers', ['conditionid' => $conditionid]);

        $this->assertEquals($pluginname, $cache->pluginname);
        $this->assertEquals(self::$course->id, $cache->courseid);
        $this->assertEquals(self::$user->id, $cache->userid);
        $this->assertEquals($conditionid, $cache->conditionid);

        $this->assertEquals(self::$course->id, $trigger->courseid);
        $this->assertEquals(self::$rule->get_id(), $trigger->ruleid);
        $this->assertEquals(self::$user->id, $trigger->userid);
        $this->assertEquals($conditionid, $trigger->conditionid);
    }

    /**
     * Data provider for execute
     *
     * @return array
     */
    public static function dataprovider(): array {
        return [
            [86400],
            [86400 * 3],
        ];
    }

    /**
     * Get name test
     *
     * @covers \notificationscondition_forumnoreply\task\forumnoreply_crontask::get_name
     * @return void
     */
    public function test_get_name(): void {
        $task = \core\task\manager::get_scheduled_task(forumnoreply_crontask::class);

        $this->assertIsString($task->get_name());
    }
}
