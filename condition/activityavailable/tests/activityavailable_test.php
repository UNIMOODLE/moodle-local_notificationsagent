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
 * @package    notificationscondition_activityavailable
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace notificationscondition_activityavailable;

use local_notificationsagent\evaluationcontext;
use local_notificationsagent\notificationplugin;
use local_notificationsagent\helper\test\phpunitutil;
use local_notificationsagent\rule;
use notificationscondition_activityavailable\activityavailable;
use local_notificationsagent\form\editrule_form;

/**
 * Class for testing the activityavailable.
 * @group notificationsagent
 */
class activityavailable_test extends \advanced_testcase {

    /**
     * @var rule
     */
    private static $rule;
    /**
     * @var activityavailable
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

        self::$subplugin = new activityavailable(self::$rule->to_record());
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
        self::$subtype = 'activityavailable';
        self::$elements = ['[AAAA]'];
    }

    /**
     * Test evaluate.
     *
     * @param string $conditionjson
     * @param bool   $expected
     *
     * @throws \coding_exception
     * @covers       \notificationscondition_activityavailable\activityavailable::evaluate
     *
     * @dataProvider dataprovider
     */
    public function test_evaluate(string $conditionjson, bool $expected) {
        $quizgenerator = self::getDataGenerator()->get_plugin_generator('mod_quiz');
        $cmtestaa = $quizgenerator->create_instance([
            'name' => 'Quiz unittest',
            'course' => self::$coursetest->id,
            "timeopen" => self::CM_DATESTART,
            "timeclose" => self::CM_DATEEND,
            'availability' => $conditionjson,
        ]);

        self::$context->set_params(json_encode(['cmid' => $cmtestaa->cmid]));

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
                '{"op":"&","c":[{"type":"profile","sf":"firstname","op":"isequalto","v":"Fernando"}],"showc":[true]}', true,
            ],
            [
                '{"op":"&","c":[{"type":"profile","sf":"firstname","op":"isequalto","v":"Miguel"}],"showc":[true]}', false,
            ],
        ];
    }

    /**
     * Test get subtype.
     *
     * @covers \notificationscondition_activityavailable\activityavailable::get_subtype
     */
    public function test_getsubtype() {
        $this->assertSame(self::$subtype, self::$subplugin->get_subtype());
    }

    /**
     * Test is generic.
     *
     * @covers \notificationscondition_activityavailable\activityavailable::is_generic
     */
    public function test_isgeneric() {
        $this->assertFalse(self::$subplugin->is_generic());
    }

    /**
     * Test get elements.
     *
     * @covers \notificationscondition_activityavailable\activityavailable::get_elements
     */
    public function test_getelements() {
        $this->assertSame(self::$elements, self::$subplugin->get_elements());
    }

    /**
     * Test check capability.
     *
     * @covers \notificationscondition_activityavailable\activityavailable::check_capability
     */
    public function test_checkcapability() {
        $this->assertSame(
            has_capability('local/notificationsagent:' . self::$subtype, self::$coursecontext),
            self::$subplugin->check_capability(self::$coursecontext)
        );
    }

    /**
     * Test estimate next time
     *
     * @covers       \notificationscondition_activityavailable\activityavailable::estimate_next_time
     * @dataProvider dataestimate
     *
     * @param string   $conditionjson
     * @param int      $complementary
     * @param int|null $expected
     *
     * @return void
     * @throws \coding_exception
     */
    public function test_estimatenexttime($conditionjson, $complementary, $expected) {
        \uopz_set_return('time', 1704099600);
        $quizgen = self::getDataGenerator()->get_plugin_generator('mod_quiz');
        $cmtestent = $quizgen->create_instance([
            'name' => 'Quiz unittest',
            'course' => self::$coursetest->id,
            'availability' => $conditionjson,
        ]);

        self::$context->set_params(json_encode(['cmid' => $cmtestent->cmid]));
        self::$context->set_complementary($complementary);

        $result = self::$subplugin->estimate_next_time(self::$context);

        $this->assertEquals($expected, $result);

        uopz_unset_return('time');
    }

    /**
     * Data provider for test_estimatenexttime
     *
     * @return array[]
     */
    public static function dataestimate(): array {
        return [
            'condition. available' => [
                '{"op":"&","c":[{"type":"profile","sf":"firstname","op":"isequalto","v":"Fernando"}],"showc":[true]}',
                notificationplugin::COMPLEMENTARY_CONDITION, 1704099600,
            ],
            'condition. not available' => [
                '{"op":"&","c":[{"type":"profile","sf":"firstname","op":"isequalto","v":"Miguel"}],"showc":[true]}',
                notificationplugin::COMPLEMENTARY_CONDITION, null,
            ],
            'exception. available' => [
                '{"op":"&","c":[{"type":"profile","sf":"firstname","op":"isequalto","v":"Fernando"}],"showc":[true]}',
                notificationplugin::COMPLEMENTARY_EXCEPTION, null,
            ],
            'exception. not available' => [
                '{"op":"&","c":[{"type":"profile","sf":"firstname","op":"isequalto","v":"Miguel"}],"showc":[true]}',
                notificationplugin::COMPLEMENTARY_EXCEPTION, 1704099600,
            ],
        ];
    }

    /**
     * Test get title.
     *
     * @covers \notificationscondition_activityavailable\activityavailable::get_title
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
     * @covers \notificationscondition_activityavailable\activityavailable::get_description
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
     * @covers \notificationscondition_activityavailable\activityavailable::convert_parameters
     */
    public function test_convertparameters() {
        $id = self::$subplugin->get_id();
        $params = [
            $id."_activityavailable_cmid" => "7",
        ];
        $expected = '{"cmid":7}';
        $method = phpunitutil::get_method(self::$subplugin, 'convert_parameters');
        $result = $method->invoke(self::$subplugin, $params);
        $this->assertSame($expected, $result);

    }

    /**
     * Test process markups.
     *
     * @covers \notificationscondition_activityavailable\activityavailable::process_markups
     */
    public function test_processmarkups() {
        $quizgenerator = self::getDataGenerator()->get_plugin_generator('mod_quiz');
        $cmgen = $quizgenerator->create_instance([
            'course' => self::$coursetest->id,
        ]);
        $expected = str_replace(self::$subplugin->get_elements(), [$cmgen->name], self::$subplugin->get_title());
        $params[self::$subplugin::UI_ACTIVITY] = $cmgen->cmid;
        $params = json_encode($params);

        self::$subplugin->set_parameters($params);
        $content = [];
        self::$subplugin->process_markups($content, self::$coursetest->id);
        $this->assertSame([$expected], $content);
    }

    /**
     * Test get ui.
     *
     * @covers \notificationscondition_activityavailable\activityavailable::get_ui
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

        $this->assertTrue($mform->elementExists($uiactivityname));
    }
}
