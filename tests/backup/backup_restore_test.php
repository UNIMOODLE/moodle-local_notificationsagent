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
 * Tests for course backup and restore of notification rules.
 *
 * @package    local_notificationsagent
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_notificationsagent\backup;

use local_notificationsagent\rule;

/**
 * Student rules are copied only when the backup includes users.
 *
 * @group notificationsagent
 * @covers \backup_local_notificationsagent_plugin
 * @covers \restore_local_notificationsagent_plugin
 */
final class backup_restore_test extends \advanced_testcase {
    /** @var \stdClass Teacher who creates the teacher rule */
    private \stdClass $teacher;

    /** @var \stdClass Student who creates the student rule */
    private \stdClass $student;

    /** @var string Name of the teacher rule */
    private const TEACHER_RULE = 'Teacher rule';

    /** @var string Name of the student rule */
    private const STUDENT_RULE = 'Student rule';

    /**
     * Rules restored depend on the users setting and on whether the backup has the student flag.
     *
     * @dataProvider restore_provider
     *
     * @param bool $backupusers Include users in the backup
     * @param bool $restoreusers Include users in the restore
     * @param bool $stripstudentflag Remove isstudentrule, as in a backup from before the field existed
     * @param string[] $expected Rule names expected in the new course
     */
    public function test_backup_and_restore(
        bool $backupusers,
        bool $restoreusers,
        bool $stripstudentflag,
        array $expected
    ): void {
        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->create_course_with_rules();
        $newcourseid = $this->backup_and_restore($course, $backupusers, $restoreusers, $stripstudentflag);

        $this->assertEqualsCanonicalizing($expected, $this->get_rule_names($newcourseid));
        $this->assert_teacher_rule_has_parts($newcourseid);
        $this->assert_no_orphan_children();
    }

    /**
     * Data provider for backup and restore of teacher and student rules.
     *
     * @return array<string, array>
     */
    public static function restore_provider(): array {
        return [
            'Without users' => [
                false, false, false, [self::TEACHER_RULE],
            ],
            'With users' => [
                true, true, false, [self::TEACHER_RULE, self::STUDENT_RULE],
            ],
            'Backup with users, restore without users' => [
                true, false, false, [self::TEACHER_RULE],
            ],
            'Old backup without the student flag' => [
                true, false, true, [self::TEACHER_RULE, self::STUDENT_RULE],
            ],
        ];
    }

    /**
     * With user data, restored rules keep the mapped creator ids.
     */
    public function test_restored_createdby_with_users(): void {
        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->create_course_with_rules();
        $newcourseid = $this->backup_and_restore($course, true, true, false);

        $this->assert_rule_createdby($newcourseid, self::TEACHER_RULE, (int) $this->teacher->id);
        $this->assert_rule_createdby($newcourseid, self::STUDENT_RULE, (int) $this->student->id);
    }

    /**
     * On another site without user data, the teacher rule is owned by the user who runs the restore.
     */
    public function test_restored_createdby_foreign_site_without_users(): void {
        $this->resetAfterTest();
        $generator = $this->getDataGenerator();
        $admin = $generator->create_user();
        $generator->role_assign('manager', $admin->id, \context_system::instance());

        $course = $this->create_course_with_rules();
        $this->setAdminUser();
        $newcourseid = $this->backup_and_restore($course, false, false, false, true, (int) $admin->id);

        $this->assert_rule_createdby($newcourseid, self::TEACHER_RULE, (int) $admin->id);
    }

    /**
     * Course copy without user data does not copy the student rule.
     */
    public function test_course_copy_without_userdata(): void {
        global $CFG, $DB;

        require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
        require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');

        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->create_course_with_rules();

        $formdata = new \stdClass();
        $formdata->courseid = $course->id;
        $formdata->fullname = 'Copied course';
        $formdata->shortname = 'copied' . $course->id;
        $formdata->category = $course->category;
        $formdata->visible = 1;
        $formdata->startdate = $course->startdate;
        $formdata->enddate = $course->enddate;
        $formdata->idnumber = 'copied' . $course->id;
        $formdata->userdata = 0;

        $copydata = \copy_helper::process_formdata($formdata);
        $copyids = \copy_helper::create_copy($copydata);

        $this->expectOutputRegex('/Course copy:/');
        $task = \core\task\manager::get_next_adhoc_task(time());
        $this->assertInstanceOf(\core\task\asynchronous_copy_task::class, $task);
        $task->execute();
        \core\task\manager::adhoc_task_complete($task);

        $restored = $DB->get_record('backup_controllers', ['backupid' => $copyids['restoreid']], '*', MUST_EXIST);

        $this->assertEqualsCanonicalizing([self::TEACHER_RULE], $this->get_rule_names($restored->itemid));
        $this->assert_teacher_rule_has_parts($restored->itemid);
        $this->assert_no_orphan_children();
    }

