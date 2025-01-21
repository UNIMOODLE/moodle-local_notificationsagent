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
 * @package    notificationsaction_bootstrapnotifications
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace notificationsaction_bootstrapnotifications;

use local_notificationsagent\evaluationcontext;
use local_notificationsagent\form\editrule_form;
use local_notificationsagent\notificationplugin;
use local_notificationsagent\helper\test\phpunitutil;
use local_notificationsagent\rule;
use notificationsaction_bootstrapnotifications\bootstrapnotifications;
use local_notificationsagent\helper\test\mock_base_logger;

/**
 * Test boostrapnotificationss
 *
 * @group notificationsagent
 */
final class bootstrapnotifications_test extends \advanced_testcase {
    /**
     * @var rule
     */
    private static $rule;

    /**
     * @var bootstrapnotifications
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
     * Set up the test fixture.
     * This method is called before a test is executed.
     *
     * @return void
     */
    public function setUp(): void {
        parent::setUp();
        $this->resetAfterTest(true);
        self::$rule = new rule();

        self::$subplugin = new bootstrapnotifications(self::$rule->to_record());
        self::$subplugin->set_id(5);
        self::$coursetest = self::getDataGenerator()->create_course(
            ['startdate' => self::COURSE_DATESTART, 'enddate' => self::COURSE_DATEEND]
        );
        self::$coursecontext = \context_course::instance(self::$coursetest->id);
        self::$user = self::getDataGenerator()->create_user();
        self::$context = new evaluationcontext();
        self::$context->set_userid(self::$user->id);
        self::$context->set_courseid(self::$coursetest->id);
        self::$subtype = 'bootstrapnotifications';
        self::$elements = ['[TTTT]'];
    }

    /**
     * Test execute action.
     *
     * @param string $param
     *
     * @covers       \notificationsaction_bootstrapnotifications\bootstrapnotifications::execute_action
     * @covers       \notificationsaction_bootstrapnotifications\bootstrapmessages::get_records
     *
     * @dataProvider dataprovider
     */
    public function test_execute_action($param): void {
        global $USER;
        self::$context->set_params($param);
        self::$context->set_rule(self::$rule);
        self::$context->set_userid(self::$user->id);
        self::$context->set_usertimesfired(1);
        self::$subplugin->set_id(self::CONDITIONID);
        $USER->id = self::$user->id;
        // Test action.
        self::$subplugin->execute_action(self::$context, $param);

        $messages = bootstrapmessages::get_records(
            ['userid' => self::$context->get_userid(), 'courseid' => self::$context->get_courseid()]
        );

        $params = self::$context->get_params();
        $test = json_decode($params, false);

        $this->assertStringContainsString($test->message, $messages[0]->get('message'));
        $this->assertEquals(self::$context->get_userid(), $messages[0]->get('userid'));
        $this->assertEquals(self::$context->get_courseid(), $messages[0]->get('courseid'));
    }

    /**
     * Data provider for get ui.
     */
    public static function dataprovider(): array {
        return [
                ['{"message":"TEST"}'],
                ['{"message":"Message content"}'],
        ];
    }

    /**
     * Test get subtype.
     *
     * @covers \notificationsaction_bootstrapnotifications\bootstrapnotifications::get_subtype
     */
    public function test_getsubtype(): void {
        $this->assertSame(self::$subtype, self::$subplugin->get_subtype());
    }

    /**
     * Test is generic.
     *
     * @covers \notificationsaction_bootstrapnotifications\bootstrapnotifications::is_generic
     */
    public function test_isgeneric(): void {
        $this->assertTrue(self::$subplugin->is_generic());
    }

    /**
     * Test get elements.
     *
     * @covers \notificationsaction_bootstrapnotifications\bootstrapnotifications::get_elements
     */
    public function test_getelements(): void {
        $this->assertSame(self::$elements, self::$subplugin->get_elements());
    }

    /**
     * Test check capability.
     *
     * @covers \notificationsaction_bootstrapnotifications\bootstrapnotifications::check_capability
     */
    public function test_checkcapability(): void {
        $this->assertSame(
            has_capability('local/notificationsagent:' . self::$subtype, self::$coursecontext),
            self::$subplugin->check_capability(self::$coursecontext)
        );
    }

    /**
     * Test get title.
     *
     * @covers \notificationsaction_bootstrapnotifications\bootstrapnotifications::get_title
     */
    public function test_gettitle(): void {
        $this->assertNotNull(self::$subplugin->get_title());
        foreach (self::$elements as $element) {
            $this->assertStringContainsString($element, self::$subplugin->get_title());
        }
    }

    /**
     * Test convert parameters.
     *
     * @covers \notificationsaction_bootstrapnotifications\bootstrapnotifications::convert_parameters
     */
    public function test_convertparameters(): void {
        $id = self::$subplugin->get_id();
        $params = [$id . "_bootstrapnotifications_message" => "Message body"];
        $expected = '{"message":"Message body"}';
        $method = phpunitutil::get_method(self::$subplugin, 'convert_parameters');
        $result = $method->invoke(self::$subplugin, $params);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test get ui.
     *
     * @covers \notificationsaction_bootstrapnotifications\bootstrapnotifications::get_ui
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
        $uiactivityname = $method->invoke(self::$subplugin, self::$subplugin::UI_MESSAGE);

        $this->assertTrue($mform->elementExists($uiactivityname));
    }

    /**
     * Test process markups.
     *
     * @covers \notificationsaction_bootstrapnotifications\bootstrapnotifications::process_markups
     */
    public function test_processmarkups(): void {
        $uimessage = 'test message';
        $expected = str_replace(
            self::$subplugin->get_elements(),
            [shorten_text(str_replace('{' . Rule::SEPARATOR . '}', ' ', $uimessage))],
            self::$subplugin->get_title()
        );
        $params[self::$subplugin::UI_MESSAGE] = $uimessage;
        $params = json_encode($params);
        self::$subplugin->set_parameters($params);
        $content = [];
        self::$subplugin->process_markups($content, self::$coursetest->id);
        $this->assertSame([$expected], $content);
    }

    /**
     * Test update after restore method
     *
     * @return void
     * @covers \notificationsaction_bootstrapnotifications\bootstrapnotifications::update_after_restore
     */
    public function test_update_after_restore(): void {
        $logger = new mock_base_logger(0);
        $this->assertFalse(self::$subplugin->update_after_restore('restoreid', self::$coursecontext->id, $logger));
    }
}
