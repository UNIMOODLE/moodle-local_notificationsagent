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
 * @package    notificationsaction_removeusergroup
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace notificationsaction_removeusergroup;

use local_notificationsagent\evaluationcontext;
use local_notificationsagent\rule;
use notificationsaction_removeusergroup\removeusergroup;

/**
 * Tests for the removeusergroup observer action.
 *
 * @group notificationsagent
 */
final class removeusergroup_observer_test extends \advanced_testcase {
    /**
     * @var rule
     */
    private static $rule;
    /**
     * @var removeusergroup
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
     * id for action
     */
    public const ACTIONID = 1;
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
    }

    /**
     * Check if the group has been deleted.
     *
     * @covers       \notificationsaction_removeusergroup_observer::group_deleted
     */
    public function test_group_deleted(): void {
        global $DB;
        $pluginname = removeusergroup::NAME;
        // Admin.
        self::setUser(2);

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

        $objaction = new \stdClass();
        $objaction->ruleid = self::$rule->get_id();
        $objaction->type = 'action';
        $objaction->pluginname = $pluginname;
        $objaction->parameters = json_encode($objparameters);

        $actionid = $DB->insert_record('notificationsagent_action', $objaction);
        $this->assertIsInt($actionid);

        groups_delete_group(self::$group->id);

        $rule = self::$rule::create_instance($ruleid);
        $this->assertEquals(rule::PAUSE_RULE, $rule->get_status());
    }
}
