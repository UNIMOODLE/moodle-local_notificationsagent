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
 * @package    notificationscondition_activitylastsend
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace notificationscondition_activitylastsend;

use local_notificationsagent\evaluationcontext;
use local_notificationsagent\notificationplugin;
use local_notificationsagent\helper\test\phpunitutil;
use notificationscondition_activitylastsend\activitylastsend;
use local_notificationsagent\form\editrule_form;
use local_notificationsagent\rule;

/**
 * Activitylastsend_test class.
 *
 * @group notificationsagent
 */
class activitylastsend_test extends \advanced_testcase {
    /**
     * @var rule
     */
    private static $rule;
    /**
     * @var activitylastsend
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
     * @var \stdClass
     */
    private static $cmtestls;
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
     * Date submission
     */
    public const USER_LASTSEND = 1704445200; // 05/01/2024.

    /**
     * Set up the test environment.
     */
    public function setUp(): void {
        parent::setUp();
        $this->resetAfterTest(true);
        self::$rule = new rule();

        self::$subplugin = new activitylastsend(self::$rule->to_record());
        self::$subplugin->set_id(5);
        self::$coursetest = self::getDataGenerator()->create_course(
            ['startdate' => self::COURSE_DATESTART, 'enddate' => self::COURSE_DATEEND]
        );
        self::$coursecontext = \context_course::instance(self::$coursetest->id);
        self::$user = self::getDataGenerator()->create_user();
        self::$context = new evaluationcontext();
        self::$context->set_userid(self::$user->id);
        self::$context->set_courseid(self::$coursetest->id);
        self::$subtype = 'activitylastsend';
        self::$elements = ['[TTTT]', '[AAAA]'];
        self::$cmtestls = self::getDataGenerator()->create_module(
            "assign",
            [
                        'name' => 'Assign unittest',
                        'course' => self::$coursetest->id,
                ],
        );
    }

    /**
     *  Test evaluate.
     *
     * @param int $timeaccess
     * @param string $param
     * @param bool $expected
     *
     * @covers       \notificationscondition_activitylastsend\activitylastsend::evaluate
     *
     * @dataProvider dataprovider
     */
    public function test_evaluate($timeaccess, $param, $expected) {
        global $DB;
        self::$context->set_params($param);
        self::$context->set_timeaccess($timeaccess);
        self::$subplugin->set_id(self::CONDITIONID);

        $params = json_decode(self::$context->get_params(), true);
        $objdb = new \stdClass();
        $objdb->userid = self::$user->id;
        $objdb->courseid = self::$coursetest->id;
        $objdb->startdate = self::COURSE_DATESTART + $params['time'];
        $objdb->pluginname = self::$subtype;
        $objdb->conditionid = self::CONDITIONID;
        // Insert.
        $DB->insert_record('notificationsagent_cache', $objdb);

        // Test evaluate.
        $result = self::$subplugin->evaluate(self::$context);
        $this->assertSame($expected, $result);
    }

    /**
     * Data provider for test_evaluate().
     */
    public static function dataprovider(): array {
        return [
                [1704445200, '{"time":864000}', false],
                [1706173200, '{"time":864000}', true],
                [1707123600, '{"time":864000}', true],
        ];
    }

    /**
     * Test get_subtype.
     *
     * @covers \notificationscondition_activitylastsend\activitylastsend::get_subtype
     */
    public function test_getsubtype() {
        $this->assertSame(self::$subtype, self::$subplugin->get_subtype());
    }

    /**
     * Test is generic.
     *
     * @covers \notificationscondition_activitylastsend\activitylastsend::is_generic
     */
    public function test_isgeneric() {
        $this->assertFalse(self::$subplugin->is_generic());
    }

    /**
     * Test get elements.
     *
     * @covers \notificationscondition_activitylastsend\activitylastsend::get_elements
     */
    public function test_getelements() {
        $this->assertSame(self::$elements, self::$subplugin->get_elements());
    }

    /**
     * Test check capability.
     *
     * @covers \notificationscondition_activitylastsend\activitylastsend::check_capability
     */
    public function test_checkcapability() {
        $this->assertSame(
            has_capability('notificationscondition/' . self::$subtype.':'.self::$subtype, self::$coursecontext),
            self::$subplugin->check_capability(self::$coursecontext)
        );
    }

