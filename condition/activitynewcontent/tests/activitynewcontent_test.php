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
 * @package    notificationscondition_activitynewcontent
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace notificationscondition_activitynewcontent;

use local_notificationsagent\evaluationcontext;
use local_notificationsagent\form\editrule_form;
use local_notificationsagent\notificationplugin;
use local_notificationsagent\helper\test\phpunitutil;
use local_notificationsagent\rule;
use notificationscondition_activitynewcontent\activitynewcontent;

/**
 * Class activitynewcontent_test
 *
 * @group notificationsagent
 */
class activitynewcontent_test extends \advanced_testcase {

    /**
     * @var rule
     */
    private static $rule;
    /**
     * @var activitynewcontent
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

        self::$subplugin = new activitynewcontent(self::$rule->to_record());
        self::$subplugin->set_id(5);
        self::$coursetest = self::getDataGenerator()->create_course(
            ['startdate' => self::COURSE_DATESTART, 'enddate' => self::COURSE_DATEEND]
        );
        self::$coursecontext = \context_course::instance(self::$coursetest->id);
        self::$user = self::getDataGenerator()->create_user();
        self::$context = new evaluationcontext();
        self::$context->set_userid(self::$user->id);
        self::$context->set_courseid(self::$coursetest->id);
        self::$subtype = 'activitynewcontent';
        self::$elements = ['[AAAA]'];
    }

    /**
     * Test evaluate.
     *
     * @param int  $timeaccess
     * @param bool $usecache
     * @param bool $expected
     *
     * @covers       \notificationscondition_activitynewcontent\activitynewcontent::evaluate
     *
     * @dataProvider dataprovider
     */
    public function test_evaluate($timeaccess, $usecache, $expected) {
        self::$context->set_timeaccess($timeaccess);
        self::$subplugin->set_id(self::CONDITIONID);
        $quizgenerator = self::getDataGenerator()->get_plugin_generator('mod_quiz');
        $cmtestnc = $quizgenerator->create_instance([
            'name' => 'Quiz unittest',
            'course' => self::$coursetest->id,
        ]);

        self::$context->set_params(json_encode(['cmid' => $cmtestnc->cmid]));

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

        // Test evaluate.
        $result = self::$subplugin->evaluate(self::$context);
        $this->assertSame($expected, $result);

    }

    /**
     * Data provider for test_evaluate()
     */
    public static function dataprovider(): array {
        return [
            [1704445200, true, true],
            [1706173200, true, true],
            [1707123600, true, true],
            [1704445200, false, false],
            [1706173200, false, false],
            [1707123600, false, false],
        ];
    }

    /**
     * Test get subtype.
     *
     * @covers \notificationscondition_activitynewcontent\activitynewcontent::get_subtype
     */
    public function test_getsubtype() {
        $this->assertSame(self::$subtype, self::$subplugin->get_subtype());
    }

    /**
     * Test is generic.
     *
     * @covers \notificationscondition_activitynewcontent\activitynewcontent::is_generic
     */
    public function test_isgeneric() {
        $this->assertTrue(self::$subplugin->is_generic());
    }

    /**
     * Test get elements.
     *
     * @covers \notificationscondition_activitynewcontent\activitynewcontent::get_elements
     */
    public function test_getelements() {
        $this->assertSame(self::$elements, self::$subplugin->get_elements());
    }

    /**
     * Test check capability.
     *
     * @covers \notificationscondition_activitynewcontent\activitynewcontent::check_capability
     */
    public function test_checkcapability() {
        $this->assertSame(
            has_capability('local/notificationsagent:' . self::$subtype, self::$coursecontext),
            self::$subplugin->check_capability(self::$coursecontext)
        );
    }

    /**
     * Test estimate next time.
     *
     * @covers \notificationscondition_activitynewcontent\activitynewcontent::estimate_next_time
     */
    public function test_estimatenexttime() {
        // Test estimate next time.
        $this->assertNull(self::$subplugin->estimate_next_time(self::$context));
    }

    /**
     * Test get title.
     *
     * @covers \notificationscondition_activitynewcontent\activitynewcontent::get_title
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
     * @covers \notificationscondition_activitynewcontent\activitynewcontent::get_description
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
     * @covers \notificationscondition_activitynewcontent\activitynewcontent::process_markups
     */
    public function test_processmarkups() {
        $modcomponent = 'mod_quiz';
        $quizgenerator = self::getDataGenerator()->get_plugin_generator($modcomponent);
        $cmgen = $quizgenerator->create_instance([
            'course' => self::$coursetest->id,
        ]);
        $params[self::$subplugin::MODNAME] = $modcomponent;
        $params = json_encode($params);
        $expected = str_replace(self::$subplugin->get_elements(), [$modcomponent], self::$subplugin->get_title());
        self::$subplugin->set_parameters($params);
        $content = [];
        self::$subplugin->process_markups($content, self::$coursetest->id);
        $this->assertSame([$expected], $content);
    }

    /**
     * Test get ui.
     *
     * @covers \notificationscondition_activitynewcontent\activitynewcontent::get_ui
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
        $uiactivityname = $method->invoke(self::$subplugin, self::$subplugin::MODNAME);

        $this->assertTrue($mform->elementExists($uiactivityname));
    }

    /**
     * Test convert parameters.
     *
     * @covers \notificationscondition_activitynewcontent\activitynewcontent::convert_parameters
     */
    public function test_convertparameters() {
        $id = self::$subplugin->get_id();
        $params = [
            $id . "_activitynewcontent_modname" => "quiz",
        ];
        $expected = '{"modname":"quiz"}';
        $method = phpunitutil::get_method(self::$subplugin, 'convert_parameters');
        $result = $method->invoke(self::$subplugin, $params);
        $this->assertSame($expected, $result);
    }

    /**
     * Test validation.
     *
     * @covers       \notificationscondition_activitynewcontent\activitynewcontent::validation
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
