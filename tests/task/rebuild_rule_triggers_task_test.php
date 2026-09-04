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

/**
 * Tests for the adhoc rebuild_rule_triggers_task.
 *
 * @package    local_notificationsagent
 * @copyright  2023 Proyecto UNIMOODLE
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_notificationsagent\task;

use core\task\manager;
use local_notificationsagent\evaluationcontext;
use local_notificationsagent\form\editrule_form;
use local_notificationsagent\notificationplugin;
use local_notificationsagent\notificationsagent;
use local_notificationsagent\rule;
use notificationscondition_coursestart\coursestart;
use notificationscondition_enrolend\enrolend;

/**
 * Adhoc rebuild triggers task tests.
 *
 * @group notificationsagent
 * @coversDefaultClass \local_notificationsagent\task\rebuild_rule_triggers_task
 */
final class rebuild_rule_triggers_task_test extends \advanced_testcase {
    /** @var \stdClass */
    private static $user;

    /** @var \stdClass */
    private static $course;

    public const COURSE_DATESTART = 1704099600;

    public const COURSE_DATEEND = 1706605200;

    public function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
        rule::reset_isgeneric_cache();
        $this->reset_course_visible_for_rules_cache();

        self::$user = self::getDataGenerator()->create_user();
        self::$course = self::getDataGenerator()->create_course([
            'startdate' => self::COURSE_DATESTART,
            'enddate' => self::COURSE_DATEEND,
        ]);
        self::getDataGenerator()->enrol_user(self::$user->id, self::$course->id, 'student');
    }

    /**
     * Reset static visibility cache between tests.
     */
    private function reset_course_visible_for_rules_cache(): void {
        $reflection = new \ReflectionClass(notificationsagent::class);
        $property = $reflection->getProperty('coursevisibleforrulescache');
        $property->setAccessible(true);
        $property->setValue(null, []);
    }

    /**
     * Return queued rebuild adhoc tasks for a rule.
     *
     * @param int $ruleid Rule identifier
     * @return rebuild_rule_triggers_task[]
     */
    private function get_rebuild_tasks_for_rule(int $ruleid): array {
        $tasks = manager::get_adhoc_tasks(rebuild_rule_triggers_task::class);

        return array_values(array_filter(
            $tasks,
            function (rebuild_rule_triggers_task $task) use ($ruleid): bool {
                $data = $task->get_custom_data();

                return (int) $data->ruleid === $ruleid;
            }
        ));
    }

    /**
     * Build save_form data for an enrolend (non-generic) rule.
     *
     * @param int $courseid Course identifier
     * @param int $enrolenddays Days before enrolment end
     * @return \stdClass
     */
    private function build_enrolend_save_form_data(int $courseid, int $enrolenddays = 1): \stdClass {
        $dataform = new \stdClass();
        $dataform->title = 'Non-generic enrolend rule';
        $dataform->type = rule::RULE_TYPE;
        $dataform->courseid = $courseid;
        $dataform->timesfired = 2;
        $dataform->runtime_group = ['runtime_days' => 2, 'runtime_hours' => 0, 'runtime_minutes' => 0];
        $dataform->{editrule_form::FORM_JSON_CONDITION} = json_encode([
            '1' => [
                'pluginname' => enrolend::NAME,
                'action' => editrule_form::FORM_JSON_ACTION_INSERT,
            ],
        ]);
        $dataform->{editrule_form::FORM_JSON_EXCEPTION} = '[]';
        $dataform->{editrule_form::FORM_JSON_ACTION} = json_encode([
            '1' => [
                'pluginname' => 'messageagent',
                'action' => editrule_form::FORM_JSON_ACTION_INSERT,
            ],
        ]);
        $dataform->{editrule_form::FORM_JSON_AC} = '';
        $dataform->{'1_enrolend_days'} = $enrolenddays;
        $dataform->{'1_enrolend_hours'} = 0;
        $dataform->{'1_enrolend_minutes'} = 0;
        $dataform->{'1_messageagent_title'} = 'Title';
        $dataform->{'1_messageagent_message'} = ['text' => 'Message body', 'format' => FORMAT_HTML];

        return $dataform;
    }

    /**
     * Build save_form data with enrolend and coursestart conditions.
     *
     * @param int $courseid Course identifier
     * @return \stdClass
     */
    private function build_mixed_save_form_data(int $courseid): \stdClass {
        $dataform = $this->build_enrolend_save_form_data($courseid);
        $dataform->title = 'Mixed enrolend and coursestart rule';
        $dataform->{editrule_form::FORM_JSON_CONDITION} = json_encode([
            '1' => [
                'pluginname' => enrolend::NAME,
                'action' => editrule_form::FORM_JSON_ACTION_INSERT,
            ],
            '2' => [
                'pluginname' => coursestart::NAME,
                'action' => editrule_form::FORM_JSON_ACTION_INSERT,
            ],
        ]);
        $dataform->{'2_coursestart_days'} = 2;
        $dataform->{'2_coursestart_hours'} = 0;
        $dataform->{'2_coursestart_minutes'} = 0;

        return $dataform;
    }

    /**
     * Enrol the test user with a fixed enrolment end date.
     */
    private function enrol_user_with_timeend(): void {
        $timeend = time() + YEARSECS;
        self::getDataGenerator()->enrol_user(
            self::$user->id,
            self::$course->id,
            'student',
            'manual',
            time(),
            $timeend
        );
    }

    /**
     * Create a hidden course for visibility guard tests.
     *
     * @return \stdClass
     */
    private function create_hidden_course(): \stdClass {
        global $DB;

        $course = self::getDataGenerator()->create_course([
            'startdate' => self::COURSE_DATESTART,
            'enddate' => self::COURSE_DATEEND,
        ]);
        $DB->set_field('course', 'visible', 0, ['id' => $course->id]);
        $course->visible = 0;
        $this->reset_course_visible_for_rules_cache();

        return $course;
    }

    /**
     * Task exposes a human-readable name for the task log.
     *
     * @covers ::get_name
     */
    public function test_get_name(): void {
        $task = new rebuild_rule_triggers_task();
        $this->assertSame(
            get_string('taskrebuildtriggers', 'local_notificationsagent'),
            $task->get_name()
        );
    }

    /**
     * bump_rebuild_token increments the stored token for a rule and course.
     *
     * @covers \local_notificationsagent\notificationsagent::bump_rebuild_token
     */
    public function test_bump_rebuild_token_increments(): void {
        $ruleid = 42;
        $courseid = self::$course->id;

        $this->assertSame(0, notificationsagent::get_rebuild_token($ruleid, $courseid));
        $this->assertSame(1, notificationsagent::bump_rebuild_token($ruleid, $courseid));
        $this->assertSame(1, notificationsagent::get_rebuild_token($ruleid, $courseid));
        $this->assertSame(2, notificationsagent::bump_rebuild_token($ruleid, $courseid));
    }

    /**
     * set_rebuild_in_progress must not reset the rebuild token.
     *
     * @covers \local_notificationsagent\notificationsagent::set_rebuild_in_progress
     */
    public function test_set_rebuild_in_progress_preserves_token(): void {
        $ruleid = 99;
        $courseid = self::$course->id;

        notificationsagent::bump_rebuild_token($ruleid, $courseid);
        notificationsagent::set_rebuild_in_progress($ruleid, $courseid, true);

        $this->assertSame(1, notificationsagent::get_rebuild_token($ruleid, $courseid));
        $this->assertTrue(notificationsagent::is_rebuild_in_progress($ruleid, $courseid));

        notificationsagent::set_rebuild_in_progress($ruleid, $courseid, false);
        $this->assertFalse(notificationsagent::is_rebuild_in_progress($ruleid, $courseid));
    }

    /**
     * execute() with incomplete custom data must return without side effects.
     *
     * @covers ::execute
     */
    public function test_execute_invalid_custom_data_returns_silently(): void {
        $task = new rebuild_rule_triggers_task();
        $task->set_custom_data((object) ['ruleid' => 1, 'courseid' => self::$course->id]);
        $task->execute();

        $this->assertFalse(notificationsagent::is_rebuild_in_progress(1, self::$course->id));
    }

    /**
     * execute() with a stale token must clear the in-progress flag in finally.
     *
     * @covers ::execute
     */
    public function test_execute_stale_token_clears_inprogress(): void {
        $this->setAdminUser();
        $this->enrol_user_with_timeend();

        $rule = new rule(null, rule::RULE_TYPE, rule::RULE_ADD);
        $rule->save_form($this->build_enrolend_save_form_data(self::$course->id));
        $ruleid = $rule->get_id();

        notificationsagent::bump_rebuild_token($ruleid, self::$course->id);

        $tasks = $this->get_rebuild_tasks_for_rule($ruleid);
        $this->assertCount(1, $tasks);
        $tasks[0]->execute();

        $this->assertFalse(notificationsagent::is_rebuild_in_progress($ruleid, self::$course->id));
    }

    /**
     * Stale isgeneric cache must not prevent defer when save_form adds a non-generic condition.
     *
     * @covers \local_notificationsagent\rule::save_form
     */
    public function test_save_form_stale_isgeneric_cache_still_defers(): void {
        global $USER;

        $this->setAdminUser();
        $this->enrol_user_with_timeend();

        $dataform = new \stdClass();
        $dataform->title = 'Empty rule';
        $dataform->type = rule::RULE_TYPE;
        $dataform->courseid = self::$course->id;
        $dataform->timesfired = 2;
        $dataform->runtime_group = ['runtime_days' => 2, 'runtime_hours' => 0, 'runtime_minutes' => 0];
        $USER->id = get_admin()->id;
        $ruleid = (new rule())->create($dataform);

        $this->assertTrue(rule::is_rule_generic($ruleid));

        $rule = new rule($ruleid, rule::RULE_TYPE, rule::RULE_EDIT);
        $rule->save_form($this->build_enrolend_save_form_data(self::$course->id));

        $this->assertTrue($rule->was_rebuild_queued());
        $this->assertNotEmpty($this->get_rebuild_tasks_for_rule($ruleid));
    }

    /**
     * A newer rebuild token must invalidate a previously queued adhoc task.
     *
     * @covers ::execute
     */
    public function test_execute_double_save_invalidates_previous_task(): void {
        global $DB;

        $this->setAdminUser();
        $this->enrol_user_with_timeend();

        $rule = new rule(null, rule::RULE_TYPE, rule::RULE_ADD);
        $rule->save_form($this->build_enrolend_save_form_data(self::$course->id, 1));
        $ruleid = $rule->get_id();

        $oldtasks = $this->get_rebuild_tasks_for_rule($ruleid);
        $this->assertCount(1, $oldtasks);

        $newtoken = notificationsagent::bump_rebuild_token($ruleid, self::$course->id);
        rule::queue_rebuild_triggers_task($ruleid, self::$course->id, $newtoken);

        $oldtasks[0]->execute();
        $this->assertEmpty($DB->get_records('notificationsagent_triggers', [
            'ruleid' => $ruleid,
            'courseid' => self::$course->id,
        ]));

        $currenttoken = notificationsagent::get_rebuild_token($ruleid, self::$course->id);
        $currenttasks = array_values(array_filter(
            $this->get_rebuild_tasks_for_rule($ruleid),
            function (rebuild_rule_triggers_task $task) use ($currenttoken): bool {
                return (int) $task->get_custom_data()->token === $currenttoken;
            }
        ));
        $this->assertCount(1, $currenttasks);
        $currenttasks[0]->execute();
        $this->assertNotEmpty($DB->get_records('notificationsagent_triggers', [
            'ruleid' => $ruleid,
            'courseid' => self::$course->id,
        ]));
    }

    /**
     * Rebuild must process every condition of a mixed rule.
     *
     * @covers ::execute
     */
    public function test_execute_multi_condition_rule_rebuilds_all_conditions(): void {
        global $DB;

        $this->setAdminUser();
        $this->enrol_user_with_timeend();

        $rule = new rule(null, rule::RULE_TYPE, rule::RULE_ADD);
        $rule->save_form($this->build_mixed_save_form_data(self::$course->id));
        $ruleid = $rule->get_id();

        $conditionids = $DB->get_fieldset_select(
            'notificationsagent_condition',
            'id',
            'ruleid = ? AND pluginname IN (?, ?)',
            [$ruleid, enrolend::NAME, coursestart::NAME]
        );
        $this->assertCount(2, $conditionids);

        $tasks = $this->get_rebuild_tasks_for_rule($ruleid);
        $this->assertCount(1, $tasks);
        $tasks[0]->execute();

        foreach ($conditionids as $conditionid) {
            $this->assertNotEmpty($DB->get_records('notificationsagent_cache', [
                'courseid' => self::$course->id,
                'conditionid' => $conditionid,
            ]));
        }
        $this->assertNotEmpty($DB->get_records('notificationsagent_triggers', [
            'ruleid' => $ruleid,
            'courseid' => self::$course->id,
        ]));
    }

    /**
     * execute() must skip rebuild for hidden courses and clear in-progress.
     *
     * @covers ::execute
     */
    public function test_execute_hidden_course_skips_rebuild(): void {
        global $DB;

        $this->setAdminUser();
        $hiddencourse = $this->create_hidden_course();
        $timeend = time() + YEARSECS;
        self::getDataGenerator()->enrol_user(
            self::$user->id,
            $hiddencourse->id,
            'student',
            'manual',
            time(),
            $timeend
        );

        $dataform = $this->build_enrolend_save_form_data($hiddencourse->id);
        $rule = new rule(null, rule::RULE_TYPE, rule::RULE_ADD);
        $rule->save_form($dataform);
        $ruleid = $rule->get_id();

        $tasks = $this->get_rebuild_tasks_for_rule($ruleid);
        $this->assertCount(1, $tasks);
        $tasks[0]->execute();

        $this->assertEmpty($DB->get_records('notificationsagent_triggers', [
            'ruleid' => $ruleid,
            'courseid' => $hiddencourse->id,
        ]));
        $this->assertFalse(notificationsagent::is_rebuild_in_progress($ruleid, $hiddencourse->id));
    }

    /**
     * execute() must tolerate a missing rule and still clear in-progress.
     *
     * @covers ::execute
     */
    public function test_execute_missing_rule_clears_inprogress(): void {
        $ruleid = 99999;
        $courseid = self::$course->id;
        $token = notificationsagent::bump_rebuild_token($ruleid, $courseid);

        $task = new rebuild_rule_triggers_task();
        $task->set_custom_data((object) [
            'ruleid' => $ruleid,
            'courseid' => $courseid,
            'token' => $token,
        ]);
        $task->execute();

        $this->assertFalse(notificationsagent::is_rebuild_in_progress($ruleid, $courseid));
    }

    /**
     * generate_cache_triggers with duringrebuild must write even when in-progress is set.
     *
     * @covers \local_notificationsagent\notificationsagent::generate_cache_triggers
     */
    public function test_generate_cache_triggers_duringrebuild_writes_when_inprogress(): void {
        global $DB, $USER;

        $this->setAdminUser();
        $this->enrol_user_with_timeend();

        $dataform = new \stdClass();
        $dataform->title = 'Trigger test rule';
        $dataform->type = rule::RULE_TYPE;
        $dataform->courseid = self::$course->id;
        $dataform->timesfired = 2;
        $dataform->runtime_group = ['runtime_days' => 2, 'runtime_hours' => 0, 'runtime_minutes' => 0];
        $USER->id = get_admin()->id;
        $ruleid = (new rule())->create($dataform);

        $conditionid = $DB->insert_record('notificationsagent_condition', (object) [
            'ruleid' => $ruleid,
            'type' => 'condition',
            'pluginname' => enrolend::NAME,
            'parameters' => '{"time":86400}',
            'cmid' => 0,
            'complementary' => notificationplugin::COMPLEMENTARY_CONDITION,
        ]);

        notificationsagent::set_rebuild_in_progress($ruleid, self::$course->id, true);

        $subplugin = new enrolend($ruleid, $conditionid);
        $context = new evaluationcontext();
        $context->set_courseid(self::$course->id);
        $context->set_userid(0);
        $context->set_params('{"time":86400}');
        $context->set_timeaccess(time());
        $context->set_complementary(notificationplugin::COMPLEMENTARY_CONDITION);

        notificationsagent::generate_cache_triggers($subplugin, $context, true);

        $this->assertNotEmpty($DB->get_records('notificationsagent_cache', [
            'conditionid' => $conditionid,
            'courseid' => self::$course->id,
        ]));

        notificationsagent::set_rebuild_in_progress($ruleid, self::$course->id, false);
    }

    /**
     * queue_rebuild_triggers_task must not duplicate tasks with identical custom data.
     *
     * @covers \local_notificationsagent\rule::queue_rebuild_triggers_task
     */
    public function test_queue_rebuild_deduplicates_same_custom_data(): void {
        $ruleid = 500;
        $courseid = self::$course->id;
        $token = notificationsagent::bump_rebuild_token($ruleid, $courseid);

        rule::queue_rebuild_triggers_task($ruleid, $courseid, $token);
        rule::queue_rebuild_triggers_task($ruleid, $courseid, $token);

        $this->assertCount(1, $this->get_rebuild_tasks_for_rule($ruleid));
    }
}
