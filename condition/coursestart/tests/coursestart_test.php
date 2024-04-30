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
// Project implemented by the \"Recovery, Transformation and Resilience Plan.
// Funded by the European Union - Next GenerationEU\".
//
// Produced by the UNIMOODLE University Group: Universities of
// Valladolid, Complutense de Madrid, UPV/EHU, León, Salamanca,
// Illes Balears, Valencia, Rey Juan Carlos, La Laguna, Zaragoza, Málaga,
// Córdoba, Extremadura, Vigo, Las Palmas de Gran Canaria y Burgos.

/**
 * Version details
 *
 * @package    notificationscondition_coursestart
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace notificationscondition_coursestart;

use local_notificationsagent\evaluationcontext;
use local_notificationsagent\form\editrule_form;
use local_notificationsagent\helper\test\phpunitutil;
use local_notificationsagent\notificationplugin;
use local_notificationsagent\rule;

use local_notificationsagent\helper\test\mock_base_logger;

/**
 * Test for the notificationscondition_coursestart plugin.
 *
 * @group notificationsagent
 */
class coursestart_test extends \advanced_testcase {

    /**
     * @var rule
     */
    private static $rule;

    /**
     * @var coursestart
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

        self::$subplugin = new coursestart(self::$rule->to_record());
        self::$subplugin->set_id(5);
        self::$coursetest = self::getDataGenerator()->create_course(
            ['startdate' => self::COURSE_DATESTART, 'enddate' => self::COURSE_DATEEND]
        );
        self::$coursecontext = \context_course::instance(self::$coursetest->id);
        self::$user = self::getDataGenerator()->create_user();
        self::$context = new evaluationcontext();
        self::$context->set_userid(self::$user->id);
        self::$context->set_courseid(self::$coursetest->id);
        self::$subtype = 'coursestart';
        self::$elements = ['[TTTT]'];

    }

    /**
     * Test evaluate function.
     *
     * @dataProvider dataprovider
     *
     * @param int    $timeaccess    The access time.
     * @param bool   $usecache      Whether to use cache or not.
     * @param string $param         The parameters.
     * @param array  $complementary The complementary array.
     * @param bool   $expected      The expected result.
     *
     * @return void
     */
    public function test_evaluate($timeaccess, $usecache, $param, $complementary, $expected) {
        self::$context->set_params($param);
        self::$context->set_timeaccess($timeaccess);
        self::$context->set_complementary($complementary);
        self::$subplugin->set_id(self::CONDITIONID);

        if ($usecache) {
            global $DB;
            $params = json_decode(self::$context->get_params(), true);
            $objdb = new \stdClass();
            $objdb->userid = self::$user->id;
            $objdb->courseid = self::$coursetest->id;
            $objdb->startdate = self::COURSE_DATESTART + $params['time'];
            $objdb->pluginname = self::$subtype;
            $objdb->conditionid = self::CONDITIONID;
            // Insert.
            $DB->insert_record('notificationsagent_cache', $objdb);
        }

        // Test evaluate.
        $result = self::$subplugin->evaluate(self::$context);
        $this->assertSame($expected, $result);

    }

    /**
     * Generate a data provider for testing the `evaluate` method of the `notificationscondition_coursestart` class.
     *
     * @return array The data provider array.
     */
    public static function dataprovider(): array {
        return [
            [1704445200, false, '{"time":864000}', notificationplugin::COMPLEMENTARY_CONDITION, false], // 0 false complementary
            [1705050000, false, '{"time":864000}', notificationplugin::COMPLEMENTARY_CONDITION, true],
            [1705050000, false, '{"time":864000}', notificationplugin::COMPLEMENTARY_EXCEPTION, true],
            [1704445200, true, '{"time":864000}', notificationplugin::COMPLEMENTARY_CONDITION, false],
            [1705050000, true, '{"time":864000}', notificationplugin::COMPLEMENTARY_CONDITION, true],
            [1705050000, true, '{"time":864000}', notificationplugin::COMPLEMENTARY_EXCEPTION, true],

        ];
    }

