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
 * Tests for hidden course handling in notificationsagent.
 *
 * @package    local_notificationsagent
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_notificationsagent;

use local_notificationsagent\task\notificationsagent_trigger_cron;
use notificationscondition_sessionend\sessionend;

/**
 * Hidden course tests for notificationsagent.
 *
 * @group notificationsagent
 */
final class notificationsagent_hidden_course_test extends \advanced_testcase {
    /**
     * @var rule
     */
    private static $rule;
    /**
     * @var \stdClass
     */
    private static $user;
    /**
     * Date start for the course
     */
    public const COURSE_DATESTART = 1704099600; // 01/01/2024 10:00:00.
    /**
     * Date end for the course
     */
    public const COURSE_DATEEND = 1706605200; // 30/01/2024 10:00:00.
    /**
     * Trigger date inside cron interval
     */
    public const TRIGGER_DATE = 1706173200;
    /**
     * Random id for activity
     */
    public const CMID = 246000;

    public function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
        self::$rule = new rule();
        self::$user = self::getDataGenerator()->create_user();
        $this->reset_course_visible_for_rules_cache();
    }

    /**
     * Reset static visibility cache between tests.
     *
     * @return void
     */
    private function reset_course_visible_for_rules_cache(): void {
        $reflection = new \ReflectionClass(notificationsagent::class);
        $property = $reflection->getProperty('coursevisibleforrulescache');
        $property->setAccessible(true);
        $property->setValue(null, []);
    }

    /**
     * Create a course and hide it in the database.
     *
     * @param array $options Course generator options.
     * @return \stdClass
     */
    private function create_hidden_course(array $options = []): \stdClass {
        global $DB;

        $course = self::getDataGenerator()->create_course(array_merge([
            'startdate' => self::COURSE_DATESTART,
            'enddate' => self::COURSE_DATEEND,
        ], $options));
        $DB->set_field('course', 'visible', 0, ['id' => $course->id]);
        $course->visible = 0;
        $this->reset_course_visible_for_rules_cache();

        return $course;
    }

    /**
     * is_course_visible_for_rules reflects course visibility.
     *
     * @param int $visible Course visible flag.
     * @param bool $expected Whether the course is processable.
     *
     * @covers \local_notificationsagent\notificationsagent::is_course_visible_for_rules
     * @dataProvider course_visible_provider
     */
    public function test_is_course_visible_for_rules(int $visible, bool $expected): void {
        global $DB;

        $course = self::getDataGenerator()->create_course([
            'startdate' => self::COURSE_DATESTART,
            'enddate' => self::COURSE_DATEEND,
        ]);
        if ($visible === 0) {
            $DB->set_field('course', 'visible', 0, ['id' => $course->id]);
            $this->reset_course_visible_for_rules_cache();
        }

        $this->assertEquals($expected, notificationsagent::is_course_visible_for_rules($course->id));
    }

    /**
     * Data provider for test_is_course_visible_for_rules.
     *
     * @return array<string, array{int, bool}>
     */
    public static function course_visible_provider(): array {
        return [
            'visible course' => [1, true],
            'hidden course' => [0, false],
        ];
    }

    /**
     * get_all_courses_by_ruleid excludes a hidden course scoped directly on the rule.
     *
     * @covers \local_notificationsagent\notificationsagent::get_all_courses_by_ruleid
     */
    public function test_get_all_courses_by_ruleid_excludes_hidden_course(): void {
        global $USER;

        $hiddencourse = $this->create_hidden_course();

        $dataform = new \StdClass();
        $dataform->title = 'Rule Test';
        $dataform->type = 1;
        $dataform->courseid = $hiddencourse->id;
        $dataform->timesfired = 2;
        $dataform->runtime_group = ['runtime_days' => 5, 'runtime_hours' => 0, 'runtime_minutes' => 0];
        $USER->id = self::$user->id;
        $ruleid = self::$rule->create($dataform);

        $courses = notificationsagent::get_all_courses_by_ruleid($ruleid);

        $this->assertEmpty($courses);
    }

    /**
     * get_all_courses_by_ruleid on a category returns only visible courses.
     *
     * @covers \local_notificationsagent\notificationsagent::get_all_courses_by_ruleid
     */
    public function test_get_all_courses_by_ruleid_category_excludes_hidden_courses(): void {
        global $DB, $USER;

        $category = self::getDataGenerator()->create_category();
        $visiblecourse = self::getDataGenerator()->create_course([
            'category' => $category->id,
            'startdate' => self::COURSE_DATESTART,
            'enddate' => self::COURSE_DATEEND,
            'visible' => 1,
        ]);
        $hiddencourse = $this->create_hidden_course(['category' => $category->id]);

        $dataform = new \StdClass();
        $dataform->title = 'Rule Test';
        $dataform->type = 1;
        $dataform->courseid = $visiblecourse->id;
        $dataform->timesfired = 2;
        $dataform->runtime_group = ['runtime_days' => 5, 'runtime_hours' => 0, 'runtime_minutes' => 0];
        $USER->id = self::$user->id;
        $ruleid = self::$rule->create($dataform);

        $DB->insert_record(
            'notificationsagent_context',
            [
                'ruleid' => $ruleid,
                'contextid' => CONTEXT_COURSECAT,
                'objectid' => $category->id,
            ]
        );

        $courses = notificationsagent::get_all_courses_by_ruleid($ruleid);

        $this->assertArrayHasKey($visiblecourse->id, $courses);
        $this->assertArrayNotHasKey($hiddencourse->id, $courses);
    }

    /**
     * get_conditions_by_course returns no conditions for a hidden course.
     *
     * @covers \local_notificationsagent\notificationsagent::get_conditions_by_course
     */
    public function test_get_conditions_by_course_hidden_returns_empty(): void {
        global $DB, $USER;

        $hiddencourse = $this->create_hidden_course();

        $dataform = new \StdClass();
        $dataform->title = 'Rule Test';
        $dataform->type = 1;
        $dataform->courseid = $hiddencourse->id;
        $dataform->timesfired = 2;
        $dataform->runtime_group = ['runtime_days' => 5, 'runtime_hours' => 0, 'runtime_minutes' => 0];
        $USER->id = self::$user->id;
        $ruleid = self::$rule->create($dataform);

        $DB->insert_record(
            'notificationsagent_condition',
            [
                'ruleid' => $ruleid,
                'courseid' => $hiddencourse->id,
                'type' => 'condition',
                'pluginname' => sessionend::NAME,
                'parameters' => '{"time":"86400"}',
                'cmid' => self::CMID,
            ]
        );

        rule::create_instance($ruleid);

        $conditions = notificationsagent::get_conditions_by_course(sessionend::NAME, $hiddencourse->id);

        $this->assertEmpty($conditions);
    }

    /**
     * get_conditions_by_cm returns no conditions for a hidden course.
     *
     * @covers \local_notificationsagent\notificationsagent::get_conditions_by_cm
     */
    public function test_get_conditions_by_cm_hidden_returns_empty(): void {
        global $DB, $USER;

        $hiddencourse = $this->create_hidden_course();

        $dataform = new \StdClass();
        $dataform->title = 'Rule Test';
        $dataform->type = 1;
        $dataform->courseid = $hiddencourse->id;
        $dataform->timesfired = 2;
        $dataform->runtime_group = ['runtime_days' => 5, 'runtime_hours' => 0, 'runtime_minutes' => 0];
        $USER->id = self::$user->id;
        $ruleid = self::$rule->create($dataform);

        $DB->insert_record(
            'notificationsagent_condition',
            [
                'ruleid' => $ruleid,
                'courseid' => $hiddencourse->id,
                'type' => 'condition',
                'pluginname' => sessionend::NAME,
                'parameters' => '{"time":"86400"}',
                'cmid' => self::CMID,
            ]
        );

        rule::create_instance($ruleid);

        $conditions = notificationsagent::get_conditions_by_cm(sessionend::NAME, $hiddencourse->id, self::CMID);

        $this->assertEmpty($conditions);
    }

    /**
     * get_triggersbytimeinterval ignores triggers for hidden courses.
     *
     * @covers \local_notificationsagent\notificationsagent::get_triggersbytimeinterval
     */
    public function test_get_triggersbytimeinterval_skips_hidden_course(): void {
        global $DB, $USER;

        $hiddencourse = $this->create_hidden_course();
        self::getDataGenerator()->enrol_user(self::$user->id, $hiddencourse->id);

        $dataform = new \StdClass();
        $dataform->title = 'Rule Test';
        $dataform->type = 1;
        $dataform->courseid = $hiddencourse->id;
        $dataform->timesfired = 2;
        $dataform->runtime_group = ['runtime_days' => 5, 'runtime_hours' => 0, 'runtime_minutes' => 0];
        $USER->id = self::$user->id;
        $ruleid = self::$rule->create($dataform);

        $DB->insert_record(
            'notificationsagent_triggers',
            [
                'ruleid' => $ruleid,
                'conditionid' => 24900,
                'courseid' => $hiddencourse->id,
                'userid' => self::$user->id,
                'startdate' => self::TRIGGER_DATE,
            ]
        );

        $triggers = notificationsagent::get_triggersbytimeinterval(
            self::TRIGGER_DATE + 60,
            self::TRIGGER_DATE - 60
        );

        $this->assertEmpty($triggers);
    }

    /**
     * Trigger cron does not execute rules when the course has been hidden.
     *
     * @covers \local_notificationsagent\task\notificationsagent_trigger_cron::execute
     */
    public function test_trigger_cron_skips_hidden_course(): void {
        global $DB, $USER;

        $course = self::getDataGenerator()->create_course([
            'startdate' => self::COURSE_DATESTART,
            'enddate' => self::COURSE_DATEEND,
        ]);
        self::getDataGenerator()->enrol_user(self::$user->id, $course->id);

        $dataform = new \StdClass();
        $dataform->title = 'Rule Test';
        $dataform->type = 1;
        $dataform->courseid = $course->id;
        $dataform->timesfired = 2;
        $dataform->runtime_group = ['runtime_days' => 5, 'runtime_hours' => 0, 'runtime_minutes' => 0];
        $USER->id = self::$user->id;
        $ruleid = self::$rule->create($dataform);
        self::$rule->set_id($ruleid);

        $conditionid = $DB->insert_record(
            'notificationsagent_condition',
            [
                'ruleid' => $ruleid,
                'courseid' => $course->id,
                'type' => 'condition',
                'pluginname' => 'sessionend',
                'parameters' => '{"time":864001}',
                'cmid' => self::CMID,
            ]
        );

        $DB->insert_record(
            'notificationsagent_action',
            [
                'ruleid' => $ruleid,
                'courseid' => $course->id,
                'type' => 'action',
                'pluginname' => 'messageagent',
                'parameters' => '{"title":"Title","message":{"text":"Message"}}',
            ]
        );

        rule::create_instance($ruleid);

        $DB->insert_record(
            'notificationsagent_triggers',
            [
                'ruleid' => $ruleid,
                'conditionid' => $conditionid,
                'courseid' => $course->id,
                'userid' => self::$user->id,
                'startdate' => self::TRIGGER_DATE,
            ]
        );

        $DB->set_field('course', 'visible', 0, ['id' => $course->id]);
        $this->reset_course_visible_for_rules_cache();

        $task = \core\task\manager::get_scheduled_task(notificationsagent_trigger_cron::class);
        $task->set_timestarted(self::TRIGGER_DATE);
        $task->execute();

        $this->assertEmpty($DB->get_records('notificationsagent_report'));
    }
}
