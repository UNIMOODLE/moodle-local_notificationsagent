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
 * Tests for the rules system report.
 *
 * @package    local_notificationsagent
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_notificationsagent\reportbuilder\local\systemreports;

use context;
use context_course;
use context_system;
use core_reportbuilder\local\filters\select;
use core_reportbuilder\local\helpers\user_filter_manager;
use core_reportbuilder\system_report_factory;
use core_reportbuilder\table\system_report_table;
use stdClass;

/**
 * Tests that the rules report can not show rows out of the context course or of other users.
 *
 * @group      notificationsagent
 * @covers     \local_notificationsagent\reportbuilder\local\systemreports\rules
 * @covers     \local_notificationsagent\local\entities\rule
 */
final class rules_test extends \advanced_testcase {
    /** @var stdClass[] Courses indexed by label */
    private array $courses = [];

    /** @var stdClass[] Users indexed by label */
    private array $users = [];

    /**
     * Set up the courses, users and report rows shared by the tests.
     */
    protected function setUp(): void {
        global $DB;

        parent::setUp();
        $this->resetAfterTest();

        $generator = $this->getDataGenerator();
        $this->courses['a'] = $generator->create_course();
        $this->courses['b'] = $generator->create_course();

        $this->users['teacher'] = $generator->create_user();
        $this->users['student1'] = $generator->create_user();
        $this->users['student2'] = $generator->create_user();
        $this->users['studentb'] = $generator->create_user();
        $this->users['manager'] = $generator->create_user();

        $generator->enrol_user($this->users['teacher']->id, $this->courses['a']->id, 'editingteacher');
        $generator->enrol_user($this->users['student1']->id, $this->courses['a']->id, 'student');
        $generator->enrol_user($this->users['student1']->id, $this->courses['b']->id, 'student');
        $generator->enrol_user($this->users['student2']->id, $this->courses['a']->id, 'student');
        $generator->enrol_user($this->users['studentb']->id, $this->courses['b']->id, 'student');

        $managerroleid = $DB->get_field('role', 'id', ['shortname' => 'manager'], MUST_EXIST);
        $generator->role_assign($managerroleid, $this->users['manager']->id, context_system::instance()->id);

        $ruleid = $DB->insert_record('notificationsagent_rule', [
            'name' => 'Rule',
            'description' => '',
            'status' => 0,
            'createdby' => $this->users['teacher']->id,
            'createdat' => 1700000000,
            'template' => 1,
        ]);
        $actionid = $DB->insert_record('notificationsagent_action', [
            'ruleid' => $ruleid,
            'pluginname' => 'messageagent',
            'type' => 'action',
            'parameters' => '{}',
        ]);

        $rows = [
            'a_student1' => ['a', 'student1'],
            'a_student2' => ['a', 'student2'],
            'b_student1' => ['b', 'student1'],
            'b_studentb' => ['b', 'studentb'],
        ];
        foreach ($rows as $label => [$course, $user]) {
            $DB->insert_record('notificationsagent_report', [
                'ruleid' => $ruleid,
                'userid' => $this->users[$user]->id,
                'courseid' => $this->courses[$course]->id,
                'actionid' => $actionid,
                'actiondetail' => json_encode(['row' => $label]),
                'timestamp' => 1700000000,
            ]);
        }
    }

    /**
     * Test the rows shown to each actor, with reset or manipulated filters.
     *
     * @dataProvider report_rows_provider
     *
     * @param string $actor User label
     * @param string $contextlevel Report context: 'course' (course a) or 'system'
     * @param array $filters Filter label => value label, empty to reset the filters
     * @param string[] $expected Expected row labels
     */
    public function test_report_rows(string $actor, string $contextlevel, array $filters, array $expected): void {
        $this->setUser($this->users[$actor]);

        $filtervalues = [];
        if (isset($filters['course'])) {
            $filtervalues['course:courseselector_operator'] = select::EQUAL_TO;
            $filtervalues['course:courseselector_values'] = [$this->courses[$filters['course']]->id];
        }
        if (isset($filters['user'])) {
            $filtervalues['rule:userfullname_operator'] = select::EQUAL_TO;
            $filtervalues['rule:userfullname_values'] = [$this->users[$filters['user']]->id];
        }

        $rows = $this->get_report_rows($this->get_report_context($contextlevel), $filtervalues);

        $this->assertEquals($expected, $rows);
    }

