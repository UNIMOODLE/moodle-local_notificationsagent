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
 * @package    notificationscondition_activitymodified
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace notificationscondition_activitymodified;

use local_notificationsagent\evaluationcontext;
use local_notificationsagent\notificationplugin;
use local_notificationsagent\helper\test\phpunitutil;
use local_notificationsagent\rule;
use notificationscondition_activitymodified\activitymodified;
use local_notificationsagent\form\editrule_form;

/**
 * @group notificationsagent
 */
class activitymodified_test extends \advanced_testcase {

    /**
     * @var rule
     */
    private static $rule;
    private static $subplugin;
    /**
     * @var \stdClass
     */
    private static $coursetest;
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

    public function setUp(): void {
        parent::setUp();
        $this->resetAfterTest(true);
        self::$rule = new rule();

        self::$subplugin = new activitymodified(self::$rule);
        self::$coursetest = self::getDataGenerator()->create_course(
            ['startdate' => self::COURSE_DATESTART, 'enddate' => self::COURSE_DATEEND]
        );
        self::$coursecontext = \context_course::instance(self::$coursetest->id);
        self::$user = self::getDataGenerator()->create_user();
        self::$context = new evaluationcontext();
        self::$context->set_userid(self::$user->id);
        self::$context->set_courseid(self::$coursetest->id);
        self::$subtype = 'activitymodified';
        self::$elements = ['[AAAA]'];
        self::$activity = self::getDataGenerator()->create_module('assign', ['course' => self::$coursetest->id]);
    }

    /**
     *
     * @param int  $timeaccess    Time access
     * @param bool $usecache      Use cache?
     * @param bool $useuploadfile Use an uploaded file?
     * @param bool $expected      Expected result
     *
     * @covers       \notificationscondition_activitymodified\activitymodified::evaluate
     *
     * @dataProvider dataprovider
     */
    public function test_evaluate($timeaccess, $usecache, $useuploadfile, $expected) {
        global $DB;

        self::$context->set_params(
            json_encode(
                ['cmid' => self::$activity->cmid],
            )
        );
        self::$context->set_timeaccess($timeaccess);
        self::$subplugin->set_id(self::CONDITIONID);

        uopz_set_return('time', self::$context->get_timeaccess());
        $activityctx = \context_module::instance(self::$activity->cmid);

        if ($usecache) {
            $objdb = new \stdClass();
            $objdb->userid = self::$user->id;
            $objdb->courseid = self::$coursetest->id;
            $objdb->timestart = self::COURSE_DATESTART + 864000;
            $objdb->pluginname = self::$subtype;
            $objdb->conditionid = self::CONDITIONID;
            // Insert.
            $DB->insert_record('notificationsagent_cache', $objdb);
        }

        if ($useuploadfile) {
            $fs = get_file_storage();
            $filerecord = [
                'contextid' => $activityctx->id,
                'component' => 'mod_assign',
                'filearea' => 'content',
                'itemid' => 0,
                'filepath' => '/',
                'filename' => 'user-test-file.txt',
                'userid' => self::$user->id,
                'timecreated' => $timeaccess + 60,
                'timemodified' => $timeaccess + 60,
            ];

            $fs->create_file_from_string($filerecord, 'User upload');
        }

        // Test evaluate.
        $result = self::$subplugin->evaluate(self::$context);
        $this->assertSame($expected, $result);

        uopz_unset_return('time');
    }

    public static function dataprovider(): array {
        return [
            'Testing evaluate with cache #0' => [1704445200, true, false, false],
            'Testing evaluate with cache #1' => [1706173200, true, false, true],
            'Testing evaluate with cache #2' => [1707123600, true, false, true],
            'Testing evaluate without cache and an uploaded file 1 minute ago' => [1707123600, false, true, true],
            'Testing evaluate without cache and not uploaded file' => [1707123600, false, false, false],
        ];
    }

    /**
     * @covers \notificationscondition_activitymodified\activitymodified::get_subtype
     */
    public function test_getsubtype() {
        $this->assertSame(self::$subtype, self::$subplugin->get_subtype());
    }

    /**
     * @covers \notificationscondition_activitymodified\activitymodified::is_generic
     */
    public function test_isgeneric() {
        $this->assertTrue(self::$subplugin->is_generic());
    }

    /**
     * @covers \notificationscondition_activitymodified\activitymodified::get_elements
     */
    public function test_getelements() {
        $this->assertSame(self::$elements, self::$subplugin->get_elements());
    }

    /**
     * @covers \notificationscondition_activitymodified\activitymodified::check_capability
     */
    public function test_checkcapability() {
        $this->assertSame(
            has_capability('local/notificationsagent:' . self::$subtype, self::$coursecontext),
            self::$subplugin->check_capability(self::$coursecontext)
        );
    }

    /**
     * @covers \notificationscondition_activitymodified\activitymodified::get_title
     */
    public function test_gettitle() {
        $this->assertNotNull(self::$subplugin->get_title());
        foreach (self::$elements as $element) {
            $this->assertStringContainsString($element, self::$subplugin->get_title());
        }
    }

