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
 * @package    notificationscondition_calendarstart
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace notificationscondition_calendarstart;

use local_notificationsagent\evaluationcontext;
use local_notificationsagent\form\editrule_form;
use local_notificationsagent\notificationplugin;
use local_notificationsagent\helper\test\phpunitutil;
use local_notificationsagent\rule;
use notificationscondition_calendarstart\calendarstart;

/**
 * @group notificationsagent
 */
class calendarstart_test extends \advanced_testcase {

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
    public const DURATION = 30 * 86400;
    /**
     * @var \stdClass
     */
    private static $caledarevent;

    public function setUp(): void {
        parent::setUp();
        $this->resetAfterTest(true);
        self::$rule = new rule();

        self::$subplugin = new calendarstart(self::$rule);
        self::$coursetest = self::getDataGenerator()->create_course(
            ['startdate' => self::COURSE_DATESTART, 'enddate' => self::COURSE_DATEEND]
        );
        self::$coursecontext = \context_course::instance(self::$coursetest->id);
        self::$user = self::getDataGenerator()->create_and_enrol(self::$coursecontext, 'manager');
        self::setUser(self::$user);
        self::$caledarevent = self::getDataGenerator()->create_event(
            [
                'timestart' => self::COURSE_DATESTART,
                'timeduration' => self::DURATION,
                'courseid' => self::$coursetest->id,
                'userid' => self::$user->id,
            ]
        );

        self::$context = new evaluationcontext();
        self::$context->set_userid(self::$user->id);
        self::$context->set_courseid(self::$coursetest->id);
        self::$subtype = 'calendarstart';
        self::$elements = ['[TTTT]', '[CCCC]'];
    }

    /**
     *
     * @param int    $timeaccess
     * @param string $param
     * @param bool   $expected
     *
     * @covers       \notificationscondition_calendarstart\calendarstart::evaluate
     *
     * @dataProvider dataprovider
     */
    public function test_evaluate($timeaccess, $usecache, $param, $complementary, $expected) {
        $auxarray = json_decode($param, true);
        $auxarray['cmid'] = self::$caledarevent->id;
        $param = json_encode($auxarray);

        self::$context->set_params($param);
        self::$context->set_timeaccess($timeaccess);
        self::$context->set_complementary($complementary);
        self::$subplugin->set_id(self::CONDITIONID);

        $params = json_decode(self::$context->get_params(), true);

        if ($usecache) {
            global $DB;
            $objdb = new \stdClass();
            $objdb->userid = self::$user->id;
            $objdb->courseid = self::$coursetest->id;
            if ($params['radio'] === 1) {
                $objdb->timestart = self::$caledarevent->timestart + $params['time'];
            } else {
                $objdb->timestart = self::$caledarevent->timestart + $params['time'] + self::DURATION;
            }

            $objdb->pluginname = self::$subtype;
            $objdb->conditionid = self::CONDITIONID;
            // Insert.
            $DB->insert_record('notificationsagent_cache', $objdb);
        }

        // Test evaluate.
        $result = self::$subplugin->evaluate(self::$context);
        $this->assertSame($expected, $result);
    }

    public static function dataprovider(): array {
        return [
            [1704445200, false, '{"time":864000, "radio":1}', notificationplugin::COMPLEMENTARY_CONDITION, false],
            [1706173200, false, '{"time":864000, "radio":1}', notificationplugin::COMPLEMENTARY_CONDITION, true],
            [1704445200, false, '{"time":864000, "radio":0}', notificationplugin::COMPLEMENTARY_CONDITION, false],
            [1706173200, false, '{"time":864000, "radio":0}', notificationplugin::COMPLEMENTARY_CONDITION, false],
            [1707987600, false, '{"time":864000, "radio":0}', notificationplugin::COMPLEMENTARY_CONDITION, true],
            [1704445200, true, '{"time":864000, "radio":1}', notificationplugin::COMPLEMENTARY_CONDITION, false],
            [1706173200, true, '{"time":864000, "radio":1}', notificationplugin::COMPLEMENTARY_CONDITION, true],
            [1704445200, true, '{"time":864000, "radio":0}', notificationplugin::COMPLEMENTARY_CONDITION, false],
            [1706173200, true, '{"time":864000, "radio":0}', notificationplugin::COMPLEMENTARY_CONDITION, false],
            [1707987600, true, '{"time":864000, "radio":0}', notificationplugin::COMPLEMENTARY_CONDITION, true],
        ];
    }