    /**
     * Test estimate next time.
     *
     * @param int $timeaccess
     * @param string $param
     * @param int $complementary
     * @param bool $completion
     *
     * @covers       \notificationscondition_activitylastsend\activitylastsend::estimate_next_time
     * @dataProvider dataestimate
     */
    public function test_estimatenexttime($timeaccess, $param, $complementary, $completion) {
        \uopz_set_return('time', 1704099600);
        // Test estimate next time.
        self::$context->set_timeaccess($timeaccess);
        self::$context->set_complementary($complementary);
        self::$subplugin->set_id(self::CONDITIONID);

        $assgigngenerator = self::getDataGenerator()->get_plugin_generator('mod_assign');
        $assigncm = $assgigngenerator->create_instance([
                'course' => self::$coursetest->id,
        ]);
        $assigncontext = \context_module::instance($assigncm->cmid);
        if ($completion) {
            $fs = get_file_storage();
            $filerecord = [
                    'contextid' => $assigncontext->id,
                    'component' => 'mod_assign',
                    'filearea' => 'content',
                    'itemid' => 0,
                    'filepath' => '/',
                    'filename' => 'user-test-file.txt',
                    'userid' => self::$user->id,
                    'timecreated' => self::USER_LASTSEND,
                    'timemodified' => self::USER_LASTSEND,
            ];

            $fs->create_file_from_string($filerecord, 'User upload');
        }

        self::$context->set_params(json_encode(['time' => $param, 'cmid' => $assigncm->cmid]));
        // Test estimate next time.
        if (!$completion) {
            self::assertNull(self::$subplugin->estimate_next_time(self::$context));
        } else {
            if (self::$context->is_complementary()) {
                if ($timeaccess >= self::USER_LASTSEND && $timeaccess <= self::USER_LASTSEND + $param) {
                    self::assertEquals(time(), self::$subplugin->estimate_next_time(self::$context));
                } else if ($timeaccess > self::USER_LASTSEND + $param) {
                    self::assertNull(self::$subplugin->estimate_next_time(self::$context));
                }
            } else {
                if ($timeaccess >= self::USER_LASTSEND && $timeaccess <= self::USER_LASTSEND + $param) {
                    self::assertEquals(self::USER_LASTSEND + $param, self::$subplugin->estimate_next_time(self::$context));
                } else if ($timeaccess > self::USER_LASTSEND + $param) {
                    self::assertEquals(time(), self::$subplugin->estimate_next_time(self::$context));
                }
            }
        }
        \uopz_unset_return('time');
    }

    /**
     * Data provider for test_estimatenexttime
     */
    public static function dataestimate(): array {
        return [
                [1704186000, 864000, notificationplugin::COMPLEMENTARY_CONDITION, false],
                [1705741200, 864000, notificationplugin::COMPLEMENTARY_CONDITION, true],
                [1704186000, 864000, notificationplugin::COMPLEMENTARY_CONDITION, true],
                [self::USER_LASTSEND + 120, 864000, notificationplugin::COMPLEMENTARY_CONDITION, true],
                [1705741200, 864000, notificationplugin::COMPLEMENTARY_EXCEPTION, true],
                [self::USER_LASTSEND + 120, 864000, notificationplugin::COMPLEMENTARY_EXCEPTION, true],
        ];
    }

    /**
     * Test get title.
     *
     * @covers \notificationscondition_activitylastsend\activitylastsend::get_title
     */
    public function test_gettitle() {
        $this->assertNotNull(self::$subplugin->get_title());
        foreach (self::$elements as $element) {
            $this->assertStringContainsString($element, self::$subplugin->get_title());
        }
    }

    /**
     * Test convert parameters.
     *
     * @covers \notificationscondition_activitylastsend\activitylastsend::convert_parameters
     */
    public function test_convertparameters() {
        $id = self::$subplugin->get_id();
        $params = [
                $id . "_activitylastsend_days" => "1",
                $id . "_activitylastsend_hours" => "0",
                $id . "_activitylastsend_minutes" => "1",
                $id . "_activitylastsend_cmid" => "7",
        ];
        $expected = '{"time":86460,"cmid":7}';
        $method = phpunitutil::get_method(self::$subplugin, 'convert_parameters');
        $result = $method->invoke(self::$subplugin, $params);
        $this->assertSame($expected, $result);
    }

    /**
     * Test process markups.
     *
     * @covers \notificationscondition_activitylastsend\activitylastsend::process_markups
     */
    public function test_processmarkups() {
        $quizgenerator = self::getDataGenerator()->get_plugin_generator('mod_quiz');
        $cmgen = $quizgenerator->create_instance([
                'course' => self::$coursetest->id,
        ]);
        $time = '86460';
        $params[self::$subplugin::UI_TIME] = $time;
        $params[self::$subplugin::UI_ACTIVITY] = $cmgen->cmid;
        $params = json_encode($params);
        $expected = str_replace(
            self::$subplugin->get_elements(),
            [\local_notificationsagent\helper\helper::to_human_format($time, true), $cmgen->name],
            self::$subplugin->get_title()
        );
        self::$subplugin->set_parameters($params);
        $content = [];
        self::$subplugin->process_markups($content, self::$coursetest->id);
        $this->assertSame([$expected], $content);
    }

