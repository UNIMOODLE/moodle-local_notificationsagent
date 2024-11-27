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
 * @package    notificationscondition_ondates
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace notificationscondition_ondates;

use local_notificationsagent\evaluationcontext;
use local_notificationsagent\form\editrule_form;
use local_notificationsagent\helper\test\mock_base_logger;
use local_notificationsagent\notificationplugin;
use local_notificationsagent\helper\test\phpunitutil;
use local_notificationsagent\rule;
use notificationscondition_ondates\ondates;

/**
 * Tests for the ondates condition.
 *
 * @group notificationsagent
 */
class ondates_test extends \advanced_testcase {
    /**
     * @var rule
     */
    private static $rule;
    /**
     * @var string
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

        self::$subplugin = new ondates(self::$rule->to_record());
        self::$subplugin->set_id(5);
        self::$coursetest = self::getDataGenerator()->create_course(
            ['startdate' => self::COURSE_DATESTART, 'enddate' => self::COURSE_DATEEND]
        );
        self::$coursecontext = \context_course::instance(self::$coursetest->id);
        self::$user = self::getDataGenerator()->create_user();
        self::$context = new evaluationcontext();
        self::$context->set_userid(self::$user->id);
        self::$context->set_courseid(self::$coursetest->id);
        self::$subtype = 'ondates';
        self::$elements = ['FFFF', 'FFFF'];
    }

    /**
     * Test evaluate
     *
     * @param int $timeaccess timeaccess
     * @param bool $usecache usecache
     * @param bool $complementary complementary
     * @param bool $expected expected
     * @param array $params params
     *
     *
     * @covers       \notificationscondition_ondates\ondates::evaluate
     *
     * @dataProvider dataprovider
     */
    public function test_evaluate($timeaccess, $usecache, $complementary, $expected, $params) {
        self::$context->set_timeaccess($timeaccess);
        self::$context->set_complementary($complementary);
        self::$context->set_params($params);
        self::$subplugin->set_id(self::CONDITIONID);

        if ($usecache) {
            global $DB;
            $objdb = new \stdClass();
            $objdb->userid = self::$user->id;
            $objdb->courseid = self::$coursetest->id;
            $objdb->startdate = $timeaccess;
            $objdb->pluginname = self::$subtype;
            $objdb->conditionid = self::CONDITIONID;
            // Insert.
            $DB->insert_record('notificationsagent_cache', $objdb);
        }
        // Insert.
        self::$subplugin->set_id(self::CONDITIONID);

        $result = self::$subplugin->evaluate(self::$context);

        $this->assertSame($expected, $result);
    }

    /**
     * Data provider for test_evaluate.
     */
    public static function dataprovider(): array {
        return [
                [
                        1701598161, false, notificationplugin::COMPLEMENTARY_CONDITION, false,
                        '{"startdate":1713913200, "enddate":1714345199}',
                ],
                [
                        1701511761, false, notificationplugin::COMPLEMENTARY_CONDITION, false,
                        '{"startdate":1714345199, "enddate":1713913200}',
                ],
                [
                        1701691707, false, notificationplugin::COMPLEMENTARY_CONDITION, true, '
                        {"startdate":1701622222, "enddate":1714345199}',
                ],
                [
                        1703498961, false, notificationplugin::COMPLEMENTARY_CONDITION, false,
                        '{"startdate":1713913200, "enddate":1714345199}',
                ],
                [
                        1701400000, true, notificationplugin::COMPLEMENTARY_CONDITION, false,
                        '{"startdate":1701500000, "enddate":1701510000}',
                ],
        ];
    }

    /**
     * Test get subtype.
     *
     * @covers \notificationscondition_ondates\ondates::get_subtype
     */
    public function test_getsubtype() {
        $this->assertSame(self::$subtype, self::$subplugin->get_subtype());
    }

    /**
     * Test is generic.
     *
     * @covers \notificationscondition_ondates\ondates::is_generic
     */
    public function test_isgeneric() {
        $this->assertTrue(self::$subplugin->is_generic());
    }

