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

use local_notificationsagent\helper\helper;

/**
 * Testing rule class
 *
 * @group notificationsagent
 */
final class notificationsagent_rule_test extends \advanced_testcase {
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
    private static $cmtest;
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
    public const USER_LASTACCESS = 1706605200;
    /**
     *  Random id for activity
     */
    public const CMID = 246000;

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
        self::$cmtest = $quizgenerator->create_instance([
                'name' => 'Quiz unittest',
                'course' => self::$course->id,
                "timeopen" => self::CM_DATESTART,
                "timeclose" => self::CM_DATEEND,
        ]);
    }

    /**
     * Testing evaluate function
     *
     * @covers       \local_notificationsagent\rule::evaluate
     * @covers       \local_notificationsagent\rule::set_conditions
     * @covers       \local_notificationsagent\rule::set_exceptions
     * @covers       \local_notificationsagent\rule::get_conditions
     * @dataProvider dataprovider
     *
     * @param int $date
     * @param array $conditiondata
     * @param array $exceptiondata
     * @param bool $expected
     *
     * @return void
     * @throws \dml_exception
     */
    public function test_evaluate(int $date, array $conditiondata, array $exceptiondata, bool $expected): void {
        global $DB, $USER;

        $dataform = new \StdClass();
        $dataform->title = "Rule Test";
        $dataform->type = 1;
        $dataform->courseid = self::$course->id;
        $dataform->timesfired = 2;
        $dataform->runtime_group = ['runtime_days' => 5, 'runtime_hours' => 0, 'runtime_minutes' => 0];
        $USER->id = self::$user->id;
        $ruleid = self::$rule->create($dataform);
        self::$rule->set_id($ruleid);
        self::$cmtest->cmid = self::CMID;
        $userid = self::$user->id;
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
            $objdb->cmid = self::CMID;
            // Insert.
            $conditionid = $DB->insert_record('notificationsagent_condition', $objdb);
            $context->set_triggercondition($conditionid);
        }

        foreach ($exceptiondata as $exception) {
            // Conditions.
            $objdb = new \stdClass();
            $objdb->ruleid = $ruleid;
            $objdb->courseid = $courseid;
            $objdb->type = 'condition';
            $objdb->pluginname = $exception['pluginname'];
            $objdb->parameters = $exception['params'];
            $objdb->cmid = self::CMID;
            $objdb->complementary = notificationplugin::COMPLEMENTARY_EXCEPTION;
            // Insert.
            $DB->insert_record('notificationsagent_condition', $objdb);
        }
        $instance = self::$rule::create_instance($ruleid);
        $conditions = $instance->get_conditions();
        $this->assertNotEmpty($instance->get_conditions($conditiondata[0]['pluginname']));
        $exceptions = $instance->get_exceptions();
        self::$rule->set_conditions($conditions);
        self::$rule->set_exceptions($exceptions);
        $result = self::$rule->evaluate($context);

        $this->assertSame($expected, $result);
    }

    /**
     * Provider for evaluate
     *
     * @return array[]
     */
    public static function dataprovider(): array {
        return [
            // Evaluate date, conditions, exceptions, expected.
                [
                        1704186000, [['pluginname' => 'coursestart', 'params' => '{"time":864000}']],
                        [['pluginname' => '', 'params' => '']], false,
                ],
                [
                        1705050000, [['pluginname' => 'coursestart', 'params' => '{"time":864000}']],
                        [['pluginname' => '', 'params' => '']], true,
                ],
                [
                        1705050000, [['pluginname' => 'courseend', 'params' => '{"time":864000}']],
                        [['pluginname' => '', 'params' => '']],
                        false,
                ],
                [
                        1706173200, [['pluginname' => 'courseend', 'params' => '{"time":864000}']],
                        [['pluginname' => '', 'params' => '']],
                        true,
                ],
                [
                        1706173200,
                        [
                                ['pluginname' => 'coursestart', 'params' => '{"time":864000}'],
                                ['pluginname' => 'courseend', 'params' => '{"time":864000}'],
                        ],
                        [['pluginname' => '', 'params' => '']], true,
                ],
                [
                        1706173200,
                        [
                                ['pluginname' => 'coursestart', 'params' => '{"time":864000}'],
                                ['pluginname' => 'courseend', 'params' => '{"time":864000}'],
                        ],
                        [['pluginname' => 'sessionend', 'params' => '{"time":864000}']], true,
                ],
                [
                        1708851600, [['pluginname' => 'sessionend', 'params' => '{"time":86400}']],
                        [['pluginname' => '', 'params' => '']],
                        true,
                ],
                [
                        1708851600, [['pluginname' => 'sessionend', 'params' => '{"time":86400}']],
                        [['pluginname' => 'coursestart', 'params' => '{"time":86400}']], false,
                ],
                [
                        1706173200,
                        [
                                ['pluginname' => 'coursestart', 'params' => '{"time":864000}'],
                        ],
                        [
                                ['pluginname' => 'courseend', 'params' => '{"time":864000}'],
                        ], false,
                ],
        ];
    }

    /**
     * @covers \local_notificationsagent\rule::create
     * @covers \local_notificationsagent\rule::create_instance
     * @covers \local_notificationsagent\rule::get_exceptions
     * @covers \local_notificationsagent\rule::get_actions
     * @covers \local_notificationsagent\rule::set_createdby
     * @covers \local_notificationsagent\rule::get_template
     * @covers \local_notificationsagent\rule::get_name
     * @covers \local_notificationsagent\rule::get_id
     * @covers \local_notificationsagent\rule::set_default_context
     * @covers \local_notificationsagent\rule::get_default_context
     * @covers \local_notificationsagent\rule::get_conditions
     * @covers \local_notificationsagent\rule::get_condition
     * @covers \local_notificationsagent\helper\helper::build_category_array
     * @covers \local_notificationsagent\helper\helper::build_output_categories
     * @covers \local_notificationsagent\helper\helper::count_category_courses
     * @covers \local_notificationsagent\rule::get_moodle_url
     *
     */

    /**
     * Testing rule create
     *
     * @covers \local_notificationsagent\rule::create
     *
     * @return void
     * @throws \dml_exception
     */
    public function test_create(): void {
        global $DB, $USER;
        $USER->id = self::$user->id;
        // Simulate data from form.
        $dataform = new \StdClass();
        $dataform->title = "Rule Test";
        $dataform->type = 1;
        $dataform->courseid = self::$course->id;
        $dataform->timesfired = 2;
        $dataform->runtime_group = ['runtime_days' => 5, 'runtime_hours' => 0, 'runtime_minutes' => 0];

        $ruleid = self::$rule->create($dataform);
        // Conditions.
        $DB->insert_record(
            'notificationsagent_condition',
            [
                        'ruleid' => $ruleid, 'type' => 'condition', 'complementary' => notificationplugin::COMPLEMENTARY_CONDITION,
                        'parameters' => '{"time":300,"forum":3}',
                        'pluginname' => 'forumnoreply',
                ],
        );
        $DB->insert_record(
            'notificationsagent_condition',
            [
                        'ruleid' => $ruleid, 'type' => 'condition', 'complementary' => notificationplugin::COMPLEMENTARY_EXCEPTION,
                        'parameters' => '{"time":300}',
                        'pluginname' => 'coursestart',
                ],
        );

        $DB->insert_record(
            'notificationsagent_action',
            [
                        'ruleid' => $ruleid, 'type' => 'action', 'pluginname' => 'messageagent',
                        'parameters' => '{"title":"Friday - {Current_time}","message":" It is friday."}',
                ],
        );

        $instance = self::$rule::create_instance($ruleid);

        $this->assertInstanceOf(rule::class, $instance);
        $this->assertIsNumeric($ruleid);
        $this->assertGreaterThan(0, $ruleid);
        $this->assertSame('Rule Test', $instance->get_name());
        $this->assertSame('1', $instance->get_template());
        $this->assertSame($USER->id, $instance->get_createdby());
        $this->assertEquals(self::$rule->get_id(), $instance->get_id());
        $this->assertNotEmpty($instance->get_conditions());
        $this->assertNotEmpty($instance->get_conditions('forumnoreply'));
        $this->assertNotEmpty($instance->get_condition('forumnoreply'));
        $this->assertNotEmpty($instance->get_conditions_to_evaluate());
        $this->assertNotEmpty($instance->get_exceptions());
        $this->assertNotEmpty($instance->get_actions());
        $this->assertSame(self::$course->id, $instance->get_default_context());

        // Find optimal file where insert this (lib.php functions).
        $categories = helper::build_category_array(\core_course_category::get(self::$course->category), self::$rule->get_id());
        $this->assertIsArray($categories);
        $outputcategories = helper::build_output_categories([$categories]);
        $this->assertIsString($outputcategories);
        $this->assertGreaterThan(0, strlen($outputcategories));
        $this->assertNotNull(\local_notificationsagent\helper\helper::get_module_url(self::$course->id, self::$cmtest->cmid));
    }

    /**
     * Testing rule update
     *
     * @param int $timesfired
     * @param bool $expected
     *
     * @covers       \local_notificationsagent\rule::update
     * @covers       \local_notificationsagent\rule::get_timesfired
     * @covers       \local_notificationsagent\rule::get_runtime
     * @covers       \local_notificationsagent\rule::get_createdby
     * @covers       \local_notificationsagent\rule::get_name
     * @dataProvider updateprovider
     *
     */
    public function test_update($timesfired, $expected): void {
        self::setUser(self::$user->id);
        // Simulate data from form.
        $dataform = new \StdClass();
        $dataform->title = "Rule Test";
        $dataform->type = 1;
        $dataform->courseid = self::$course->id;
        $dataform->timesfired = 2;
        $dataform->runtime_group = ['runtime_days' => 5, 'runtime_hours' => 0, 'runtime_minutes' => 0];

        $ruleid = self::$rule->create($dataform);

        $instance = self::$rule::create_instance($ruleid);

        // Simulate data from edit form.
        $dataupdate = new \StdClass();
        $dataupdate->title = "Rule Test update";
        $dataupdate->type = 1;
        $dataupdate->courseid = self::$course->id;
        $dataupdate->timesfired = $timesfired;
        $dataupdate->runtime_group = ['runtime_days' => 1, 'runtime_hours' => 0, 'runtime_minutes' => 0];

        // Test update.
        $instance->update($dataupdate);

        $this->assertSame('Rule Test update', $instance->get_name());
        $this->assertSame($expected, $instance->get_timesfired());
        $this->assertSame(86400, $instance->get_runtime());
        $this->assertSame(self::$user->id, $instance->get_createdby());
    }

    /**
     * Provider for update rule
     *
     * @return array[]
     */
    public static function updateprovider(): array {
        return [
                [18, 18],
                [0, 1],
                [null, 1],
        ];
    }

    /**
     * Testing delete rule
     *
     * @covers \local_notificationsagent\rule::before_delete
     * @covers \local_notificationsagent\rule::delete
     * @covers \local_notificationsagent\rule::delete_conditions
     * @covers \local_notificationsagent\rule::delete_actions
     * @covers \local_notificationsagent\rule::delete_context
     * @covers \local_notificationsagent\rule::delete_launched
     * @covers \local_notificationsagent\rule::delete_cache
     * @covers \local_notificationsagent\rule::delete_triggers
     * @return void
     */
    public function test_delete(): void {
        global $DB, $USER;
        $USER->id = self::$user->id;
        // Simulate data from form.
        $dataform = new \StdClass();
        $dataform->title = "Rule Test";
        $dataform->type = 1;
        $dataform->courseid = self::$course->id;
        $dataform->timesfired = 2;
        $dataform->runtime_group = ['runtime_days' => 5, 'runtime_hours' => 0, 'runtime_minutes' => 0];

        $ruleid = self::$rule->create($dataform);

        // Conditions.
        $conditionid = $DB->insert_record(
            'notificationsagent_condition',
            [
                        'ruleid' => $ruleid,
                        'type' => 'condition',
                        'complementary' => notificationplugin::COMPLEMENTARY_CONDITION,
                        'parameters' => '{"time":300,"forum":3}',
                        'pluginname' => 'forumnoreply',
                ],
        );
        $this->assertIsInt($conditionid);
        $condition2 = $DB->insert_record(
            'notificationsagent_condition',
            [
                        'ruleid' => $ruleid, 'type' => 'condition', 'complementary' => notificationplugin::COMPLEMENTARY_EXCEPTION,
                        'parameters' => '{"time":300}',
                        'pluginname' => 'coursestart',
                ],
        );
        $this->assertIsInt($condition2);
        $action = $DB->insert_record(
            'notificationsagent_action',
            [
                        'ruleid' => $ruleid, 'type' => 'action', 'pluginname' => 'messageagent',
                        'parameters' => '{"title":"Friday - {Current_time}","message":" It is friday."}',
                ],
        );
        $this->assertIsInt($action);
        $DB->insert_record(
            'notificationsagent_cache',
            [
                        'ruleid' => $ruleid,
                        'pluginname' => 'forumnoreply',
                        'courseid' => self::$course->id,
                        'userid' => self::$user->id,
                        'startdate' => time(),
                        'conditionid' => $conditionid,
                ],
        );

        $cacheid = $DB->insert_record(
            'notificationsagent_cache',
            [
                        'ruleid' => $ruleid,
                        'courseid' => self::$course->id,
                        'userid' => self::$user->id,
                        'startdate' => time(),
                        'conditionid' => $conditionid,
                ],
        );
        $this->assertIsInt($cacheid);
        $launched = $DB->insert_record(
            'notificationsagent_launched',
            [
                        'ruleid' => $ruleid, 'courseid' => self::$course->id,
                        'userid' => self::$user->id,
                        'timesfired' => 2, 'timecreated' => time(),
                        'timemodified' => time(),
                ],
        );
        $this->assertIsInt($launched);
        $contexid = $DB->insert_record(
            'notificationsagent_context',
            [
                        'ruleid' => $ruleid, 'contextid' => 50,
                        'objectid' => 2,
                ],
        );
        $this->assertIsInt($contexid);
        $instance = self::$rule::create_instance($ruleid);

        $this->assertInstanceOf(rule::class, $instance);
        $delete = $instance->delete();
        $this->assertTrue($delete);

        $cache = $DB->get_record('notificationsagent_cache', ['conditionid' => $conditionid]);
        $trigger = $DB->get_record('notificationsagent_triggers', ['conditionid' => $conditionid]);
        $launched = $DB->get_record('notificationsagent_launched', ['ruleid' => self::$rule->get_id()]);
        $context = $DB->get_record('notificationsagent_launched', ['ruleid' => self::$rule->get_id()]);
        $conditions = $DB->get_record('notificationsagent_condition', ['ruleid' => self::$rule->get_id()]);
        $actions = $DB->get_record('notificationsagent_action', ['ruleid' => self::$rule->get_id()]);
        $rule = $DB->get_record('notificationsagent_rule', ['id' => self::$rule->get_id()]);

        $this->assertFalse($launched);
        $this->assertFalse($cache);
        $this->assertFalse($trigger);
        $this->assertFalse($conditions);
        $this->assertNotEmpty($actions);
        $this->assertFalse($context);
        $this->assertNotEmpty($rule);
        $this->assertEquals(1, $rule->deleted);
    }

    /**
     *  Testing get rules
     *
     * @param string $role
     * @param int $shared
     * @param int $siteid
     *
     * @covers       \local_notificationsagent\rule::get_rules_index
     * @covers       \local_notificationsagent\rule::get_shared_rules
     * @covers       \local_notificationsagent\rule::set_shared
     * @covers       \local_notificationsagent\rule::get_course_rules
     * @covers       \local_notificationsagent\rule::get_site_rules
     * @covers       \local_notificationsagent\rule::get_dataform
     * @covers       \local_notificationsagent\rule::has_context
     * @covers       \local_notificationsagent\rule::get_owner_rules_by_course
     * @covers       \local_notificationsagent\rule::get_owner_rules
     * @covers       \local_notificationsagent\rule::get_default_context
     * @covers       \local_notificationsagent\rule::get_course_rules_forced
     * @covers       \local_notificationsagent\rule::get_rules_assign
     * @covers       \local_notificationsagent\rule::get_assignedcontext
     * @dataProvider dataprovider_get_rules
     *
     * @return void
     * @throws \dml_exception
     */
    public function test_get_rules($role, $shared, $siteid): void {
        global $USER, $DB;
        if (empty($role)) {
            self::setAdminUser();
        } else {
            $user = self::getDataGenerator()->create_and_enrol(self::$course, $role);
            $USER->id = $user->id;
        }

        $siteid === 0 ? $courseid = self::$course->id : $courseid = SITEID;

        $context = \context_course::instance($courseid);
        $dataform = new \StdClass();
        $dataform->title = "Rule Test " . $role;
        $dataform->type = 1;
        $dataform->courseid = $courseid;
        $dataform->timesfired = 2;
        $dataform->runtime_group = ['runtime_days' => 5, 'runtime_hours' => 0, 'runtime_minutes' => 0];

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

        $assignedcontext = self::$rule->get_assignedcontext();
        $this->assertIsNumeric($assignedcontext['course'][0]);
        $this->assertGreaterThan(0, $assignedcontext['course'][0]);
        $this->assertSame($assignedcontext['category'][0], self::$course->category);

        $rulecontext = self::$rule->has_context();
        $this->assertTrue($rulecontext);

        $instance = rule::create_instance($ruleid);
        $instance->set_shared($shared);
        $rules = $instance::get_rules_index($context, $courseid);

        $this->assertNotEmpty($rules);
        $this->assertTrue($instance->has_context());

        foreach ($rules as $ruleobj) {
            $this->assertEquals($ruleid, $ruleobj->get_id());
            $this->assertEquals("Rule Test " . $role, $ruleobj->get_name());
            $this->assertEquals($courseid, $ruleobj->get_default_context());
            $this->assertEquals("Rule Test " . $role, $ruleobj->get_dataform()['title']);
            $this->assertEquals(2, $ruleobj->get_dataform()['timesfired']);
            $this->assertTrue($ruleobj->has_context());
            $this->assertEquals($shared, $instance->get_shared());
        }
        $this->assertIsArray($instance->get_rules_assign($context, $courseid));
    }

    /**
     * Set up the data to be used in the test execution.
     *
     * @return array
     */
    public static function dataprovider_get_rules(): array {
        return [
                'Admin' => [null, 1, 0],
                'Admin shared rule' => [null, 0, 0],
                'Admin siteid courses' => [null, 0, 1],
                'Editingteacher' => ['editingteacher', 1, 0],
                'Student' => ['student', 1, 0],

        ];
    }

    /**
     * Testing clone of rule
     *
     * @covers \local_notificationsagent\rule::clone
     * @covers \local_notificationsagent\rule::clone_conditions
     * @covers \local_notificationsagent\rule::clone_actions
     *
     * @return void
     */
    public function test_clone(): void {
        global $DB, $USER;
        $USER->id = self::$user->id;
        // Simulate data from form.
        $dataform = new \StdClass();
        $dataform->title = "Rule Test";
        $dataform->type = 1;
        $dataform->courseid = self::$course->id;
        $dataform->timesfired = 2;
        $dataform->runtime_group = ['runtime_days' => 5, 'runtime_hours' => 0, 'runtime_minutes' => 0];

        $ruleid = self::$rule->create($dataform);

        // Conditions.
        $conditionid = $DB->insert_record(
            'notificationsagent_condition',
            [
                        'ruleid' => $ruleid, 'type' => 'condition', 'complementary' => notificationplugin::COMPLEMENTARY_CONDITION,
                        'parameters' => '{"time":300,"forum":3}',
                        'pluginname' => 'forumnoreply',
                ],
        );
        // Actions.
        $actionid = $DB->insert_record(
            'notificationsagent_action',
            [
                        'ruleid' => $ruleid, 'type' => 'action', 'pluginname' => 'messageagent',
                        'parameters' => '{"title":"Friday - {Current_time}","message":" It is friday."}',
                ],
        );

        $instance = self::$rule::create_instance($ruleid);
        $this->assertInstanceOf(rule::class, $instance);

        $instance->clone($instance->get_id());

        $clonedrule = $DB->get_records('notificationsagent_rule', null, 'id', '*', 1);
        $ind = key($clonedrule);
        $clonedcond = $DB->get_records('notificationsagent_condition', null, 'id', '*', 1);
        $indcon = key($clonedcond);
        $clonedact = $DB->get_records('notificationsagent_action', null, 'id', '*', 1);
        $indact = key($clonedact);

        $this->assertNotEquals($instance->get_id(), $clonedrule[$ind]->id);
        $this->assertEquals($instance->get_name(), $clonedrule[$ind]->name);
        $this->assertEquals($instance->get_status(), $clonedrule[$ind]->status);
        $this->assertEquals($instance->get_shared(), $clonedrule[$ind]->shared);
        $this->assertEquals($instance->get_defaultrule(), $clonedrule[$ind]->defaultrule);

        $this->assertNotEquals($instance->get_conditions()[$conditionid]->get_id(), $clonedcond[$indcon]->id);
        $this->assertEquals($instance->get_conditions()[$conditionid]->get_pluginname(), $clonedcond[$indcon]->pluginname);
        $this->assertEquals($instance->get_conditions()[$conditionid]->get_type(), $clonedcond[$indcon]->type);
        $this->assertEquals($instance->get_conditions()[$conditionid]->get_parameters(), $clonedcond[$indcon]->parameters);
        $this->assertEquals($instance->get_conditions()[$conditionid]->get_iscomplementary(), $clonedcond[$indcon]->complementary);

        $this->assertNotEquals($instance->get_actions()[$actionid]->get_id(), $clonedact[$indact]->id);
        $this->assertEquals($instance->get_actions()[$actionid]->get_pluginname(), $clonedact[$indact]->pluginname);
        $this->assertEquals($instance->get_actions()[$actionid]->get_type(), $clonedact[$indact]->type);
        $this->assertEquals($instance->get_actions()[$actionid]->get_parameters(), $clonedact[$indact]->parameters);
    }

    /**
     * Testing categories output
     *
     * @covers       \local_notificationsagent\helper\helper::build_category_array
     * @covers       \local_notificationsagent\helper\helper::build_output_categories
     */
    public function test_build_output_categories(): void {
        self::setUser(self::$user->id);
        // Simulate data from form.

        $dataform = new \StdClass();
        $dataform->title = "Rule Test";
        $dataform->type = 1;
        $dataform->courseid = self::$course->id;
        $dataform->timesfired = 2;
        $dataform->runtime_group = ['runtime_days' => 5, 'runtime_hours' => 0, 'runtime_minutes' => 0];

        $categories = helper::build_category_array(\core_course_category::get(self::$course->category), self::$rule->get_id());
        $outputcategories = helper::build_output_categories([$categories]);

        $this->assertIsString($outputcategories);
        $this->assertGreaterThan(0, strlen($outputcategories));
        $this->assertNotNull(\local_notificationsagent\helper\helper::get_module_url(self::$course->id, self::$cmtest->cmid));
    }

    /**
     * Test count category courses
     *
     * @return void
     * @covers \local_notificationsagent\helper\helper::count_category_courses
     */
    public function test_count_category_courses(): void {
        $category = \core_course_category::get(self::$course->category);
        $cat = helper::count_category_courses($category);
        $this->assertEquals(1, $cat);
    }
}
