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
 * @package    notificationsaction_messageagent
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace notificationsaction_messageagent;

use Generator;
use local_notificationsagent\evaluationcontext;
use local_notificationsagent\form\editrule_form;
use local_notificationsagent\notificationplugin;
use local_notificationsagent\helper\test\phpunitutil;
use local_notificationsagent\rule;
use local_notificationsagent\helper\test\mock_base_logger;
use notificationsaction_messageagent\messageagent;

/**
 * Test for messageagent.
 *
 * @group notificationsagent
 */
final class messageagent_test extends \advanced_testcase {
    /**
     * @var rule
     */
    private static $rule;

    /**
     * @var messageagent
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
        $this->resetAfterTest();
        self::$rule = new rule();

        self::$subplugin = new messageagent(self::$rule->to_record());
        self::$subplugin->set_id(5);
        self::$coursetest = self::getDataGenerator()->create_course(
            ['startdate' => self::COURSE_DATESTART, 'enddate' => self::COURSE_DATEEND]
        );
        self::$coursecontext = \context_course::instance(self::$coursetest->id);
        self::$user = self::getDataGenerator()->create_user();
        self::$context = new evaluationcontext();
        self::$context->set_userid(self::$user->id);
        self::$context->set_courseid(self::$coursetest->id);
        self::$subtype = 'messageagent';
        self::$elements = ['[TTTT]', '[BBBB]'];
    }

    /**
     * Test execute_action method.
     *
     * @param string $param
     * @param int $user
     *
     * @covers       \notificationsaction_messageagent\messageagent::execute_action
     *
     * @dataProvider dataprovider
     */
    public function test_execute_action($param, $user): void {
        $auxarray = json_decode($param, true);
        self::$context->set_params($param);
        self::$context->set_rule(self::$rule);
        self::$context->set_userid(self::$user->id);
        self::$context->set_courseid(self::$coursecontext->id);
        self::$context->set_usertimesfired(1);
        self::$subplugin->set_id(self::CONDITIONID);
        self::$rule->set_createdby($user === 0 ? self::$user->id : $user);
        // Test action.
        unset_config('noemailever');
        $sink = $this->redirectMessages();
        $result = self::$subplugin->execute_action(self::$context, $param);
        $messages = $sink->get_messages();
        $this->assertCount(1, $messages);
        $this->assertIsInt($result);
        if ($user !== 0) {
            $this->assertEquals(2, $messages[0]->useridfrom);
        } else {
            $this->assertStringContainsString($auxarray['title'], $messages[0]->subject);
            $this->assertSame(self::$user->id, $messages[0]->useridto);
        }
        $this->assertStringContainsString($auxarray['message'], $messages[0]->fullmessage);
        $sink->close();
    }

    /**
     * Data provider for test_execute_action.
     */
    public static function dataprovider(): array {
        return [
                ['{"title":"TEST","message":"Message body"}', 2],
                ['{"title":"TEST","message":"Message body"}', 0],
        ];
    }

    /**
     * Test get_subtype method.
     *
     * @covers \notificationsaction_messageagent\messageagent::get_subtype
     */
    public function test_getsubtype(): void {
        $this->assertSame(self::$subtype, self::$subplugin->get_subtype());
    }

    /**
     * Test is_generic method.
     *
     * @covers \notificationsaction_messageagent\messageagent::is_generic
     */
    public function test_isgeneric(): void {
        $this->assertTrue(self::$subplugin->is_generic());
    }

    /**
     * Test get_elements method.
     *
     * @covers \notificationsaction_messageagent\messageagent::get_elements
     */
    public function test_getelements(): void {
        $this->assertSame(self::$elements, self::$subplugin->get_elements());
    }

    /**
     * Test check_capability method.
     *
     * @covers \notificationsaction_messageagent\messageagent::check_capability
     */
    public function test_checkcapability(): void {
        $this->assertSame(
            has_capability('local/notificationsagent:' . self::$subtype, self::$coursecontext),
            self::$subplugin->check_capability(self::$coursecontext)
        );
    }

