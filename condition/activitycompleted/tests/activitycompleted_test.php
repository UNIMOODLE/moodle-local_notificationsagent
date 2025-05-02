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
 * @package    notificationscondition_activitycompleted
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace notificationscondition_activitycompleted;

use local_notificationsagent\evaluationcontext;
use local_notificationsagent\notificationplugin;
use local_notificationsagent\helper\test\phpunitutil;
use local_notificationsagent\rule;
use notificationscondition_activitycompleted\activitycompleted;
use local_notificationsagent\form\editrule_form;

/**
 * Class for testing the activitycompleted.
 *
 * @group notificationsagent
 */
final class activitycompleted_test extends \advanced_testcase {
    /**
     * @var rule
     */
    private static $rule;
    /**
     * @var activitycompleted
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

        self::$subplugin = new activitycompleted(self::$rule->to_record());
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
        self::$subtype = 'activitycompleted';
        self::$elements = ['[AAAA]'];
    }

    /**
     * Test evaluate.
     *
     * @param string $conditionjson
     * @param int $completed
     * @param bool $expected
     *
     * @covers       \notificationscondition_activitycompleted\activitycompleted::evaluate
     *
     * @dataProvider dataprovider
     */
    public function test_evaluate(string $conditionjson, int $completed, bool $expected): void {
        global $DB;
        $quizgenerator = self::getDataGenerator()->get_plugin_generator('mod_quiz');
        $cmtestaa = $quizgenerator->create_instance([
                'name' => 'Quiz unittest',
                'course' => self::$coursetest->id,
                "timeopen" => self::CM_DATESTART,
                "timeclose" => self::CM_DATEEND,

        ]);

        self::$context->set_params(json_encode(['cmid' => $cmtestaa->cmid]));

        if ($expected) {
            $DB->insert_record(
                'course_modules_completion',
                ['coursemoduleid' => $cmtestaa->cmid,
                            'userid' => self::$user->id,
                            'completionstate' => 1,
                            'timemodified' => time()]
            );
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
                [
                        '{"op":"&","c":[{"type":"profile","sf":"firstname","op":"isequalto","v":"Fernando"}],"showc":[true]}', 1,
                        true,
                ],
                [
                        '{"op":"&","c":[{"type":"profile","sf":"firstname","op":"isequalto","v":"Fernando"}],"showc":[true]}', 1,
                        false,
                ],
                [
                        '{"op":"&","c":[{"type":"profile","sf":"firstname","op":"isequalto","v":"Miguel"}],"showc":[true]}', 0,
                        false,
                ],
        ];
    }

    /**
     * Test get subtype.
     *
     * @covers \notificationscondition_activitycompleted\activitycompleted::get_subtype
     */
    public function test_getsubtype(): void {
        $this->assertSame(self::$subtype, self::$subplugin->get_subtype());
    }

    /**
     * Test is generic.
     *
     * @covers \notificationscondition_activitycompleted\activitycompleted::is_generic
     */
    public function test_isgeneric(): void {
        $this->assertFalse(self::$subplugin->is_generic());
    }

    /**
     * Test get elements.
     *
     * @covers \notificationscondition_activitycompleted\activitycompleted::get_elements
     */
    public function test_getelements(): void {
        $this->assertSame(self::$elements, self::$subplugin->get_elements());
    }

    /**
     * Test check capability.
     *
     * @covers \notificationscondition_activitycompleted\activitycompleted::check_capability
     */
    public function test_checkcapability(): void {
        $this->assertSame(
            has_capability('notificationscondition/' . self::$subtype.':'.self::$subtype, self::$coursecontext),
            self::$subplugin->check_capability(self::$coursecontext)
        );
    }

    /**
     * Test estimate next time
     *
     * @covers       \notificationscondition_activitycompleted\activitycompleted::estimate_next_time
     * @dataProvider dataestimate
     *
     * @param string $conditionjson
     * @param int $complementary
     * @param int|null $expected
     * @param int $completed
     *
     * @return void
     * @throws \coding_exception
     */
    public function test_estimatenexttime($conditionjson, $complementary, $expected, $completed): void {
        global $DB;
        \uopz_set_return('time', 1704099600);
        $quizgen = self::getDataGenerator()->get_plugin_generator('mod_quiz');
        $cmtestent = $quizgen->create_instance([
                'name' => 'Quiz unittest',
                'course' => self::$coursetest->id,
                'availability' => $conditionjson,
        ]);

        self::$context->set_params(json_encode(['cmid' => $cmtestent->cmid]));
        self::$context->set_complementary($complementary);

        if ($completed) {
            $DB->insert_record(
                'course_modules_completion',
                ['coursemoduleid' => $cmtestent->cmid, 'userid' => self::$user->id, 'completionstate' => 1,
                            'timemodified' => time()]
            );
        }

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
                'condition. completed' => [
                        '{"op":"&","c":[{"type":"profile","sf":"firstname","op":"isequalto","v":"Fernando"}],"showc":[true]}',
                        notificationplugin::COMPLEMENTARY_CONDITION, 1704099600, true,
                ],
                'condition. not completed' => [
                        '{"op":"&","c":[{"type":"profile","sf":"firstname","op":"isequalto","v":"Miguel"}],"showc":[true]}',
                        notificationplugin::COMPLEMENTARY_CONDITION, null, false,
                ],
                'exception. completed' => [
                        '{"op":"&","c":[{"type":"profile","sf":"firstname","op":"isequalto","v":"Fernando"}],"showc":[true]}',
                        notificationplugin::COMPLEMENTARY_EXCEPTION, null, true,
                ],
                'exception. not completed' => [
                        '{"op":"&","c":[{"type":"profile","sf":"firstname","op":"isequalto","v":"Miguel"}],"showc":[true]}',
                        notificationplugin::COMPLEMENTARY_EXCEPTION, 1704099600, false,
                ],
        ];
    }

    /**
     * Test get title.
     *
     * @covers \notificationscondition_activitycompleted\activitycompleted::get_title
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
     * @covers \notificationscondition_activitycompleted\activitycompleted::get_description
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
     * @covers \notificationscondition_activitycompleted\activitycompleted::convert_parameters
     */
    public function test_convertparameters(): void {
        $id = self::$subplugin->get_id();
        $params = [
                $id . "_activitycompleted_cmid" => "7",
        ];
        $expected = '{"cmid":7}';
        $method = phpunitutil::get_method(self::$subplugin, 'convert_parameters');
        $result = $method->invoke(self::$subplugin, $params);
        $this->assertSame($expected, $result);
    }

    /**
     * Test process markups.
     *
     * @covers \notificationscondition_activitycompleted\activitycompleted::process_markups
     */
    public function test_processmarkups(): void {
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
     * @covers \notificationscondition_activitycompleted\activitycompleted::get_ui
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

        $this->assertTrue($mform->elementExists($uiactivityname));
    }

    /**
     * Test validation.
     *
     * @covers       \notificationscondition_activitycompleted\activitycompleted::validation
     */
    public function test_validation(): void {
        $quizgenerator = self::getDataGenerator()->get_plugin_generator('mod_quiz');
        $cmtestaa = $quizgenerator->create_instance([
                'name' => 'Quiz unittest',
                'course' => self::$coursetest->id,
                "timeopen" => self::CM_DATESTART,
                "timeclose" => self::CM_DATEEND,
                'visible' => true,
        ]);
        $objparameters = new \stdClass();
        $objparameters->cmid = $cmtestaa->cmid;

        self::$subplugin->set_parameters(json_encode($objparameters));
        $this->assertTrue(self::$subplugin->validation(self::$coursetest->id));
    }
}
