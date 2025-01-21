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

namespace local_notificationsagent\task;

use local_notificationsagent\rule;
use local_notificationsagent\evaluationcontext;
use local_notificationsagent\notificationplugin;

/**
 * Testing trigger cron test
 *
 * @group notificationsagent
 */
final class notificationsagent_trigger_cron_test extends \advanced_testcase {
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
    private static $cmtesttc;
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
        self::getDataGenerator()->create_user_course_lastaccess(self::$user, self::$course, self::USER_LASTACCESS);

        $quizgenerator = self::getDataGenerator()->get_plugin_generator('mod_quiz');
        self::$cmtesttc = $quizgenerator->create_instance([
                'name' => 'Quiz unittest',
                'course' => self::$course->id,
                "timeopen" => self::CM_DATESTART,
                "timeclose" => self::CM_DATEEND,
        ]);

        self::getDataGenerator()->enrol_user(self::$user->id, self::$course->id);
    }

    /**
     * Testing execute methd of
     *
     * @param int $date
     * @param array $conditiondata
     * @param array $exceptiondata
     * @param array $actiondata
     * @param bool $genericuser
     * @param bool $expected
     *
     * @return void
     * @dataProvider dataprovider_cron
     * @covers       \local_notificationsagent\task\notificationsagent_trigger_cron::execute
     * @covers       \local_notificationsagent\helper\helper::custom_mtrace
     */

    public function test_execute(
        int $date,
        array $conditiondata,
        array $exceptiondata,
        array $actiondata,
        bool $genericuser,
        bool $expected
    ): void {
        global $DB, $USER;

        $conditions = [];
        $conditionid = 1;
        $exceptions = [];
        $dataform = new \StdClass();
        $dataform->title = "Rule Test";
        $dataform->type = 1;
        $dataform->courseid = self::$course->id;
        $dataform->timesfired = 2;
        $dataform->runtime_group = ['runtime_days' => 5, 'runtime_hours' => 0, 'runtime_minutes' => 0];
        $USER->id = self::$user->id;
        $ruleid = self::$rule->create($dataform);
        self::$rule->set_id($ruleid);
        self::$cmtesttc->cmid = self::$cmtesttc->id;
        $userid = $genericuser ? -1 : self::$user->id;
        $courseid = self::$course->id;

        // Context.
        $context = new evaluationcontext();
        $context->set_userid($userid);
        $context->set_courseid($courseid);
        $context->set_timeaccess($date);

        foreach ($conditiondata as $condition) {
            // Conditions.
            $objdb = new \stdClass();
            $objdb->ruleid = $ruleid;
            $objdb->courseid = $courseid;
            $objdb->type = 'condition';
            $objdb->pluginname = $condition['pluginname'];
            $objdb->parameters = $condition['params'];
            $objdb->cmid = self::$cmtesttc->id;
            // Insert.
            $conditionid = $DB->insert_record('notificationsagent_condition', $objdb);
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
            $objdb->cmid = self::$cmtesttc->id;
            $objdb->complementary = notificationplugin::COMPLEMENTARY_EXCEPTION;
            // Insert.
            $DB->insert_record('notificationsagent_condition', $objdb);
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
            $DB->insert_record('notificationsagent_action', $objdb);
        }

        self::$rule::create_instance($ruleid);

        $DB->insert_record(
            'notificationsagent_triggers',
            [
                        'ruleid' => self::$rule->get_id(),
                        'conditionid' => $conditionid,
                        'courseid' => self::$course->id,
                        'userid' => $userid,
                        'startdate' => $date,
                ]
        );

        $task = \core\task\manager::get_scheduled_task(notificationsagent_trigger_cron::class);
        $task->set_timestarted($date);
        $task->execute();

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
    }

    /**
     * Provider for cron trigger
     *
     * @return array[]
     */
    public static function dataprovider_cron(): array {
        return [
                [ // ACCION A UNO.
                        1706173200,
                        [
                                ['pluginname' => 'sessionend', 'params' => '{"time":864001}'],
                        ],
                        [['pluginname' => '', 'params' => '']],
                        [
                                [
                                        'pluginname' => 'messageagent',
                                        'params' => '{
                        "title":"Title" ,"message":{"text":"Message to {User_FirstName}"}
                        }',
                                ],
                        ],
                        false,
                        true,
                ],
                [ // ACCION A TODOS.
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
                        "title":"Title" ,"message":{"text":"Message to {User_FirstName}"}
                        }',
                                ],
                        ],
                        true,
                        true,
                ],
                [
                        1706173200,
                        [
                                ['pluginname' => 'sessionend', 'params' => '{"time":864001}'],
                        ],
                        [['pluginname' => '', 'params' => '']],
                        [
                                [
                                        'pluginname' => 'messageagent',
                                        'params' => '{
                        "title":"Title" ,"message":{"text":"Message to {User_FirstName}"}
                        }',
                                ],
                        ],
                        true,
                        true,
                ],
                [
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
                        "title":"Title" ,"message":{"text":"Message to {User_FirstName}"}
                        }',
                                ],
                        ],
                        true,
                        true,
                ],
                [
                        1706173200,
                        [
                                ['pluginname' => 'coursestart', 'params' => '{"time":864001}'],

                        ],
                        [['pluginname' => '', 'params' => '']],
                        [
                                [
                                        'pluginname' => 'messageagent',
                                        'params' => '{
                        "title":"Title" ,"message":{"text":"Message to {User_FirstName}"}
                        }',
                                ],
                        ],
                        true,
                        true,
                ],
                [
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
                        "title":"Title" ,"message":{"text":"Message to {User_FirstName}"}
                        }',
                                ],
                        ],
                        true,
                        true,
                ],
                [
                        1706173200,
                        [
                                ['pluginname' => 'coursestart', 'params' => '{"time":86400000}'],

                        ],
                        [['pluginname' => '', 'params' => '']],
                        [
                                [
                                        'pluginname' => 'messageagent',
                                        'params' => '{
                        "title":"Title" ,"message":{"text":"Message to {User_FirstName}"}
                        }',
                                ],
                        ],
                        true,
                        false,
                ],
        ];
    }

    /**
     * Get name test
     *
     * @covers \local_notificationsagent\task\notificationsagent_trigger_cron::get_name
     * @return void
     */
    public function test_get_name(): void {
        $task = \core\task\manager::get_scheduled_task(notificationsagent_trigger_cron::class);

        $this->assertIsString($task->get_name());
    }
}
