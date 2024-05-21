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
 * @package    notificationscondition_weekdays
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace notificationscondition_weekdays;

use local_notificationsagent\evaluationcontext;
use local_notificationsagent\form\editrule_form;
use local_notificationsagent\notificationplugin;
use local_notificationsagent\helper\test\phpunitutil;
use local_notificationsagent\rule;
use notificationscondition_weekdays\weekdays;

/**
 * Tests for the weekdays condition.
 *
 * @group notificationsagent
 */
class weekdays_test extends \advanced_testcase {

    /**
     * @var rule
     */
    private static $rule;
    /**
     * @var weekdays
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

    public function setUp(): void {
        parent::setUp();
        $this->resetAfterTest(true);
        self::$rule = new rule();

        self::$subplugin = new weekdays(self::$rule->to_record());
        self::$subplugin->set_id(5);
        self::$coursetest = self::getDataGenerator()->create_course(
            ['startdate' => self::COURSE_DATESTART, 'enddate' => self::COURSE_DATEEND]
        );
        self::$coursecontext = \context_course::instance(self::$coursetest->id);
        self::$user = self::getDataGenerator()->create_user();
        self::$context = new evaluationcontext();
        self::$context->set_userid(self::$user->id);
        self::$context->set_courseid(self::$coursetest->id);
        self::$subtype = 'weekdays';
        self::$elements = ['LMXJVSD'];

    }

    /**
     * Test evaluate
     *
     * @param int    $timeaccess
     * @param string $params
     * @param bool   $expected
     *
     * @covers       \notificationscondition_weekdays\weekdays::evaluate
     *
     * @dataProvider dataprovider
     */
    public function test_evaluate($timeaccess, $params, $expected) {
        self::$context->set_timeaccess($timeaccess);
        self::$context->set_params($params);

        $result = self::$subplugin->evaluate(self::$context);

        $this->assertSame($expected, $result);
    }

    /**
     * Provider for evaluate
     *
     * @return array
     */
    public static function dataprovider(): array {
        return [
            'Sat' => [1701598161, '{"weekdays":[4,5]}', false],
            'Sun' => [1701511761, '{"weekdays":[4,5]}', false],
            'Thu' => [1712786400, '{"weekdays":[4,5]}', true],
            'Fri' => [1712872800, '{"weekdays":[4,5]}', true],

        ];
    }

    /**
     * Test get subtype.
     *
     * @covers \notificationscondition_weekdays\weekdays::get_subtype
     */
    public function test_getsubtype() {
        $this->assertSame(self::$subtype, self::$subplugin->get_subtype());
    }

    /**
     * Test is generic.
     *
     * @covers \notificationscondition_weekdays\weekdays::is_generic
     */
    public function test_isgeneric() {
        $this->assertTrue(self::$subplugin->is_generic());
    }

    /**
     * Test get elements.
     *
     * @covers \notificationscondition_weekdays\weekdays::get_elements
     */
    public function test_getelements() {
        $this->assertSame(self::$elements, self::$subplugin->get_elements());
    }

    /**
     * Test check capability.
     *
     * @covers \notificationscondition_weekdays\weekdays::check_capability
     */
    public function test_checkcapability() {
        $this->assertSame(
            has_capability('local/notificationsagent:' . self::$subtype, self::$coursecontext),
            self::$subplugin->check_capability(self::$coursecontext)
        );
    }

    /**
     * Test weeekend estimate next time
     *
     * @covers       \notificationscondition_weekdays\weekdays::estimate_next_time
     * @covers       \notificationscondition_weekdays\weekdays::correct_weekday
     * @dataProvider dataweekdays
     *
     * @param int    $timeaccess
     * @param int    $expected
     * @param string $param
     * @param int    $complementary
     *
     * @return void
     */
    public function test_estimatenexttime($timeaccess, $expected, $param, $complementary) {
        \uopz_set_return('time', $timeaccess);
        date_default_timezone_set('Europe/Madrid');
        self::$context->set_complementary($complementary);
        self::$context->set_timeaccess($timeaccess);
        self::$context->set_params($param);
        // Test estimate next time.
        $this->assertEquals($expected, self::$subplugin->estimate_next_time(self::$context));
        \uopz_unset_return('time');

    }

    /**
     * Dataprovider for estimatenext time
     *
     * @return array[]
     */
    public static function dataweekdays(): array {
        return [
            'Condition Thursday 4' => [1712181600, 1712181600, '{"weekdays":[4]}', 0],
            'Condition Monday 8' => [1712527200, 1712786400, '{"weekdays":[4]}', 0],
            'Condition Monday 15' => [1713132000, 1713391200, '{"weekdays":[4]}', 0],
            'Exception Thursday 4' => [1712181600, 1712268000, '{"weekdays":[4]}', 1],
            'Exception Monday 8' => [1712527200, 1712527200, '{"weekdays":[4]}', 1],
            'Exception Monday 15' => [1713132000, 1713132000, '{"weekdays":[4]}', 1],
        ];
    }

