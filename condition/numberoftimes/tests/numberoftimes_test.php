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
 * @package    local_notificationsagent
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace notificationscondition_numberoftimes;

use local_notificationsagent\evaluationcontext;
use local_notificationsagent\form\editrule_form;
use local_notificationsagent\helper\test\phpunitutil;
use local_notificationsagent\notificationplugin;
use local_notificationsagent\rule;
use notificationscondition_numberoftimes\numberoftimes;

/**
 * @covers \notificationscondition_numberoftimes\numberoftimes
 * @group  notificationsagent
 */
class numberoftimes_test extends \advanced_testcase {

    private static $rule;
    private static $subplugin;
    private static $coursetest;
    private static $subtype;
    private static $user;
    private static $context;
    private static $coursecontext;
    private static $elements;
    public const CONDITIONID = 1;
    public const COURSE_DATESTART = 1704099600; // 01/01/2024 10:00:00.
    public const COURSE_DATEEND = 1706605200; // 30/01/2024 10:00:00,

    public function setUp(): void {
        parent::setUp();
        $this->resetAfterTest(true);
        self::$rule = new rule();
        self::$rule->set_id(25600);
        self::$subplugin = new numberoftimes(self::$rule);
        self::$coursetest = self::getDataGenerator()->create_course(
            ['startdate' => self::COURSE_DATESTART, 'enddate' => self::COURSE_DATEEND]
        );
        self::$coursecontext = \context_course::instance(self::$coursetest->id);
        self::$user = self::getDataGenerator()->create_user();
        self::$context = new evaluationcontext();
        self::$context->set_userid(self::$user->id);
        self::$context->set_courseid(self::$coursetest->id);
        self::$context->set_rule(self::$rule);
        self::$subtype = 'numberoftimes';
        self::$elements = ['[TTTT]', '[N]'];
    }

    /**
     *
     * @param int    $timeaccess
     * @param string $param
     * @param bool   $expected
     *
     * @covers       \notificationscondition_numberoftimes\numberoftimes::evaluate
     *
     * @dataProvider dataprovider
     */
    public function test_evaluate($timeaccess, $timemodified, $timesfired, $param, $launched, $expected) {
        self::$context->set_params($param);
        self::$context->set_timeaccess($timeaccess);
        self::$subplugin->set_id(self::CONDITIONID);

        if ($launched) {
            global $DB;
            $objdb = new \stdClass();
            $objdb->userid = self::$user->id;
            $objdb->courseid = self::$coursetest->id;
            $objdb->timemodified = $timemodified;
            $objdb->timecreated = $timemodified;
            $objdb->timesfired = $timesfired;
            $objdb->ruleid = self::$rule->get_id();
            // Insert.
            $DB->insert_record('notificationsagent_launched', $objdb);
        }

        // Test evaluate.
        $result = self::$subplugin->evaluate(self::$context);
        $this->assertSame($expected, $result);

    }

    public static function dataprovider(): array {
        return [
            [1705741810, 1704445200, 5, '{"time":864000, "times":4}', false, true],
            [1705741810, 1704445200, 5, '{"time":864000, "times":4}', true, false],
            [1705741810, 1704445200, 1, '{"time":864000, "times":4}', true, true],
            [1704445200 + 864001, 1704445200, 1, '{"time":864000, "times":4}', true, true],
            [1704445200 + 864001, 1704445200, 7, '{"time":864000, "times":4}', true, false],
        ];
    }

    /**
     * @covers \notificationscondition_numberoftimes\numberoftimes::get_subtype
     */
    public function test_getsubtype() {
        $this->assertSame(self::$subtype, self::$subplugin->get_subtype());
    }

    /**
     * @covers \notificationscondition_numberoftimes\numberoftimes::is_generic
     */
    public function test_isgeneric() {
        $this->assertFalse(self::$subplugin->is_generic());
    }

    /**
     * @covers \notificationscondition_numberoftimes\numberoftimes::get_elements
     */
    public function test_getelements() {
        $this->assertSame(self::$elements, self::$subplugin->get_elements());
    }

    /**
     * @covers \notificationscondition_numberoftimes\numberoftimes::check_capability
     */
    public function test_checkcapability() {
        $this->assertSame(
            has_capability('local/notificationsagent:' . self::$subtype, self::$coursecontext),
            self::$subplugin->check_capability(self::$coursecontext)
        );
    }

    /**
     * @covers \notificationscondition_numberoftimes\numberoftimes::get_cmid
     */
    public function test_getcmid() {
        $this->assertNull(self::$subplugin->get_cmid(self::$context));
    }

    /**
     * @covers \notificationscondition_numberoftimes\numberoftimes::estimate_next_time
     */
    public function test_estimatenexttime() {
        // Test estimate next time.
        $this->assertNull(self::$subplugin->estimate_next_time(self::$context));
    }

