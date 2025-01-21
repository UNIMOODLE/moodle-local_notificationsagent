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
 * @package    notificationscondition_activityopen
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace notificationscondition_activityopen;

use local_notificationsagent\evaluationcontext;
use local_notificationsagent\form\editrule_form;
use local_notificationsagent\notificationplugin;
use local_notificationsagent\helper\test\phpunitutil;
use local_notificationsagent\rule;
use notificationscondition_activityopen\activityopen;

/**
 * Class for testing the activityopen observer.
 *
 * @group notificationsagent
 */
final class activityopen_test extends \advanced_testcase {
    /**
     * @var rule
     */
    private static $rule;
    /**
     * @var activityopen
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
    private static $cmtestao;
    /**
     * id for condition
     */
    public const CONDITIONID = 1;
    /**
     * id for condition
     */
    public const CMID = 246000;
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

    /**
     * Set up the test environment.
     */
    public function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
        self::$rule = new rule();

        self::$subplugin = new activityopen(self::$rule->to_record());
        self::$subplugin->set_id(5);
        self::$subplugin->set_id(self::CONDITIONID);
        self::$coursetest = self::getDataGenerator()->create_course(
            ['startdate' => self::COURSE_DATESTART, 'enddate' => self::COURSE_DATEEND]
        );
        self::$coursecontext = \context_course::instance(self::$coursetest->id);
        self::$user = self::getDataGenerator()->create_user();
        self::$context = new evaluationcontext();
        self::$context->set_userid(self::$user->id);
        self::$context->set_courseid(self::$coursetest->id);
        self::$subtype = 'activityopen';
        self::$elements = ['[TTTT]', '[AAAA]'];
        $quizgenerator = self::getDataGenerator()->get_plugin_generator('mod_quiz');
        self::$cmtestao = $quizgenerator->create_instance([
                'course' => self::$coursetest->id,
                "timeopen" => self::CM_DATESTART,
                "timeclose" => self::CM_DATEEND,
        ]);
    }

    /**
     * Test evaluate.
     *
     * @param int $timeaccess
     * @param bool $usecache
     * @param int $param
     * @param bool $complementary
     * @param bool $expected
     *
     * @covers       \notificationscondition_activityopen\activityopen::evaluate
     *
     * @dataProvider dataprovider
     * @throws \coding_exception
     */
    public function test_evaluate($timeaccess, $usecache, $param, $complementary, $expected): void {
        self::$context->set_timeaccess($timeaccess);
        self::$context->set_complementary($complementary);

        $cm = get_coursemodule_from_instance('quiz', self::$cmtestao->id, self::$coursetest->id);
        self::$context->set_params(json_encode(['time' => $param, 'cmid' => $cm->id]));

        if ($usecache) {
            global $DB;
            $objdb = new \stdClass();
            $objdb->userid = self::$user->id;
            $objdb->courseid = self::$coursetest->id;
            $objdb->startdate = self::$cmtestao->timeopen + $param;
            $objdb->pluginname = self::$subtype;
            $objdb->conditionid = self::CONDITIONID;
            // Insert.
            $DB->insert_record('notificationsagent_cache', $objdb);
        }

        // Test evaluate.
        $result = self::$subplugin->evaluate(self::$context);
        $this->assertSame($expected, $result);
        // Test estimate next time.
    }

    /**
     * Data provider for test_evaluate.
     */
    public static function dataprovider(): array {
        return [
                [1704445200, false, 864000, notificationplugin::COMPLEMENTARY_CONDITION, false],
                [1704445200, true, 864000, notificationplugin::COMPLEMENTARY_EXCEPTION, false],
                [1705050000, false, 864000, notificationplugin::COMPLEMENTARY_EXCEPTION, true],
                [1705050000, true, 864000, notificationplugin::COMPLEMENTARY_CONDITION, true],

        ];
    }

    /**
     * Test get_subtype.
     *
     * @covers \notificationscondition_activityopen\activityopen::get_subtype
     */
    public function test_getsubtype(): void {
        $this->assertSame(self::$subtype, self::$subplugin->get_subtype());
    }

    /**
     * Test is_generic.
     *
     * @covers \notificationscondition_activityopen\activityopen::is_generic
     */
    public function test_isgeneric(): void {
        $this->assertTrue(self::$subplugin->is_generic());
    }

    /**
     * Test get_elements.
     *
     * @covers \notificationscondition_activityopen\activityopen::get_elements
     */
    public function test_getelements(): void {
        $this->assertSame(self::$elements, self::$subplugin->get_elements());
    }

    /**
     * Test check_capability.
     *
     * @covers \notificationscondition_activityopen\activityopen::check_capability
     */
    public function test_checkcapability(): void {
        $this->assertSame(
            has_capability('local/notificationsagent:' . self::$subtype, self::$coursecontext),
            self::$subplugin->check_capability(self::$coursecontext)
        );
    }

    /**
     * Test estimate next time.
     *
     * @param int $timeaccess
     * @param string $param
     * @param bool $complementary
     *
     * @covers       \notificationscondition_activityopen\activityopen::estimate_next_time
     * @dataProvider dataestimate
     */
    public function test_estimatenexttime($timeaccess, $param, $complementary): void {
        self::$context->set_complementary($complementary);
        self::$context->set_params($param);
        self::$context->set_timeaccess($timeaccess);
        $cm = get_coursemodule_from_instance('quiz', self::$cmtestao->id, self::$coursetest->id);
        self::$context->set_params(json_encode(['time' => $param, 'cmid' => $cm->id]));
        $params = json_decode(self::$context->get_params(), false);
        // Condition.
        if (!self::$context->is_complementary()) {
            if (
                $timeaccess >= self::CM_DATESTART
                    && ($timeaccess <= self::CM_DATESTART +
                            $params->{notificationplugin::UI_TIME})
            ) {
                self::assertEquals(
                    self::CM_DATESTART + $params->{notificationplugin::UI_TIME},
                    self::$subplugin->estimate_next_time(self::$context)
                );
            } else {
                self::assertEquals(time(), self::$subplugin->estimate_next_time(self::$context));
            }
        }
        // Exception.
        if (self::$context->is_complementary()) {
            if (
                $timeaccess >= self::CM_DATESTART
                    && $timeaccess <= self::CM_DATESTART +
                    $params->{notificationplugin::UI_TIME}
            ) {
                $this->assertEquals(time(), self::$subplugin->estimate_next_time(self::$context));
            } else {
                self::assertNull(self::$subplugin->estimate_next_time(self::$context));
            }
        }
    }

    /**
     * Data provider for test_estimatenexttime.
     */
    public static function dataestimate(): array {
        return [
                [1704445200, 864000, notificationplugin::COMPLEMENTARY_CONDITION],
                [1704445200, 864000, notificationplugin::COMPLEMENTARY_EXCEPTION],
                [1705050000, 864000, notificationplugin::COMPLEMENTARY_EXCEPTION],
                [1705050000, 864000, notificationplugin::COMPLEMENTARY_CONDITION],

        ];
    }

    /**
     * Test get_title.
     *
     * @covers \notificationscondition_activityopen\activityopen::get_title
     */
    public function test_gettitle(): void {
        $this->assertNotNull(self::$subplugin->get_title());
        foreach (self::$elements as $element) {
            $this->assertStringContainsString($element, self::$subplugin->get_title());
        }
    }

    /**
     * Test get_description.
     *
     * @covers \notificationscondition_activityopen\activityopen::get_description
     */
    public function test_getdescription(): void {
        $this->assertSame(
            self::$subplugin->get_description(),
            [
                        'title' => self::$subplugin->get_title(),
                        'name' => self::$subplugin->get_subtype(),
                ]
        );
    }

    /**
     * Test convert parameters.
     *
     * @covers \notificationscondition_activityopen\activityopen::convert_parameters
     */
    public function test_convertparameters(): void {
        $id = self::$subplugin->get_id();
        $params = [
                $id . "_activityopen_days" => "1",
                $id . "_activityopen_hours" => "0",
                $id . "_activityopen_minutes" => "1",
                $id . "_activityopen_cmid" => "7",
        ];
        $expected = '{"time":86460,"cmid":7}';
        $method = phpunitutil::get_method(self::$subplugin, 'convert_parameters');
        $result = $method->invoke(self::$subplugin, $params);
        $this->assertSame($expected, $result);
    }

    /**
     * Test process_markups.
     *
     * @covers \notificationscondition_activityopen\activityopen::process_markups
     */
    public function test_processmarkups(): void {
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
     * Test get_ui.
     *
     * @covers \notificationscondition_activityopen\activityopen::get_ui
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
     * Test set_default.
     *
     * @covers \notificationscondition_activityopen\activityopen::set_default
     */
    public function test_setdefault(): void {
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
     * Test validation.
     *
     * @covers       \notificationscondition_activityopen\activityopen::validation
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
