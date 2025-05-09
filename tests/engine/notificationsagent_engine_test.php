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
use local_notificationsagent\rule;

/**
 * Testing notificationsagent engine class
 *
 * @group notificationsagent
 */
class notificationsagent_engine_test extends \advanced_testcase {
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
     * Settin up test context
     *
     * @return void
     * @throws \coding_exception
     */
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
        self::getDataGenerator()->create_user_course_lastaccess(self::$user, self::$course, self::USER_LASTACCESS);

        $quizgenerator = self::getDataGenerator()->get_plugin_generator('mod_quiz');
        self::$cmteste = $quizgenerator->create_instance([
                'name' => 'Quiz unittest',
                'course' => self::$course->id,
                "timeopen" => self::CM_DATESTART,
                "timeclose" => self::CM_DATEEND,
        ]);

        self::getDataGenerator()->enrol_user(self::$user->id, self::$course->id);
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
     * @param bool $genericuser
     * @param bool $expected
     *
     * @return void
     * @covers       ::notificationsagent_engine_evaluate_rule
     */
    public function test_notificationsagent_engine_evaluate_rule(
        int $date,
        array $conditiondata,
        array $exceptiondata,
        array $actiondata,
        int $genericuser,
        bool $expected
    ) {
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
            foreach ($launched as $launch) {
                $this->assertEquals($launch->ruleid, self::$rule->get_id());
                $this->assertEquals($launch->courseid, self::$course->id);
                $this->assertEquals($launch->userid, ($genericuser == 2 || $genericuser == 3) ? self::$user->id : -1);
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
                ],

                'Gen7' => [
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
                ],
        ];
    }
}
