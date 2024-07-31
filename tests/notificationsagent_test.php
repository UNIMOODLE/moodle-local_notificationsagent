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

use notificationscondition_ac\ac;
use notificationscondition_sessionend\sessionend;
use notificationscondition_sessionstart\sessionstart;

/**
 * Testing notificationsagent class
 *
 * @group notificationsagent
 */
class notificationsagent_test extends \advanced_testcase {
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
        $rule = new rule();
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
        self::getDataGenerator()->create_user_course_lastaccess(self::$user, self::$course, self::USER_LASTACCESS);

        $quizgenerator = self::getDataGenerator()->get_plugin_generator('mod_quiz');
        self::$cmtestnt = $quizgenerator->create_instance([
                'name' => 'Quiz unittest',
                'course' => self::$course->id,
                "timeopen" => self::CM_DATESTART,
                "timeclose" => self::CM_DATEEND,
        ]);

        self::getDataGenerator()->enrol_user(self::$user->id, self::$course->id);
        self::$rule = $rule;
    }

    /**
     *  * Testing notificationsagent_condition_get_cm_dates.
     *
     * @covers       \local_notificationsagent\notificationsagent::notificationsagent_condition_get_cm_dates
     * @dataProvider dataprovider
     *
     * @param int $timeopen
     * @param int $timeclose
     * @param string $modname
     * @param string $fieldopen
     * @param string $fieldclose
     *
     * @return void
     */
    public function test_notificationsagent_condition_get_cm_dates($timeopen, $timeclose, $modname, $fieldopen, $fieldclose) {
        $this->resetAfterTest();
        $course = self::getDataGenerator()->create_course();
        $manager = self::getDataGenerator()->create_and_enrol($course, 'manager');
        self::setUser($manager);

        $options = [
                'name' => 'Quiz unittest',
                'course' => $course->id,
                "{$fieldopen}" => $timeopen,
        ];

        !empty($fieldclose) ? $options[$fieldclose] = $timeclose : '';

        $cmtest = self::getDataGenerator()->create_module(
            "{$modname}",
            $options
        );

        $result = notificationsagent::notificationsagent_condition_get_cm_dates($cmtest->cmid);
        if (empty($timeopen)) {
            $this->assertEquals($course->startdate, $result->timestart);
        } else {
            $this->assertEquals($timeopen, $result->timestart);
        }

        if (!empty($fieldclose)) {
            $this->assertEquals($timeclose, $result->timeend);
        }
    }

    /**
     * Dataprovider for condition_get_cm_dates
     */
    public static function dataprovider(): array {
        return [
                'assign' => [1704099600, 1705741200, 'assign', 'allowsubmissionsfromdate', 'duedate'],
                'choice' => [1704099600, 1705741200, 'choice', 'timeopen', 'timeclose'],
                'data' => [1704099600, 1705741200, 'data', 'timeavailablefrom', 'timeavailableto'],
                'feedback' => [1704099600, 1705741200, 'feedback', 'timeopen', 'timeclose'],
                'quiz' => [1704099600, 1705741200, 'quiz', 'timeopen', 'timeclose'],
                'forum' => [1704099600, 1705741200, 'forum', 'duedate', 'cutoffdate'],
                'lesson' => [1704099600, 1705741200, 'lesson', 'available', 'deadline'],
                'scorm' => [1704099600, 1705741200, 'scorm', 'timeopen', 'timeclose'],
                'workshop' => [1704099600, 1705741200, 'workshop', 'submissionstart', 'submissionend'],
                'no datestart' => [null, 1705741200, 'workshop', 'submissionstart', 'submissionend'],
                'chattime' => [1704099600, 0, 'chat', 'chattime', null],
        ];
    }

    /**
     * Dataprovider for launched
     *
     * @return array[]
     */
    public static function launched_provider(): array {
        return [
                [3, 3, true],
                [3, 2, false],
                [3, 5, true],
        ];
    }

    /**
     * Testing get_userbycourse
     *
     * @covers \local_notificationsagent\notificationsagent::get_usersbycourse
     * @return void
     */
    public function test_get_usersbycourse() {
        $context = \context_course::instance(self::$course->id);
        $noenrolluser = self::getDataGenerator()->create_user();
        $manager = self::getDataGenerator()->create_and_enrol(self::$course, 'manager');
        $teacher = self::getDataGenerator()->create_and_enrol(self::$course, 'teacher');
        $studentsuspended = self::getDataGenerator()->create_and_enrol(self::$course, 'student', ['suspended' => 1]);

        $this->assertCount(1, notificationsagent::get_usersbycourse($context));
        $this->assertEquals(self::$user->id, notificationsagent::get_usersbycourse($context)[self::$user->id]->id);
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
            [
                "(userid =" . self::$user->id ."  AND courseid= ".self::$course->id." AND conditionid=" . self::CMID .")"
                ],
            [
                        [
                                'userid' => self::$user->id,
                                'courseid' => self::$course->id,
                                'startdate' => self::CM_DATESTART,
                                'pluginname' => 'sessionend',
                                'conditionid' => self::CMID,
                        ],
                ]
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
        $this->assertEquals(self::CM_DATESTART, $cache->startdate);

        notificationsagent::set_timer_cache(
            [
                "(userid =" . self::$user->id ."  AND courseid= ".self::$course->id." AND conditionid=" . self::CMID .")"
                ],
            [
                        [
                                'userid' => self::$user->id,
                                'courseid' => self::$course->id,
                                'startdate' => self::CM_DATESTART + 86400,
                                'pluginname' => "sessionend",
                                'conditionid' => self::CMID,
                        ],
                ]
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
        $this->assertEquals(self::CM_DATESTART + 86400, $cacheupdated->startdate);
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
            [
                "(userid =" . self::$user->id ."  AND courseid= ".self::$course->id." AND conditionid=" . self::CMID .")"
                ],
            [
                        [
                                'ruleid' => self::$rule->get_id(),
                                'userid' => self::$user->id,
                                'courseid' => self::$course->id,
                                'startdate' => self::CM_DATESTART,
                                'conditionid' => self::CMID,
                        ],
                ]
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
        $this->assertEquals(self::CM_DATESTART, $trigger->startdate);
        $this->assertEquals(self::$user->id, $trigger->userid);
        $this->assertEquals(self::$course->id, $trigger->courseid);

        notificationsagent::set_time_trigger(
            [
                "(userid =" . self::$user->id ."  AND courseid= ".self::$course->id." AND conditionid=" . self::CMID .")"
                ],
            [
                        [
                                'ruleid' => self::$rule->get_id(),
                                'userid' => self::$user->id,
                                'courseid' => self::$course->id,
                                'startdate' => self::CM_DATESTART + 1,
                                'conditionid' => self::CMID,
                        ],
                ]
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
        $this->assertEquals(self::CMID, $triggerupdated->conditionid);
        $this->assertEquals(self::CM_DATESTART + 1, $triggerupdated->startdate);
        $this->assertEquals(self::$user->id, $triggerupdated->userid);
        $this->assertEquals(self::$course->id, $triggerupdated->courseid);
    }

    /**
     * Testing get_conditions_by_course
     *
     * @covers \local_notificationsagent\notificationsagent::get_conditions_by_course
     * @return void
     */
    public function test_get_conditions_by_course() {
        global $USER, $DB;
        // Rule.
        $dataform = new \StdClass();
        $dataform->title = "Rule Test";
        $dataform->type = 1;
        $dataform->courseid = self::$course->id;
        $dataform->timesfired = 2;
        $dataform->runtime_group = ['runtime_days' => 5, 'runtime_hours' => 0, 'runtime_minutes' => 0];
        $USER->id = self::$user->id;
        $ruleid = self::$rule->create($dataform);

        $DB->insert_record(
            'notificationsagent_context',
            [
                        'ruleid' => $ruleid,
                        'contextid' => CONTEXT_COURSECAT,
                        'objectid' => self::$course->category,
                ]
        );

        $defaultcontext = self::$rule->get_default_context();

        $this->assertIsNumeric($defaultcontext);

        $context = self::$rule->has_context();
        $this->assertTrue($context);

        // Condition.
        $pluginname = sessionend::NAME;
        $objdb = new \stdClass();
        $objdb->ruleid = $ruleid;
        $objdb->courseid = self::$course->id;
        $objdb->type = 'condition';
        $objdb->pluginname = $pluginname;
        $objdb->parameters = '{"time":"86400"}';
        $objdb->cmid = self::CMID;
        // Insert.
        $DB->insert_record('notificationsagent_condition', $objdb);

        rule::create_instance($ruleid);
        $data = notificationsagent::get_conditions_by_course($pluginname, self::$course->id);

        $data = $data[key($data)];
        $this->assertEquals(self::$rule->get_id(), $data->ruleid);
        $this->assertEquals($pluginname, $data->pluginname);
        if ($data->contextid === CONTEXT_COURSE) {
            $this->assertEquals(self::$course->id, $data->objectid);
        }
        if ($data->contextid === CONTEXT_COURSECAT) {
            $this->assertEquals(self::$course->category, $data->objectid);
        }

    }

    /**
     * Testing get_conditons_by_cm
     *
     * @return void
     * @throws \dml_exception
     * @covers \local_notificationsagent\notificationsagent::get_conditions_by_cm
     */
    public function test_get_conditions_by_cm() {
        global $DB, $USER;
        $dataform = new \StdClass();
        $dataform->title = "Rule Test";
        $dataform->type = 1;
        $dataform->courseid = self::$course->id;
        $dataform->timesfired = 2;
        $dataform->runtime_group = ['runtime_days' => 5, 'runtime_hours' => 0, 'runtime_minutes' => 0];
        $USER->id = self::$user->id;
        $ruleid = self::$rule->create($dataform);

        // Condition.
        $pluginname = sessionend::NAME;
        $objdb = new \stdClass();
        $objdb->ruleid = $ruleid;
        $objdb->courseid = self::$course->id;
        $objdb->type = 'condition';
        $objdb->pluginname = $pluginname;
        $objdb->parameters = '{"time":"86400"}';
        $objdb->cmid = self::CMID;
        // Insert.
        $conditionid = $DB->insert_record('notificationsagent_condition', $objdb);

        $instance = rule::create_instance($ruleid);
        $context = self::$rule->get_default_context();
        $this->assertNotEmpty($context);

        $conditions = notificationsagent::get_conditions_by_cm($pluginname, self::$course->id, self::CMID);
        $conditions = $conditions[key($conditions)];

        $this->assertEquals($instance->get_id(), $conditions->ruleid);
        $this->assertEquals($pluginname, $conditions->pluginname);
        $this->assertEquals(self::$course->id, $conditions->objectid);
        $this->assertEquals($instance->get_conditions($pluginname)[$conditionid]->get_parameters(), $conditions->parameters);
    }

    /**
     * Testing get conditions by plugin
     *
     * @return void
     * @throws \dml_exception
     * @covers \local_notificationsagent\notificationsagent::get_conditions_by_plugin
     */
    public function test_get_conditions_by_plugin() {
        global $DB, $USER;
        $dataform = new \StdClass();
        $dataform->title = "Rule Test";
        $dataform->type = 1;
        $dataform->courseid = self::$course->id;
        $dataform->timesfired = 2;
        $dataform->runtime_group = ['runtime_days' => 5, 'runtime_hours' => 0, 'runtime_minutes' => 0];
        $USER->id = self::$user->id;
        $ruleid = self::$rule->create($dataform);

        $DB->insert_record(
            'notificationsagent_context',
            [
                        'ruleid' => $ruleid,
                        'contextid' => CONTEXT_COURSECAT,
                        'objectid' => self::$course->category,
                ]
        );

        // Condition.
        $pluginname = sessionstart::NAME;
        $objdb = new \stdClass();
        $objdb->ruleid = $ruleid;
        $objdb->courseid = self::$course->id;
        $objdb->type = 'condition';
        $objdb->pluginname = $pluginname;
        $objdb->parameters = '{"time":"86400"}';
        $objdb->cmid = self::CMID;
        // Insert.
        $conditionid = $DB->insert_record('notificationsagent_condition', $objdb);

        $instance = rule::create_instance($ruleid);
        $context = self::$rule->get_default_context();
        $this->assertNotEmpty($context);

        $result = notificationsagent::get_conditions_by_plugin($pluginname);
        $result = $result[key($result)];

        $this->assertEquals($instance->get_id(), $result->ruleid);
        $this->assertTrue(in_array(self::$course->id, $result->courses));
        $this->assertEquals($instance->get_conditions($pluginname)[$conditionid]->get_parameters(), $result->parameters);
    }

    /**
     * Testing get availability conditions
     *
     * @return void
     * @covers \local_notificationsagent\notificationsagent::get_availability_conditions
     */
    public function test_get_availability_conditions() {
        global $USER, $DB;
        $dataform = new \StdClass();
        $dataform->title = "Rule Test";
        $dataform->type = 1;
        $dataform->courseid = self::$course->id;
        $dataform->timesfired = 2;
        $dataform->runtime_group = ['runtime_days' => 5, 'runtime_hours' => 0, 'runtime_minutes' => 0];
        $USER->id = self::$user->id;
        $ruleid = self::$rule->create($dataform);

        $DB->insert_record(
            'notificationsagent_context',
            [
                        'ruleid' => $ruleid,
                        'contextid' => CONTEXT_COURSECAT,
                        'objectid' => self::$course->category,
                ]
        );
        $pluginname = ac::NAME;
        $objdb = new \stdClass();
        $objdb->ruleid = $ruleid;
        $objdb->courseid = self::$course->id;
        $objdb->type = 'condition';
        $objdb->pluginname = $pluginname;
        $objdb->parameters = '{"op":"&","c":[{"type":"profile","sf":"firstname","op":"isequalto","v":"Fernando"}],"showc":[true]}';
        $objdb->cmid = self::CMID;
        // Insert.
        $conditionid = $DB->insert_record('notificationsagent_condition', $objdb);
        $this->assertIsNumeric($conditionid);
        $defaultcontext = self::$rule->get_default_context();
        $this->assertIsNumeric($defaultcontext);

        $rule = rule::create_instance(self::$rule->get_id());

        $data = notificationsagent::get_availability_conditions();
        $data = $data[key($data)];

        $this->assertEquals($rule->get_id(), $data->ruleid);
        $this->assertTrue(in_array(self::$course->id, $data->courses));
    }

    /**
     * Testing get_course_category_context_byruleid
     *
     * @return void
     * @covers \local_notificationsagent\notificationsagent::get_course_category_context_byruleid
     */
    public function test_get_course_category_context_byruleid() {
        global $DB, $USER;
        $dataform = new \StdClass();
        $dataform->title = "Rule Test";
        $dataform->type = 1;
        $dataform->courseid = self::$course->id;
        $dataform->timesfired = 2;
        $dataform->runtime_group = ['runtime_days' => 5, 'runtime_hours' => 0, 'runtime_minutes' => 0];
        $USER->id = self::$user->id;
        $ruleid = self::$rule->create($dataform);

        $DB->insert_record(
            'notificationsagent_context',
            [
                        'ruleid' => $ruleid,
                        'contextid' => CONTEXT_COURSECAT,
                        'objectid' => self::$course->category,
                ]
        );
        $data = notificationsagent::get_course_category_context_byruleid($ruleid);
        $data = $data[key($data)];
        $this->assertEquals(self::$course->id, $data);
    }

    /**
     * Testing is rule off
     *
     * @covers       \local_notificationsagent\notificationsagent::is_ruleoff
     * @dataProvider ruleoffprovider
     *
     * @param int|null $ruleoff
     * @param bool $expected
     *
     * @return void
     * @throws \dml_exception
     */
    public function test_is_ruleoff($ruleoff, $expected) {
        global $DB;
        $ruleid = self::$rule->get_id();
        $courseid = self::$course->id;
        $pluginname = sessionstart::NAME;
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
        $objdbtrigger->ruleoff = $ruleoff;

        // Insert.
        $cacheid = $DB->insert_record('notificationsagent_triggers', $objdbtrigger);
        $this->assertIsNumeric($cacheid);

        $this->assertSame($expected, notificationsagent::is_ruleoff(self::$rule->get_id(), self::$user->id, $courseid));
    }

    /**
     * Provider for ruleoff
     *
     * @return array[]
     */
    public static function ruleoffprovider(): array {
        return [
                [1704099600, true],
                [null, false],
        ];
    }

    /**
     * Testing get triggers bytimeinterval
     *
     * @param int $date
     * @param int $timestarted
     * @param int $tasklastrunttime
     * @param bool $emptyresult
     *
     * @return void
     * @dataProvider datatriggers
     * @covers       \local_notificationsagent\notificationsagent::get_triggersbytimeinterval
     */
    public function test_get_triggersbytimeinterval($date, $timestarted, $tasklastrunttime, $emptyresult) {
        global $USER, $DB;
        // Rule.
        $dataform = new \StdClass();
        $dataform->title = "Rule Test";
        $dataform->type = 1;
        $dataform->courseid = self::$course->id;
        $dataform->timesfired = 2;
        $dataform->runtime_group = ['runtime_days' => 5, 'runtime_hours' => 0, 'runtime_minutes' => 0];
        $USER->id = self::$user->id;
        $ruleid = self::$rule->create($dataform);
        $this->assertIsNumeric($ruleid);

        $objdbtrigger = new \stdClass();
        $objdbtrigger->ruleid = self::$rule->get_id();
        $objdbtrigger->conditionid = 24900;
        $objdbtrigger->courseid = self::$course->id;
        $objdbtrigger->userid = self::$user->id;
        $objdbtrigger->startdate = $date;

        // Insert.
        $trigger = $DB->insert_record('notificationsagent_triggers', $objdbtrigger);
        $this->assertIsNumeric($trigger);

        $triggers = notificationsagent::get_triggersbytimeinterval($timestarted, $tasklastrunttime);
        if ($emptyresult) {
            $this->assertEmpty($triggers);
        } else {
            $triggers = $triggers[key($triggers)];
            $this->assertEquals($ruleid, $triggers->ruleid);
            $this->assertEquals(24900, $triggers->conditionid);
            $this->assertEquals(self::$course->id, $triggers->courseid);
            $this->assertEquals(self::$user->id, $triggers->userid);
        }
    }

    /**
     * Data provider for triggerbyinterval
     *
     * @return array[]
     */
    public static function datatriggers(): array {
        return [
                'On dates' => [1704099600, 1704099600 + 60, 1704099600 - 60, false],
                'Out of date' => [1704099600 - 84600, 1704099600 + 60, 1704099600 - 60, true],
        ];
    }

    /**
     * Test the supported_cm function of the notificationsagent class.
     *
     * This function tests the supported_cm function of the notificationsagent class by creating a course, enrolling a manager,
     * creating a module, and checking if the returned result matches the expected result.
     *
     * @param mixed $expected The expected result of the supported_cm function.
     * @param string $modname The name of the module to be created.
     *
     * @covers       \local_notificationsagent\notificationsagent::supported_cm
     * @dataProvider datasupported
     */
    public function test_supportedcm($expected, string $modname) {
        $this->resetAfterTest();
        $course = self::getDataGenerator()->create_course();
        $manager = self::getDataGenerator()->create_and_enrol($course, 'manager');
        self::setUser($manager);

        $options = [
                'name' => 'Quiz unittest',
                'course' => $course->id,
        ];

        $cmtest = self::getDataGenerator()->create_module(
            "{$modname}",
            $options
        );

        $result = notificationsagent::supported_cm($cmtest->cmid, $course->id);

        $this->assertEquals($expected, $result);
    }

    /**
     * Dataprovider for condition_get_cm_dates
     */
    public static function datasupported(): array {
        return [
                'assign' => [true, 'assign'],
                'choice' => [true, 'choice'],
                'data' => [true, 'data'],
                'feedback' => [true, 'feedback'],
                'quiz' => [true, 'quiz'],
                'forum' => [true, 'forum'],
                'lesson' => [true, 'lesson'],
                'scorm' => [true, 'scorm'],
                'workshop' => [true, 'workshop'],
                'no datestart' => [true, 'workshop'],
                'chattime' => [true, 'chat'],
                'no supported cm' => [false, 'book'],
        ];
    }

    /**
     * Test  bulk_delete_conditions_by_userid
     *
     * @return void
     * @covers \local_notificationsagent\notificationsagent::bulk_delete_conditions_by_userid
     */
    public function test_bulk_delete_conditions_by_userid() {
        global $DB;
        $objdbtrigger = new \stdClass();
        $objdbtrigger->ruleid = self::$rule->get_id();
        $objdbtrigger->conditionid = 24900;
        $objdbtrigger->courseid = self::$course->id;
        $objdbtrigger->userid = self::$user->id;
        $objdbtrigger->startdate = self::COURSE_DATESTART;
        $inserttrigger = $DB->insert_record('notificationsagent_triggers', $objdbtrigger);
        $this->assertIsNumeric($inserttrigger);

        $objdb = new \stdClass();
        $objdb->userid = self::$user->id;
        $objdb->courseid = self::$course->id;
        $objdb->startdate = time();
        $objdb->pluginname = 'sessionend';
        $objdb->conditionid = 1;
        $insertcache = $DB->insert_record('notificationsagent_cache', $objdb);
        $this->assertIsNumeric($insertcache);

        notificationsagent::bulk_delete_conditions_by_userid([$insertcache, $inserttrigger], self::$user->id);

        $cache = $DB->get_record('notificationsagent_cache', ['conditionid' => $insertcache]);
        $trigger = $DB->get_record('notificationsagent_triggers', ['conditionid' => $inserttrigger]);

        $this->assertEmpty($trigger);
        $this->assertEmpty($cache);
    }

    /**
     * Test Evaluate expression
     *
     * @param string $operator
     * @param int $a
     * @param int $b
     * @param bool $expected
     * @covers       \local_notificationsagent\notificationsagent::evaluate_expression
     * @dataProvider dataexpresion
     * @return void
     */
    public function test_evaluate_expression($operator, $a, $b, $expected) {
        $result = notificationsagent::evaluate_expression($operator, $a, $b);
        $this->assertSame($expected, $result);
    }

    /**
     * Data provider for evaluate expression
     *
     * @return array[]
     */
    public static function dataexpresion(): array {
        return [
                ['=', 3, 2, false],
                ['!=', 3, 2, true],
                ['>', 3, 2, true],
                ['<', 3, 2, false],
                ['>=', 3, 3, true],
                ['<=', 3, 3, true],
                ['', 3, 3, false],
        ];
    }
}
