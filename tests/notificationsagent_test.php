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

use core\task\manager;
use local_notificationsagent\evaluationcontext;
use local_notificationsagent\form\editrule_form;
use local_notificationsagent\notificationplugin;
use local_notificationsagent\task\rebuild_rule_triggers_task;
use notificationscondition_ac\ac;
use notificationscondition_coursestart\coursestart;
use notificationscondition_enrolend\enrolend;
use notificationscondition_ondates\ondates;
use notificationscondition_sessionend\sessionend;
use notificationscondition_sessionstart\sessionstart;
use notificationscondition_usergroupadd\usergroupadd;

/**
 * Testing notificationsagent class
 *
 * @group notificationsagent
 */
final class notificationsagent_test extends \advanced_testcase {
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

    public function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
        $this->reset_course_visible_for_rules_cache();
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
     * Reset static visibility cache between tests.
     *
     * @return void
     */
    private function reset_course_visible_for_rules_cache(): void {
        $reflection = new \ReflectionClass(notificationsagent::class);
        $property = $reflection->getProperty('coursevisibleforrulescache');
        $property->setAccessible(true);
        $property->setValue(null, []);
    }

    /**
     * Reset static rule genericity cache between tests.
     *
     * @return void
     */
    private function reset_isgeneric_cache(): void {
        rule::reset_isgeneric_cache();
    }

    /**
     * Create a rule record for trigger generation tests.
     *
     * @param int $courseid Course identifier
     * @return int Rule identifier
     */
    private function create_rule_for_trigger_tests(int $courseid): int {
        global $USER;

        $this->setAdminUser();
        $dataform = new \stdClass();
        $dataform->title = 'Trigger test rule';
        $dataform->type = rule::RULE_TYPE;
        $dataform->courseid = $courseid;
        $dataform->timesfired = 2;
        $dataform->runtime_group = ['runtime_days' => 2, 'runtime_hours' => 0, 'runtime_minutes' => 0];
        $USER->id = get_admin()->id;

        return (new rule())->create($dataform);
    }

    /**
     * Return queued rebuild adhoc tasks for a rule.
     *
     * @param int $ruleid Rule identifier
     * @return rebuild_rule_triggers_task[]
     */
    private function get_rebuild_tasks_for_rule(int $ruleid): array {
        $tasks = manager::get_adhoc_tasks(rebuild_rule_triggers_task::class);

        return array_values(array_filter(
            $tasks,
            function (rebuild_rule_triggers_task $task) use ($ruleid): bool {
                $data = $task->get_custom_data();

                return (int) $data->ruleid === $ruleid;
            }
        ));
    }

    /**
     * Build save_form data for a coursestart (generic) rule.
     *
     * @param int $courseid Course identifier
     * @return \stdClass
     */
    private function build_coursestart_save_form_data(int $courseid): \stdClass {
        $dataform = new \stdClass();
        $dataform->title = 'Generic coursestart rule';
        $dataform->type = rule::RULE_TYPE;
        $dataform->courseid = $courseid;
        $dataform->timesfired = 2;
        $dataform->runtime_group = ['runtime_days' => 2, 'runtime_hours' => 0, 'runtime_minutes' => 0];
        $dataform->{editrule_form::FORM_JSON_CONDITION} = json_encode([
            '1' => [
                'pluginname' => coursestart::NAME,
                'action' => editrule_form::FORM_JSON_ACTION_INSERT,
            ],
        ]);
        $dataform->{editrule_form::FORM_JSON_EXCEPTION} = '[]';
        $dataform->{editrule_form::FORM_JSON_ACTION} = json_encode([
            '1' => [
                'pluginname' => 'messageagent',
                'action' => editrule_form::FORM_JSON_ACTION_INSERT,
            ],
        ]);
        $dataform->{editrule_form::FORM_JSON_AC} = '';
        $dataform->{'1_coursestart_days'} = 2;
        $dataform->{'1_coursestart_hours'} = 0;
        $dataform->{'1_coursestart_minutes'} = 0;
        $dataform->{'1_messageagent_title'} = 'Title';
        $dataform->{'1_messageagent_message'} = ['text' => 'Message body', 'format' => FORMAT_HTML];

        return $dataform;
    }

