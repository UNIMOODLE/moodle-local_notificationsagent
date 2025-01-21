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
 * Tests for activitymodified.
 *
 * @group notificationsagent
 */
final class activitymodified_test extends \advanced_testcase {
    /**
     * @var rule
     */
    private static $rule;

    /**
     * @var activitymodified
     */
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

    /**
     * @var int
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
        $this->resetAfterTest(true);
        self::$rule = new rule();

        self::$subplugin = new activitymodified(self::$rule->to_record());
        self::$subplugin->set_id(5);
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
     * Test for evaluating the condition.
     *
     * @param int  $timeaccess    timeaccess in timestamp
     * @param bool $usecache      use cache or not
     * @param bool $useuploadfile use upload file or not
     * @param bool $expected      expected result
     * @param array $complementary
     *
     * @dataProvider dataprovider
     * @covers       \notificationscondition_activitymodified\activitymodified::evaluate
     * @covers       \notificationscondition_activitymodified\activitymodified::estimate_next_time
     */
    public function test_evaluate($timeaccess, $usecache, $useuploadfile, $expected, $complementary): void {
        global $DB;

        self::$context->set_params(
            json_encode(
                ['cmid' => self::$activity->cmid],
            )
        );
        self::$context->set_timeaccess($timeaccess);
        self::$context->set_complementary($complementary);
        self::$subplugin->set_id(self::CONDITIONID);

        \uopz_set_return('time', self::$context->get_timeaccess());
        $activityctx = \context_module::instance(self::$activity->cmid);

        if ($usecache) {
            $objdb = new \stdClass();
            $objdb->userid = self::$user->id;
            $objdb->courseid = self::$coursetest->id;
            $objdb->startdate = self::COURSE_DATESTART + 864000;
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
        if ($result && !self::$context->is_complementary()) {
            $this->assertEquals(time(), self::$subplugin->estimate_next_time(self::$context));
        }

        if (!$result && self::$context->is_complementary()) {
            $this->assertEquals(time(), self::$subplugin->estimate_next_time(self::$context));
        }

        \uopz_unset_return('time');
    }

    /**
     * Data provider for test_evaluate.
     */
    public static function dataprovider(): array {
        return [
            'Testing evaluate with cache #0' => [1704445200, true, false, false, notificationplugin::COMPLEMENTARY_EXCEPTION],
            'Testing evaluate with cache #1' => [1706173200, true, false, true, notificationplugin::COMPLEMENTARY_CONDITION],
            'Testing evaluate with cache #2' => [1707123600, true, false, true, notificationplugin::COMPLEMENTARY_CONDITION],
            'Testing evaluate without cache and an uploaded file 1 minute ago' => [
                1707123600, false, true, true, notificationplugin::COMPLEMENTARY_CONDITION,
            ],
            'Testing evaluate without cache and not uploaded file' => [
                1707123600, false, false, false, notificationplugin::COMPLEMENTARY_EXCEPTION,
            ],
        ];
    }

    /**
     * Test for getting the subtype.
     *
     * @covers \notificationscondition_activitymodified\activitymodified::get_subtype
     */
    public function test_getsubtype(): void {
        $this->assertSame(self::$subtype, self::$subplugin->get_subtype());
    }

    /**
     * Test for checking if the condition is generic.
     *
     * @covers \notificationscondition_activitymodified\activitymodified::is_generic
     */
    public function test_isgeneric(): void {
        $this->assertTrue(self::$subplugin->is_generic());
    }

    /**
     * Test for getting the elements.
     *
     * @covers \notificationscondition_activitymodified\activitymodified::get_elements
     */
    public function test_getelements(): void {
        $this->assertSame(self::$elements, self::$subplugin->get_elements());
    }

    /**
     * Test for checking the capability.
     *
     * @covers \notificationscondition_activitymodified\activitymodified::check_capability
     */
    public function test_checkcapability(): void {
        $this->assertSame(
            has_capability('local/notificationsagent:' . self::$subtype, self::$coursecontext),
            self::$subplugin->check_capability(self::$coursecontext)
        );
    }

    /**
     * Test for getting the title.
     *
     * @covers \notificationscondition_activitymodified\activitymodified::get_title
     */
    public function test_gettitle(): void {
        $this->assertNotNull(self::$subplugin->get_title());
        foreach (self::$elements as $element) {
            $this->assertStringContainsString($element, self::$subplugin->get_title());
        }
    }

    /**
     * Test for converting the parameters.
     *
     * @covers \notificationscondition_activitymodified\activitymodified::convert_parameters
     */
    public function test_convertparameters(): void {
        $id = self::$subplugin->get_id();
        $params = [
            $id . "_activitymodified_cmid" => "7",
        ];
        $expected = '{"cmid":7}';
        $method = phpunitutil::get_method(self::$subplugin, 'convert_parameters');
        $result = $method->invoke(self::$subplugin, $params);
        $this->assertSame($expected, $result);
    }

    /**
     * Test for processing markups.
     *
     * @covers \notificationscondition_activitymodified\activitymodified::process_markups
     */
    public function test_processmarkups(): void {
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
     * Test for getting the UI.
     *
     * @covers \notificationscondition_activitymodified\activitymodified::get_ui
     */
    public function test_getui(): void {
        $courseid = self::$coursetest->id;
        $typeaction = "add";
        $customdata = [
            'rule' => self::$rule->to_record(),
            'timesfired' => rule::MINIMUM_EXECUTION,
            'courseid' => $courseid,
            'getaction' => $typeaction,
        ];

        $form = new editrule_form(new \moodle_url('/'), $customdata);
        $form->definition();
        $form->definition_after_data();
        $mform = phpunitutil::get_property($form, '_form');
        $subtype = notificationplugin::TYPE_CONDITION;
        self::$subplugin->get_ui($mform, $courseid, $subtype);

        $method = phpunitutil::get_method(self::$subplugin, 'get_name_ui');
        $uiactivityname = $method->invoke(self::$subplugin, self::$subplugin::UI_ACTIVITY);

        $this->assertTrue($mform->elementExists($uiactivityname));
    }

    /**
     * Test get any new content
     *
     * @param int  $fileuploadtime
     * @param int  $eventtimecreation
     * @param bool $expected
     *
     * @return void
     * @covers       \notificationscondition_activitymodified\activitymodified::get_any_new_content
     * @dataProvider dataprovider_getanynewcontent
     */
    public function test_get_any_new_content($fileuploadtime, $eventtimecreation, $expected): void {
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
            $activity->cmid,
            $eventtimecreation
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

    /**
     * Test validation.
     *
     * @covers       \notificationscondition_activitymodified\activitymodified::validation
     */
    public function test_validation(): void {
        $quizgenerator = self::getDataGenerator()->get_plugin_generator('mod_quiz');
        $cmtestaa = $quizgenerator->create_instance([
            'name' => 'Quiz unittest',
            'course' => self::$coursetest->id,
            'visible' => true,
        ]);
        $objparameters = new \stdClass();
        $objparameters->cmid = $cmtestaa->cmid;

        self::$subplugin->set_parameters(json_encode($objparameters));
        $this->assertTrue(self::$subplugin->validation(self::$coursetest->id));
    }
}