    /**
     * @covers \notificationscondition_calendarstart\calendarstart::get_subtype
     */
    public function test_getsubtype() {
        $this->assertSame(self::$subtype, self::$subplugin->get_subtype());
    }

    /**
     * @covers \notificationscondition_calendarstart\calendarstart::is_generic
     */
    public function test_isgeneric() {
        $this->assertTrue(self::$subplugin->is_generic());
    }

    /**
     * @covers \notificationscondition_calendarstart\calendarstart::get_elements
     */
    public function test_getelements() {
        $this->assertSame(self::$elements, self::$subplugin->get_elements());
    }

    /**
     * @covers \notificationscondition_calendarstart\calendarstart::check_capability
     */
    public function test_checkcapability() {
        $this->assertSame(
            has_capability('local/notificationsagent:' . self::$subtype, self::$coursecontext),
            self::$subplugin->check_capability(self::$coursecontext)
        );
    }

    /**
     * @covers \notificationscondition_calendarstart\calendarstart::get_cmid
     */
    public function test_getcmid() {
        $this->assertNull(self::$subplugin->get_cmid(self::$context));
    }

    /**
     * @covers       \notificationscondition_calendarstart\calendarstart::estimate_next_time
     * @dataProvider dataprovider
     */
    public function test_estimatenexttime($timeaccess, $usecache, $param, $complementary, $expected) {
        $auxarray = json_decode($param, true);
        $auxarray['cmid'] = self::$caledarevent->id;
        $param = json_encode($auxarray);

        self::$context->set_params($param);
        self::$context->set_timeaccess($timeaccess);
        self::$context->set_complementary($complementary);
        self::$subplugin->set_id(self::CONDITIONID);

        $params = json_decode(self::$context->get_params(), true);
        // Test estimate next time.
        if (self::$context->is_complementary()) {
            self::assertNull(self::$subplugin->estimate_next_time(self::$context));
        } else {
            if ($params['radio'] == 1) {
                self::assertSame(self::COURSE_DATESTART + $params['time'], self::$subplugin->estimate_next_time(self::$context));
            } else {
                self::assertSame(
                    self::COURSE_DATESTART + $params['time'] + self::DURATION,
                    self::$subplugin->estimate_next_time(self::$context)
                );
            }
        }
    }

    /**
     * @covers \notificationscondition_calendarstart\calendarstart::get_title
     */
    public function test_gettitle() {
        $this->assertNotNull(self::$subplugin->get_title());
        foreach (self::$elements as $element) {
            $this->assertStringContainsString($element, self::$subplugin->get_title());
        }
    }

    /**
     * @covers \notificationscondition_calendarstart\calendarstart::get_description
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
     * @covers \notificationscondition_calendarstart\calendarstart::process_markups
     */
    public function test_processmarkups() {
        $time = self::$caledarevent->timestart;
        $params[self::$subplugin::UI_TIME] = $time;
        $params = json_encode($params);
        $expected = str_replace(self::$subplugin->get_elements(), [to_human_format($time, true)], self::$subplugin->get_title());
        self::$subplugin->set_parameters($params);
        $content = [];
        self::$subplugin->process_markups($content, self::$coursetest->id);
        $this->assertSame([$expected], $content);
    }