    /**
     * Test get cmid.
     *
     * @covers \notificationscondition_weekdays\weekdays::get_cmid
     */
    public function test_getcmid() {
        // Test estimate next time.
        $this->assertNull(self::$subplugin->get_cmid(self::$context));
    }

    /**
     * Test get title.
     *
     * @covers \notificationscondition_weekdays\weekdays::get_title
     */
    public function test_gettitle() {
        $this->assertNotNull(self::$subplugin->get_title());
        foreach (self::$elements as $element) {
            $this->assertStringContainsString($element, self::$subplugin->get_title());
        }
    }

    /**
     * Test get description.
     *
     * @covers \notificationscondition_weekdays\weekdays::get_description
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
     * Test convert parameters.
     *
     * @covers \notificationscondition_weekdays\weekdays::convert_parameters
     * @covers \notificationscondition_weekdays\weekdays::get_weekdays_list
     */
    public function test_convertparameters() {
        $expected = '{"weekdays":[0,2,4]}';
        $calendar = \core_calendar\type_factory::get_calendar_instance();
        $weekdays = $calendar->get_weekdays();
        $count = 0;
        // Weekdays as unmarked.
        foreach ($weekdays as $day) {
            $reflectionmethod = new \ReflectionMethod(weekdays::class, 'get_name_ui');
            $reflectionmethod->setAccessible(true);
            $nameui = $reflectionmethod->invoke(self::$subplugin, self::$subplugin::UI_DAYOFWEEK . $count);
            $params[$nameui] = "0";
            $count++;
        }
        $expecteddecode = json_decode($expected);
        // Weekdays selected.
        foreach ($expecteddecode->weekdays as $day) {
            $nameui = $reflectionmethod->invoke(self::$subplugin, self::$subplugin::UI_DAYOFWEEK . $day);
            $params[$nameui] = "1";
        }

        $method = phpunitutil::get_method(self::$subplugin, 'convert_parameters');
        $result = $method->invoke(self::$subplugin, $params);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test process markups.
     *
     * @covers \notificationscondition_weekdays\weekdays::process_markups
     */
    public function test_processmarkups() {
        $params = '{"weekdays":[0, 1]}';
        $paramsencoded = json_decode($params);
        self::$subplugin->set_parameters($params);
        $calendar = \core_calendar\type_factory::get_calendar_instance();
        $calendar->get_weekdays();
        $weekdays = $calendar->get_weekdays();
        foreach ($paramsencoded->weekdays as $day) {
            empty($contentdays) ? $contentdays = $weekdays[$day]['fullname']
                : $contentdays .= ', ' . $weekdays[$day]['fullname'];
        }
        $expected = str_replace(self::$subplugin->get_elements(), $contentdays, self::$subplugin->get_title());

        $content = [];
        self::$subplugin->process_markups($content, self::$coursetest->id);
        $this->assertSame($expected, $content[0]);
    }

    /**
     * Test get ui.
     *
     * @covers \notificationscondition_weekdays\weekdays::get_ui
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
        $calendar = \core_calendar\type_factory::get_calendar_instance();
        $calendar->get_weekdays();
        $weekdays = $calendar->get_weekdays();
        $indexday = 0;

        $uigroupelements = [];
        $uigroupname = $method->invoke(self::$subplugin, self::$subplugin->get_subtype());
        $this->assertTrue($mform->elementExists($uigroupname));
        foreach ($mform->getElement($uigroupname)->getElements() as $element) {
            $uigroupelements[] = $element->getName();
        }
        $this->assertEquals(7, count($uigroupelements));

        foreach ($weekdays as $day) {
            $uidayname = $method->invoke(self::$subplugin, self::$subplugin::UI_DAYOFWEEK . $indexday);
            $checkboxnames[] = $uidayname;
            $indexday++;
        }
        $this->assertEquals($checkboxnames, $uigroupelements);

    }

    /**
     * Test validate form.
     *
     * @dataProvider datavalidation
     * @covers       \notificationscondition_weekdays\weekdays::validation
     */
    public function test_validation($params) {
        self::$subplugin->set_parameters($params);
        $this->assertIsBool(self::$subplugin->validation(self::$coursetest->id));
    }

    /**
     * Dataprovider for validation
     *
     * @return array[]
     */
    public static function datavalidation(): array {
        return [
            '2 days selected' => ['{"weekdays":[]}'],
            '0 days selected' => ['{"weekdays":[4, 5]}'],
        ];
    }

    /**
     * Test load data form.
     *
     * @covers \notificationscondition_weekdays\weekdays::load_dataform
     */
    public function test_loaddataform() {
        $params = '{"weekdays":[4]}';
        self::$subplugin->set_parameters($params);
        $this->assertIsArray(self::$subplugin->load_dataform());
    }

}
