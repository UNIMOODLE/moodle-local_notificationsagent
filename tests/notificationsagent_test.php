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
 * @package    local_notificationsagent
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_notificationsagent;

defined('MOODLE_INTERNAL') || die();

/**
 * @group notificationsagent
 */
class notificationsagent_test extends \advanced_testcase {

    private static $rule;
    private static $user;
    private static $course;
    private static $cmtestnt;
    public const COURSE_DATESTART = 1704099600; // 01/01/2024 10:00:00.
    public const COURSE_DATEEND = 1706605200; // 30/01/2024 10:00:00,
    public const CM_DATESTART = 1704099600; // 01/01/2024 10:00:00,
    public const CM_DATEEND = 1705741200; // 20/01/2024 10:00:00,
    public const USER_FIRSTACCESS = 1704099600;
    public const USER_LASTACCESS = 1704099600;
    public const CMID = 246000;

    final public function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
        $rule = new rule();
        $rule->set_name("Rule Test");
        $rule->set_id(246000);
        $rule->set_runtime(['runtime_days' => 5, 'runtime_hours' => 0, 'runtime_minutes' => 0]);
        self::$rule = $rule;
        self::$user = self::getDataGenerator()->create_user();
        self::$course = self::getDataGenerator()->create_course(
            ([
                'startdate' => self::COURSE_DATESTART,
                'enddate' => self::COURSE_DATEEND,
            ])
        );
        self::getDataGenerator()->create_user_course_lastaccess(self::$user, self::$course, self::USER_LASTACCESS);

        $quizgenerator = self::getDataGenerator()->get_plugin_generator('mod_quiz');
        self::$cmtestnt = $quizgenerator->create_instance([
            'name' => 'Quiz unittest',
            'course' => self::$course->id,
            "timeopen" => self::CM_DATESTART,
            "timeclose" => self::CM_DATEEND,
        ]);

