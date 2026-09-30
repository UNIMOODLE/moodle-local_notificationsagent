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
 * Tests for the get_rule_assigned_contexts external function.
 *
 * @package    local_notificationsagent
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_notificationsagent\external;

use core_external\external_api;
use local_notificationsagent\rule;

/**
 * Testing external get rule assigned contexts.
 *
 * @group notificationsagent
 * @covers \local_notificationsagent\external\get_rule_assigned_contexts
 */
final class get_rule_assigned_contexts_test extends \advanced_testcase {
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
     * Call the external function and clean the returned value.
     *
     * @param int $ruleid
     * @return array
     */
    private function execute_service(int $ruleid): array {
        $result = get_rule_assigned_contexts::execute($ruleid);
        return external_api::clean_returnvalue(get_rule_assigned_contexts::execute_returns(), $result);
    }

    /**
     * The service returns the categories and courses assigned to the rule.
     */
    public function test_execute_returns_assigned_contexts(): void {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();

        $course = self::getDataGenerator()->create_course();
        $category = self::getDataGenerator()->create_category();
        $ruleid = $this->create_rule($course->id);
        $DB->insert_record('notificationsagent_context', [
            'ruleid' => $ruleid,
            'contextid' => CONTEXT_COURSECAT,
            'objectid' => $category->id,
        ]);

        $result = $this->execute_service($ruleid);

        $this->assertEmpty($result['warnings']);
        $this->assertSame([(int) $category->id], $result['category']);
        $this->assertSame([(int) $course->id], $result['course']);
    }

    /**
     * The service returns a warning when the rule does not exist.
     */
    public function test_execute_rule_not_found(): void {
        $this->resetAfterTest();
        $this->setAdminUser();

        $result = $this->execute_service(0);

        $this->assertSame('nosuchinstance', $result['warnings'][0]['warningcode']);
        $this->assertSame([], $result['category']);
        $this->assertSame([], $result['course']);
    }

    /**
     * The service returns a warning when the user cannot assign rules.
     */
    public function test_execute_without_capability(): void {
        $this->resetAfterTest();

        $course = self::getDataGenerator()->create_course();
        $ruleid = $this->create_rule($course->id);
        $teacher = self::getDataGenerator()->create_and_enrol($course, 'editingteacher');
        $this->setUser($teacher);

        $result = $this->execute_service($ruleid);

        $this->assertSame('nopermissions', $result['warnings'][0]['warningcode']);
        $this->assertSame([], $result['course']);
    }
}
