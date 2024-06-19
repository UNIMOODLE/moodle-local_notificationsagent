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
 * @package    notificationscondition_ac
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace notificationscondition_ac;

use local_notificationsagent\evaluationcontext;
use local_notificationsagent\notificationplugin;
use local_notificationsagent\helper\test\phpunitutil;
use local_notificationsagent\rule;
use notificationscondition_ac\ac;
use local_notificationsagent\form\editrule_form;
use local_notificationsagent\helper\test\mock_base_logger;

/**
 * Class for testing the ac.
 *
 * @group notificationsagent
 */
class ac_test extends \advanced_testcase {
    /**
     * @var rule
     */
    private static $rule;
    /**
     * @var ac
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

        self::$subplugin = new ac(self::$rule->to_record());
        self::$subplugin->set_id(5);
        self::$coursetest = self::getDataGenerator()->create_course(
            ['startdate' => self::COURSE_DATESTART, 'enddate' => self::COURSE_DATEEND]
        );
        self::$coursecontext = \context_course::instance(self::$coursetest->id);
        self::$user = self::getDataGenerator()->create_user(['firstname' => 'Fernando']);
        self::setUser(self::$user);
        self::$context = new evaluationcontext();
        self::$context->set_userid(self::$user->id);
        self::$context->set_courseid(self::$coursetest->id);
        self::$subtype = 'ac';
        self::$elements = null;
    }

    /**
     * Test evaluate.
     *
     * @param string $conditionjson
     * @param bool $expected
     *
     * @covers       \notificationscondition_ac\ac::evaluate
     *
     * @dataProvider dataprovider
     */
    public function test_evaluate(string $conditionjson, bool $expected) {
        self::$context->set_params($conditionjson);

        // Test evaluate.
        $result = self::$subplugin->evaluate(self::$context);
        $this->assertSame($expected, $result);
    }

