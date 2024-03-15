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
// Project implemented by the \"Recovery, Transformation and Resilience Plan.
// Funded by the European Union - Next GenerationEU\".
//
// Produced by the UNIMOODLE University Group: Universities of
// Valladolid, Complutense de Madrid, UPV/EHU, León, Salamanca,
// Illes Balears, Valencia, Rey Juan Carlos, La Laguna, Zaragoza, Málaga,
// Córdoba, Extremadura, Vigo, Las Palmas de Gran Canaria y Burgos.

/**
 * Version details
 *
 * @package    notificationscondition_activitymodified
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace notificationscondition_activitymodified;

use local_notificationsagent\rule;
use local_notificationsagent\notificationsagent;

/**
 * @group notificationsagent
 */
class activitymodified_observer_test extends \advanced_testcase {
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
    private static $activity;
    /**
     * Date start for the course
     */
    public const COURSE_DATESTART = 1704099600; // 01/01/2024 10:00:00.
    /**
     * Date end for the course
     */
    public const COURSE_DATEEND = 1706605200; // 30/01/2024 10:00:00,

    /**
     * Set up the function and perform necessary initialization steps.
     */
    final public function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
        $rule = new rule();
        self::$rule = $rule;
        self::$course = self::getDataGenerator()->create_course(
            ([
                'startdate' => self::COURSE_DATESTART,
                'enddate' => self::COURSE_DATEEND,
            ])
        );
        self::$activity = self::getDataGenerator()->create_module('assign', ['course' => self::$course->id]);
    }

    /**
     * Check if the user has added new content to the activity.
     *
     * @param string $role           Role name
     * @param int    $fileuploadtime File uploaded time
     *
     * @covers       \notificationscondition_activitymodified_observer::course_module_updated
     * @dataProvider dataprovider
     */
    public function test_execute($role, $fileuploadtime) {
        global $DB, $USER;

        if (!is_null($fileuploadtime)) {
            \uopz_set_return('time', $fileuploadtime);
        }

        $pluginname = 'activitymodified';

        self::$user = self::getDataGenerator()->create_and_enrol(self::$course, $role);
        self::setUser(self::$user->id);

        $hasmanagefiles = has_capability('moodle/course:managefiles', \context_course::instance(self::$course->id, self::$user));

        $assigncontext = \context_module::instance(self::$activity->cmid);

        $dataform = new \StdClass();
        $dataform->title = "Rule Test";
        $dataform->type = 1;
        $dataform->courseid = self::$course->id;
        $dataform->timesfired = 2;
        $dataform->runtime_group = ['runtime_days' => 5, 'runtime_hours' => 0, 'runtime_minutes' => 0];
        $USER->id = self::$user->id;
        $ruleid = self::$rule->create($dataform);
        self::$rule->set_id($ruleid);

        $objparameters = new \stdClass();
        $objparameters->cmid = self::$activity->cmid;

        $objcondition = new \stdClass();
        $objcondition->ruleid = self::$rule->get_id();
        $objcondition->courseid = self::$course->id;
        $objcondition->type = 'condition';
        $objcondition->pluginname = $pluginname;
        $objcondition->parameters = json_encode($objparameters);
        $objcondition->cmid = self::$activity->cmid;

        $conditionid = $DB->insert_record('notificationsagent_condition', $objcondition);
        $this->assertIsInt($conditionid);
        self::$rule::create_instance($ruleid);

        if (!is_null($fileuploadtime)) {
            $fs = get_file_storage();
            $filerecord = [
                'contextid' => $assigncontext->id,
                'component' => 'mod_assign',
                'filearea' => 'content',
                'itemid' => 0,
                'filepath' => '/',
                'filename' => 'user-test-file.txt',
                'userid' => self::$user->id,
                'timecreated' => $fileuploadtime + 60,
                'timemodified' => $fileuploadtime + 60,
            ];

            $fs->create_file_from_string($filerecord, 'User upload');
        }

        $event = \core\event\course_module_updated::create([
            'context' => \context_course::instance(self::$course->id),
            'userid' => self::$user->id,
            'courseid' => self::$course->id,
            'objectid' => self::$activity->cmid,
            'other' => [
                'modulename' => 'assign',
                'instanceid' => self::$activity->cmid,
                'name' => self::$activity->name,
            ],
        ]);
        $event->trigger();

        $trigger = $DB->get_record('notificationsagent_triggers', ['conditionid' => $conditionid]);
        $cache = $DB->get_record('notificationsagent_cache', ['conditionid' => $conditionid]);

        if (!is_null($fileuploadtime) && $hasmanagefiles) {
            $this->assertEquals(self::$course->id, $trigger->courseid);
            $this->assertEquals(self::$rule->get_id(), $trigger->ruleid);
            $this->assertEquals(notificationsagent::GENERIC_USERID, $trigger->userid);

            $this->assertEquals($conditionid, $cache->conditionid);
            $this->assertEquals($pluginname, $cache->pluginname);
            $this->assertEquals(self::$course->id, $cache->courseid);
            $this->assertEquals(notificationsagent::GENERIC_USERID, $cache->userid);
        } else {
            $this->assertFalse($trigger);
            $this->assertFalse($cache);
        }

        if (!is_null($fileuploadtime)) {
            uopz_unset_return('time');
        }
    }

    /**
     * Set up the data to be used in the test execution.
     *
     * @return array
     */
    public static function dataprovider(): array {
        return [
            'Testing a file that was not uploaded' => ['editingteacher', null],
            'Testing a file that was uploaded by a student 1 minute ago' => ['student', 1709727168],
            'Testing a file that was uploaded by a teacher 1 minute ago' => ['editingteacher', 1712142763],
        ];
    }
}
