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
use local_notificationsagent\helper\test\phpunitutil;
use local_notificationsagent\notificationplugin;
use local_notificationsagent\rule;

/**
 * Test for the notificationscondition_calendarstart plugin.
 *
 * @group notificationsagent
 */
class calendarstart_test extends \advanced_testcase {
    /**
     * @var rule
     */
    private static $rule;

    /**
     * @var calendarstart
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
     * Duration for the event
     */
    public const DURATION = 30 * 86400;
    /**
     * @var \stdClass
     */
    private static $caledarevent;

    /**
     * Set up the test environment.
     */
    public function setUp(): void {
        parent::setUp();
        $this->resetAfterTest(true);
        self::$rule = new rule();

        self::$subplugin = new calendarstart(self::$rule->to_record());
        self::$subplugin->set_id(5);
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
     * Test the evaluation function.
     *
     * @param int $timeaccess The access time.
     * @param bool $usecache Whether to use cache or not.
     * @param string $param The parameters.
     * @param bool $complementary The complementary array.
     * @param bool $expected The expected result.
     *
     * @dataProvider dataprovider
     * @covers       \notificationscondition_calendarstart\calendarstart::evaluate
     *
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
                $objdb->startdate = self::$caledarevent->timestart + $params['time'];
            } else {
                $objdb->startdate = self::$caledarevent->timestart + $params['time'] + self::DURATION;
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

    /**
     * Data provider for test_evaluate().
     */
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
     * Test for get_subtype.
     *
     * @covers \notificationscondition_calendarstart\calendarstart::get_subtype
     */
    public function test_getsubtype() {
        $this->assertSame(self::$subtype, self::$subplugin->get_subtype());
    }

    /**
     * Test for is_generic.
     *
     * @covers \notificationscondition_calendarstart\calendarstart::is_generic
     */
    public function test_isgeneric() {
        $this->assertTrue(self::$subplugin->is_generic());
    }

    /**
     * Test for get_elements.
     *
     * @covers \notificationscondition_calendarstart\calendarstart::get_elements
     */
    public function test_getelements() {
        $this->assertSame(self::$elements, self::$subplugin->get_elements());
    }

    /**
     * Test for check_capability.
     *
     * @covers \notificationscondition_calendarstart\calendarstart::check_capability
     */
    public function test_checkcapability() {
        $this->assertSame(
            has_capability('notificationscondition/' . self::$subtype.':'.self::$subtype, self::$coursecontext),
            self::$subplugin->check_capability(self::$coursecontext)
        );
    }

    /**
     * Test for get_cmid.
     *
     * @covers \notificationscondition_calendarstart\calendarstart::get_cmid
     */
    public function test_getcmid() {
        $this->assertNull(self::$subplugin->get_cmid(self::$context));
    }

    /**
     * Test the estimate next time function.
     *
     * @param int $timeaccess The access time.
     * @param bool $usecache Whether to use cache or not.
     * @param string $param The parameters.
     * @param array $complementary The complementary array.
     * @param null|int $expected The expected result.
     *
     * @covers       \notificationscondition_calendarstart\calendarstart::estimate_next_time
     * @dataProvider dataestimate
     */
    public function test_estimatenexttime($timeaccess, $usecache, $param, $complementary, $expected) {
        \uopz_set_return('time', $timeaccess);
        $auxarray = json_decode($param, true);
        $auxarray['cmid'] = self::$caledarevent->id;
        $param = json_encode($auxarray);

        self::$context->set_params($param);
        self::$context->set_timeaccess($timeaccess);
        self::$context->set_complementary($complementary);
        self::$subplugin->set_id(self::CONDITIONID);
        $params = json_decode(self::$context->get_params(), false);

        $event = calendar_get_events_by_id([$params->{notificationplugin::UI_ACTIVITY}]);
        $timeevent = $event[$params->{notificationplugin::UI_ACTIVITY}]->timestart;
        $timeduration = $event[$params->{notificationplugin::UI_ACTIVITY}]->timeduration;

        // Test estimate next time.
        // Exception.
        if (self::$context->is_complementary()) {
            // After beggining of event.
            if ($params->{calendarstart::UI_RADIO} == 1) {
                if ($timeaccess >= $timeevent && $timeaccess <= $timeevent + $params->{notificationplugin::UI_TIME}) {
                    self::assertSame(
                        time(),
                        self::$subplugin->estimate_next_time(self::$context)
                    );
                } else if ($timeaccess >= $timeevent + $params->{notificationplugin::UI_TIME}) {
                    self::assertNull(self::$subplugin->estimate_next_time(self::$context));
                }
                // Radio 0.
            } else if ($params->{calendarstart::UI_RADIO} == 0) {
                if (
                    $timeaccess >= $timeevent + $timeduration
                        && $timeaccess <= $timeevent +
                        $params->{notificationplugin::UI_TIME} + $timeduration
                ) {
                    self::assertSame(
                        time(),
                        self::$subplugin->estimate_next_time(self::$context)
                    );
                } else if ($timeaccess >= $timeevent + $params->{notificationplugin::UI_TIME} + $timeduration) {
                    self::assertNull(
                        self::$subplugin->estimate_next_time(self::$context)
                    );
                }
            }

            // Condition.
        } else {
            // After beggining of event.
            if ($params->{calendarstart::UI_RADIO} == 1) {
                if ($timeaccess >= $timeevent && $timeaccess <= $timeevent + $params->{notificationplugin::UI_TIME}) {
                    self::assertSame(
                        self::COURSE_DATESTART + $params->{notificationplugin::UI_TIME},
                        self::$subplugin->estimate_next_time(self::$context)
                    );
                } else if ($timeaccess >= $timeevent + $params->{notificationplugin::UI_TIME}) {
                    self::assertSame(
                        time(),
                        self::$subplugin->estimate_next_time(self::$context)
                    );
                }
                // Radio 0.
            } else if ($params->{calendarstart::UI_RADIO} == 0) {
                if (
                    $timeaccess >= $timeevent + $timeduration
                        && $timeaccess <= $timeevent +
                        $params->{notificationplugin::UI_TIME} + $timeduration
                ) {
                    self::assertSame(
                        self::COURSE_DATESTART + $params->{notificationplugin::UI_TIME} + $timeduration,
                        self::$subplugin->estimate_next_time(self::$context)
                    );
                } else if ($timeaccess >= $timeevent + $params->{notificationplugin::UI_TIME} + $timeduration) {
                    self::assertSame(
                        time(),
                        self::$subplugin->estimate_next_time(self::$context)
                    );
                }
            }
        }
        \uopz_unset_return('time');
    }

    /**
     * Dataprovider for estimate next time
     *
     * @return array[]
     */
    public static function dataestimate(): array {
        return [
                [1704445200, false, '{"time":864000, "radio":1}', notificationplugin::COMPLEMENTARY_CONDITION, false],
                [1706173200, false, '{"time":864000, "radio":1}', notificationplugin::COMPLEMENTARY_CONDITION, true],
                [1704445200, false, '{"time":864000, "radio":0}', notificationplugin::COMPLEMENTARY_CONDITION, false],
                [1706173200, false, '{"time":864000, "radio":0}', notificationplugin::COMPLEMENTARY_CONDITION, false],
                [1707987600, false, '{"time":864000, "radio":0}', notificationplugin::COMPLEMENTARY_CONDITION, true],
                [
                        self::COURSE_DATESTART + self::DURATION + 1, false, '{"time":864000, "radio":0}',
                        notificationplugin::COMPLEMENTARY_CONDITION, true,
                ],
                [1704445200, false, '{"time":864000, "radio":1}', notificationplugin::COMPLEMENTARY_EXCEPTION, false],
                [1706173200, false, '{"time":864000, "radio":1}', notificationplugin::COMPLEMENTARY_EXCEPTION, true],
                [1704445200, false, '{"time":864000, "radio":0}', notificationplugin::COMPLEMENTARY_EXCEPTION, false],
                [1706173200, false, '{"time":864000, "radio":0}', notificationplugin::COMPLEMENTARY_EXCEPTION, false],
                [1707987600, false, '{"time":864000, "radio":0}', notificationplugin::COMPLEMENTARY_EXCEPTION, true],
                [
                        self::COURSE_DATESTART + self::DURATION + 1, false, '{"time":864000, "radio":0}',
                        notificationplugin::COMPLEMENTARY_EXCEPTION, true,
                ],
        ];
    }

    /**
     * Test for get_title.
     *
     * @covers \notificationscondition_calendarstart\calendarstart::get_title
     */
    public function test_gettitle() {
        $this->assertNotNull(self::$subplugin->get_title());
        foreach (self::$elements as $element) {
            $this->assertStringContainsString($element, self::$subplugin->get_title());
        }
    }

    /**
     * Test for get_description.
     *
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
     * Test for process_markups.
     *
     * @covers \notificationscondition_calendarstart\calendarstart::process_markups
     */
    public function test_processmarkups() {
        $time = self::$caledarevent->timestart;
        $params[self::$subplugin::UI_TIME] = $time;
        $params = json_encode($params);
        $expected = str_replace(
            self::$subplugin->get_elements(),
            [\local_notificationsagent\helper\helper::to_human_format($time, true)],
            self::$subplugin->get_title()
        );
        self::$subplugin->set_parameters($params);
        $content = [];
        self::$subplugin->process_markups($content, self::$coursetest->id);
        $this->assertSame([$expected], $content);
    }

    /**
     * Test for get_ui.
     *
     * @covers \notificationscondition_calendarstart\calendarstart::get_ui
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
        $uiradiogroup = $method->invoke(self::$subplugin, self::$subplugin::UI_RADIO_GROUP);
        $uiradiogroupelements = [];
        foreach ($mform->getElement($uiradiogroup)->getElements() as $element) {
            $uiradiogroupelements[$element->getName()] = $element->getName();
        }
        $uigroupname = $method->invoke(self::$subplugin, self::$subplugin->get_subtype());
        $uigroupelements = [];
        foreach ($mform->getElement($uigroupname)->getElements() as $element) {
            $uigroupelements[] = $element->getName();
        }
        $uiradio = $method->invoke(self::$subplugin, self::$subplugin::UI_RADIO);
        $uidays = $method->invoke(self::$subplugin, self::$subplugin::UI_DAYS);
        $uihours = $method->invoke(self::$subplugin, self::$subplugin::UI_HOURS);
        $uiminutes = $method->invoke(self::$subplugin, self::$subplugin::UI_MINUTES);

        $this->assertTrue($mform->elementExists($uiactivityname));
        $this->assertTrue($mform->elementExists($uigroupname));
        $this->assertTrue(in_array($uiradio, $uiradiogroupelements));
        $this->assertTrue(in_array($uidays, $uigroupelements));
        $this->assertTrue(in_array($uihours, $uigroupelements));
        $this->assertTrue(in_array($uiminutes, $uigroupelements));
    }

    /**
     * Test for set_default.
     *
     * @covers \notificationscondition_calendarstart\calendarstart::set_default
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
        $uiradiogroup = $method->invoke(self::$subplugin, self::$subplugin::UI_RADIO_GROUP);
        $default = [];
        foreach ($mform->getElement($uiradiogroup)->getElements() as $element) {
            $default[$element->getName()][] = $element->getValue();
        }
        $uigroupname = $method->invoke(self::$subplugin, self::$subplugin->get_subtype());
        foreach ($mform->getElement($uigroupname)->getElements() as $element) {
            $default[$element->getName()] = $element->getValue();
        }

        $uiradio = $method->invoke(self::$subplugin, self::$subplugin::UI_RADIO);
        $uidays = $method->invoke(self::$subplugin, self::$subplugin::UI_DAYS);
        $uihours = $method->invoke(self::$subplugin, self::$subplugin::UI_HOURS);
        $uiminutes = $method->invoke(self::$subplugin, self::$subplugin::UI_MINUTES);

        $this->assertTrue(isset($default[$uiradio]) && in_array(self::$subplugin::UI_RADIO_DEFAULT_VALUE, $default[$uiradio]));
        $this->assertTrue(isset($default[$uidays]) && $default[$uidays] == self::$subplugin::UI_DAYS_DEFAULT_VALUE);
        $this->assertTrue(isset($default[$uihours]) && $default[$uihours] == self::$subplugin::UI_HOURS_DEFAULT_VALUE);
        $this->assertTrue(isset($default[$uiminutes]) && $default[$uiminutes] == self::$subplugin::UI_MINUTES_DEFAULT_VALUE);
    }

    /**
     * Test for convert_parameters.
     *
     * @covers \notificationscondition_calendarstart\calendarstart::convert_parameters
     */
    public function test_convertparameters() {
        $id = self::$subplugin->get_id();
        $params = [
                $id . "_calendarstart_days" => "1",
                $id . "_calendarstart_hours" => "0",
                $id . "_calendarstart_minutes" => "1",
                $id . "_calendarstart_cmid" => "7",
                $id . "_calendarstart_radio" => "1",
        ];
        $expected = '{"time":86460,"cmid":7,"radio":"1"}';
        $method = phpunitutil::get_method(self::$subplugin, 'convert_parameters');
        $result = $method->invoke(self::$subplugin, $params);
        $this->assertSame($expected, $result);
    }

    /**
     * Test validation.
     *
     * @covers       \notificationscondition_calendarstart\calendarstart::validation
     */
    public function test_validation() {
        self::setUser(2);// Admin.
        $event = self::getDataGenerator()->create_event([
                'eventtype' => 'course',
                'courseid' => self::$coursetest->id,
        ]);

        $objparameters = new \stdClass();
        $objparameters->cmid = $event->id;

        self::$subplugin->set_parameters(json_encode($objparameters));
        $this->assertTrue(self::$subplugin->validation(self::$coursetest->id));
    }
}
