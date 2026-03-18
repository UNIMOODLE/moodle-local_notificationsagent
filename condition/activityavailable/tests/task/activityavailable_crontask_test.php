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
 * Version details
 *
 * @package    notificationscondition_activityavailable
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace notificationscondition_activityavailable\task;

use local_notificationsagent\rule;
use notificationscondition_activityavailable\activityavailable;

/**
 * Class for testing the activityavailable_crontask task.
 *
 * @group notificationsagent
 */
final class activityavailable_crontask_test extends \advanced_testcase {
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
    public const COURSE_DATEEND = 1706605200; // 30/01/2024 10:00:00,
    /**
     * Activity date start
     */
    public const CM_DATESTART = 1704099600; // 01/01/2024 10:00:00,
    /**
     * Activity date end
     */
    public const CM_DATEEND = 1705741200; // 20/01/2024 10:00:00,

    public function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
        self::$user = self::getDataGenerator()->create_user();
    }

    /**
     * Task execute: visible courses get cache triggers; hidden courses are skipped.
     *
     * @param int $visible Course visible flag (0 = hidden, 1 = visible).
     * @param bool $expecttrigger Whether a trigger row should exist after execute.
     *
     * @covers       \notificationscondition_activityavailable\task\activityavailable_crontask::execute
     * @covers       \local_notificationsagent\helper\helper::custom_mtrace
     * @dataProvider data_provider_execute
     */
    public function test_execute(int $visible, bool $expecttrigger): void {
        global $DB;

        $course = self::getDataGenerator()->create_course([
                'startdate' => self::COURSE_DATESTART,
                'enddate' => self::COURSE_DATEEND,
                'visible' => $visible,
        ]);
        self::getDataGenerator()->enrol_user(self::$user->id, $course->id);

        $pluginname = activityavailable::NAME;
        $rule = new rule();

        $quizgen = self::getDataGenerator()->get_plugin_generator('mod_quiz');
        $cmtestacct = $quizgen->create_instance([
                'course' => $course->id,
                'timeopen' => self::CM_DATESTART,
                'timeclose' => self::CM_DATEEND,
        ]);

        $dataform = new \StdClass();
        $dataform->title = "Rule Test";
        $dataform->type = 1;
        $dataform->courseid = $course->id;
        $dataform->timesfired = 2;
        $dataform->runtime_group = ['runtime_days' => 5, 'runtime_hours' => 0, 'runtime_minutes' => 0];
        $this->setUser(self::$user);
        $ruleid = $rule->create($dataform);
        $rule->set_id($ruleid);

        $objdb = new \stdClass();
        $objdb->ruleid = $rule->get_id();
        $objdb->courseid = $course->id;
        $objdb->type = 'condition';
        $objdb->pluginname = $pluginname;
        $objdb->parameters = '{"cmid":' . $cmtestacct->cmid . '}';
        $objdb->cmid = $cmtestacct->id;

        $conditionid = $DB->insert_record('notificationsagent_condition', $objdb);
        $this->assertIsInt($conditionid);
        rule::create_instance($ruleid);

        $task = \core\task\manager::get_scheduled_task(activityavailable_crontask::class);
        $task->execute();
        $trigger = $DB->get_record(
            'notificationsagent_triggers',
            [
                        'conditionid' => $conditionid,
                        'userid' => self::$user->id,
                        'courseid' => $course->id,
                        'ruleid' => $rule->get_id(),
            ]
        );

        if ($expecttrigger) {
            $this->assertNotFalse($trigger);
            $this->assertEquals($course->id, $trigger->courseid);
            $this->assertEquals(self::$user->id, $trigger->userid);
            $this->assertEquals($rule->get_id(), $trigger->ruleid);
        } else {
            $this->assertFalse($trigger);
        }
    }

    /**
     * Visible vs hidden course expectations for test_execute.
     *
     * @return array<string, array{int, bool}>
     */
    public static function data_provider_execute(): array {
        return [
                'visible course generates trigger' => [1, true],
                'hidden course skips trigger' => [0, false],
        ];
    }

    /**
     * Get name test
     *
     * @covers \notificationscondition_activityavailable\task\activityavailable_crontask::get_name
     * @return void
     */
    public function test_get_name(): void {
        $task = \core\task\manager::get_scheduled_task(activityavailable_crontask::class);

        $this->assertIsString($task->get_name());
    }
}