        self::getDataGenerator()->enrol_user(self::$user->id, self::$course->id);

    }

    /**
     * Testing notificationsagent_condition_get_cm_dates.
     *
     * @covers       \local_notificationsagent\notificationsagent::notificationsagent_condition_get_cm_dates
     * @dataProvider dataprovider
     */
    public function test_notificationsagent_condition_get_cm_dates($timeopen, $timeclose, $modname, $fieldopen, $fieldclose) {
        $this->resetAfterTest();
        $course = self::getDataGenerator()->create_course();
        $manager = self::getDataGenerator()->create_and_enrol($course, 'manager');
        self::setUser($manager);
        $cmtest = self::getDataGenerator()->create_module(
            "{$modname}", [
            'name' => 'Quiz unittest',
            'course' => $course->id,
            "{$fieldopen}" => $timeopen,
            "{$fieldclose}" => $timeclose,
        ],
        );

        $result = notificationsagent::notificationsagent_condition_get_cm_dates($cmtest->cmid);

        $this->assertEquals($timeopen, $result->timestart);
        $this->assertEquals($timeclose, $result->timeend);
    }

    public static function dataprovider(): array {
        return [
            [1704099600, 1705741200, 'assign', 'allowsubmissionsfromdate', 'duedate'],
            [1704099600, 1705741200, 'choice', 'timeopen', 'timeclose'],
            [1704099600, 1705741200, 'data', 'timeavailablefrom', 'timeavailableto'],
            [1704099600, 1705741200, 'feedback', 'timeopen', 'timeclose'],
            [1704099600, 1705741200, 'quiz', 'timeopen', 'timeclose'],
            [1704099600, 1705741200, 'forum', 'duedate', 'cutoffdate'],
            [1704099600, 1705741200, 'lesson', 'available', 'deadline'],
            [1704099600, 1705741200, 'scorm', 'timeopen', 'timeclose'],
            [1704099600, 1705741200, 'workshop', 'submissionstart', 'submissionend'],
        ];
    }

    /**
     * Testing was_launched_indicated_times.
     *
     * @return void
     * @throws \dml_exception
     * @dataProvider launched_provider
     */
    public function test_was_launched_indicated_times($timesfired, $firedtimes, $expected) {
        global $DB;
        self::$rule->set_timesfired($timesfired);

        $DB->insert_record('notificationsagent_launched', [
            'ruleid' => self::$rule->get_id(),
            'courseid' => self::$course->id,
            'userid' => self::$user->id,
            'timesfired' => $firedtimes,
            'timecreated' => 1708604296,
            'timemodified' => 1708604296,
        ]);

        $result = notificationsagent::was_launched_indicated_times(
            self::$rule->get_id(), self::$rule->get_timesfired(), self::$course->id, self::$user->id
        );

        $this->assertSame($expected, $result);

    }

    public static function launched_provider(): array {
        return [
            [3, 3, true],
            [3, 2, false],
            [3, 5, true],
        ];
    }

    public function test_get_usersbycourse() {
        $context = \context_course::instance(self::$course->id);
        $noenrolluser = self::getDataGenerator()->create_user();
        $manager = self::getDataGenerator()->create_and_enrol(self::$course, 'manager');
        $teacher = self::getDataGenerator()->create_and_enrol(self::$course, 'teacher');
        $studentsuspended = self::getDataGenerator()->create_and_enrol(self::$course, 'student', ['suspended' => 1]);
        //$studentnotactive = self::getDataGenerator()->create_and_enrol(self::$course, 'student', ['status' => 1]);

        $this->assertCount(1, notificationsagent::get_usersbycourse($context));
        $this->assertEquals(self::$user->id, notificationsagent::get_usersbycourse($context)[self::$user->id]->id);

    }

    /**
     *  Testing delete cache by rule
     *
     * @return void
     * @throws \dml_exception
     * @covers \local_notificationsagent\notificationsagent::delete_cache_by_ruleid
     */
    public function test_delete_cache_by_rule() {
        global $DB;
        $ruleid = self::$rule->get_id();
        $courseid = self::$course->id;
        $pluginname = 'sessionstart';
        $objdb = new \stdClass();
        $objdb->ruleid = $ruleid;
        $objdb->courseid = $courseid;
        $objdb->type = 'condition';
        $objdb->pluginname = $pluginname;
        $objdb->parameters = '{"time":84600}';
        $objdb->cmid = self::CMID;
        // Insert.
        $conditionid = $DB->insert_record('notificationsagent_condition', $objdb);
        $this->assertIsNumeric($conditionid);

        $objdbcache = new \stdClass();
        $objdbcache->conditionid = $conditionid;
        $objdbcache->courseid = $courseid;
        $objdbcache->pluginname = $pluginname;
        $objdbcache->userid = self::$user->id;
        $objdbcache->timestart = time();
        // Insert.
        $cacheid = $DB->insert_record('notificationsagent_cache', $objdbcache);
        $this->assertIsNumeric($cacheid);

        notificationsagent::delete_cache_by_ruleid(self::$rule->get_id());

        $deleted = $DB->get_record('notificationsagent_cache', ['conditionid' => $conditionid]);

        $this->assertFalse($deleted);

    }

    /**
     * Testing delete triggers by ruleid
     *
     * @return void
     * @throws \dml_exception
     * @covers \local_notificationsagent\notificationsagent::delete_triggers_by_ruleid
     */
    public function test_delete_triggers_by_ruleid() {
        global $DB;
        $ruleid = self::$rule->get_id();
        $courseid = self::$course->id;
        $pluginname = 'sessionstart';
        $objdb = new \stdClass();
        $objdb->ruleid = $ruleid;
        $objdb->courseid = $courseid;
        $objdb->type = 'condition';
        $objdb->pluginname = $pluginname;
        $objdb->parameters = '{"time":84600}';
        $objdb->cmid = self::CMID;
        // Insert.
        $conditionid = $DB->insert_record('notificationsagent_condition', $objdb);
        $this->assertIsNumeric($conditionid);

        $objdbtrigger = new \stdClass();
        $objdbtrigger->ruleid = self::$rule->get_id();
        $objdbtrigger->conditionid = $conditionid;
        $objdbtrigger->courseid = $courseid;
        $objdbtrigger->userid = self::$user->id;
        $objdbtrigger->startdate = time();
        // Insert.
        $cacheid = $DB->insert_record('notificationsagent_triggers', $objdbtrigger);
        $this->assertIsNumeric($cacheid);

        notificationsagent::delete_triggers_by_ruleid(self::$rule->get_id());

        $deleted = $DB->get_record('notificationsagent_triggers', ['conditionid' => $conditionid]);

        $this->assertFalse($deleted);

    }

    /**
     * Testing set timer cache
     *
     * @return void
     * @throws \dml_exception
     * @covers \local_notificationsagent\notificationsagent::set_timer_cache
     */
    public function test_set_timer_cache() {
        global $DB;

        notificationsagent::set_timer_cache(
            self::$user->id,
            self::$course->id,
            self::CM_DATESTART,
            'sessionend',
            self::CMID,
            true
        );

        $cache = $DB->get_record(
            'notificationsagent_cache',
            [
                'conditionid' => self::CMID,
                'userid' => self::$user->id,
                'courseid' => self::$course->id,
            ]
        );
        $this->assertIsNumeric($cache->id);
        $this->assertEquals(self::$user->id, $cache->userid);
        $this->assertEquals(self::$course->id, $cache->courseid);
        $this->assertEquals(self::CM_DATESTART, $cache->timestart);

        notificationsagent::set_timer_cache(
            self::$user->id,
            self::$course->id,
            self::CM_DATESTART + 86400,
            'sessionend',
            self::CMID,
            true
        );

        $cacheupdated = $DB->get_record(
            'notificationsagent_cache',
            [
                'conditionid' => self::CMID,
                'userid' => self::$user->id,
                'courseid' => self::$course->id,
            ]
        );
        $this->assertIsNumeric($cacheupdated->id);
        $this->assertEquals(self::$user->id, $cacheupdated->userid);
        $this->assertEquals(self::$course->id, $cacheupdated->courseid);
        $this->assertEquals(self::CM_DATESTART + 86400, $cacheupdated->timestart);

        $result = notificationsagent::set_timer_cache(
            self::$user->id,
            self::$course->id,
            self::CM_DATESTART + 86400 * 2,
            'sessionend',
            self::CMID,
            false
        );

        $this->assertNull($result);
        $cachenoupdated = $DB->get_record('notificationsagent_cache', ['conditionid' => self::CMID]);
        $this->assertIsNumeric($cachenoupdated->id);
        $this->assertEquals(self::$user->id, $cachenoupdated->userid);
        $this->assertEquals(self::$course->id, $cachenoupdated->courseid);
        $this->assertEquals(self::CM_DATESTART + 86400, $cachenoupdated->timestart);

    }

    /**
     * Testing set timer triggers
     *
     * @return void
     * @throws \dml_exception
     * @covers \local_notificationsagent\notificationsagent::set_time_trigger
     */
    public function test_set_time_trigger() {
        global $DB;

        notificationsagent::set_time_trigger(
            self::$rule->get_id(),
            self::CMID,
            self::$user->id,
            self::$course->id,
            self::CM_DATESTART
        );

        $trigger = $DB->get_record(
            'notificationsagent_triggers',
            [
                'ruleid' => self::$rule->get_id(),
                'userid' => self::$user->id,
                'courseid' => self::$course->id,
            ]
        );
        $this->assertIsNumeric($trigger->id);
        $this->assertEquals(self::$rule->get_id(), $trigger->ruleid);
        $this->assertEquals(self::CMID, $trigger->conditionid);
        $this->assertEquals(self::$user->id, $trigger->userid);
        $this->assertEquals(self::$course->id, $trigger->courseid);

        notificationsagent::set_time_trigger(
            self::$rule->get_id(),
            self::CMID + 1,
            self::$user->id,
            self::$course->id,
            self::CM_DATEEND
        );

        $triggerupdated = $DB->get_record(
            'notificationsagent_triggers',
            [
                'ruleid' => self::$rule->get_id(),
                'userid' => self::$user->id,
                'courseid' => self::$course->id,
            ]
        );
        $this->assertIsNumeric($triggerupdated->id);
        $this->assertEquals(self::$rule->get_id(), $triggerupdated->ruleid);
        $this->assertEquals(self::CMID + 1, $triggerupdated->conditionid);
        $this->assertEquals(self::$user->id, $triggerupdated->userid);
        $this->assertEquals(self::$course->id, $triggerupdated->courseid);

    }

}