    /**
     * Data provider for test_evaluate.
     */
    public static function dataprovider(): array {
        return [
                [
                        '{"op":"&","c":[{"op":"&","c":[{"type":"profile","sf":"firstname","op":"isequalto","v":"Fernando"}]},
                        {"op":"!|","c":[]}],"showc":[true,true],"errors":["availability:error_list_nochildren"]}',
                        true,
                ],
                [
                        '{"op":"&","c":[{"op":"&","c":[{"type":"profile","sf":"firstname","op":"isequalto","v":"Miguel"}]},
                        {"op":"!|","c":[]}],"showc":[true,true],"errors":["availability:error_list_nochildren"]}',
                        false,
                ],
        ];
    }

    /**
     * Test get subtype.
     *
     * @covers \notificationscondition_ac\ac::get_subtype
     */
    public function test_getsubtype() {
        $this->assertSame(self::$subtype, self::$subplugin->get_subtype());
    }

    /**
     * Test is generic.
     *
     * @covers \notificationscondition_ac\ac::is_generic
     */
    public function test_isgeneric() {
        $this->assertFalse(self::$subplugin->is_generic());
    }

    /**
     * Test get elements.
     *
     * @covers \notificationscondition_ac\ac::get_elements
     */
    public function test_getelements() {
        $this->assertSame(self::$elements, self::$subplugin->get_elements());
    }

    /**
     * Test check capability.
     *
     * @covers \notificationscondition_ac\ac::check_capability
     */
    public function test_checkcapability() {
        $this->assertFalse(self::$subplugin->check_capability(self::$coursecontext));
    }

    /**
     * Test estimate next time
     *
     * @covers       \notificationscondition_ac\ac::estimate_next_time
     * @dataProvider dataestimate
     *
     * @param string $conditionjson
     * @param int|null $expected
     *
     * @return void
     * @throws \coding_exception
     */
    public function test_estimatenexttime($conditionjson, $expected) {
        \uopz_set_return('time', 1704099600);

        self::$context->set_params($conditionjson);

        $result = self::$subplugin->estimate_next_time(self::$context);

        $this->assertEquals($expected, $result);

        \uopz_unset_return('time');
    }

    /**
     * Data provider for test_estimatenexttime
     *
     * @return array[]
     */
    public static function dataestimate(): array {
        return [
                'condition. available' => [
                        '{"op":"&","c":[{"op":"&","c":[{"type":"profile","sf":"firstname","op":"isequalto","v":"Fernando"}]},
                        {"op":"!|","c":[]}],"showc":[true,true],"errors":["availability:error_list_nochildren"]}',
                        1704099600,
                ],
                'condition. not available' => [
                        '{"op":"&","c":[{"op":"&","c":[{"type":"profile","sf":"firstname","op":"isequalto","v":"Miguel"}]},
                        {"op":"!|","c":[]}],"showc":[true,true],"errors":["availability:error_list_nochildren"]}',
                        null,
                ],
        ];
    }

    /**
     * Test get title.
     *
     * @covers \notificationscondition_ac\ac::get_title
     */
    public function test_gettitle() {
        $this->assertNotNull(self::$subplugin->get_title());
        $this->assertIsString(self::$subplugin->get_title());
    }

    /**
     * Test get description.
     *
     * @covers \notificationscondition_ac\ac::get_description
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
     * @covers \notificationscondition_ac\ac::convert_parameters
     */
    public function test_convertparameters() {
        $json =
                '{"op":"&","c":[{"op":"&","c":[{"type":"profile","sf":"firstname","op":"isequalto","v":"Fernando"}]},
                {"op":"!|","c":[]}],"showc":[true,true],"errors":["availability:error_list_nochildren"]}';
        $params = [
                editrule_form::FORM_JSON_AC => $json,
        ];
        $expected = $json;
        $method = phpunitutil::get_method(self::$subplugin, 'convert_parameters');
        $result = $method->invoke(self::$subplugin, $params);
        $this->assertSame($expected, $result);
    }

    /**
     * Test process markups.
     *
     * @covers \notificationscondition_ac\ac::process_markups
     */
    public function test_processmarkups() {
        $expected = 'Your First name is Fernando';
        $json =
                '{"op":"&","c":[{"op":"&","c":[{"type":"profile","sf":"firstname","op":"isequalto","v":"Fernando"}]},
                {"op":"!|","c":[]}],"showc":[true,true],"errors":["availability:error_list_nochildren"]}';
        self::$subplugin->set_parameters($json);
        $content = [];
        self::$subplugin->process_markups($content, self::$coursetest->id, false);
        $this->assertSame([$expected], $content);
    }

    /**
     * Test get ui.
     *
     * @covers \notificationscondition_ac\ac::get_ui
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
        $this->assertEmpty(self::$subplugin->get_ui($mform, $courseid, $subtype));
    }

    /**
     * Test update after restore method
     *
     * @return void
     * @covers \notificationscondition_ac\ac::update_after_restore
     */
    public function test_update_after_restore() {
        $logger = new mock_base_logger(0);
        $this->assertFalse(self::$subplugin->update_after_restore('restoreid', self::$coursecontext->id, $logger));
    }

    /**
     * Test validation.
     *
     * @param array $params
     * @param bool $expected
     * @dataProvider datavalidation
     * @covers       \notificationscondition_ac\ac::validation
     */
    public function test_validation($params, $expected) {
        $quizgenerator = self::getDataGenerator()->get_plugin_generator('mod_quiz');
        $cmtestaa = $quizgenerator->create_instance([
                'name' => 'Quiz unittest',
                'course' => self::$coursetest->id,
                "timeopen" => self::CM_DATESTART,
                "timeclose" => self::CM_DATEEND,
                'visible' => true,
        ]);
        $params = str_replace('|CMID|', $cmtestaa->cmid, $params);
        self::$subplugin->set_parameters($params);
        $this->assertSame(self::$subplugin->validation(self::$coursetest->id), $expected);
    }

    /**
     * Dataprovider for validation
     *
     * @return array[]
     */
    public static function datavalidation(): array {
        return [
                'Activity completion' => ['{"op":"&","c":[{"op":"&","c":[{"type":"completion","cm":|CMID|,"e":1}]},
                {"op":"!|","c":[]}],"showc":[true,true],"errors":["availability:error_list_nochildren"]}',
                        true],
                'Activity completion2' => ['{"op":"&","c":[{"op":"&","c":[{"type":"completion","cm":100,"e":1}]},
                {"op":"!|","c":[]}],"showc":[true,true],"errors":["availability:error_list_nochildren"]}',
                        false],
        ];
    }

    /**
     * Test load_dataform.
     *
     * @covers \notificationscondition_ac\ac::load_dataform
     */
    public function test_loaddataform() {
        $json =
                '{"op":"&","c":[{"op":"&","c":[{"type":"profile","sf":"firstname","op":"isequalto","v":"Fernando"}]},
                {"op":"!|","c":[]}],"showc":[true,true],"errors":["availability:error_list_nochildren"]}';
        self::$subplugin->set_parameters($json);

        $expected = [
                editrule_form::FORM_JSON_AC => $json,
        ];

        $this->assertSame($expected, self::$subplugin->load_dataform());
    }
}