    /**
     * @covers \notificationscondition_activitymodified\activitymodified::estimate_next_time
     */
    public function test_estimatenexttime() {
        self::assertNull(self::$subplugin->estimate_next_time(self::$context));
    }

    /**
     * @covers \notificationscondition_activitymodified\activitymodified::convert_parameters
     */
    public function test_convertparameters() {
        $params = [
            "5_activitymodified_cmid" => "7",
        ];
        $expected = '{"cmid":7}';
        $method = phpunitutil::get_method(self::$subplugin, 'convert_parameters');
        $result = $method->invoke(self::$subplugin, 5, $params);
        $this->assertSame($expected, $result);
    }

    /**
     * @covers \notificationscondition_activitymodified\activitymodified::process_markups
     */
    public function test_processmarkups() {
        $quizgenerator = self::getDataGenerator()->get_plugin_generator('mod_quiz');
        $cmgen = $quizgenerator->create_instance([
            'course' => self::$coursetest->id,
        ]);
        $params[self::$subplugin::UI_ACTIVITY] = $cmgen->cmid;
        $params = json_encode($params);
        $expected = str_replace(self::$subplugin->get_elements(), [$cmgen->name], self::$subplugin->get_title());
        self::$subplugin->set_parameters($params);
        $content = [];
        self::$subplugin->process_markups($content, self::$coursetest->id);
        $this->assertSame([$expected], $content);
    }

    /**
     * @covers \notificationscondition_activitymodified\activitymodified::get_ui
     */
    public function test_getui() {
        $courseid = self::$coursetest->id;
        $ruletype = rule::RULE_TYPE;
        $typeaction = "add";
        $customdata = [
            'ruleid' => self::$rule->get_id(),
            notificationplugin::TYPE_CONDITION => self::$rule->get_conditions(),
            notificationplugin::TYPE_EXCEPTION => self::$rule->get_exceptions(),
            notificationplugin::TYPE_ACTION => self::$rule->get_actions(),
            'type' => $ruletype,
            'timesfired' => rule::MINIMUM_EXECUTION,
            'courseid' => $courseid,
            'getaction' => $typeaction,
        ];

        $form = new editrule_form(new \moodle_url('/'), $customdata);
        $form->definition();
        $form->definition_after_data();
        $mform = phpunitutil::get_property($form, '_form');
        $id = time();
        $subtype = notificationplugin::TYPE_CONDITION;
        self::$subplugin->get_ui($mform, $id, $courseid, $subtype);

        $method = phpunitutil::get_method(self::$subplugin, 'get_name_ui');
        $uiactivityname = $method->invoke(self::$subplugin, $id, self::$subplugin::UI_ACTIVITY);

        $this->assertTrue($mform->elementExists($uiactivityname));
    }

    /**
     * @return void
     * @throws \coding_exception
     * @throws \file_exception
     * @throws \stored_file_creation_exception
     * @covers       \notificationscondition_activitymodified\activitymodified::get_any_new_content
     * @dataProvider dataprovider_getanynewcontent
     */
    public function test_get_any_new_content($fileuploadtime, $eventtimecreation, $expected) {
        $activity = $this->getDataGenerator()->create_module('assign', ['course' => self::$coursetest->id]);
        $activityctx = \context_module::instance($activity->cmid);

        $editingteacher = self::getDataGenerator()->create_and_enrol(self::$coursetest, 'editingteacher');

        if (!is_null($fileuploadtime)) {
            $fs = get_file_storage();
            $filerecord = [
                'contextid' => $activityctx->id,
                'component' => 'mod_assign',
                'filearea' => 'content',
                'itemid' => 0,
                'filepath' => '/',
                'filename' => 'teacher-test-file.txt',
                'userid' => $editingteacher->id,
                'timecreated' => $fileuploadtime,
                'timemodified' => $fileuploadtime,
            ];

            $fs->create_file_from_string($filerecord, 'Teacher upload');
        }

        $istherecontent = activitymodified::get_any_new_content(
            $activity->cmid, $eventtimecreation
        );

        if (!is_null($fileuploadtime)) {
            $this->assertEquals($istherecontent, $expected);
        } else {
            $this->assertFalse($istherecontent);
        }
    }

    /**
     * Set up the data to be used in the test_get_any_new_content execution.
     *
     * @return array
     */
    public static function dataprovider_getanynewcontent(): array {
        return [
            'Testing a file that was not uploaded' => [null, 1709796705, false],
            'Testing a file that was uploaded 1 second ago' => [1709793643, 1709793644, true],
            'Testing a file that was uploaded 30 seconds ago' => [1709793809, 1709793839, true],
            'Testing a file that was uploaded 59 seconds ago' => [1709793960, 1709794019, true],
            'Testing a file that was uploaded 1 minute ago' => [1709793104, 1709793164, true],
            'Testing a file that was uploaded 2 minutes ago' => [1709794112, 1709794232, false],
            'Testing a file that was uploaded 7 minutes ago' => [1709880112, 1709880532, false],
            'Testing a file that was uploaded several days ago' => [1709966932, 1711443119, false],
        ];
    }
}