    /**
     * Build save_form data for an enrolend (non-generic) rule.
     *
     * @param int $courseid Course identifier
     * @return \stdClass
     */
    private function build_enrolend_save_form_data(int $courseid): \stdClass {
        $dataform = new \stdClass();
        $dataform->title = 'Non-generic enrolend rule';
        $dataform->type = rule::RULE_TYPE;
        $dataform->courseid = $courseid;
        $dataform->timesfired = 2;
        $dataform->runtime_group = ['runtime_days' => 2, 'runtime_hours' => 0, 'runtime_minutes' => 0];
        $dataform->{editrule_form::FORM_JSON_CONDITION} = json_encode([
            '1' => [
                'pluginname' => enrolend::NAME,
                'action' => editrule_form::FORM_JSON_ACTION_INSERT,
            ],
        ]);
        $dataform->{editrule_form::FORM_JSON_EXCEPTION} = '[]';
        $dataform->{editrule_form::FORM_JSON_ACTION} = json_encode([
            '1' => [
                'pluginname' => 'messageagent',
                'action' => editrule_form::FORM_JSON_ACTION_INSERT,
            ],
        ]);
        $dataform->{editrule_form::FORM_JSON_AC} = '';
        $dataform->{'1_enrolend_days'} = 1;
        $dataform->{'1_enrolend_hours'} = 0;
        $dataform->{'1_enrolend_minutes'} = 0;
        $dataform->{'1_messageagent_title'} = 'Title';
        $dataform->{'1_messageagent_message'} = ['text' => 'Message body', 'format' => FORMAT_HTML];

        return $dataform;
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
    public function test_notificationsagent_condition_get_cm_dates($timeopen, $timeclose, $modname, $fieldopen, $fieldclose): void {
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
    public function test_get_usersbycourse(): void {
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
    public function test_set_timer_cache(): void {
        global $DB;

        notificationsagent::set_timer_cache(
            [
                "(userid =" . self::$user->id . "  AND courseid= " . self::$course->id . " AND conditionid=" . self::CMID . ")",
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
                "(userid =" . self::$user->id . "  AND courseid= " . self::$course->id . " AND conditionid=" . self::CMID . ")",
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
    public function test_set_time_trigger(): void {
        global $DB;

        notificationsagent::set_time_trigger(
            [
                "(userid =" . self::$user->id . "  AND courseid= " . self::$course->id . " AND conditionid=" . self::CMID . ")",
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
                "(userid =" . self::$user->id . "  AND courseid= " . self::$course->id . " AND conditionid=" . self::CMID . ")",
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
    public function test_get_conditions_by_course(): void {
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
    public function test_get_conditions_by_cm(): void {
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
     * Testing that condition queries are cached until the cache is invalidated
     *
     * @return void
     * @covers \local_notificationsagent\notificationsagent::get_conditions_by_course
     * @covers \local_notificationsagent\notificationsagent::get_conditions_by_cm
     * @covers \local_notificationsagent\notificationsagent::invalidate_conditions_cache
     */
    public function test_conditions_cache_lifecycle(): void {
        global $DB, $USER;

        $dataform = new \StdClass();
        $dataform->title = "Rule Test";
        $dataform->type = 1;
        $dataform->courseid = self::$course->id;
        $dataform->timesfired = 2;
        $dataform->runtime_group = ['runtime_days' => 5, 'runtime_hours' => 0, 'runtime_minutes' => 0];
        $USER->id = self::$user->id;
        $ruleid = self::$rule->create($dataform);

        $pluginname = sessionend::NAME;

        // No conditions yet: the empty result is cached as well.
        $this->assertEmpty(notificationsagent::get_conditions_by_course($pluginname, self::$course->id));
        $this->assertEmpty(notificationsagent::get_conditions_by_cm($pluginname, self::$course->id, self::CMID));

        $objdb = new \stdClass();
        $objdb->ruleid = $ruleid;
        $objdb->courseid = self::$course->id;
        $objdb->type = 'condition';
        $objdb->pluginname = $pluginname;
        $objdb->parameters = '{"time":"86400"}';
        $objdb->cmid = self::CMID;
        $conditionid = $DB->insert_record('notificationsagent_condition', $objdb);

        // A direct insert does not purge, so the cached result is still served.
        $this->assertEmpty(notificationsagent::get_conditions_by_course($pluginname, self::$course->id));
        $this->assertEmpty(notificationsagent::get_conditions_by_cm($pluginname, self::$course->id, self::CMID));

        notificationsagent::invalidate_conditions_cache();

        $bycourse = notificationsagent::get_conditions_by_course($pluginname, self::$course->id);
        $this->assertArrayHasKey($conditionid, $bycourse);
        $this->assertEquals($ruleid, $bycourse[$conditionid]->ruleid);
        $this->assertEquals(self::$course->id, $bycourse[$conditionid]->objectid);

        $bycm = notificationsagent::get_conditions_by_cm($pluginname, self::$course->id, self::CMID);
        $this->assertArrayHasKey($conditionid, $bycm);
        $this->assertEquals($ruleid, $bycm[$conditionid]->ruleid);

        // Deleting the rule through the API purges the cache on its own.
        rule::create_instance($ruleid)->delete();

        $this->assertEmpty(notificationsagent::get_conditions_by_course($pluginname, self::$course->id));
        $this->assertEmpty(notificationsagent::get_conditions_by_cm($pluginname, self::$course->id, self::CMID));
    }

    /**
     * Testing that conditions are cached until the cache is invalidated
     *
     * @return void
     * @throws \dml_exception
     * @covers \local_notificationsagent\notificationsagent::get_conditions_by_course
     * @covers \local_notificationsagent\notificationsagent::get_conditions_by_cm
     * @covers \local_notificationsagent\notificationsagent::invalidate_conditions_cache
     */
    public function test_conditions_cache(): void {
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

        notificationsagent::invalidate_conditions_cache();

        $bycourse = notificationsagent::get_conditions_by_course($pluginname, self::$course->id);
        $bycm = notificationsagent::get_conditions_by_cm($pluginname, self::$course->id, self::CMID);
        $this->assertArrayHasKey($conditionid, $bycourse);
        $this->assertArrayHasKey($conditionid, $bycm);

        // Without a purge, the cached rows are returned even though the condition is gone.
        $DB->delete_records('notificationsagent_condition', ['id' => $conditionid]);
        $this->assertEquals($bycourse, notificationsagent::get_conditions_by_course($pluginname, self::$course->id));
        $this->assertEquals($bycm, notificationsagent::get_conditions_by_cm($pluginname, self::$course->id, self::CMID));

        notificationsagent::invalidate_conditions_cache();

        $this->assertEmpty(notificationsagent::get_conditions_by_course($pluginname, self::$course->id));
        $this->assertEmpty(notificationsagent::get_conditions_by_cm($pluginname, self::$course->id, self::CMID));
    }

    /**
     * Testing get conditions by plugin
     *
     * @return void
     * @throws \dml_exception
     * @covers \local_notificationsagent\notificationsagent::get_conditions_by_plugin
     */
    public function test_get_conditions_by_plugin(): void {
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
    public function test_get_availability_conditions(): void {
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
    public function test_get_course_category_context_byruleid(): void {
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
    public function test_is_ruleoff($ruleoff, $expected): void {
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
    public function test_get_triggersbytimeinterval($date, $timestarted, $tasklastrunttime, $emptyresult): void {
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
    public function test_supportedcm($expected, string $modname): void {
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
                'no supported cm' => [false, 'book'],
        ];
    }

    /**
     * Test  bulk_delete_conditions_by_userid
     *
     * @return void
     * @covers \local_notificationsagent\notificationsagent::bulk_delete_conditions_by_userid
     */
    public function test_bulk_delete_conditions_by_userid(): void {
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
    public function test_evaluate_expression($operator, $a, $b, $expected): void {
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

    /**
     * Mixed rules must schedule generic conditions per enrolled user.
     *
     * @covers \local_notificationsagent\notificationsagent::generate_cache_triggers
     * @covers \local_notificationsagent\rule::is_rule_generic
     */
    public function test_generate_cache_triggers_mixed_rule_uses_per_user_triggers(): void {
        global $DB;

        $this->reset_isgeneric_cache();
        $studentone = self::getDataGenerator()->create_user();
        $studenttwo = self::getDataGenerator()->create_user();
        self::getDataGenerator()->enrol_user($studentone->id, self::$course->id, 'student');
        self::getDataGenerator()->enrol_user($studenttwo->id, self::$course->id, 'student');

        $ruleid = $this->create_rule_for_trigger_tests(self::$course->id);
        $now = time();
        $ondatesparams = json_encode([
            'startdate' => $now - DAYSECS,
            'enddate' => $now + YEARSECS,
        ]);

        $ondatesconditionid = $DB->insert_record('notificationsagent_condition', (object) [
            'ruleid' => $ruleid,
            'courseid' => self::$course->id,
            'type' => 'condition',
            'pluginname' => ondates::NAME,
            'parameters' => $ondatesparams,
            'cmid' => 0,
        ]);
        $DB->insert_record('notificationsagent_condition', (object) [
            'ruleid' => $ruleid,
            'courseid' => self::$course->id,
            'type' => 'condition',
            'pluginname' => sessionstart::NAME,
            'parameters' => '{"time":86400}',
            'cmid' => 0,
        ]);

        $subplugin = new ondates($ruleid, $ondatesconditionid);
        $context = new evaluationcontext();
        $context->set_params($subplugin->get_parameters());
        $context->set_complementary(false);
        $context->set_timeaccess($now);
        $context->set_courseid(self::$course->id);

        notificationsagent::generate_cache_triggers($subplugin, $context);

        $this->assertFalse(rule::is_rule_generic($ruleid));
        $this->assertEmpty($DB->get_records('notificationsagent_triggers', [
            'ruleid' => $ruleid,
            'userid' => notificationsagent::GENERIC_USERID,
        ]));

        [$usersql, $userparams] = $DB->get_in_or_equal([$studentone->id, $studenttwo->id], SQL_PARAMS_NAMED);
        $triggers = $DB->get_records_select(
            'notificationsagent_triggers',
            'ruleid = :ruleid AND userid ' . $usersql,
            ['ruleid' => $ruleid] + $userparams
        );
        $this->assertCount(2, $triggers);
        $userids = array_map('intval', array_column($triggers, 'userid'));
        $this->assertEqualsCanonicalizing([$studentone->id, $studenttwo->id], $userids);
    }

    /**
     * Mixed rules defer sync cache on save and rebuild per-user cache via generate_cache_triggers.
     *
     * @covers \local_notificationsagent\notificationconditionplugin::save
     * @covers \local_notificationsagent\notificationsagent::generate_cache_triggers
     */
    public function test_save_mixed_rule_generic_condition_uses_per_user_cache(): void {
        global $DB;

        $this->reset_isgeneric_cache();
        $now = time();
        $ondatesparams = json_encode([
            'startdate' => $now - DAYSECS,
            'enddate' => $now + YEARSECS,
        ]);

        $ruleid = $this->create_rule_for_trigger_tests(self::$course->id);
        $DB->insert_record('notificationsagent_condition', (object) [
            'ruleid' => $ruleid,
            'courseid' => self::$course->id,
            'type' => 'condition',
            'pluginname' => sessionstart::NAME,
            'parameters' => '{"time":0}',
            'cmid' => 0,
            'complementary' => notificationplugin::COMPLEMENTARY_CONDITION,
        ]);
        $ondatesconditionid = $DB->insert_record('notificationsagent_condition', (object) [
            'ruleid' => $ruleid,
            'courseid' => self::$course->id,
            'type' => 'condition',
            'pluginname' => ondates::NAME,
            'parameters' => $ondatesparams,
            'cmid' => 0,
            'complementary' => notificationplugin::COMPLEMENTARY_CONDITION,
        ]);

        $subplugin = new ondates($ruleid, $ondatesconditionid);
        $start = usergetdate($now - DAYSECS);
        $end = usergetdate($now + YEARSECS);
        $data = new \stdClass();
        $data->courseid = self::$course->id;
        $data->{$ondatesconditionid . '_ondates_startdate'} = [
            'day' => $start['mday'],
            'month' => $start['mon'],
            'year' => $start['year'],
        ];
        $data->{$ondatesconditionid . '_ondates_enddate'} = [
            'day' => $end['mday'],
            'month' => $end['mon'],
            'year' => $end['year'],
        ];

        $arraytimer = [];
        $students = [(object) ['id' => self::$user->id]];
        $subplugin->save(
            editrule_form::FORM_JSON_ACTION_UPDATE,
            $data,
            notificationplugin::COMPLEMENTARY_CONDITION,
            $arraytimer,
            $students
        );

        $this->assertFalse(rule::is_rule_generic($ruleid));
        $this->assertEmpty($DB->get_records('notificationsagent_cache', [
            'conditionid' => $ondatesconditionid,
            'userid' => notificationsagent::GENERIC_USERID,
        ]));
        $this->assertEmpty($DB->get_records('notificationsagent_cache', [
            'conditionid' => $ondatesconditionid,
            'userid' => self::$user->id,
        ]));

        $context = new evaluationcontext();
        $context->set_params($subplugin->get_parameters());
        $context->set_complementary(notificationplugin::COMPLEMENTARY_CONDITION);
        $context->set_timeaccess($now);
        $context->set_courseid(self::$course->id);

        notificationsagent::generate_cache_triggers($subplugin, $context);

        $this->assertEmpty($DB->get_records('notificationsagent_cache', [
            'conditionid' => $ondatesconditionid,
            'userid' => notificationsagent::GENERIC_USERID,
        ]));
        $this->assertNotEmpty($DB->get_records('notificationsagent_cache', [
            'conditionid' => $ondatesconditionid,
            'userid' => self::$user->id,
        ]));
    }

    /**
     * Fully generic rules keep scheduling with GENERIC_USERID.
     *
     * @covers \local_notificationsagent\notificationsagent::generate_cache_triggers
     */
    public function test_generate_cache_triggers_fully_generic_rule_uses_generic_userid(): void {
        global $DB;

        $this->reset_isgeneric_cache();
        $ruleid = $this->create_rule_for_trigger_tests(self::$course->id);
        $now = time();
        $ondatesparams = json_encode([
            'startdate' => $now - DAYSECS,
            'enddate' => $now + YEARSECS,
        ]);

        $ondatesconditionid = $DB->insert_record('notificationsagent_condition', (object) [
            'ruleid' => $ruleid,
            'courseid' => self::$course->id,
            'type' => 'condition',
            'pluginname' => ondates::NAME,
            'parameters' => $ondatesparams,
            'cmid' => 0,
        ]);

        $subplugin = new ondates($ruleid, $ondatesconditionid);
        $context = new evaluationcontext();
        $context->set_params($subplugin->get_parameters());
        $context->set_complementary(false);
        $context->set_timeaccess($now);
        $context->set_courseid(self::$course->id);

        notificationsagent::generate_cache_triggers($subplugin, $context);

        $this->assertTrue(rule::is_rule_generic($ruleid));
        $triggers = $DB->get_records('notificationsagent_triggers', ['ruleid' => $ruleid]);
        $this->assertCount(1, $triggers);
        $trigger = reset($triggers);
        $this->assertEquals(notificationsagent::GENERIC_USERID, (int) $trigger->userid);
    }

    /**
     * Consolidation keeps a single trigger row per user with the latest startdate.
     *
     * @covers \local_notificationsagent\notificationsagent::consolidate_rule_triggers
     */
    public function test_consolidate_rule_triggers_single_row_per_user(): void {
        global $DB;

        $studentone = self::getDataGenerator()->create_user();
        $studenttwo = self::getDataGenerator()->create_user();
        self::getDataGenerator()->enrol_user($studentone->id, self::$course->id, 'student');
        self::getDataGenerator()->enrol_user($studenttwo->id, self::$course->id, 'student');

        $ruleid = $this->create_rule_for_trigger_tests(self::$course->id);
        $enrolendconditionid = $DB->insert_record('notificationsagent_condition', (object) [
            'ruleid' => $ruleid,
            'courseid' => self::$course->id,
            'type' => 'condition',
            'pluginname' => enrolend::NAME,
            'parameters' => '{"time":86400}',
            'cmid' => 0,
        ]);
        $usergroupconditionid = $DB->insert_record('notificationsagent_condition', (object) [
            'ruleid' => $ruleid,
            'courseid' => self::$course->id,
            'type' => 'condition',
            'pluginname' => usergroupadd::NAME,
            'parameters' => '{"cmid":0}',
            'cmid' => 0,
        ]);

        $earlier = time() + DAYSECS;
        $later = time() + (2 * DAYSECS);
        foreach ([$studentone->id, $studenttwo->id] as $userid) {
            $DB->insert_record('notificationsagent_cache', (object) [
                'userid' => $userid,
                'courseid' => self::$course->id,
                'startdate' => $earlier,
                'pluginname' => enrolend::NAME,
                'conditionid' => $enrolendconditionid,
            ]);
            $DB->insert_record('notificationsagent_cache', (object) [
                'userid' => $userid,
                'courseid' => self::$course->id,
                'startdate' => $later,
                'pluginname' => usergroupadd::NAME,
                'conditionid' => $usergroupconditionid,
            ]);
            foreach ([$enrolendconditionid, $usergroupconditionid] as $conditionid) {
                $DB->insert_record('notificationsagent_triggers', (object) [
                    'userid' => $userid,
                    'courseid' => self::$course->id,
                    'startdate' => $conditionid === $usergroupconditionid ? $later : $earlier,
                    'conditionid' => $conditionid,
                    'ruleid' => $ruleid,
                ]);
            }
        }

        notificationsagent::consolidate_rule_triggers($ruleid, self::$course->id);

        foreach ([$studentone->id, $studenttwo->id] as $userid) {
            $triggers = $DB->get_records('notificationsagent_triggers', [
                'ruleid' => $ruleid,
                'courseid' => self::$course->id,
                'userid' => $userid,
            ]);
            $this->assertCount(1, $triggers);
            $trigger = reset($triggers);
            $this->assertEquals($later, (int) $trigger->startdate);
            $this->assertEquals($usergroupconditionid, (int) $trigger->conditionid);
        }
    }

    /**
     * Consolidation must not overwrite triggers while ruleoff is active.
     *
     * @covers \local_notificationsagent\notificationsagent::consolidate_rule_triggers
     */
    public function test_consolidate_rule_triggers_respects_ruleoff(): void {
        global $DB;

        $ruleid = $this->create_rule_for_trigger_tests(self::$course->id);
        $conditionid = $DB->insert_record('notificationsagent_condition', (object) [
            'ruleid' => $ruleid,
            'courseid' => self::$course->id,
            'type' => 'condition',
            'pluginname' => enrolend::NAME,
            'parameters' => '{"time":86400}',
            'cmid' => 0,
        ]);
        $ruleofftime = time() + WEEKSECS;
        $DB->insert_record('notificationsagent_triggers', (object) [
            'userid' => self::$user->id,
            'courseid' => self::$course->id,
            'startdate' => $ruleofftime,
            'conditionid' => $conditionid,
            'ruleid' => $ruleid,
            'ruleoff' => time(),
        ]);
        $DB->insert_record('notificationsagent_cache', (object) [
            'userid' => self::$user->id,
            'courseid' => self::$course->id,
            'startdate' => time() + DAYSECS,
            'pluginname' => enrolend::NAME,
            'conditionid' => $conditionid,
        ]);

        notificationsagent::consolidate_rule_triggers($ruleid, self::$course->id);

        $trigger = $DB->get_record('notificationsagent_triggers', [
            'ruleid' => $ruleid,
            'courseid' => self::$course->id,
            'userid' => self::$user->id,
        ]);
        $this->assertNotFalse($trigger);
        $this->assertEquals($ruleofftime, (int) $trigger->startdate);
        $this->assertNotNull($trigger->ruleoff);
    }

    /**
     * Non-generic rules defer trigger creation and queue an adhoc rebuild task.
     *
     * @covers \local_notificationsagent\rule::save_form
     * @covers \local_notificationsagent\task\rebuild_rule_triggers_task::execute
     */
    public function test_save_form_non_generic_rule_defers_triggers_until_adhoc(): void {
        global $DB;

        $this->setAdminUser();
        $timeend = time() + YEARSECS;
        self::getDataGenerator()->enrol_user(
            self::$user->id,
            self::$course->id,
            'student',
            'manual',
            time(),
            $timeend
        );

        $rule = new rule(null, rule::RULE_TYPE, rule::RULE_ADD);
        $rule->save_form($this->build_enrolend_save_form_data(self::$course->id));

        $ruleid = $rule->get_id();
        $this->assertTrue($rule->was_rebuild_queued());
        $this->assertEmpty($DB->get_records('notificationsagent_triggers', [
            'ruleid' => $ruleid,
            'courseid' => self::$course->id,
        ]));

        $tasks = $this->get_rebuild_tasks_for_rule($ruleid);
        $this->assertCount(1, $tasks);
        $tasks[0]->execute();

        $this->assertNotEmpty($DB->get_records('notificationsagent_triggers', [
            'ruleid' => $ruleid,
            'courseid' => self::$course->id,
        ]));
        $this->assertFalse(notificationsagent::is_rebuild_in_progress($ruleid, self::$course->id));
    }

    /**
     * Fully generic rules still create triggers synchronously without adhoc tasks.
     *
     * @covers \local_notificationsagent\rule::save_form
     */
    public function test_save_form_generic_rule_creates_triggers_synchronously(): void {
        global $DB;

        $this->setAdminUser();

        $rule = new rule(null, rule::RULE_TYPE, rule::RULE_ADD);
        $rule->save_form($this->build_coursestart_save_form_data(self::$course->id));

        $ruleid = $rule->get_id();
        $this->assertFalse($rule->was_rebuild_queued());
        $this->assertEmpty($this->get_rebuild_tasks_for_rule($ruleid));
        $this->assertNotEmpty($DB->get_records('notificationsagent_triggers', [
            'ruleid' => $ruleid,
            'courseid' => self::$course->id,
        ]));
    }

    /**
     * Adhoc rebuild tasks with a stale token must not write triggers.
     *
     * @covers \local_notificationsagent\task\rebuild_rule_triggers_task::execute
     */
    public function test_rebuild_rule_triggers_task_stale_token_skips_rebuild(): void {
        global $DB;

        $this->setAdminUser();
        $timeend = time() + YEARSECS;
        self::getDataGenerator()->enrol_user(
            self::$user->id,
            self::$course->id,
            'student',
            'manual',
            time(),
            $timeend
        );

        $rule = new rule(null, rule::RULE_TYPE, rule::RULE_ADD);
        $rule->save_form($this->build_enrolend_save_form_data(self::$course->id));
        $ruleid = $rule->get_id();

        notificationsagent::bump_rebuild_token($ruleid, self::$course->id);

        $tasks = $this->get_rebuild_tasks_for_rule($ruleid);
        $this->assertCount(1, $tasks);
        $tasks[0]->execute();

        $this->assertEmpty($DB->get_records('notificationsagent_triggers', [
            'ruleid' => $ruleid,
            'courseid' => self::$course->id,
        ]));
    }

    /**
     * generate_cache_triggers must not write cache while a rebuild is in progress.
     *
     * @covers \local_notificationsagent\notificationsagent::generate_cache_triggers
     */
    public function test_generate_cache_triggers_skips_when_rebuild_in_progress(): void {
        global $DB;

        $ruleid = $this->create_rule_for_trigger_tests(self::$course->id);
        $timeend = time() + YEARSECS;
        self::getDataGenerator()->enrol_user(
            self::$user->id,
            self::$course->id,
            'student',
            'manual',
            time(),
            $timeend
        );
        $conditionid = $DB->insert_record('notificationsagent_condition', (object) [
            'ruleid' => $ruleid,
            'courseid' => self::$course->id,
            'type' => 'condition',
            'pluginname' => enrolend::NAME,
            'parameters' => '{"time":86400}',
            'cmid' => 0,
        ]);

        notificationsagent::set_rebuild_in_progress($ruleid, self::$course->id, true);

        $subplugin = new enrolend($ruleid, $conditionid);
        $context = new evaluationcontext();
        $context->set_courseid(self::$course->id);
        $context->set_userid(self::$user->id);
        $context->set_params('{"time":86400}');
        $context->set_timeaccess(time());
        $context->set_complementary(notificationplugin::COMPLEMENTARY_CONDITION);

        notificationsagent::generate_cache_triggers($subplugin, $context);

        $this->assertEmpty($DB->get_records('notificationsagent_cache', [
            'conditionid' => $conditionid,
            'userid' => self::$user->id,
        ]));

        notificationsagent::set_rebuild_in_progress($ruleid, self::$course->id, false);
    }
}
