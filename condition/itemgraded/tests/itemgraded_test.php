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
 * @package    notificationscondition_itemgraded
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace notificationscondition_itemgraded;

use local_notificationsagent\notificationsagent;
use local_notificationsagent\evaluationcontext;
use local_notificationsagent\form\editrule_form;
use local_notificationsagent\helper\test\mock_base_logger;
use local_notificationsagent\helper\test\phpunitutil;
use local_notificationsagent\notificationplugin;
use local_notificationsagent\rule;
defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->dirroot . '/local/notificationsagent/condition/itemgraded/compatibility.php');
/**
 * Class for testing the itemgraded.
 *
 * @group notificationsagent
 */
class itemgraded_test extends \advanced_testcase {
    /**
     * @var rule
     */
    private static $rule;
    /**
     * @var itemgraded
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

        self::$subplugin = new itemgraded(self::$rule->to_record());
        self::$subplugin->set_id(5);
        self::$coursetest = self::getDataGenerator()->create_course(
            ['startdate' => self::COURSE_DATESTART, 'enddate' => self::COURSE_DATEEND]
        );
        self::$coursecontext = \context_course::instance(self::$coursetest->id);
        self::$user = self::getDataGenerator()->create_user();
        self::$context = new evaluationcontext();
        self::$context->set_userid(self::$user->id);
        self::$context->set_courseid(self::$coursetest->id);
        self::$subtype = 'itemgraded';
        self::$elements = ['[OOOP]', '[GGGG]', '[AAAA]'];
    }

    /**
     * Test evaluate.
     *
     * @param int $timeaccess
     * @param string $param
     * @param bool $complementary
     * @param int $correctquestions
     * @param bool $expected
     *
     * @covers       \notificationscondition_itemgraded\itemgraded::evaluate
     *
     * @dataProvider dataprovider
     */
    public function test_evaluate($timeaccess, $param, $complementary, $correctquestions, $expected) {
        self::$context->set_timeaccess($timeaccess);
        self::$context->set_complementary($complementary);
        self::$subplugin->set_id(self::CONDITIONID);

        $quizgenerator = self::getDataGenerator()->get_plugin_generator('mod_quiz');
        // Generate quiz.
        $quiz = $quizgenerator->create_instance(['course' => self::$coursetest->id,
                'grade' => 100.0,
                'sumgrades' => 10,
                'layout' => '1,0',
        ]);

        $cm = get_coursemodule_from_instance('quiz', $quiz->id, self::$coursetest->id);
        $this->assertNotNull($cm);
        $questiongenerator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $cat = $questiongenerator->create_question_category();
        // Generate question.
        for ($i = 0; $i < 10; $i++) {
            $truefalse = $questiongenerator->create_question('truefalse', null, ['category' => $cat->id]);
            quiz_add_quiz_question($truefalse->id, $quiz);
        }

        // Create quiz object.
        $quizobj = \quiz::create($quiz->id, self::$user->id);

        $quba = \question_engine::make_questions_usage_by_activity('mod_quiz', $quizobj->get_context());
        $quba->set_preferred_behaviour($quizobj->get_quiz()->preferredbehaviour);
        $this->assertNotNull($truefalse);
        $timenow = time();
        // Create attempt.
        $attempt = quiz_create_attempt($quizobj, 1, false, $timenow, false, self::$user->id);
        // Start attempt.
        quiz_start_new_attempt($quizobj, $quba, $attempt, 1, $timenow);

        // Save question started.
        quiz_attempt_save_started($quizobj, $quba, $attempt);

        $attemptobj = \quiz_attempt::create($attempt->id);
        $params = json_decode($param);
        $params->cmid = $cm->id;
        self::$context->set_params(json_encode($params));
        // Fill correct answers.
        for ($i = 0; $i < $correctquestions; $i++) {
            $attemptobj->process_submitted_actions($timenow, false, [$i + 1 => ['answer' => '1']]);
        }
        // Fill incorrect answers.
        while ($i < 10) {
            $attemptobj->process_submitted_actions($timenow, false, [$i + 1 => ['answer' => '0']]);
            $i++;
        }

        $attemptobj->process_finish(time(), false);
        $result = self::$subplugin->evaluate(self::$context);

        $this->assertSame($result, $expected);
    }

    /**
     * Data provider for test_evaluate.
     */
    public static function dataprovider(): array {
        return [
                'CONDITION = 50' => [1704445200, '{"op":"=", "grade":50}', notificationplugin::COMPLEMENTARY_CONDITION, 5, true],
                'CONDITION > 10' => [1704445200, '{"op":">", "grade":10}', notificationplugin::COMPLEMENTARY_CONDITION, 6, true],
                'CONDITION > 50 Incorrect' => [1704445200, '{"op":">", "grade":50}', notificationplugin::COMPLEMENTARY_CONDITION, 2,
                        false],

        ];
    }