    /**
     * Test get ui.
     *
     * @covers \notificationscondition_activitylastsend\activitylastsend::get_ui
     */
    public function test_getui() {
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
        $uigroupname = $method->invoke(self::$subplugin, self::$subplugin->get_subtype());
        $uigroupelements = [];
        foreach ($mform->getElement($uigroupname)->getElements() as $element) {
            $uigroupelements[] = $element->getName();
        }
        $uidays = $method->invoke(self::$subplugin, self::$subplugin::UI_DAYS);
        $uihours = $method->invoke(self::$subplugin, self::$subplugin::UI_HOURS);
        $uiminutes = $method->invoke(self::$subplugin, self::$subplugin::UI_MINUTES);

        $this->assertTrue($mform->elementExists($uiactivityname));
        $this->assertTrue($mform->elementExists($uigroupname));
        $this->assertTrue(in_array($uidays, $uigroupelements));
        $this->assertTrue(in_array($uihours, $uigroupelements));
        $this->assertTrue(in_array($uiminutes, $uigroupelements));
    }

    /**
     * Test set default.
     *
     * @covers \notificationscondition_activitylastsend\activitylastsend::set_default
     */
    public function test_setdefault() {
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
        $addjson = phpunitutil::get_method($form, 'addjson');
        $addjson->invoke($form, notificationplugin::TYPE_CONDITION, self::$subplugin::NAME);
        $form->definition_after_data();

        $mform = phpunitutil::get_property($form, '_form');
        $jsoncondition = $mform->getElementValue(editrule_form::FORM_JSON_CONDITION);
        $arraycondition = array_keys(json_decode($jsoncondition, true));
        $id = $arraycondition[0];// Temp value.
        self::$subplugin->set_id($id);

        $method = phpunitutil::get_method(self::$subplugin, 'get_name_ui');
        $uigroupname = $method->invoke(self::$subplugin, self::$subplugin->get_subtype());
        $defaulttime = [];
        foreach ($mform->getElement($uigroupname)->getElements() as $element) {
            $defaulttime[$element->getName()] = $element->getValue();
        }

        $uidays = $method->invoke(self::$subplugin, self::$subplugin::UI_DAYS);
        $uihours = $method->invoke(self::$subplugin, self::$subplugin::UI_HOURS);
        $uiminutes = $method->invoke(self::$subplugin, self::$subplugin::UI_MINUTES);

        $this->assertTrue(isset($defaulttime[$uidays]) && $defaulttime[$uidays] == self::$subplugin::UI_DAYS_DEFAULT_VALUE);
        $this->assertTrue(isset($defaulttime[$uihours]) && $defaulttime[$uihours] == self::$subplugin::UI_HOURS_DEFAULT_VALUE);
        $this->assertTrue(
            isset($defaulttime[$uiminutes]) && $defaulttime[$uiminutes] == self::$subplugin::UI_MINUTES_DEFAULT_VALUE
        );
    }

    /**
     * Test get cm id files.
     *
     * @param string $fileuploadtime
     * @param string $crontimestarted
     *
     * @throws \coding_exception
     * @throws \file_exception
     * @throws \stored_file_creation_exception
     * @covers       \notificationscondition_activitylastsend\activitylastsend::get_cmidfiles
     * @dataProvider dataprovidercmifiles
     */
    public function test_get_cmidfiles($fileuploadtime, $crontimestarted) {
        $assgigngenerator = self::getDataGenerator()->get_plugin_generator('mod_assign');
        $assigncm = $assgigngenerator->create_instance([
                'course' => self::$coursetest->id,
        ]);
        $assigncontext = \context_module::instance($assigncm->cmid);

        if (!is_null($fileuploadtime)) {
            $fs = get_file_storage();
            $filerecord = [
                    'contextid' => $assigncontext->id,
                    'component' => 'mod_assign',
                    'filearea' => 'content',
                    'itemid' => 0,
                    'filepath' => '/',
                    'filename' => 'user-test-file.txt',
                    'userid' => self::$user->id,
                    'timecreated' => $fileuploadtime,
                    'timemodified' => $fileuploadtime,
            ];

            $fs->create_file_from_string($filerecord, 'User upload');
        }

        $data = activitylastsend::get_cmidfiles(
            $assigncm->cmid,
            self::$user->id,
            86400,
            $crontimestarted
        );

        if (!is_null($fileuploadtime)) {
            $this->assertEquals($fileuploadtime, $data->timemodified);
            $this->assertEquals(self::$user->id, $data->userid);
        } else {
            $this->assertEmpty($data);
        }
    }

    /**
     * Data provider for test_get_cmidfiles().
     */
    public static function dataprovidercmifiles(): array {
        return [
                'Testing a file that was not uploaded' => [null, time()],
                'Testing a file that was uploaded 2 minutes ago' => [1709014050, 1709014170],
                'Testing a file that was uploaded 5 minutes ago' => [1711650868, 1711651168],
                'Testing a file that was uploaded several days ago' => [1709022090, 1709116470],
        ];
    }

    /**
     * Test validation.
     *
     * @covers       \notificationscondition_activitylastsend\activitylastsend::validation
     */
    public function test_validation() {
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
