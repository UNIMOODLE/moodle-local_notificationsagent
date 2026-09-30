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
// Funded by the European Union - Next GenerationEU".
//
// Produced by the UNIMOODLE University Group: Universities of
// Valladolid, Complutense de Madrid, UPV/EHU, León, Salamanca,
// Illes Balears, Valencia, Rey Juan Carlos, La Laguna, Zaragoza, Málaga,
// Córdoba, Extremadura, Vigo, Las Palmas de Gran Canaria y Burgos.

/**
 * Tests for the rule assignment helper.
 *
 * @package    local_notificationsagent
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_notificationsagent\helper;

use local_notificationsagent\rule;

/**
 * Testing the rule assignment helper.
 *
 * @group notificationsagent
 * @covers \local_notificationsagent\helper\rule_assignment
 */
final class rule_assignment_test extends \advanced_testcase {
    /**
     * Create a rule in the given course.
     *
     * @param int $courseid
     * @return int Rule id
     */
    private function create_rule(int $courseid): int {
        $dataform = new \stdClass();
        $dataform->title = 'Rule Test';
        $dataform->type = rule::RULE_TYPE;
        $dataform->courseid = $courseid;
        $dataform->timesfired = 1;
        $dataform->runtime_group = ['runtime_days' => 1, 'runtime_hours' => 0, 'runtime_minutes' => 0];

        return (new rule())->create($dataform);
    }

    /**
     * Assigned contexts can be written and read back.
     */
    public function test_set_and_get_assigned_contexts(): void {
        $this->resetAfterTest();
        $this->setAdminUser();

        $course = self::getDataGenerator()->create_course();
        $othercourse = self::getDataGenerator()->create_course();
        $category = self::getDataGenerator()->create_category();
        $ruleid = $this->create_rule($course->id);

        rule_assignment::set_assigned_contexts($ruleid, [$category->id], [$othercourse->id]);
        $assigned = rule_assignment::get_assigned_contexts($ruleid);

        $this->assertEquals([$category->id], $assigned['category']);
        $courses = $assigned['course'];
        sort($courses);
        $expected = [SITEID, $othercourse->id];
        sort($expected);
        $this->assertEquals($expected, $courses);
    }

    /**
     * Forced is only updated when the user can force rules.
     */
    public function test_set_forced_requires_forcerule_capability(): void {
        global $DB;
        $this->resetAfterTest();

        $course = self::getDataGenerator()->create_course();
        $ruleid = $this->create_rule($course->id);
        $teacher = self::getDataGenerator()->create_and_enrol($course, 'editingteacher');

        $this->setUser($teacher);
        rule_assignment::set_forced($ruleid, rule::FORCED_RULE);
        $this->assertEquals(rule::NONFORCED_RULE, $DB->get_field('notificationsagent_rule', 'forced', ['id' => $ruleid]));

        $this->setAdminUser();
        rule_assignment::set_forced($ruleid, rule::FORCED_RULE);
        $this->assertEquals(rule::FORCED_RULE, $DB->get_field('notificationsagent_rule', 'forced', ['id' => $ruleid]));
    }
}