    /**
     * @covers \notificationscondition_numberoftimes\numberoftimes::get_title
     */
    public function test_gettitle() {
        $this->assertNotNull(self::$subplugin->get_title());
        foreach (self::$elements as $element) {
            $this->assertStringContainsString($element, self::$subplugin->get_title());
        }
    }

    /**
     * @covers \notificationscondition_numberoftimes\numberoftimes::get_description
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
     * @covers \notificationscondition_numberoftimes\numberoftimes::process_markups
     */
    public function test_processmarkups() {
        $ntimes = 1;
        $params[self::$subplugin::N_TIMES] = $ntimes;
        $time = 86400;
        $params[self::$subplugin::UI_TIME] = $time;
        $params = json_encode($params);
        $expected = str_replace(self::$subplugin->get_elements(), [to_human_format($time, true), $ntimes],
            self::$subplugin->get_title());
        self::$subplugin->set_parameters($params);
        $content = [];
        self::$subplugin->process_markups($content, self::$coursetest->id);
        $this->assertSame([$expected], $content);
    }

    /**
     * @covers \notificationscondition_numberoftimes\numberoftimes::convert_parameters
     */
    public function test_convertparameters() {
        $params = [
            "5_numberoftimes_days" => "1",
            "5_numberoftimes_hours" => "0",
            "5_numberoftimes_minutes" => "0",
            "5_numberoftimes_seconds" => "1",
            "5_numberoftimes_times" => "1",
        ];
        $expected = '{"time":86401,"times":1}';
        $method = phpunitutil::get_method(self::$subplugin, 'convert_parameters');
        $result = $method->invoke(self::$subplugin, 5, $params);
        $this->assertSame($expected, $result);
    }

    /**
     * @covers \notificationscondition_numberoftimes\numberoftimes::get_ui
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
        $uigroupname = $method->invoke(self::$subplugin, $id, self::$subplugin->get_subtype());
        $uigroupelements = [];
        foreach ($mform->getElement($uigroupname)->getElements() as $element) {
            $uigroupelements[] = $element->getName();
        }
        $uitimes = $method->invoke(self::$subplugin, $id, self::$subplugin::N_TIMES);
        $uidays = $method->invoke(self::$subplugin, $id, self::$subplugin::UI_DAYS);
        $uihours = $method->invoke(self::$subplugin, $id, self::$subplugin::UI_HOURS);
        $uiminutes = $method->invoke(self::$subplugin, $id, self::$subplugin::UI_MINUTES);
        $uiseconds = $method->invoke(self::$subplugin, $id, self::$subplugin::UI_SECONDS);

        $this->assertTrue($mform->elementExists($uitimes));
        $this->assertTrue($mform->elementExists($uigroupname));
        $this->assertTrue(in_array($uidays, $uigroupelements));
        $this->assertTrue(in_array($uihours, $uigroupelements));
        $this->assertTrue(in_array($uiminutes, $uigroupelements));
        $this->assertTrue(in_array($uiseconds, $uigroupelements));
    }

    /**
     * @covers \notificationscondition_numberoftimes\numberoftimes::set_default
     */
    public function test_setdefault() {
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
        $addjson = phpunitutil::get_method($form, 'addJson');
        $addjson->invoke($form, notificationplugin::TYPE_CONDITION, self::$subplugin::NAME);
        $form->definition_after_data();

        $mform = phpunitutil::get_property($form, '_form');
        $jsoncondition = $mform->getElementValue(editrule_form::FORM_JSON_CONDITION);
        $arraycondition = array_keys(json_decode($jsoncondition, true));
        $id = $arraycondition[0];

        $method = phpunitutil::get_method(self::$subplugin, 'get_name_ui');
        $uigroupname = $method->invoke(self::$subplugin, $id, self::$subplugin->get_subtype());
        $defaulttime = [];
        foreach ($mform->getElement($uigroupname)->getElements() as $element) {
            $defaulttime[$element->getName()] = $element->getValue();
        }

        $uidays = $method->invoke(self::$subplugin, $id, self::$subplugin::UI_DAYS);
        $uihours = $method->invoke(self::$subplugin, $id, self::$subplugin::UI_HOURS);
        $uiminutes = $method->invoke(self::$subplugin, $id, self::$subplugin::UI_MINUTES);
        $uiseconds = $method->invoke(self::$subplugin, $id, self::$subplugin::UI_SECONDS);

        $this->assertTrue(isset($defaulttime[$uidays]) && $defaulttime[$uidays] == self::$subplugin::UI_DAYS_DEFAULT_VALUE);
        $this->assertTrue(isset($defaulttime[$uihours]) && $defaulttime[$uihours] == self::$subplugin::UI_HOURS_DEFAULT_VALUE);
        $this->assertTrue(
            isset($defaulttime[$uiminutes]) && $defaulttime[$uiminutes] == self::$subplugin::UI_MINUTES_DEFAULT_VALUE
        );
        $this->assertTrue(
            isset($defaulttime[$uiseconds]) && $defaulttime[$uiseconds] == self::$subplugin::UI_SECONDS_DEFAULT_VALUE
        );
    }
}
