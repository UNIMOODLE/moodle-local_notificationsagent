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

namespace local_notificationsagent\engine;

use local_notificationsagent\engine\notificationsagent_engine;
use local_notificationsagent\evaluationcontext;
use local_notificationsagent\notificationplugin;
use local_notificationsagent\notificationsagent;
use local_notificationsagent\rule;

/**
 * Testing notificationsagent engine class
 *
 * @coversDefaultClass \local_notificationsagent\engine\notificationsagent_engine
 * @group notificationsagent
 */
final class notificationsagent_engine_test extends \advanced_testcase {
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
    private static $cmteste;
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
    public const USER_FIRSTACCESS = 1704099600; // 30/01/2024 10:00:00,
    /**
     * User last access to a course
     */
    public const USER_LASTACCESS = 1704099600; // 01/01/2024 10:00:00.
    /**
     *  Random id for activity
     */
    public const CMID = 246000;

    /**
     * Engine evaluation date used by most scenarios.
     */
    private const ENGINE_DATE = 1706173200;

    /**
     * Weekend date used by generic weekend scenarios.
     */
    private const WEEKEND_DATE = 1701511761;

    /**
     * Hybrid conditions (non-generic rule).
     */
    private const HYBRID_CONDITIONS = [
        ['pluginname' => 'coursestart', 'params' => '{"time":864001}'],
        ['pluginname' => 'sessionend', 'params' => '{"time":864001}'],
    ];

    /**
     * Generic coursestart condition.
     */
    private const GENERIC_COURSESTART_CONDITION = [
        ['pluginname' => 'coursestart', 'params' => '{"time":864001}'],
    ];

    /**
     * Messageagent action payload.
     */
    private const MESSAGE_ACTION = [
        'pluginname' => 'messageagent',
        'params' => '{"title":"Title","message":{"text":"Message body"}}',
    ];

    /**
     * Non-generic sessionend condition.
     */
    private const NON_GENERIC_SESSIONEND_CONDITION = [
        ['pluginname' => 'sessionend', 'params' => '{"time":864001}'],
    ];

    /**
     * Generic weekend condition.
     */
    private const GENERIC_WEEKEND_CONDITION = [
        ['pluginname' => 'weekend', 'params' => '{}'],
    ];

    /**
     * Bootstrap notifications action payload.
     */
    private const BOOTSTRAP_ACTION = [
        'pluginname' => 'bootstrapnotifications',
        'params' => '{"message":"Bootstrap body"}',
    ];

    /**
     * Settin up test context
     *
     * @return void
     * @throws \coding_exception
     */
    public function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
        set_config('calendar_weekend', 65);
        $rule = new rule();
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
        self::$cmteste = $quizgenerator->create_instance([
                'name' => 'Quiz unittest',
                'course' => self::$course->id,
                "timeopen" => self::CM_DATESTART,
                "timeclose" => self::CM_DATEEND,
        ]);