    /**
     * Test get subtype.
     *
     * @covers \notificationscondition_itemgraded\itemgraded::get_subtype
     */
    public function test_getsubtype() {
        $this->assertSame(self::$subtype, self::$subplugin->get_subtype());
    }

    /**
     * Test is generic.
     *
     * @covers \notificationscondition_itemgraded\itemgraded::is_generic
     */
    public function test_isgeneric() {
        $this->assertFalse(self::$subplugin->is_generic());
    }

    /**
     * Test get elements.
     *
     * @covers \notificationscondition_itemgraded\itemgraded::get_elements
     */
    public function test_getelements() {
        $this->assertSame(self::$elements, self::$subplugin->get_elements());
    }

    /**
     * Test check capability.
     *
     * @covers \notificationscondition_itemgraded\itemgraded::check_capability
     */
    public function test_checkcapability() {
        $this->assertSame(
            has_capability('notificationscondition/' . self::$subtype.':'.self::$subtype, self::$coursecontext),
            self::$subplugin->check_capability(self::$coursecontext)
        );
    }

    /**
     * Test get cmid.
     *
     * @covers \notificationscondition_itemgraded\itemgraded::get_cmid
     */
    public function test_getcmid() {
        $this->assertNull(self::$subplugin->get_cmid(self::$context));
    }

    /**
     * Test estimate next time.
     *
     * @param int $timeaccess
     * @param string $param
     * @param bool $complementary
     * @param int $correctquestions
     * @param bool $expected
     *
     * @covers       \notificationscondition_itemgraded\itemgraded::estimate_next_time
     * @dataProvider dataestimate
     */
    public function test_estimatenexttime($timeaccess, $param, $complementary, $correctquestions, $expected) {
        self::$context->set_timeaccess($timeaccess);
        self::$context->set_complementary($complementary);
        self::$subplugin->set_id(self::CONDITIONID);

        $quizgenerator = self::getDataGenerator()->get_plugin_generator('mod_quiz');
        // Generate quiz.
        $quiz = $quizgenerator->create_instance(['course' => self::$coursetest->id,
                'grade' => 100.0,
                'sumgrades' => 10,
                'layout' => '1,0',
        ]);

        $cm = get_coursemodule_from_instance('quiz', $quiz->id, self::$coursetest->id);
        $this->assertNotNull($cm);
        $questiongenerator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $cat = $questiongenerator->create_question_category();
        // Generate question.
        for ($i = 0; $i < 10; $i++) {
            $truefalse = $questiongenerator->create_question('truefalse', null, ['category' => $cat->id]);
            quiz_add_quiz_question($truefalse->id, $quiz);
        }

        // Create quiz object.
        $quizobj = \quiz::create($quiz->id, self::$user->id);

        $quba = \question_engine::make_questions_usage_by_activity('mod_quiz', $quizobj->get_context());
        $quba->set_preferred_behaviour($quizobj->get_quiz()->preferredbehaviour);
        $this->assertNotNull($truefalse);
        $timenow = time();
        // Create attempt.
        $attempt = quiz_create_attempt($quizobj, 1, false, $timenow, false, self::$user->id);
        // Start attempt.
        quiz_start_new_attempt($quizobj, $quba, $attempt, 1, $timenow);

        // Save question started.
        quiz_attempt_save_started($quizobj, $quba, $attempt);

        $attemptobj = \quiz_attempt::create($attempt->id);
        $params = json_decode($param);
        $params->cmid = $cm->id;
        self::$context->set_params(json_encode($params));
        // Fill correct answers.
        for ($i = 0; $i < $correctquestions; $i++) {
            $attemptobj->process_submitted_actions($timenow, false, [$i + 1 => ['answer' => '1']]);
        }
        // Fill incorrect answers.
        while ($i < 10) {
            $attemptobj->process_submitted_actions($timenow, false, [$i + 1 => ['answer' => '0']]);
            $i++;
        }

        $attemptobj->process_finish(time(), false);
        $result = self::$subplugin->estimate_next_time(self::$context);

        if ($expected) {
            $this->assertNotNull($result);
        } else {
            $this->assertNull($result);
        }
    }