    /**
     * Test convert_parameters method.
     *
     * @covers \notificationsaction_messageagent\messageagent::convert_parameters
     */
    public function test_convertparameters(): void {
        $id = self::$subplugin->get_id();
        $params = [$id . "_messageagent_title" => "Test title", $id . "_messageagent_message" => ['text' => "Message body"]];
        $expected = '{"title":"Test title","message":{"text":"Message body"}}';
        $method = phpunitutil::get_method(self::$subplugin, 'convert_parameters');
        $result = $method->invoke(self::$subplugin, $params);
        $this->assertSame($expected, $result);
    }

    /**
     * Test get_title method.
     *
     * @covers \notificationsaction_messageagent\messageagent::get_title
     */
    public function test_gettitle(): void {
        $this->assertNotNull(self::$subplugin->get_title());
        foreach (self::$elements as $element) {
            $this->assertStringContainsString($element, self::$subplugin->get_title());
        }
    }

    /**
     * Test get_description method.
     *
     * @covers \notificationsaction_messageagent\messageagent::get_description
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
     * Test get_ui method.
     *
     * @covers \notificationsaction_messageagent\messageagent::get_ui
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
        $uititlename = $method->invoke(self::$subplugin, self::$subplugin::UI_TITLE);
        $uiamessagename = $method->invoke(self::$subplugin, self::$subplugin::UI_MESSAGE);

        $this->assertTrue($mform->elementExists($uititlename));
        $this->assertTrue($mform->elementExists($uiamessagename));
    }

    /**
     * Test process_markups method.
     *
     * @covers \notificationsaction_messageagent\messageagent::process_markups
     */
    public function test_processmarkups(): void {
        $uititle = 'test title';
        $uimessage = 'test message';

        $paramstoreplace = [
                shorten_text(str_replace('{' . rule::SEPARATOR . '}', ' ', $uititle)),
                shorten_text(format_string(str_replace('{' . rule::SEPARATOR . '}', ' ', $uimessage))),
        ];
        $expected = str_replace(self::$subplugin->get_elements(), $paramstoreplace, self::$subplugin->get_title());

        $params[self::$subplugin::UI_TITLE] = $uititle;
        $params[self::$subplugin::UI_MESSAGE]['text'] = $uimessage;
        $params = json_encode($params);
        self::$subplugin->set_parameters($params);
        $content = [];
        self::$subplugin->process_markups($content, self::$coursetest->id);
        $this->assertSame([$expected], $content);
    }

    /**
     * Test to verify that get_parameters_placeholders returns a json string with the
     * correct keys and values.
     *
     * @param string $param
     *
     * @covers       \notificationsaction_messageagent\messageagent::get_parameters_placeholders
     * @dataProvider dataprovidergetparametersplaceholders
     */
    public function test_getparametersplaceholders($param): void {
        $auxarray = json_decode($param, true);

        // Format message text // delete ['text'].
        $auxarray['message'] = $auxarray[self::$subplugin::UI_MESSAGE]['text'];

        self::$subplugin->set_parameters($param);
        $actual = self::$subplugin->get_parameters_placeholders();

        $this->assertSame(json_encode($auxarray), $actual);
    }

    /**
     * Data provider for get_parameters_placeholders method.
     */
    public static function dataprovidergetparametersplaceholders(): Generator {
        $data['title'] = 'TEST';
        $data['message']['text'] = 'Message body';
        yield [json_encode($data)];
        $data['title'] = 'TEST2';
        $data['message']['text'] = 'Message body';
        yield [json_encode($data)];
    }

    /**
     * Test update after restore method
     *
     * @return void
     * @covers \notificationsaction_messageagent\messageagent::update_after_restore
     */
    public function test_update_after_restore(): void {
        $logger = new mock_base_logger(0);
        $this->assertFalse(self::$subplugin->update_after_restore('restoreid', self::$coursecontext->id, $logger));
    }
}
