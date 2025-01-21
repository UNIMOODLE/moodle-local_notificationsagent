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
 * @package    notificationscondition_usergroupadd
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace notificationscondition_usergroupadd;

use local_notificationsagent\evaluationcontext;
use local_notificationsagent\form\editrule_form;
use local_notificationsagent\helper\test\mock_base_logger;
use local_notificationsagent\helper\test\phpunitutil;
use local_notificationsagent\notificationplugin;
use local_notificationsagent\rule;

/**
 * Tests for the usergroupadd observer condition.
 *
 * @group notificationsagent
 */
final class usergroupadd_observer_test extends \advanced_testcase {
    /**
     * @var rule
     */
    private static $rule;
    /**
     * @var usergroupadd
     */
    private static $subplugin;
    /**
     * @var \stdClass
     */
    private static $course;
    /**
     * @var string
     */
    private static $subtype;
    /**
     * @var \stdClass
     */
    private static $user;
    /**
     * @var evaluationcontext
     */
    private static $context;
    /**
     * @var bool|\context|\context_course
     */
    private static $coursecontext;
    /**
     * @var array|string[]
     */
    private static $elements;
    /**
     * @var string
     */
    private static $role;
    /**
     * @var \stdClass
     */
    private static $group;
    /**
     * @var \stdClass
     */
    private static $activity;
    /**
     * id for condition
     */
    public const CONDITIONID = 1;
    /**
     * Date start for the course
     */
    public const COURSE_DATESTART = 1704099600; // 01/01/2024 10:00:00.
    /**
     * Date end for the course
     */
    public const COURSE_DATEEND = 1706605200; // 30/01/2024 10:00:00,

    /**
     * Set up the test environment.
     */
    public function setUp(): void {
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
        self::$group = $this->getDataGenerator()->create_group(['courseid' => self::$course->id]);
        self::$activity = self::getDataGenerator()->create_module('assign', ['course' => self::$course->id]);
        self::$user = self::getDataGenerator()->create_user();
    }

    /**
     * Check if the user has been added into a group.
     *
     * @param string $role Role name
     *
     * @covers       \notificationscondition_usergroupadd_observer::group_member_added
     * @dataProvider dataprovider
     */
    public function test_execute($role): void {
        global $DB, $USER;
        $pluginname = usergroupadd::NAME;

        self::$user = self::getDataGenerator()->create_and_enrol(self::$course, $role);
        self::setUser(self::$user->id);

        $dataform = new \StdClass();
        $dataform->title = "Rule Test";
        $dataform->type = 1;
        $dataform->courseid = self::$course->id;
        $dataform->timesfired = 2;
        $dataform->runtime_group = ['runtime_days' => 5, 'runtime_hours' => 0, 'runtime_minutes' => 0, 'runtime_seconds' => 30];
        $USER->id = self::$user->id;
        $ruleid = self::$rule->create($dataform);
        self::$rule->set_id($ruleid);

        $objparameters = new \stdClass();
        $objparameters->cmid = self::$group->id;

        $objcondition = new \stdClass();
        $objcondition->ruleid = self::$rule->get_id();
        $objcondition->courseid = self::$course->id;
        $objcondition->type = 'condition';
        $objcondition->pluginname = $pluginname;
        $objcondition->parameters = json_encode($objparameters);
        $objcondition->cmid = self::$group->id;

        $conditionid = $DB->insert_record('notificationsagent_condition', $objcondition);

        $this->assertIsInt($conditionid);
        self::$rule::create_instance($ruleid);
        self::$rule->set_createdby(self::$user->id);
        $this->getDataGenerator()->create_group_member(['userid' => self::$user->id, 'groupid' => self::$group->id]);
        $event = \core\event\group_member_added::create([
                'context' => \context_course::instance(self::$course->id),
                'userid' => self::$user->id,
                'courseid' => self::$course->id,
                'objectid' => self::$group->id,
                'relateduserid' => self::$user->id,
                'other' => [
                        'modulename' => 'assign',
                        'instanceid' => self::$activity->cmid,
                        'name' => self::$activity->name,
                        'component' => '',
                        'itemid' => '',
                ],
        ]);
        $event->trigger();

        $trigger = $DB->get_record('notificationsagent_triggers', ['conditionid' => $conditionid]);
        $cache = $DB->get_record('notificationsagent_cache', ['conditionid' => $conditionid]);
        $this->assertInstanceOf('\core\event\group_member_added', $event);
        $this->assertEquals($cache->pluginname, $pluginname);
        $this->assertEquals($cache->userid, self::$user->id);
        $this->assertEquals($cache->courseid, self::$course->id);

        $this->assertEquals($trigger->ruleid, $ruleid);
        $this->assertEquals($trigger->userid, self::$user->id);
        $this->assertEquals($trigger->courseid, self::$course->id);
        $this->assertTrue(self::$rule->can_delete());
        self::$rule->reject_share_rule($ruleid);
        $this->assertEquals(self::$rule->get_default_context(), self::$course->id);
        self::$rule->before_delete();
    }

    /**
     * Check if the group has been deleted.
     *
     * @covers       \notificationscondition_usergroupadd_observer::group_deleted
     */
    public function test_group_deleted(): void {
        global $DB;
        $pluginname = usergroupadd::NAME;

        self::setUser(2);// Admin.

        $dataform = new \StdClass();
        $dataform->title = "Rule Test";
        $dataform->type = 1;
        $dataform->courseid = self::$course->id;
        $dataform->timesfired = 2;
        $dataform->runtime_group = ['runtime_days' => 5, 'runtime_hours' => 0, 'runtime_minutes' => 0, 'runtime_seconds' => 30];
        $ruleid = self::$rule->create($dataform);
        self::$rule->set_id($ruleid);

        $objparameters = new \stdClass();
        $objparameters->cmid = self::$group->id;

        $objcondition = new \stdClass();
        $objcondition->ruleid = self::$rule->get_id();
        $objcondition->courseid = self::$course->id;
        $objcondition->type = 'condition';
        $objcondition->pluginname = $pluginname;
        $objcondition->parameters = json_encode($objparameters);
        $objcondition->cmid = self::$group->id;

        $conditionid = $DB->insert_record('notificationsagent_condition', $objcondition);
        $this->assertIsInt($conditionid);

        groups_delete_group(self::$group->id);

        $rule = self::$rule::create_instance($ruleid);
        $this->assertEquals(rule::PAUSE_RULE, $rule->get_status());
    }

    /**
     * Set up the data to be used in the test execution.
     *
     * @return array
     */
    public static function dataprovider(): array {
        return [
                'Testing as a student' => ['teacher'],
                'Testing as a teacher' => ['student'],
                'Testing as a editingteacher' => ['editingteacher'],
        ];
    }
}
