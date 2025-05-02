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
// Funded by the European Union - Next GenerationEU\".
//
// Produced by the UNIMOODLE University Group: Universities of
// Valladolid, Complutense de Madrid, UPV/EHU, León, Salamanca,
// Illes Balears, Valencia, Rey Juan Carlos, La Laguna, Zaragoza, Málaga,
// Córdoba, Extremadura, Vigo, Las Palmas de Gran Canaria y Burgos.

/**
 * Version details
 *
 * @package    notificationscondition_courseend
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace notificationscondition_courseend;

use local_notificationsagent\evaluationcontext;
use local_notificationsagent\form\editrule_form;
use local_notificationsagent\helper\test\mock_base_logger;
use local_notificationsagent\helper\test\phpunitutil;
use local_notificationsagent\notificationplugin;
use local_notificationsagent\rule;

/**
 * Class for testing the courseend.
 *
 * @group notificationsagent
 */
final class courseend_test extends \advanced_testcase {
    /**
     * @var rule
     */
    private static $rule;
    /**
     * @var courseend
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

        self::$subplugin = new courseend(self::$rule->to_record());
        self::$subplugin->set_id(5);
        self::$coursetest = self::getDataGenerator()->create_course(
            ['startdate' => self::COURSE_DATESTART, 'enddate' => self::COURSE_DATEEND]
        );
        self::$coursecontext = \context_course::instance(self::$coursetest->id);
        self::$user = self::getDataGenerator()->create_user();
        self::$context = new evaluationcontext();
        self::$context->set_userid(self::$user->id);
        self::$context->set_courseid(self::$coursetest->id);
        self::$subtype = 'courseend';
        self::$elements = ['[TTTT]'];
    }

    /**
     * Test evaluate.
     *
     * @param int $timeaccess
     * @param bool $usecache
     * @param string $param
     * @param bool $complementary
     * @param bool $expected
     *
     * @covers       \notificationscondition_courseend\courseend::evaluate
     *
     * @dataProvider dataprovider
     */
    public function test_evaluate($timeaccess, $usecache, $param, $complementary, $expected): void {
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
            $objdb->startdate = self::COURSE_DATEEND - $params['time'];
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
     * Data provider for test_evaluate.
     */
    public static function dataprovider(): array {
        return [
                [1704445200, false, '{"time":864000}', notificationplugin::COMPLEMENTARY_CONDITION, false],
                [1706173200, false, '{"time":864000}', notificationplugin::COMPLEMENTARY_CONDITION, true],
                [1707123600, false, '{"time":864000}', notificationplugin::COMPLEMENTARY_CONDITION, false],
                [1704445200, true, '{"time":864000}', notificationplugin::COMPLEMENTARY_CONDITION, false],
                [1706173200, true, '{"time":864000}', notificationplugin::COMPLEMENTARY_CONDITION, true],
                [1707123600, true, '{"time":864000}', notificationplugin::COMPLEMENTARY_CONDITION, false],
                [1704445200, false, '{"time":864000}', notificationplugin::COMPLEMENTARY_EXCEPTION, false],
                [1706173200, false, '{"time":864000}', notificationplugin::COMPLEMENTARY_EXCEPTION, true],
                [1707123600, false, '{"time":864000}', notificationplugin::COMPLEMENTARY_EXCEPTION, false],
                [1704445200, true, '{"time":864000}', notificationplugin::COMPLEMENTARY_EXCEPTION, false],
                [1706173200, true, '{"time":864000}', notificationplugin::COMPLEMENTARY_EXCEPTION, true],
                [1707123600, true, '{"time":864000}', notificationplugin::COMPLEMENTARY_EXCEPTION, false],
        ];
    }

    /**
     * Test get subtype.
     *
     * @covers \notificationscondition_courseend\courseend::get_subtype
     */
    public function test_getsubtype(): void {
        $this->assertSame(self::$subtype, self::$subplugin->get_subtype());
    }

    /**
     * Test is generic.
     *
     * @covers \notificationscondition_courseend\courseend::is_generic
     */
    public function test_isgeneric(): void {
        $this->assertTrue(self::$subplugin->is_generic());
    }

    /**
     * Test get elements.
     *
     * @covers \notificationscondition_courseend\courseend::get_elements
     */
    public function test_getelements(): void {
        $this->assertSame(self::$elements, self::$subplugin->get_elements());
    }

    /**
     * Test check capability.
     *
     * @covers \notificationscondition_courseend\courseend::check_capability
     */
    public function test_checkcapability(): void {
        $this->assertSame(
            has_capability('notificationscondition/' . self::$subtype.':'.self::$subtype, self::$coursecontext),
            self::$subplugin->check_capability(self::$coursecontext)
        );
    }

    /**
     * Test get cmid.
     *
     * @covers \notificationscondition_courseend\courseend::get_cmid
     */
    public function test_getcmid(): void {
        $this->assertNull(self::$subplugin->get_cmid(self::$context));
    }

    /**
     * Test estimate next time.
     *
     * @param int $timeaccess
     * @param string $param
     * @param bool $complementary
     *
     * @covers       \notificationscondition_courseend\courseend::estimate_next_time
     * @dataProvider dataestimate
     */
    public function test_estimatenexttime($timeaccess, $param, $complementary): void {
        \uopz_set_return('time', $timeaccess);
        self::$context->set_params($param);
        self::$context->set_timeaccess($timeaccess);
        self::$context->set_complementary($complementary);
        self::$subplugin->set_id(self::CONDITIONID);

        self::$context->set_timeaccess($timeaccess);
        self::$context->set_complementary($complementary);
        self::$subplugin->set_id(self::CONDITIONID);
        self::$context->set_params(json_encode(['time' => $param]));
        // Test estimate next time.
        if (self::$context->is_complementary()) {
            if ($timeaccess < self::COURSE_DATEEND - $param) {
                self::assertEquals(time(), self::$subplugin->estimate_next_time(self::$context));
            } else if ($timeaccess >= self::COURSE_DATEEND - $param && $timeaccess <= self::COURSE_DATEEND) {
                self::assertEquals(self::COURSE_DATEEND, self::$subplugin->estimate_next_time(self::$context));
            } else if ($timeaccess > self::COURSE_DATEEND) {
                self::assertEquals(time(), self::$subplugin->estimate_next_time(self::$context));
            }
        } else {
            if ($timeaccess < self::COURSE_DATEEND - $param) {
                self::assertEquals(self::COURSE_DATEEND - $param, self::$subplugin->estimate_next_time(self::$context));
            } else if ($timeaccess >= self::COURSE_DATEEND - $param && $timeaccess <= self::COURSE_DATEEND) {
                self::assertEquals(time(), self::$subplugin->estimate_next_time(self::$context));
            } else if ($timeaccess > self::COURSE_DATEEND) {
                self::assertNull(self::$subplugin->estimate_next_time(self::$context));
            }
        }
        \uopz_unset_return('time');
    }

    /**
     * Data for estimate next time.
     *
     * @return array[]
     */
    public static function dataestimate(): array {
        return [
                [1704445200, 864000, notificationplugin::COMPLEMENTARY_CONDITION],
                [1706173200, 864000, notificationplugin::COMPLEMENTARY_CONDITION],
                [1707123600, 864000, notificationplugin::COMPLEMENTARY_CONDITION],
                [1704445200, 864000, notificationplugin::COMPLEMENTARY_CONDITION],
                [1706173200, 864000, notificationplugin::COMPLEMENTARY_CONDITION],
                [1707123600, 864000, notificationplugin::COMPLEMENTARY_CONDITION],
                [1704445200, 864000, notificationplugin::COMPLEMENTARY_EXCEPTION],
                [1706173200, 864000, notificationplugin::COMPLEMENTARY_EXCEPTION],
                [1707123600, 864000, notificationplugin::COMPLEMENTARY_EXCEPTION],
                [1704445200, 864000, notificationplugin::COMPLEMENTARY_EXCEPTION],
                [1706173200, 864000, notificationplugin::COMPLEMENTARY_EXCEPTION],
                [1707123600, 864000, notificationplugin::COMPLEMENTARY_EXCEPTION],
        ];
    }

    /**
     * Test get title.
     *
     * @covers \notificationscondition_courseend\courseend::get_title
     */
    public function test_gettitle(): void {
        $this->assertNotNull(self::$subplugin->get_title());
        foreach (self::$elements as $element) {
            $this->assertStringContainsString($element, self::$subplugin->get_title());
        }
    }

    /**
     * Test get description.
     *
     * @covers \notificationscondition_courseend\courseend::get_description
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
     * Test process markups.
     *
     * @covers \notificationscondition_courseend\courseend::process_markups
     */
    public function test_processmarkups(): void {
        $time = self::$coursetest->enddate;
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
     * Test get ui.
     *
     * @covers \notificationscondition_courseend\courseend::get_ui
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
        $uigroupname = $method->invoke(self::$subplugin, self::$subplugin->get_subtype());
        $uigroupelements = [];
        foreach ($mform->getElement($uigroupname)->getElements() as $element) {
            $uigroupelements[] = $element->getName();
        }
        $uidays = $method->invoke(self::$subplugin, self::$subplugin::UI_DAYS);
        $uihours = $method->invoke(self::$subplugin, self::$subplugin::UI_HOURS);
        $uiminutes = $method->invoke(self::$subplugin, self::$subplugin::UI_MINUTES);

        $this->assertTrue($mform->elementExists($uigroupname));
        $this->assertTrue(in_array($uidays, $uigroupelements));
        $this->assertTrue(in_array($uihours, $uigroupelements));
        $this->assertTrue(in_array($uiminutes, $uigroupelements));
    }

    /**
     * Test set default.
     *
     * @covers \notificationscondition_courseend\courseend::set_default
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
     * Test convert parameters.
     *
     * @covers \notificationscondition_courseend\courseend::convert_parameters
     */
    public function test_convertparameters(): void {
        $id = self::$subplugin->get_id();
        $params = [
                $id . "_courseend_days" => "1",
                $id . "_courseend_hours" => "0",
                $id . "_courseend_minutes" => "1",
        ];
        $expected = '{"time":86460}';
        $method = phpunitutil::get_method(self::$subplugin, 'convert_parameters');
        $result = $method->invoke(self::$subplugin, $params);
        $this->assertSame($expected, $result);
    }

    /**
     * Test update after restore method
     *
     * @return void
     * @covers \notificationscondition_courseend\courseend::update_after_restore
     */
    public function test_update_after_restore(): void {
        $logger = new mock_base_logger(0);
        $this->assertFalse(self::$subplugin->update_after_restore('restoreid', self::$coursecontext->id, $logger));
    }

    /**
     * Test validation.
     *
     * @covers       \notificationscondition_courseend\courseend::validation
     */
    public function test_validation(): void {
        $this->assertTrue(self::$subplugin->validation(self::$coursetest->id));
    }
}