        self::getDataGenerator()->enrol_user(self::$user->id, self::$course->id, 'student');
    }

    /**
     * Creates a rule with conditions, optional exceptions and actions for engine tests.
     *
     * @param array $conditions Condition definitions
     * @param array $actions Action definitions
     * @param array $exceptions Exception definitions
     * @param int $timesfired Maximum executions per user
     * @return array{int,int} Rule id and last condition id
     */
    private function create_engine_rule(
        array $conditions,
        array $actions,
        array $exceptions = [],
        int $timesfired = 2
    ): array {
        global $DB, $USER;

        $dataform = new \stdClass();
        $dataform->title = 'Engine rule';
        $dataform->type = 1;
        $dataform->courseid = self::$course->id;
        $dataform->timesfired = $timesfired;
        $dataform->runtime_group = ['runtime_days' => 5, 'runtime_hours' => 0, 'runtime_minutes' => 0];
        $USER->id = self::$user->id;
        $ruleid = self::$rule->create($dataform);
        self::$rule->set_id($ruleid);

        $conditionid = 0;
        foreach ($conditions as $condition) {
            $conditionid = $DB->insert_record('notificationsagent_condition', (object) [
                'ruleid' => $ruleid,
                'courseid' => self::$course->id,
                'type' => 'condition',
                'pluginname' => $condition['pluginname'],
                'parameters' => $condition['params'],
                'cmid' => self::$cmteste->id,
                'complementary' => $condition['complementary'] ?? notificationplugin::COMPLEMENTARY_CONDITION,
            ]);
        }

        foreach ($exceptions as $exception) {
            if (empty($exception['pluginname'])) {
                continue;
            }
            $DB->insert_record('notificationsagent_condition', (object) [
                'ruleid' => $ruleid,
                'courseid' => self::$course->id,
                'type' => 'condition',
                'pluginname' => $exception['pluginname'],
                'parameters' => $exception['params'],
                'cmid' => self::$cmteste->id,
                'complementary' => notificationplugin::COMPLEMENTARY_EXCEPTION,
            ]);
        }

        foreach ($actions as $action) {
            $actionparams = json_decode($action['params'], true);
            if (array_key_exists('userid', $action) || !empty($actionparams[notificationplugin::UI_USER])) {
                $actionparams[notificationplugin::UI_USER] = $action['userid'] ?? self::$user->id;
                $action['params'] = json_encode($actionparams);
            }
            $DB->insert_record('notificationsagent_action', (object) [
                'ruleid' => $ruleid,
                'courseid' => self::$course->id,
                'type' => 'action',
                'pluginname' => $action['pluginname'],
                'parameters' => $action['params'],
            ]);
        }

        rule::create_instance($ruleid);

        return [$ruleid, (int) $conditionid];
    }

    /**
     * Runs the engine for a single rule and trigger.
     *
     * @param int $ruleid Rule id
     * @param int $conditionid Trigger condition id
     * @param int $userid Trigger user id
     * @param int $date Evaluation timestamp
     */
    private function evaluate_engine(int $ruleid, int $conditionid, int $userid, int $date): void {
        notificationsagent_engine::notificationsagent_engine_evaluate_rule(
            [$ruleid],
            $date,
            $userid,
            self::$course->id,
            $conditionid,
            $date
        );
    }

    /**
     * Enrols an additional student in the shared course.
     *
     * @return \stdClass Student user
     */
    private function create_second_student(): \stdClass {
        $user = self::getDataGenerator()->create_user();
        self::getDataGenerator()->enrol_user($user->id, self::$course->id, 'student');
        self::getDataGenerator()->create_user_course_lastaccess($user, self::$course, self::USER_LASTACCESS);
        return $user;
    }

    /**
     * Inserts a launched record with the required audit fields.
     *
     * @param int $ruleid Rule id
     * @param int $userid User id
     * @param int $timesfired Times fired counter
     */
    private function seed_launched_record(int $ruleid, int $userid, int $timesfired): void {
        global $DB;

        $now = time();
        $DB->insert_record('notificationsagent_launched', (object) [
            'ruleid' => $ruleid,
            'courseid' => self::$course->id,
            'userid' => $userid,
            'timesfired' => $timesfired,
            'timecreated' => $now,
            'timemodified' => $now,
        ]);
    }

    /**
     * Creates a course group for group-related actions.
     *
     * @param string $name Group name
     * @return \stdClass Group record
     */
    private function create_course_group(string $name = 'Engine test group'): \stdClass {
        return self::getDataGenerator()->create_group([
            'courseid' => self::$course->id,
            'name' => $name,
        ]);
    }

    /**
     * Testin engine evaluate rule
     *
     * @dataProvider dataprovider
     *
     * @param int $date
     * @param array $conditiondata
     * @param array $exceptiondata
     * @param array $actiondata
     * @param int $genericuser Trigger user mode (1 = -1, 2 = student, 3 = -1 hybrid)
     * @param bool $expected Whether reports are expected
     * @param int|null $expectedreportcount Expected number of report rows
     *
     * @return void
     * @covers ::notificationsagent_engine_evaluate_rule
     */
    public function test_notificationsagent_engine_evaluate_rule(
        int $date,
        array $conditiondata,
        array $exceptiondata,
        array $actiondata,
        int $genericuser,
        bool $expected,
        ?int $expectedreportcount = null
    ): void {
        global $DB, $USER;
        $conditions = [];
        $exceptions = [];
        $dataform = new \StdClass();
        $dataform->title = "Rule Test";
        $dataform->type = 1;
        $dataform->courseid = self::$course->id;
        $dataform->timesfired = 2;
        $dataform->runtime_group = ['runtime_days' => 5, 'runtime_hours' => 0, 'runtime_minutes' => 0];
        $USER->id = self::$user->id;
        $ruleid = self::$rule->create($dataform);
        $this->assertIsNumeric($ruleid);
        self::$rule->set_id($ruleid);
        self::$cmteste->cmid = self::$cmteste->id;
        if ( $genericuser == 1 ) {
            $userid = -1;
        }
        if ( $genericuser == 2 ) {
            $userid = self::$user->id;
        }
        if ( $genericuser == 3 ) {
            $userid = -1;
        }

        $courseid = self::$course->id;

        // Context.
        $context = new evaluationcontext();
        $context->set_userid($userid);
        $context->set_courseid($courseid);
        $context->set_timeaccess($date);
        $context->set_startdate($date);

        foreach ($conditiondata as $condition) {
            // Conditions.
            $objdb = new \stdClass();
            $objdb->ruleid = $ruleid;
            $objdb->courseid = $courseid;
            $objdb->type = 'condition';
            $objdb->pluginname = $condition['pluginname'];
            $objdb->parameters = $condition['params'];
            $objdb->cmid = self::$cmteste->id;
            // Insert.
            $conditionid = $DB->insert_record('notificationsagent_condition', $objdb);
            $this->assertIsNumeric($conditionid);
            $context->set_triggercondition($conditionid);
            $conditions[] = $condition;
        }

        $context->set_conditions($conditions);

        foreach ($exceptiondata as $exception) {
            // Conditions.
            $objdb = new \stdClass();
            $objdb->ruleid = $ruleid;
            $objdb->courseid = $courseid;
            $objdb->type = 'condition';
            $objdb->pluginname = $exception['pluginname'];
            $objdb->parameters = $exception['params'];
            $objdb->cmid = self::$cmteste->id;
            $objdb->complementary = notificationplugin::COMPLEMENTARY_EXCEPTION;
            // Insert.
            $exceptionid = $DB->insert_record('notificationsagent_condition', $objdb);
            $this->assertIsNumeric($exceptionid);
            $exceptions[] = $exception;
        }

        $context->set_exceptions($exceptions);

        foreach ($actiondata as $action) {
            // Usermessagent case.
            $actionparams = json_decode($action['params'], true);
            $hasuser = $actionparams[notificationplugin::UI_USER] ?? false;
            if ($hasuser) {
                $auxarray = json_decode($action['params'], true);
                $auxarray[notificationplugin::UI_USER] = self::$user->id;
                $action['params'] = json_encode($auxarray);
            }
            // Conditions.
            $objdb = new \stdClass();
            $objdb->ruleid = $ruleid;
            $objdb->courseid = $courseid;
            $objdb->type = 'action';
            $objdb->pluginname = $action['pluginname'];
            $objdb->parameters = $action['params'];
            // Insert.
            $actionid = $DB->insert_record('notificationsagent_action', $objdb);
            $this->assertIsNumeric($actionid);
        }

        self::$rule::create_instance($ruleid);

        notificationsagent_engine::notificationsagent_engine_evaluate_rule(
            [self::$rule->get_id()],
            $date,
            $userid,
            self::$course->id,
            $context->get_triggercondition(),
            $context->get_startdate()
        );
        $results = $DB->get_records('notificationsagent_report');
        if ($expected) {
            $this->assertNotEmpty($results);
            if ($expectedreportcount !== null) {
                $this->assertCount($expectedreportcount, $results);
            }
            foreach ($results as $result) {
                $this->assertEquals($result->ruleid, self::$rule->get_id());
                $this->assertEquals($result->courseid, self::$course->id);
                $this->assertEquals($result->userid, self::$user->id);
            }
        } else {
            $this->assertEmpty($results);
        }
        $launched = $DB->get_records('notificationsagent_launched');
        if ($expected) {
            $this->assertNotEmpty($launched);
            $expectstudentlaunched = false;
            foreach ($actiondata as $action) {
                if (($action['pluginname'] ?? '') === 'usermessageagent') {
                    $expectstudentlaunched = true;
                    break;
                }
            }
            foreach ($launched as $launch) {
                $this->assertEquals($launch->ruleid, self::$rule->get_id());
                $this->assertEquals($launch->courseid, self::$course->id);
                if ($genericuser == 2 || $genericuser == 3 || ($genericuser == 1 && $expectstudentlaunched)) {
                    $expectedlaunchuserid = (int) self::$user->id;
                } else {
                    $expectedlaunchuserid = notificationsagent::GENERIC_USERID;
                }
                $this->assertEquals($expectedlaunchuserid, (int) $launch->userid);
                $this->assertEquals($launch->timesfired,  1);
            }
        } else {
            $this->assertEmpty($results);
        }

    }

    /**
     * Data provider for engine
     *
     * @return array[]
     */
    public static function dataprovider(): array {
        return [
                'Gen' => [ // ACCION A UNO.
                        1706173200,
                        [
                                ['pluginname' => 'sessionend', 'params' => '{"time":864001}'],
                        ],
                        [['pluginname' => '', 'params' => '']],
                        [
                                [
                                        'pluginname' => 'messageagent',
                                        'params' => '{
                        "title":"Title" ,
                        "message":{"text":"Message to {User_FirstName} {User_LastName} {User_Email} {User_Username} {Follow_Link} "}
                        }',
                                ],
                        ],
                        2,
                        true,
                ],
                'Gen0' => [ // ACCION A TODOS.
                        1706173200,
                        [
                                ['pluginname' => 'coursestart', 'params' => '{"time":864001}'],
                                ['pluginname' => 'courseend', 'params' => '{"time":864001}'],
                        ],
                        [['pluginname' => '', 'params' => '']],
                        [
                                [
                                        'pluginname' => 'messageagent',
                                        'params' => '{
                        "title":"Title" ,
                        "message":{"text":"Message  {User_Address} {Course_FullName} {Course_Url} {Teacher_FirstName} "}
                        }',
                                ],
                        ],
                        1,
                        true,
                ],
                'Gen1' => [
                        1706173200,
                        [
                                ['pluginname' => 'sessionend', 'params' => '{"time":864001}'],
                        ],
                        [['pluginname' => '', 'params' => '']],
                        [
                                [
                                        'pluginname' => 'messageagent',
                                        'params' => '{
                        "title":"Title" ,
                        "message":{"text":"Message {Teacher_LastName} {Teacher_Email} {Teacher_Username} "}
                        }',
                                ],
                        ],
                        2,
                        true,
                ],
                'Hyb' => [
                        1706173200,
                        [
                                ['pluginname' => 'coursestart', 'params' => '{"time":864001}'],
                                ['pluginname' => 'sessionend', 'params' => '{"time":864001}'],
                        ],
                        [['pluginname' => '', 'params' => '']],
                        [
                                [
                                        'pluginname' => 'messageagent',
                                        'params' => '{
                        "title":"Title" ,"message":{"text":"Message to {Current_time}}"}
                        }',
                                ],
                        ],
                        3,
                        true,
                ],
                'Gen2' => [
                        1706173200,
                        [
                                ['pluginname' => 'coursestart', 'params' => '{"time":864001}'],

                        ],
                        [['pluginname' => '', 'params' => '']],
                        [
                                [
                                        'pluginname' => 'messageagent',
                                        'params' => '{
                        "title":"Title" ,"message":{"text":"Message {Teacher_Address} "}
                        }',
                                ],
                        ],
                        1,
                        true,
                ],
                'Gen3' => [
                        1706173200,
                        [
                                ['pluginname' => 'sessionend', 'params' => '{"time":864001}'],
                        ],
                        [['pluginname' => '', 'params' => '']],
                        [
                                [
                                        'pluginname' => 'usermessageagent',
                                        'params' => '{"title":"Title" ,"message":{"text":"Message"}, "user":"104000"}',
                                ],
                                [
                                        'pluginname' => 'messageagent',
                                        'params' => '{
                        "title":"Title" ,"message":{"text":"Message to {Course_Category_Name}"}
                        }',
                                ],
                        ],
                        2,
                        true,
                        2,
                ],
                'Gen4' => [
                        1706173200,
                        [
                                ['pluginname' => 'coursestart', 'params' => '{"time":86400000}'],

                        ],
                        [['pluginname' => '', 'params' => '']],
                        [
                                [
                                        'pluginname' => 'messageagent',
                                        'params' => '{
                        "title":"Title" ,"message":{"text":"Message {Follow_Link} "}
                        }',
                                ],
                        ],
                        1,
                        false,
                ],
                'Gen5' => [
                1706173200,
                [
                    ['pluginname' => 'weekdays', 'params' => '{"weekdays":[1,2,3,4,5,6,7]}'],

                ],
                [['pluginname' => '', 'params' => '']],
                [
                    [
                        'pluginname' => 'bootstrapnotifications',
                        'params' => '{
                        "message":"{User_FirstName}"
                        }',
                    ],
                ],
                1,
                true,
                ],
                'Gen6' => [
                1706173200,
                [
                    ['pluginname' => 'coursestart', 'params' => '{"time":864001}'],
                ],
                [['pluginname' => '', 'params' => '']],
                [
                    [
                        'pluginname' => 'messageagent',
                        'params' => '{
                        "title":"Title" ,"message":{"text":"Message to {Current_time}}"}
                        }',
                    ],
                ],
                1,
                true,
                1,
                ],

                'GenWeekend' => [
                self::WEEKEND_DATE,
                [
                    ['pluginname' => 'weekend', 'params' => '{}'],
                ],
                [['pluginname' => '', 'params' => '']],
                [
                    self::MESSAGE_ACTION,
                ],
                1,
                true,
                1,
                ],

                'Hyb1' => [
                1706173200,
                [
                    ['pluginname' => 'coursestart', 'params' => '{"time":864001}'],
                    ['pluginname' => 'sessionend', 'params' => '{"time":864001}'],
                ],
                [['pluginname' => '', 'params' => '']],
                [
                    [
                        'pluginname' => 'usermessageagent',
                        'params' => '{"title":"Title" ,"message":{"text":"Message"}, "user":"104000"}',
                    ],
                    [
                        'pluginname' => 'messageagent',
                        'params' => '{
                        "title":"Title" ,"message":{"text":"Message to {Course_Category_Name}"}
                        }',
                    ],
                ],
                3,
                true,
                2,
                ],

                'GenNonGenericOnly' => [
                self::ENGINE_DATE,
                [
                    ['pluginname' => 'sessionend', 'params' => '{"time":864001}'],
                ],
                [['pluginname' => '', 'params' => '']],
                [
                    self::MESSAGE_ACTION,
                ],
                2,
                true,
                1,
                ],

                'GenWeekendUsermessage' => [
                self::WEEKEND_DATE,
                [
                    ['pluginname' => 'weekend', 'params' => '{}'],
                ],
                [['pluginname' => '', 'params' => '']],
                [
                    [
                        'pluginname' => 'usermessageagent',
                        'params' => '{"title":"Title","message":{"text":"Weekend message"},"user":"104000"}',
                    ],
                ],
                1,
                true,
                1,
                ],
        ];
    }

    /**
     * Hybrid rules with generic trigger broadcast messageagent to each enrolled student.
     *
     * @return void
     * @covers ::notificationsagent_engine_evaluate_rule
     */
    public function test_hybrid_rule_broadcasts_message_to_each_student(): void {
        global $DB;

        $this->create_second_student();
        [$ruleid, $conditionid] = $this->create_engine_rule(self::HYBRID_CONDITIONS, [self::MESSAGE_ACTION]);

        $this->evaluate_engine($ruleid, $conditionid, notificationsagent::GENERIC_USERID, self::ENGINE_DATE);

        $this->assertCount(2, $DB->get_records('notificationsagent_report', ['ruleid' => $ruleid]));
        $launched = $DB->get_records('notificationsagent_launched', ['ruleid' => $ruleid]);
        $this->assertCount(2, $launched);
        foreach ($launched as $launch) {
            $this->assertEquals(1, $launch->timesfired);
        }
    }

    /**
     * Hybrid rules only report usermessageagent for the configured target student.
     *
     * @return void
     * @covers ::notificationsagent_engine_evaluate_rule
     */
    public function test_hybrid_rule_usermessageagent_reports_only_target_student(): void {
        global $DB;

        $otherstudent = $this->create_second_student();
        [$ruleid, $conditionid] = $this->create_engine_rule(self::HYBRID_CONDITIONS, [[
            'pluginname' => 'usermessageagent',
            'params' => '{"title":"Title","message":{"text":"Private message"},"user":"104000"}',
        ]]);

        unset_config('noemailever');
        $sink = $this->redirectMessages();
        $this->evaluate_engine($ruleid, $conditionid, notificationsagent::GENERIC_USERID, self::ENGINE_DATE);
        $sink->close();

        $reports = $DB->get_records('notificationsagent_report', ['ruleid' => $ruleid]);
        $this->assertCount(1, $reports);
        $report = reset($reports);
        $this->assertEquals((int) self::$user->id, (int) $report->userid);

        $launched = $DB->get_records('notificationsagent_launched', ['ruleid' => $ruleid]);
        $this->assertCount(2, $launched);
        $launcheduserids = array_map(static fn(\stdClass $record): int => (int) $record->userid, $launched);
        $this->assertEqualsCanonicalizing(
            [(int) self::$user->id, (int) $otherstudent->id],
            $launcheduserids
        );
        foreach ($launched as $launch) {
            $this->assertEquals(1, (int) $launch->timesfired);
        }
    }

    /**
     * Generic rules broadcast messageagent to each enrolled student using is_send_once false path.
     *
     * @return void
     * @covers ::notificationsagent_engine_evaluate_rule
     */
    public function test_generic_rule_broadcasts_message_to_each_student(): void {
        global $DB;

        $this->create_second_student();
        [$ruleid, $conditionid] = $this->create_engine_rule(self::GENERIC_COURSESTART_CONDITION, [self::MESSAGE_ACTION]);

        unset_config('noemailever');
        $sink = $this->redirectMessages();
        $this->evaluate_engine($ruleid, $conditionid, notificationsagent::GENERIC_USERID, self::ENGINE_DATE);
        $sink->close();

        $this->assertCount(2, $DB->get_records('notificationsagent_report', ['ruleid' => $ruleid]));
        $launched = $DB->get_record('notificationsagent_launched', [
            'ruleid' => $ruleid,
            'courseid' => self::$course->id,
            'userid' => notificationsagent::GENERIC_USERID,
        ]);
        $this->assertNotFalse($launched);
        $this->assertEquals(1, $launched->timesfired);
    }

    /**
     * Engine skips evaluation when the student reached the rule execution limit.
     *
     * @return void
     * @covers ::notificationsagent_engine_evaluate_rule
     */
    public function test_evaluation_skips_when_user_reached_timesfired_limit(): void {
        global $DB;

        [$ruleid, $conditionid] = $this->create_engine_rule(
            [['pluginname' => 'sessionend', 'params' => '{"time":864001}']],
            [self::MESSAGE_ACTION],
            [],
            2
        );
        $this->seed_launched_record($ruleid, self::$user->id, 2);

        $this->evaluate_engine($ruleid, $conditionid, self::$user->id, self::ENGINE_DATE);

        $this->assertEmpty($DB->get_records('notificationsagent_report', ['ruleid' => $ruleid]));
    }

    /**
     * Generic rules skip evaluation when the generic launched counter reached the limit.
     *
     * @return void
     * @covers ::notificationsagent_engine_evaluate_rule
     */
    public function test_evaluation_skips_generic_rule_when_generic_launch_limit_reached(): void {
        global $DB;

        [$ruleid, $conditionid] = $this->create_engine_rule(self::GENERIC_COURSESTART_CONDITION, [self::MESSAGE_ACTION], [], 2);
        $this->seed_launched_record($ruleid, notificationsagent::GENERIC_USERID, 2);

        $this->evaluate_engine($ruleid, $conditionid, notificationsagent::GENERIC_USERID, self::ENGINE_DATE);

        $this->assertEmpty($DB->get_records('notificationsagent_report', ['ruleid' => $ruleid]));
    }

    /**
     * Failed actions store an error flag in the report detail.
     *
     * @return void
     * @covers ::notificationsagent_engine_evaluate_rule
     */
    public function test_failed_action_records_error_in_report(): void {
        global $DB;

        $othercourse = self::getDataGenerator()->create_course();
        $foreigngroup = self::getDataGenerator()->create_group(['courseid' => $othercourse->id]);
        [$ruleid, $conditionid] = $this->create_engine_rule(
            [['pluginname' => 'sessionend', 'params' => '{"time":864001}']],
            [[
                'pluginname' => 'addusergroup',
                'params' => json_encode(['cmid' => $foreigngroup->id]),
            ]]
        );

        $this->evaluate_engine($ruleid, $conditionid, self::$user->id, self::ENGINE_DATE);

        $report = $DB->get_record('notificationsagent_report', ['ruleid' => $ruleid], '*', MUST_EXIST);
        $detail = json_decode($report->actiondetail, true);
        $this->assertArrayHasKey('error', $detail);
    }

    /**
     * Generic usermessageagent to a course manager keeps launched counter on GENERIC_USERID.
     *
     * @return void
     * @covers ::notificationsagent_engine_evaluate_rule
     */
    public function test_generic_usermessageagent_to_manager_uses_generic_launch_userid(): void {
        global $DB;

        set_config('calendar_weekend', 65);
        $teacher = self::getDataGenerator()->create_and_enrol(self::$course, 'editingteacher');
        [$ruleid, $conditionid] = $this->create_engine_rule(
            [['pluginname' => 'weekend', 'params' => '{}']],
            [[
                'pluginname' => 'usermessageagent',
                'params' => '{"title":"Title","message":{"text":"Manager message"},"user":"104000"}',
                'userid' => $teacher->id,
            ]]
        );

        unset_config('noemailever');
        $sink = $this->redirectMessages();
        $this->evaluate_engine($ruleid, $conditionid, notificationsagent::GENERIC_USERID, self::WEEKEND_DATE);
        $sink->close();

        $launched = $DB->get_record('notificationsagent_launched', [
            'ruleid' => $ruleid,
            'courseid' => self::$course->id,
            'userid' => notificationsagent::GENERIC_USERID,
        ]);
        $this->assertNotFalse($launched);
        $this->assertEquals(1, $launched->timesfired);

        $report = $DB->get_record('notificationsagent_report', ['ruleid' => $ruleid], '*', MUST_EXIST);
        $this->assertEquals(get_admin()->id, $report->userid);
    }

    /**
     * Hybrid rules rotate usermessageagent blocks per target student with generic trigger.
     *
     * @return void
     * @covers ::notificationsagent_engine_evaluate_rule
     */
    public function test_hybrid_rule_usermessageagent_rotates_message_blocks(): void {
        global $DB;

        $separator = '{' . rule::SEPARATOR . '}';
        $message = 'Hybrid one' . $separator . 'Hybrid two' . $separator . 'Hybrid three';
        [$ruleid, $conditionid] = $this->create_engine_rule(self::HYBRID_CONDITIONS, [[
            'pluginname' => 'usermessageagent',
            'params' => json_encode([
                'title' => 'Title',
                'message' => ['text' => $message],
                notificationplugin::UI_USER => self::$user->id,
            ]),
        ]], [], 3);

        unset_config('noemailever');
        $sink = $this->redirectMessages();
        $expectedblocks = ['Hybrid one', 'Hybrid two', 'Hybrid three'];

        for ($execution = 0; $execution < 3; $execution++) {
            $this->evaluate_engine($ruleid, $conditionid, notificationsagent::GENERIC_USERID, self::ENGINE_DATE);
            $messages = $sink->get_messages();
            $this->assertCount($execution + 1, $messages);
            $this->assertStringContainsString($expectedblocks[$execution], $messages[$execution]->fullmessage);
        }
        $sink->close();

        $launched = $DB->get_record('notificationsagent_launched', [
            'ruleid' => $ruleid,
            'courseid' => self::$course->id,
            'userid' => self::$user->id,
        ]);
        $this->assertNotFalse($launched);
        $this->assertEquals(3, $launched->timesfired);
    }

    /**
     * Generic rules with usermessageagent must rotate message blocks per target user.
     *
     * @return void
     * @covers ::notificationsagent_engine_evaluate_rule
     */
    public function test_generic_usermessageagent_rotates_message_blocks(): void {
        global $DB;

        $separator = '{' . rule::SEPARATOR . '}';
        $message = 'Block one' . $separator . 'Block two' . $separator . 'Block three';
        [$ruleid, $conditionid] = $this->create_engine_rule(
            [['pluginname' => 'weekend', 'params' => '{}']],
            [[
                'pluginname' => 'usermessageagent',
                'params' => json_encode([
                    'title' => 'Title',
                    'message' => ['text' => $message],
                    notificationplugin::UI_USER => self::$user->id,
                ]),
            ]],
            [],
            3
        );

        unset_config('noemailever');
        $sink = $this->redirectMessages();
        $expectedblocks = ['Block one', 'Block two', 'Block three'];

        for ($execution = 0; $execution < 3; $execution++) {
            $this->evaluate_engine($ruleid, $conditionid, notificationsagent::GENERIC_USERID, self::WEEKEND_DATE);
            $messages = $sink->get_messages();
            $this->assertCount($execution + 1, $messages);
            $this->assertStringContainsString($expectedblocks[$execution], $messages[$execution]->fullmessage);
        }
        $sink->close();

        $launched = $DB->get_record('notificationsagent_launched', [
            'ruleid' => $ruleid,
            'courseid' => self::$course->id,
            'userid' => self::$user->id,
        ]);
        $this->assertNotFalse($launched);
        $this->assertEquals(3, $launched->timesfired);
    }

    /**
     * Non-generic rules with a real trigger user only execute for that student.
     *
     * @return void
     * @covers ::notificationsagent_engine_evaluate_rule
     */
    public function test_non_generic_rule_evaluates_only_for_trigger_user(): void {
        global $DB;

        $this->create_second_student();
        [$ruleid, $conditionid] = $this->create_engine_rule(self::NON_GENERIC_SESSIONEND_CONDITION, [self::MESSAGE_ACTION]);

        unset_config('noemailever');
        $sink = $this->redirectMessages();
        $this->evaluate_engine($ruleid, $conditionid, (int) self::$user->id, self::ENGINE_DATE);
        $sink->close();

        $this->assertCount(1, $DB->get_records('notificationsagent_report', ['ruleid' => $ruleid]));
        $this->assertCount(1, $sink->get_messages());
        $launched = $DB->get_records('notificationsagent_launched', ['ruleid' => $ruleid]);
        $this->assertCount(1, $launched);
        $launch = reset($launched);
        $this->assertEquals((int) self::$user->id, (int) $launch->userid);
    }

    /**
     * Generic rules broadcast bootstrap notifications to each enrolled student.
     *
     * @return void
     * @covers ::notificationsagent_engine_evaluate_rule
     */
    public function test_generic_rule_bootstrapnotifications_broadcasts_to_each_student(): void {
        global $DB;

        $otherstudent = $this->create_second_student();
        [$ruleid, $conditionid] = $this->create_engine_rule(self::GENERIC_COURSESTART_CONDITION, [self::BOOTSTRAP_ACTION]);

        $this->evaluate_engine($ruleid, $conditionid, notificationsagent::GENERIC_USERID, self::ENGINE_DATE);

        $this->assertCount(2, $DB->get_records('notificationsagent_report', ['ruleid' => $ruleid]));
        $bootstrap = $DB->get_records('notificationsagent_bootstrap', ['courseid' => self::$course->id]);
        $this->assertCount(2, $bootstrap);
        $bootstrapuserids = array_map(static fn(\stdClass $record): int => (int) $record->userid, $bootstrap);
        $this->assertEqualsCanonicalizing(
            [(int) self::$user->id, (int) $otherstudent->id],
            $bootstrapuserids
        );
        $launched = $DB->get_record('notificationsagent_launched', [
            'ruleid' => $ruleid,
            'courseid' => self::$course->id,
            'userid' => notificationsagent::GENERIC_USERID,
        ]);
        $this->assertNotFalse($launched);
    }

    /**
     * Generic rules broadcast addusergroup to each enrolled student.
     *
     * @return void
     * @covers ::notificationsagent_engine_evaluate_rule
     */
    public function test_generic_rule_addusergroup_enrols_each_student(): void {
        global $DB;

        $otherstudent = $this->create_second_student();
        $group = $this->create_course_group();
        [$ruleid, $conditionid] = $this->create_engine_rule(self::GENERIC_COURSESTART_CONDITION, [[
            'pluginname' => 'addusergroup',
            'params' => json_encode(['cmid' => $group->id]),
        ]]);

        $this->evaluate_engine($ruleid, $conditionid, notificationsagent::GENERIC_USERID, self::ENGINE_DATE);

        $this->assertCount(2, $DB->get_records('notificationsagent_report', ['ruleid' => $ruleid]));
        $members = groups_get_members($group->id, 'u.id');
        $this->assertCount(2, $members);
        $this->assertArrayHasKey((int) self::$user->id, $members);
        $this->assertArrayHasKey((int) $otherstudent->id, $members);
    }

    /**
     * Hybrid rules with mixed actions create one targeted and one broadcast report per student.
     *
     * @return void
     * @covers ::notificationsagent_engine_evaluate_rule
     */
    public function test_hybrid_rule_mixed_actions_create_expected_reports(): void {
        global $DB;

        $this->create_second_student();
        [$ruleid, $conditionid] = $this->create_engine_rule(self::HYBRID_CONDITIONS, [
            [
                'pluginname' => 'usermessageagent',
                'params' => '{"title":"Title","message":{"text":"Targeted message"},"user":"104000"}',
            ],
            self::MESSAGE_ACTION,
        ]);

        unset_config('noemailever');
        $sink = $this->redirectMessages();
        $this->evaluate_engine($ruleid, $conditionid, notificationsagent::GENERIC_USERID, self::ENGINE_DATE);
        $sink->close();

        $this->assertCount(3, $DB->get_records('notificationsagent_report', ['ruleid' => $ruleid]));
        $this->assertCount(3, $sink->get_messages());
        $launched = $DB->get_records('notificationsagent_launched', ['ruleid' => $ruleid]);
        $this->assertCount(2, $launched);
    }

    /**
     * Generic weekend rules broadcast messageagent to each enrolled student.
     *
     * @return void
     * @covers ::notificationsagent_engine_evaluate_rule
     */
    public function test_generic_weekend_rule_broadcasts_message_to_each_student(): void {
        global $DB;

        $this->create_second_student();
        [$ruleid, $conditionid] = $this->create_engine_rule(self::GENERIC_WEEKEND_CONDITION, [self::MESSAGE_ACTION]);

        unset_config('noemailever');
        $sink = $this->redirectMessages();
        $this->evaluate_engine($ruleid, $conditionid, notificationsagent::GENERIC_USERID, self::WEEKEND_DATE);
        $sink->close();

        $this->assertCount(2, $DB->get_records('notificationsagent_report', ['ruleid' => $ruleid]));
        $this->assertCount(2, $sink->get_messages());
    }
}