    /**
     *  Test get subtype function.
     *
     * @covers \notificationscondition_coursestart\coursestart::get_subtype
     */
    public function test_getsubtype() {
        $this->assertSame('coursestart', self::$subplugin->get_subtype());
    }

    /**
     * Test is generic function.
     *
     * @covers \notificationscondition_coursestart\coursestart::is_generic
     */
    public function test_isgeneric() {
        $this->assertTrue(self::$subplugin->is_generic());
    }

    /**
     * Test get elements function.
     *
     * @covers \notificationscondition_coursestart\coursestart::get_elements
     */
    public function test_getelements() {
        $this->assertSame(self::$elements, self::$subplugin->get_elements());
    }

    /**
     * Test check capability function.
     *
     * @covers \notificationscondition_coursestart\coursestart::check_capability
     */
    public function test_checkcapability() {
        $this->assertSame(
            has_capability('local/notificationsagent:' . self::$subtype, self::$coursecontext),
            self::$subplugin->check_capability(self::$coursecontext)
        );
    }

    /**
     * Test get cmid.
     *
     * @covers \notificationscondition_coursestart\coursestart::get_cmid
     */
    public function test_getcmid() {
        $this->assertNull(self::$subplugin->get_cmid(self::$context));
    }

    /**
     * Test the estimate_next_time function.
     *
     * @param int    $timeaccess    The access time.
     * @param array  $complementary The complementary array.
     * @param string $param         The parameters.
     *
     * @covers       \notificationscondition_coursestart\coursestart::estimate_next_time
     * @dataProvider dataprovider
     */
    public function test_estimatenexttime($timeaccess, $complementary, $param) {
        \uopz_set_return('time', time());

        self::$context->set_params($param);
        self::$context->set_complementary($complementary);
        self::$context->set_timeaccess($timeaccess);
        $params = json_decode(self::$context->get_params(), false);
        // Condition.
        if (!self::$context->is_complementary()) {
            if ($timeaccess >= self::COURSE_DATESTART
                && ($timeaccess <= self::COURSE_DATESTART +
                    $params->{notificationplugin::UI_TIME})
            ) {
                self::assertEquals(
                    self::COURSE_DATESTART + $params->{notificationplugin::UI_TIME},
                    self::$subplugin->estimate_next_time(self::$context)
                );
            } else {
                self::assertEquals(time(), self::$subplugin->estimate_next_time(self::$context));
            }

        }
        // Exception.
        if (self::$context->is_complementary()) {
            if ($timeaccess >= self::COURSE_DATESTART
                && $timeaccess <= self::COURSE_DATESTART +
                $params->{notificationplugin::UI_TIME}
            ) {
                $this->assertEquals(time(), self::$subplugin->estimate_next_time(self::$context));

            } else {
                self::assertNull(self::$subplugin->estimate_next_time(self::$context));
            }
        }

        uopz_unset_return('time');
    }

    /**
     * Data provider for test_estimatenexttime.
     *
     * @covers \notificationscondition_coursestart\coursestart::datacourse
     */
    public static function datacourse() {
        return [
            'condition false 5' => [1704445200, notificationplugin::COMPLEMENTARY_CONDITION, '{"time":864000}'],
            'condition true 20' => [1705741200, notificationplugin::COMPLEMENTARY_CONDITION, '{"time":864000}'],
            'Exception false 5' => [1704445200, notificationplugin::COMPLEMENTARY_EXCEPTION, '{"time":864000}'],
            'Exception true 20' => [1705741200, notificationplugin::COMPLEMENTARY_EXCEPTION, '{"time":864000}'],
        ];
    }

    /**
     * Test get title function.
     *
     * @covers \notificationscondition_coursestart\coursestart::get_title
     */
    public function test_gettitle() {
        $this->assertNotNull(self::$subplugin->get_title());
        foreach (self::$elements as $element) {
            $this->assertStringContainsString($element, self::$subplugin->get_title());
        }
    }