    /**
     * @covers \notificationscondition_calendarstart\calendarstart::get_ui
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
        $uiradiogroup = $method->invoke(self::$subplugin, $id, self::$subplugin::UI_RADIO_GROUP);
        $uiradiogroupelements = [];
        foreach ($mform->getElement($uiradiogroup)->getElements() as $element) {
            $uiradiogroupelements[$element->getName()] = $element->getName();
        }
        $uigroupname = $method->invoke(self::$subplugin, $id, self::$subplugin->get_subtype());
        $uigroupelements = [];
        foreach ($mform->getElement($uigroupname)->getElements() as $element) {
            $uigroupelements[] = $element->getName();
        }
        $uiradio = $method->invoke(self::$subplugin, $id, self::$subplugin::UI_RADIO);
        $uidays = $method->invoke(self::$subplugin, $id, self::$subplugin::UI_DAYS);
        $uihours = $method->invoke(self::$subplugin, $id, self::$subplugin::UI_HOURS);
        $uiminutes = $method->invoke(self::$subplugin, $id, self::$subplugin::UI_MINUTES);
        $uiseconds = $method->invoke(self::$subplugin, $id, self::$subplugin::UI_SECONDS);

        $this->assertTrue($mform->elementExists($uiactivityname));
        $this->assertTrue($mform->elementExists($uigroupname));
        $this->assertTrue(in_array($uiradio, $uiradiogroupelements));
        $this->assertTrue(in_array($uidays, $uigroupelements));
        $this->assertTrue(in_array($uihours, $uigroupelements));
        $this->assertTrue(in_array($uiminutes, $uigroupelements));
        $this->assertTrue(in_array($uiseconds, $uigroupelements));
    }

    /**
     * @covers \notificationscondition_calendarstart\calendarstart::set_default
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
        $uiradiogroup = $method->invoke(self::$subplugin, $id, self::$subplugin::UI_RADIO_GROUP);
        $default = [];
        foreach ($mform->getElement($uiradiogroup)->getElements() as $element) {
            $default[$element->getName()][] = $element->getValue();
        }
        $uigroupname = $method->invoke(self::$subplugin, $id, self::$subplugin->get_subtype());
        foreach ($mform->getElement($uigroupname)->getElements() as $element) {
            $default[$element->getName()] = $element->getValue();
        }

        $uiradio = $method->invoke(self::$subplugin, $id, self::$subplugin::UI_RADIO);
        $uidays = $method->invoke(self::$subplugin, $id, self::$subplugin::UI_DAYS);
        $uihours = $method->invoke(self::$subplugin, $id, self::$subplugin::UI_HOURS);
        $uiminutes = $method->invoke(self::$subplugin, $id, self::$subplugin::UI_MINUTES);
        $uiseconds = $method->invoke(self::$subplugin, $id, self::$subplugin::UI_SECONDS);

        $this->assertTrue(isset($default[$uiradio]) && in_array(self::$subplugin::UI_RADIO_DEFAULT_VALUE, $default[$uiradio]));
        $this->assertTrue(isset($default[$uidays]) && $default[$uidays] == self::$subplugin::UI_DAYS_DEFAULT_VALUE);
        $this->assertTrue(isset($default[$uihours]) && $default[$uihours] == self::$subplugin::UI_HOURS_DEFAULT_VALUE);
        $this->assertTrue(isset($default[$uiminutes]) && $default[$uiminutes] == self::$subplugin::UI_MINUTES_DEFAULT_VALUE);
        $this->assertTrue(isset($default[$uiseconds]) && $default[$uiseconds] == self::$subplugin::UI_SECONDS_DEFAULT_VALUE);
    }

    /**
     * @covers \notificationscondition_calendarstart\calendarstart::convert_parameters
     */
    public function test_convertparameters() {
        $params = [
            "5_calendarstart_days" => "1",
            "5_calendarstart_hours" => "0",
            "5_calendarstart_minutes" => "0",
            "5_calendarstart_seconds" => "1",
            "5_calendarstart_cmid" => "7",
            "5_calendarstart_radio" => "1",
        ];
        $expected = '{"time":86401,"cmid":7,"radio":"1"}';
        $method = phpunitutil::get_method(self::$subplugin, 'convert_parameters');
        $result = $method->invoke(self::$subplugin, 5, $params);
        $this->assertSame($expected, $result);
    }
}