    /**
     * Data for estimate next time.
     *
     * @return array[]
     */
    public static function dataestimate(): array {
        return [
                'CONDITION = 50' => [1704445200, '{"op":"=", "grade":50}', notificationplugin::COMPLEMENTARY_CONDITION, 5, true],
                'EXCEPTION > 50 Correct' => [1704445200, '{"op":">", "grade":50}', notificationplugin::COMPLEMENTARY_EXCEPTION, 2,
                        true],
                'CONDITION > 50 Incorrect' => [1704445200, '{"op":">", "grade":50}', notificationplugin::COMPLEMENTARY_EXCEPTION, 7,
                        false],
        ];
    }

    /**
     * Test get title.
     *
     * @covers \notificationscondition_itemgraded\itemgraded::get_title
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
     * @covers \notificationscondition_itemgraded\itemgraded::get_description
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
     * Test process markups.
     *
     * @covers \notificationscondition_itemgraded\itemgraded::process_markups
     */
    public function test_processmarkups() {

        $params['cmid'] = 5;
        $params['op'] = ">";
        $params['grade'] = 50;
        $expected = str_replace(self::$subplugin->get_elements()[0], $params['op'], self::$subplugin->get_title());
        $expected = str_replace(self::$subplugin->get_elements()[1], $params['grade'], $expected);
        self::$subplugin->set_parameters(json_encode($params));
        $content = [];
        self::$subplugin->process_markups($content, self::$coursetest->id);
        $this->assertSame([$expected], $content);
    }

    /**
     * Test get ui.
     *
     * @param bool $template Whether to use a template or not.
     * @covers       \notificationscondition_itemgraded\itemgraded::get_ui
     * @dataProvider datagetui
     */
    public function test_getui($template = false) {
        if ($template) {
            self::$rule->set_template(0);
            self::$subplugin = new itemgraded(self::$rule->to_record());
            self::$subplugin->set_id(5);
        }
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

        $quizgenerator = self::getDataGenerator()->get_plugin_generator('mod_quiz');
        // Generate quiz.
        $quizgenerator->create_instance(['course' => self::$coursetest->id,
                'grade' => 100.0,
                'sumgrades' => 10,
                'layout' => '1,0',
        ]);

        self::$subplugin->get_ui($mform, $courseid, $subtype);

        $method = phpunitutil::get_method(self::$subplugin, 'get_name_ui');
        $uigroupname = $method->invoke(self::$subplugin, self::$subplugin->get_subtype());
        $uigroupelements = [];
        foreach ($mform->getElement($uigroupname)->getElements() as $element) {
            $uigroupelements[] = $element->getName();
        }
        $uiactivity = $method->invoke(self::$subplugin, self::$subplugin::UI_ACTIVITY);
        $uiop = $method->invoke(self::$subplugin, self::$subplugin::UI_OP);

        $uigrade = $method->invoke(self::$subplugin, self::$subplugin::UI_GRADE);

        $this->assertTrue($mform->elementExists($uigroupname));
        foreach ($mform->getElement($uigroupname)->getElements() as $element) {
            $uigroupelements[] = $element->getName();
        }

        $this->assertTrue(in_array($uiop, $uigroupelements));
        $this->assertTrue($mform->elementExists($uiactivity));
        $this->assertTrue(in_array($uigrade, $uigroupelements));
    }

    /**
     * Data for get ui
     *
     * @return array[]
     */
    public static function datagetui(): array {
        return [
                'UI RULE' => [false],
                'UI TEMPLATE' => [true],

        ];
    }

    /**
     * Test convert parameters.
     *
     * @covers \notificationscondition_itemgraded\itemgraded::convert_parameters
     */
    public function test_convertparameters() {
        $id = self::$subplugin->get_id();
        $params = [
                $id . "_itemgraded_cmid" => "5",
                $id . "_itemgraded_op" => 0,
                $id . "_itemgraded_grade" => "50",
        ];
        $expected = '{"cmid":5,"op":">","grade":50}';
        $method = phpunitutil::get_method(self::$subplugin, 'convert_parameters');
        $result = $method->invoke(self::$subplugin, $params);
        $this->assertSame($expected, $result);
    }

    /**
     * Test validation.
     *
     * @covers       \notificationscondition_itemgraded\itemgraded::validation
     */
    public function test_validation() {
        $quizgenerator = self::getDataGenerator()->get_plugin_generator('mod_quiz');
        $cmtestaa = $quizgenerator->create_instance([
                'name' => 'Quiz unittest',
                'course' => self::$coursetest->id,
                'visible' => true,
        ]);
        $objparameters = new \stdClass();
        $objparameters->cmid = $cmtestaa->cmid;

        self::$subplugin->set_parameters(json_encode($objparameters));
        $this->assertTrue(self::$subplugin->validation(self::$coursetest->id));
    }
}
