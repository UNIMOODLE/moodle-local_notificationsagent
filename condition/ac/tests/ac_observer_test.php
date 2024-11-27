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
 * @package    notificationscondition_ac
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace notificationscondition_ac;

use core\event\grouping_deleted;
use local_notificationsagent\evaluationcontext;
use local_notificationsagent\form\editrule_form;
use local_notificationsagent\helper\test\mock_base_logger;
use local_notificationsagent\helper\test\phpunitutil;
use local_notificationsagent\notificationplugin;
use local_notificationsagent\rule;

/**
 * Tests for the ac observer condition.
 *
 * @group notificationsagent
 */
class ac_observer_test extends \advanced_testcase {
    /**
     * @var rule
     */
    private static $rule;
    /**
     * @var \stdClass
     */
    private static $course;

    /**
     * @var \stdClass
     */
    private static $user;
    /**
     * @var \stdClass
     */
    private static $group;
    /**
     * @var \stdClass
     */
    private static $grouping;
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
        self::$grouping = $this->getDataGenerator()->create_grouping(['courseid' => self::$course->id]);
        $this->getDataGenerator()->create_grouping_group(['groupingid' => self::$grouping->id, 'groupid' => self::$group->id]);
        self::$activity = self::getDataGenerator()->create_module('assign', ['course' => self::$course->id]);
        self::$user = self::getDataGenerator()->create_user();
    }

    /**
     * Check if the group has been deleted.
     *
     * @covers       \notificationscondition_ac_observer::group_deleted
     */
    public function test_group_deleted() {
        global $DB;
        $pluginname = ac::NAME;

        self::setUser(2);// Admin.

        $dataform = new \StdClass();
        $dataform->title = "Rule Test";
        $dataform->type = 1;
        $dataform->courseid = self::$course->id;
        $dataform->timesfired = 2;
        $dataform->runtime_group = ['runtime_days' => 5, 'runtime_hours' => 0, 'runtime_minutes' => 0, 'runtime_seconds' => 30];
        $ruleid = self::$rule->create($dataform);
        self::$rule->set_id($ruleid);

        $json = '{"op":"&","c":[{"op":"&","c":[{"type":"group","id":' . self::$group->id .
                '}]},{"op":"!|","c":[]}],"showc":[true,true],"errors":["availability:error_list_nochildren"]}';
        $objcondition = new \stdClass();
        $objcondition->ruleid = self::$rule->get_id();
        $objcondition->courseid = self::$course->id;
        $objcondition->type = 'condition';
        $objcondition->pluginname = $pluginname;
        $objcondition->parameters = $json;
        $objcondition->cmid = null;

        $conditionid = $DB->insert_record('notificationsagent_condition', $objcondition);
        $this->assertIsInt($conditionid);

        groups_delete_group(self::$group->id);

        $rule = self::$rule::create_instance($ruleid);
        $this->assertEquals(rule::PAUSE_RULE, $rule->get_status());
    }

    /**
     * Check if the grouping has been deleted.
     *
     * @covers       \notificationscondition_ac_observer::grouping_deleted
     */
    public function test_grouping_deleted() {
        global $DB;
        $pluginname = ac::NAME;

        self::setUser(2);// Admin.

        $dataform = new \StdClass();
        $dataform->title = "Rule Test";
        $dataform->type = 1;
        $dataform->courseid = self::$course->id;
        $dataform->timesfired = 2;
        $dataform->runtime_group = ['runtime_days' => 5, 'runtime_hours' => 0, 'runtime_minutes' => 0, 'runtime_seconds' => 30];
        $ruleid = self::$rule->create($dataform);
        self::$rule->set_id($ruleid);

        $json = '{"op":"&","c":[{"op":"&","c":[{"type":"grouping","id":' . self::$grouping->id .
                '}]},{"op":"!|","c":[]}],"showc":[true,true],"errors":["availability:error_list_nochildren"]}';
        $objcondition = new \stdClass();
        $objcondition->ruleid = self::$rule->get_id();
        $objcondition->courseid = self::$course->id;
        $objcondition->type = 'condition';
        $objcondition->pluginname = $pluginname;
        $objcondition->parameters = $json;
        $objcondition->cmid = null;

        $conditionid = $DB->insert_record('notificationsagent_condition', $objcondition);
        $this->assertIsInt($conditionid);

        groups_delete_grouping(self::$grouping->id);

        $rule = self::$rule::create_instance($ruleid);
        $this->assertEquals(rule::PAUSE_RULE, $rule->get_status());
    }
}