    /**
     * Data provider for the report rows of each actor.
     *
     * @return array<string, array>
     */
    public static function report_rows_provider(): array {
        return [
            'Teacher, filters reset' => [
                'teacher', 'course', [], ['a_student1', 'a_student2'],
            ],
            'Teacher, hidden course filter set to another course' => [
                'teacher', 'course', ['course' => 'b'], ['a_student1', 'a_student2'],
            ],
            'Teacher, user filter set to a user of another course' => [
                'teacher', 'course', ['user' => 'studentb'], [],
            ],
            'Student, filters reset' => [
                'student1', 'course', [], ['a_student1'],
            ],
            'Student, hidden user filter set to another user' => [
                'student1', 'course', ['user' => 'student2'], ['a_student1'],
            ],
            'Student, hidden course filter set to another course' => [
                'student1', 'course', ['course' => 'b'], ['a_student1'],
            ],
            'Manager in course, filters reset' => [
                'manager', 'course', [], ['a_student1', 'a_student2'],
            ],
            'Manager in system, filters reset' => [
                'manager', 'system', [], ['a_student1', 'a_student2', 'b_student1', 'b_studentb'],
            ],
            'Manager in system, course filter' => [
                'manager', 'system', ['course' => 'b'], ['b_student1', 'b_studentb'],
            ],
        ];
    }

    /**
     * Test the filters shown to each actor and the user filter options.
     *
     * @dataProvider filter_options_provider
     *
     * @param string $actor User label
     * @param string $contextlevel Report context: 'course' (course a) or 'system'
     * @param bool $hascoursefilter Whether the course filter is shown
     * @param string[]|null $expectedusers Expected user filter labels, null if the user filter is hidden
     */
    public function test_filter_options(
        string $actor,
        string $contextlevel,
        bool $hascoursefilter,
        ?array $expectedusers
    ): void {
        global $PAGE;

        $this->setUser($this->users[$actor]);
        $context = $this->get_report_context($contextlevel);
        $PAGE->set_context($context);

        $report = system_report_factory::create(rules::class, $context);

        $this->assertNotNull($report->get_filter('rule:rulename'));
        $this->assertSame($hascoursefilter, $report->get_filter('course:courseselector') !== null);

        $userfilter = $report->get_filter('rule:userfullname');
        if ($expectedusers === null) {
            $this->assertNull($userfilter);
        } else {
            $this->assertNotNull($userfilter);
            $this->assertEqualsCanonicalizing(
                $this->get_ids($this->users, $expectedusers),
                array_keys($userfilter->get_options())
            );
        }
    }

    /**
     * Data provider for the filters of each actor.
     *
     * @return array<string, array>
     */
    public static function filter_options_provider(): array {
        return [
            'Teacher in course' => [
                'teacher', 'course', false, ['student1', 'student2'],
            ],
            'Student in course' => [
                'student1', 'course', false, null,
            ],
            'Manager in course' => [
                'manager', 'course', false, ['student1', 'student2'],
            ],
            'Manager in system' => [
                'manager', 'system', true, ['student1', 'student2', 'studentb'],
            ],
        ];
    }

    /**
     * Get the report context for the given level.
     *
     * @param string $contextlevel 'course' (course a) or 'system'
     * @return context
     */
    private function get_report_context(string $contextlevel): context {
        if ($contextlevel === 'course') {
            return context_course::instance($this->courses['a']->id);
        }
        return context_system::instance();
    }

    /**
     * Get the ids of the given labels.
     *
     * @param stdClass[] $records Records indexed by label
     * @param string[] $labels
     * @return int[]
     */
    private function get_ids(array $records, array $labels): array {
        return array_map(static fn(string $label): int => (int) $records[$label]->id, $labels);
    }

    /**
     * Get the row labels returned by the report for the current user.
     *
     * @param context $context Report context
     * @param array $filtervalues Filter values, empty to reset the filters
     * @return string[] Sorted row labels
     */
    private function get_report_rows(context $context, array $filtervalues): array {
        global $PAGE;

        $report = system_report_factory::create(rules::class, $context);
        $reportid = $report->get_report_persistent()->get('id');

        if ($filtervalues) {
            user_filter_manager::set($reportid, $filtervalues);
        } else {
            user_filter_manager::reset_all($reportid);
        }

        $PAGE->set_url('/');
        $table = system_report_table::create($reportid, []);
        $table->guess_base_url();
        $table->setup();
        $table->query_db(0, false);

        $rows = [];
        foreach ($table->rawdata as $record) {
            foreach ((array) $record as $alias => $value) {
                if (str_ends_with($alias, '_actiondetail')) {
                    $rows[] = json_decode($value)->row;
                }
            }
        }
        $table->close_recordset();
        sort($rows);

        return $rows;
    }
}