    /**
     * Create a course with one teacher rule and one student rule.
     *
     * @return \stdClass
     */
    private function create_course_with_rules(): \stdClass {
        global $DB;

        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $this->teacher = $generator->create_user();
        $this->student = $generator->create_user();
        $generator->enrol_user($this->teacher->id, $course->id, 'editingteacher');
        $generator->enrol_user($this->student->id, $course->id, 'student');

        $this->setUser($this->teacher);
        $teacheruleid = $this->create_rule($course->id, self::TEACHER_RULE);
        $this->setUser($this->student);
        $studentruleid = $this->create_rule($course->id, self::STUDENT_RULE);
        $this->setAdminUser();

        foreach ([$teacheruleid, $studentruleid] as $ruleid) {
            $DB->insert_record('notificationsagent_condition', [
                'ruleid' => $ruleid,
                'pluginname' => 'coursestart',
                'type' => 'condition',
                'parameters' => '{"time":0}',
                'complementary' => 0,
            ]);
            $DB->insert_record('notificationsagent_action', [
                'ruleid' => $ruleid,
                'pluginname' => 'messageagent',
                'type' => 'action',
                'parameters' => '{}',
            ]);
        }

        return $course;
    }

    /**
     * Create a rule in the course as the current user.
     *
     * @param int $courseid
     * @param string $name
     * @return int Rule id
     */
    private function create_rule(int $courseid, string $name): int {
        $dataform = new \stdClass();
        $dataform->title = $name;
        $dataform->type = rule::RULE_TYPE;
        $dataform->courseid = $courseid;
        $dataform->timesfired = 1;
        $dataform->runtime_group = ['runtime_days' => 1, 'runtime_hours' => 0, 'runtime_minutes' => 0];

        return (new rule())->create($dataform);
    }

    /**
     * Back up the course and restore it into a new course.
     *
     * @param \stdClass $course
     * @param bool $backupusers
     * @param bool $restoreusers
     * @param bool $stripstudentflag
     * @param bool $foreignsite Simulate a backup from another Moodle site
     * @param int|null $restoreuserid User id for restore_controller (defaults to current user)
     * @return int New course id
     */
    private function backup_and_restore(
        \stdClass $course,
        bool $backupusers,
        bool $restoreusers,
        bool $stripstudentflag,
        bool $foreignsite = false,
        ?int $restoreuserid = null
    ): int {
        global $CFG, $USER;

        require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
        require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');

        $CFG->backup_file_logger_level = \backup::LOG_NONE;

        $bc = new \backup_controller(
            \backup::TYPE_1COURSE,
            $course->id,
            \backup::FORMAT_MOODLE,
            \backup::INTERACTIVE_NO,
            \backup::MODE_IMPORT,
            $USER->id
        );
        $bc->get_plan()->get_setting('users')->set_status(\backup_setting::NOT_LOCKED);
        $bc->get_plan()->get_setting('users')->set_value($backupusers);
        $backupid = $bc->get_backupid();
        $bc->execute_plan();
        $bc->destroy();

        if ($stripstudentflag) {
            $file = make_backup_temp_directory($backupid) . '/course/course.xml';
            $xml = file_get_contents($file);
            $this->assertStringContainsString('<isstudentrule>', $xml);
            $stripped = preg_replace('/<isstudentrule>.*?<\/isstudentrule>/s', '', $xml);
            $this->assertNotSame($xml, $stripped);
            file_put_contents($file, $stripped);
        }
        if ($foreignsite) {
            $this->mark_backup_as_foreign_site($backupid);
        }

        $newcourseid = \restore_dbops::create_new_course(
            $course->fullname,
            $course->shortname . '_r',
            $course->category
        );
        $restoreuser = $restoreuserid ?? (int) $USER->id;
        $rc = new \restore_controller(
            $backupid,
            $newcourseid,
            \backup::INTERACTIVE_NO,
            \backup::MODE_GENERAL,
            $restoreuser,
            \backup::TARGET_NEW_COURSE
        );
        $rc->get_plan()->get_setting('users')->set_status(\backup_setting::NOT_LOCKED);
        $rc->get_plan()->get_setting('users')->set_value($restoreusers);
        $this->assertTrue($rc->execute_precheck());
        $rc->execute_plan();
        $rc->destroy();

        return $newcourseid;
    }