    /**
     * Test get elements.s
     *
     * @covers \notificationscondition_ondates\ondates::get_elements
     */
    public function test_getelements() {
        $this->assertSame(self::$elements, self::$subplugin->get_elements());
    }

    /**
     * Test check capability.
     *
     * @covers \notificationscondition_ondates\ondates::check_capability
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
     * @covers       \notificationscondition_ondates\ondates::estimate_next_time
     * @dataProvider dataondates
     *
     * @param int $timeaccess
     * @param int $expected
     * @param array $params
     * @param int $complementary
     *
     * @return void
     */
    public function test_estimatenexttime($timeaccess, $expected, $params, $complementary) {
        \uopz_set_return('time', $timeaccess);
        self::$context->set_params($params);
        // Saturday, Sunday configuration.
        self::$context->set_complementary($complementary);
        set_config('calendar_ondates', 65);
        date_default_timezone_set('Europe/Madrid');
        self::$context->set_timeaccess($timeaccess);
        // Test estimate next time.

        $this->assertEquals($expected, self::$subplugin->estimate_next_time(self::$context));
        \uopz_unset_return('time');
    }

    /**
     * Dataprovider for estimatenext time
     *
     * @return array[]
     */
    public static function dataondates(): array {
        return [
                'Condition Dec 03 2023 -> Apr 29 2024' => [1701621111, 1704506700, '{"startdate":1704506700, "enddate":1714345199}',
                        0],
                'Exception Jan 06 2024 -> Apr 28 2024' => [1701621111, 1701621111, '{"startdate":1704506700, "enddate":1714345199}',
                        1],
                'Condition Dec 03 2023 -> May 05 2024' => [1704495600, 1704495600, '{"startdate":1701622222, "enddate":1714894444}',
                        0],
                'Condition Dec 03 2023 -> May 28 2024 ' => [1716995444, false, '{"startdate":1701622222, "enddate":1716894444}', 0],
                'Exception Dec 03 2023 -> May 28 2024 ' => [
                        1716995444, 1716995444, '{"startdate":1701622222, "enddate":1716894444}', 1,
                ],
                'Exception Dec 03 2023 -> May 05 2024' => [1704495600, 1714946400, '{"startdate":1701622222, "enddate":1714946399}',
                        1],
                'Exception Jan 20 2024 -> May 05 2024' => [1704495600, 1714946400, '{"startdate":1701622222, "enddate":1714946399}',
                        1],
        ];
    }

    /**
     * Test get cmid.
     *
     * @covers \notificationscondition_ondates\ondates::get_cmid
     */
    public function test_getcmid() {
        // Test estimate next time.
        $this->assertNull(self::$subplugin->get_cmid(self::$context));
    }

    /**
     * Test get title.
     *
     * @covers \notificationscondition_ondates\ondates::get_title
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
     * @covers \notificationscondition_ondates\ondates::get_description
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
     * @covers \notificationscondition_ondates\ondates::convert_parameters
     */
    public function test_convertparameters() {
        $starddate = 1701622222;
        $enddate = 1714894444;
        $method = phpunitutil::get_method(self::$subplugin, 'get_name_ui');
        $startdatename = $method->invoke(self::$subplugin, self::$subplugin::STARTDATE);
        $enddatename = $method->invoke(self::$subplugin, self::$subplugin::ENDDATE);
        $params = '{"' . $startdatename . '": ' . $starddate . ',"' . $enddatename . '":' . $enddate . '}';
        $paramsdecoded = json_decode($params);
        $lastparams[$startdatename] = strtotime('today', $paramsdecoded->$startdatename);
        $lastparams[$enddatename] = strtotime('tomorrow', $paramsdecoded->$enddatename) - 1;
        $expected = '{"' . self::$subplugin::STARTDATE . '":' . $lastparams[$startdatename] . ',"'
                . self::$subplugin::ENDDATE . '":' . $lastparams[$enddatename] . '}';
        $method = phpunitutil::get_method(self::$subplugin, 'convert_parameters');
        $result = $method->invoke(self::$subplugin, $lastparams);
        $this->assertEquals($result, $expected);
    }