    /**
     * Test get description function.
     *
     * @covers \notificationscondition_coursestart\coursestart::get_description
     */
    public function test_getdescription() {
        $this->assertSame(
            self::$subplugin->get_description(),
            [
                'title' => self::$subplugin->get_title(),
                'name' => self::$subplugin->get_subtype(),
            ]
        );
    }

    /**
     * Test process markups function.
     *
     * @covers \notificationscondition_coursestart\coursestart::process_markups
     */
    public function test_processmarkups() {
        $time = self::$coursetest->enddate;
        $params[self::$subplugin::UI_TIME] = $time;
        $params = json_encode($params);
        $expected = str_replace(self::$subplugin->get_elements(), [to_human_format($time, true)], self::$subplugin->get_title());
        self::$subplugin->set_parameters($params);
        $content = [];
        self::$subplugin->process_markups($content, self::$coursetest->id);
        $this->assertSame([$expected], $content);
    }

    /**
     * Test get ui function.
     *
     * @covers \notificationscondition_coursestart\coursestart::get_ui
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
        $uigroupname = $method->invoke(self::$subplugin, self::$subplugin->get_subtype());
        $uigroupelements = [];
        foreach ($mform->getElement($uigroupname)->getElements() as $element) {
            $uigroupelements[] = $element->getName();
        }
        $uidays = $method->invoke(self::$subplugin, self::$subplugin::UI_DAYS);
        $uihours = $method->invoke(self::$subplugin, self::$subplugin::UI_HOURS);
        $uiminutes = $method->invoke(self::$subplugin, self::$subplugin::UI_MINUTES);
        $uiseconds = $method->invoke(self::$subplugin, self::$subplugin::UI_SECONDS);

        $this->assertTrue($mform->elementExists($uigroupname));
        $this->assertTrue(in_array($uidays, $uigroupelements));
        $this->assertTrue(in_array($uihours, $uigroupelements));
        $this->assertTrue(in_array($uiminutes, $uigroupelements));
        $this->assertTrue(in_array($uiseconds, $uigroupelements));
    }

    /**
     * Test set default function.
     *
     * @covers \notificationscondition_coursestart\coursestart::set_default
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
        $addjson = phpunitutil::get_method($form, 'addJson');
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
        $uiseconds = $method->invoke(self::$subplugin, self::$subplugin::UI_SECONDS);

        $this->assertTrue(isset($defaulttime[$uidays]) && $defaulttime[$uidays] == self::$subplugin::UI_DAYS_DEFAULT_VALUE);
        $this->assertTrue(isset($defaulttime[$uihours]) && $defaulttime[$uihours] == self::$subplugin::UI_HOURS_DEFAULT_VALUE);
        $this->assertTrue(
            isset($defaulttime[$uiminutes]) && $defaulttime[$uiminutes] == self::$subplugin::UI_MINUTES_DEFAULT_VALUE
        );
        $this->assertTrue(
            isset($defaulttime[$uiseconds]) && $defaulttime[$uiseconds] == self::$subplugin::UI_SECONDS_DEFAULT_VALUE
        );
    }

    /**
     * Test convert parameters function.
     *
     * @covers \notificationscondition_coursestart\coursestart::convert_parameters
     */
    public function test_convertparameters() {
        $id = self::$subplugin->get_id();
        $params = [
            $id . "_coursestart_days" => "1",
            $id . "_coursestart_hours" => "0",
            $id . "_coursestart_minutes" => "0",
            $id . "_coursestart_seconds" => "1",
        ];
        $expected = '{"time":86401}';
        $method = phpunitutil::get_method(self::$subplugin, 'convert_parameters');
        $result = $method->invoke(self::$subplugin, $params);
        $this->assertSame($expected, $result);
    }

    /**
     * Test update after restore method
     *
     * @return void
     * @covers \notificationscondition_coursestart\coursestart::update_after_restore
     */
    public function test_update_after_restore() {
        $logger = new mock_base_logger(0);
        $this->assertFalse(self::$subplugin->update_after_restore('restoreid', self::$coursecontext->id, $logger));
    }
}