    /**
     * Change the backup metadata so the restore treats it as coming from another site.
     *
     * @param string $backupid
     */
    private function mark_backup_as_foreign_site(string $backupid): void {
        global $CFG;

        $file = make_backup_temp_directory($backupid) . '/moodle_backup.xml';
        $xml = file_get_contents($file);
        $this->assertStringContainsString($CFG->wwwroot, $xml);
        $xml = str_replace($CFG->wwwroot, 'https://foreign.example.invalid', $xml);

        // Backups from Moodle 2.0+ compare original_site_identifier_hash, not only wwwroot.
        $foreignhash = md5('local_notificationsagent_foreign_site_test');
        $xml = preg_replace(
            '/<original_site_identifier_hash>[^<]*<\/original_site_identifier_hash>/',
            '<original_site_identifier_hash>' . $foreignhash . '</original_site_identifier_hash>',
            $xml,
            1,
            $count
        );
        $this->assertGreaterThan(0, $count, 'Backup metadata must include original_site_identifier_hash');

        file_put_contents($file, $xml);
    }

    /**
     * Rule names assigned to the course.
     *
     * @param int $courseid
     * @return string[]
     */
    private function get_rule_names(int $courseid): array {
        global $DB;

        $names = $DB->get_fieldset_sql(
            "SELECT nr.name
               FROM {notificationsagent_rule} nr
               JOIN {notificationsagent_context} nctx ON nctx.ruleid = nr.id
              WHERE nctx.contextid = :contextid
                AND nctx.objectid = :courseid
                AND nr.deleted = 0",
            [
                'contextid' => CONTEXT_COURSE,
                'courseid' => $courseid,
            ]
        );

        return array_values($names);
    }

    /**
     * The restored teacher rule keeps its condition and its action.
     *
     * @param int $courseid
     */
    private function assert_teacher_rule_has_parts(int $courseid): void {
        global $DB;

        $ruleid = $DB->get_field_sql(
            "SELECT nr.id
               FROM {notificationsagent_rule} nr
               JOIN {notificationsagent_context} nctx ON nctx.ruleid = nr.id
              WHERE nctx.contextid = :contextid
                AND nctx.objectid = :courseid
                AND nr.name = :name
                AND nr.deleted = 0",
            [
                'contextid' => CONTEXT_COURSE,
                'courseid' => $courseid,
                'name' => self::TEACHER_RULE,
            ]
        );

        $this->assertNotFalse($ruleid);
        $this->assertTrue($DB->record_exists('notificationsagent_condition', ['ruleid' => $ruleid]));
        $this->assertTrue($DB->record_exists('notificationsagent_action', ['ruleid' => $ruleid]));
    }

    /**
     * Assert the createdby value of a restored rule in the course.
     *
     * @param int $courseid
     * @param string $name
     * @param int $expecteduserid
     */
    private function assert_rule_createdby(int $courseid, string $name, int $expecteduserid): void {
        global $DB;

        $createdby = $DB->get_field_sql(
            "SELECT nr.createdby
               FROM {notificationsagent_rule} nr
               JOIN {notificationsagent_context} nctx ON nctx.ruleid = nr.id
              WHERE nctx.contextid = :contextid
                AND nctx.objectid = :courseid
                AND nr.name = :name
                AND nr.deleted = 0",
            [
                'contextid' => CONTEXT_COURSE,
                'courseid' => $courseid,
                'name' => $name,
            ]
        );

        $this->assertEquals($expecteduserid, (int) $createdby);
    }

    /**
     * Skipped rules must not leave child rows pointing at rule id 0.
     */
    private function assert_no_orphan_children(): void {
        global $DB;

        $this->assertFalse($DB->record_exists('notificationsagent_condition', ['ruleid' => 0]));
        $this->assertFalse($DB->record_exists('notificationsagent_action', ['ruleid' => 0]));
        $this->assertFalse($DB->record_exists('notificationsagent_context', ['ruleid' => 0]));
    }
}