    /**
     * Test process markups.
     *
     * @covers \notificationscondition_ondates\ondates::process_markups
     * @covers \notificationscondition_ondates\ondates::replace_first
     */
    public function test_processmarkups() {
        $content = [];
        $params = '{"startdate":1704506700, "enddate":1714345199}';
        $paramsdecoded = json_decode($params);
        self::$subplugin->set_parameters($params);
        self::$subplugin->process_markups($content, self::$coursetest->id);

        $humanvalue = self::$subplugin->replace_first(
            self::$subplugin->get_elements()[0],
            userdate($paramsdecoded->startdate),
            self::$subplugin->get_title()
        );
        $humanvalue = self::$subplugin->replace_first(
            self::$subplugin->get_elements()[1],
            userdate($paramsdecoded->enddate),
            $humanvalue
        );
        $this->assertSame($humanvalue, $content[0]);
    }

    /**
     * Test whether is ondates
     *
     * @param int $timeaccess
     * @param string $params
     * @param bool $expected
     *
     * @covers       \notificationscondition_ondates\ondates::is_ondates()
     * @dataProvider dataproviderwe
     */
    final public function test_isondates(int $timeaccess, string $params, bool $expected) {
        set_config('calendar_ondates', 65);
        $paramsdecoded = json_decode($params);
        $this->assertSame(
            $expected,
            ondates::is_ondates(
                $timeaccess,
                $paramsdecoded->startdate,
                $paramsdecoded->enddate
            )
        );
    }

    /**
     * Dataprovider for is ondates.
     */
    public static function dataproviderwe(): array {
        // Only valid for a saturday, sunday configuration.
        return [
                'On dates' => [1714039202, '{"startdate":1704506700, "enddate":1714345199}', true],
                'In 1 year' => [1745575202, '{"startdate":1704506700, "enddate":1714345199}', false],
                '1 year ago' => [1682416802, '{"startdate":1704506700, "enddate":1714345199}', false],
        ];
    }

    /**
     * Test get ui.
     *
     * @covers \notificationscondition_ondates\ondates::get_ui
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
        $startdatename = $method->invoke(self::$subplugin, self::$subplugin::STARTDATE);
        $enddatename = $method->invoke(self::$subplugin, self::$subplugin::ENDDATE);

        $this->assertTrue($mform->elementExists($startdatename));
        $this->assertTrue($mform->elementExists($enddatename));
    }

    /**
     * Test validate form.
     *
     * @param array $params
     * @param bool $expected
     *
     * @dataProvider datavalidation
     * @covers       \notificationscondition_ondates\ondates::validation
     */
    public function test_validation($params, $expected) {
        self::$subplugin->set_parameters($params);
        $this->assertSame($expected, self::$subplugin->validation(self::$coursetest->id));
    }

    /**
     * Dataprovider for validation
     *
     * @return array[]
     */
    public static function datavalidation(): array {
        return [
                'Startdate(May 02 2024) Enddate(Jan 13 3000)' => ['{"startdate": 1714627363, "enddate": 32504765577}', true],
                'Startdate(May 04 2024) Enddate(May 02 2024)' => ['{"startdate": 1714827363, "enddate": 1714627363}', false],
                'Startdate(Apr 18 2024) Enddate(Apr 22 2024)' => ['{"startdate": 1713427363, "enddate": 1713827363}', true],
        ];
    }

    /**
     * Test load data form.
     *
     * @covers \notificationscondition_ondates\ondates::load_dataform
     */
    public function test_loaddataform() {
        $params = '{"startdate": 1714627363, "enddate": 1799827363}';
        self::$subplugin->set_parameters($params);
        $this->assertIsArray(self::$subplugin->load_dataform());
    }

    /**
     * Test update after restore method
     *
     * @covers \notificationscondition_ondates\ondates::update_after_restore
     * @return void
     *
     */
    public function test_update_after_restore() {
        $logger = new mock_base_logger(0);
        $this->assertFalse(self::$subplugin->update_after_restore('restoreid', self::$coursecontext->id, $logger));
    }
}
