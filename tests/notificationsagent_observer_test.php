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
 * @package    local_notificationsagent
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_notificationsagent;

use core\event\course_deleted;
use local_notificationsagent\rule;
use notificationscondition_ac\ac;
use notificationscondition_sessionend\sessionend;
use notificationscondition_sessionstart\sessionstart;

/**
 * Testing notificationsagent class
 *
 * @group notificationsagent
 */
class notificationsagent_observer_test extends \advanced_testcase {
    /**
     * @var \local_notificationsagent\rule
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
     * @var \stdClass
     */
    private static $cmtestnt;
    /**
     * Date start for the course
     */
    public const COURSE_DATESTART = 1704099600; // 01/01/2024 10:00:00.
    /**
     * Date end for the course
     */
    public const COURSE_DATEEND = 1706605200; // 30/01/2024 10:00:00-
    /**
     * Activity date start
     */
    public const CM_DATESTART = 1704099600; // 01/01/2024 10:00:00.
    /**
     * Activity date end
     */
    public const CM_DATEEND = 1705741200; // 20/01/2024 10:00:00.
    /**
     * User first access to a course
     */
    public const USER_FIRSTACCESS = 1704099600;
    /**
     * User last access to a course
     */
    public const USER_LASTACCESS = 1704099600;
    /**
     *  Random id for activity
     */
    public const CMID = 246000;

    final public function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
        $rule = new \local_notificationsagent\rule();
        $rule->set_name("Rule Test");
        $rule->set_id(246000);
        $rule->set_runtime(['runtime_days' => 5, 'runtime_hours' => 0, 'runtime_minutes' => 0]);
        $rule->set_status(0);
        $rule->set_template(1);

        self::$user = self::getDataGenerator()->create_user();
        self::$course = self::getDataGenerator()->create_course(
            ([
                        'startdate' => self::COURSE_DATESTART,
                        'enddate' => self::COURSE_DATEEND,
                ])
        );
    }

    /**
     *  Test for deleted courses
     *
     * @covers \local_notificationsagent\notificationsagent::delete_all_by_course()
     * @covers \local_notificationsagent_observer::course_deleted
     *
     * @return void
     */
    public function test_course_deleted() {
        global $DB, $USER;

        $rule = new rule();
        $rule->set_name("Rule Test");
        $rule->set_id(246000);
        $rule->set_runtime(['runtime_days' => 5, 'runtime_hours' => 0, 'runtime_minutes' => 0]);
        $rule->set_status(0);
        $rule->set_template(1);

        $dataform = new \StdClass();
        $dataform->title = "Rule Test";
        $dataform->type = 1;
        $dataform->courseid = self::$course->id;
        $dataform->timesfired = 2;
        $dataform->runtime_group = ['runtime_days' => 5, 'runtime_hours' => 0, 'runtime_minutes' => 0];
        $USER->id = self::$user->id;
        $ruleid = $rule->create($dataform);
        $this->assertIsInt($ruleid);

        $report = new \stdClass();
        $report->ruleid = 1;
        $report->userid = self::$user->id;
        $report->courseid = self::$course->id;
        $report->actionid = 11;
        $report->actiondetail = '{"title":"Hello there!","message":""General Kenobi..."}';
        $report->timestamp = time();

        $reportid = $DB->insert_record('notificationsagent_report', $report);
        $this->assertIsNumeric($reportid);

        $objdbtrigger = new \stdClass();
        $objdbtrigger->ruleid = 1;
        $objdbtrigger->conditionid = 1;
        $objdbtrigger->courseid = self::$course->id;
        ;
        $objdbtrigger->userid = self::$user->id;
        $objdbtrigger->startdate = time();
        $objdbtrigger->ruleoff = time();

        // Insert.
        $cacheid = $DB->insert_record('notificationsagent_triggers', $objdbtrigger);
        $this->assertIsNumeric($cacheid);
        self::setAdminUser();
        $event = \core\event\course_deleted::create([
                'context' => \context_course::instance(self::$course->id),
                'userid' => 2,
                'courseid' => self::$course->id,
                'objectid' => self::$course->id,
                'other' => ['fullname' => self::$course->fullname],
        ]);
        $event->trigger();

        $deletereport = $DB->get_records('notificationsagent_report');
        $deletetrigger = $DB->get_records('notificationsagent_triggers');

        $this->assertEmpty($deletereport);
        $this->assertEmpty($deletetrigger);
    }
}
