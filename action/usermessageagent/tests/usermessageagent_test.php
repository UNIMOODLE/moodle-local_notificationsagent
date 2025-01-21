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
 * @package    notificationsaction_usermessageagent
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace notificationsaction_usermessageagent;

use Generator;
use local_notificationsagent\evaluationcontext;
use local_notificationsagent\form\editrule_form;
use local_notificationsagent\notificationplugin;
use local_notificationsagent\helper\test\phpunitutil;
use local_notificationsagent\rule;
use notificationsaction_usermessageagent\usermessageagent;
use local_notificationsagent\helper\test\mock_base_logger;

/**
 * Test for usermessageagent.
 *
 * @group notificationsagent
 */
final class usermessageagent_test extends \advanced_testcase {
    /**
     * @var rule
     */
    private static $rule;

    /**
     * @var usermessageagent
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
     */
    public function setUp(): void {
        global $USER;

        parent::setUp();
        $this->resetAfterTest(true);

        self::$coursetest = self::getDataGenerator()->create_course(
            ['startdate' => self::COURSE_DATESTART, 'enddate' => self::COURSE_DATEEND]
        );

        $rule = new rule();
        $dataform = new \StdClass();
        $dataform->title = "Rule Test";
        $dataform->type = 1;
        $dataform->courseid = self::$coursetest->id;
        $dataform->timesfired = 1;
        $dataform->runtime_group = ['runtime_days' => 5, 'runtime_hours' => 0, 'runtime_minutes' => 0];
        $usertutor = self::getDataGenerator()->create_user();
        $USER->id = $usertutor->id;
        $ruleid = $rule->create($dataform);
        self::$rule = rule::create_instance($ruleid);

        self::$subplugin = new usermessageagent(self::$rule->to_record());
        self::$subplugin->set_id(5);
        self::$coursecontext = \context_course::instance(self::$coursetest->id);
        self::$user = self::getDataGenerator()->create_user();
        self::$context = new evaluationcontext();
        self::$context->set_userid(self::$user->id);
        self::$context->set_courseid(self::$coursetest->id);
        self::$subtype = 'usermessageagent';
        self::$elements = ['[TTTT]', '[BBBB]', '[UUUU]'];
    }

    /**
     * Test for execute_action method.
     *
     * @param string $param
     * @param int $user
     *
     * @covers       \notificationsaction_usermessageagent\usermessageagent::execute_action
     *
     * @dataProvider dataprovider
     */
    public function test_execute_action($param, $user): void {
        $auxarray = json_decode($param, true);
        $auxarray['user'] = self::$user->id;
        $param = json_encode($auxarray);
        self::$rule->set_createdby($user === 0 ? self::$user->id : $user);
        self::$context->set_params($param);
        self::$context->set_rule(self::$rule);
        self::$context->set_userid(self::$user->id);
        self::$context->set_courseid(self::$coursetest->id);
        self::$context->set_usertimesfired(1);

        self::$subplugin->set_id(self::CONDITIONID);
        // Test action.
        unset_config('noemailever');
        $sink = $this->redirectMessages();
        $result = self::$subplugin->execute_action(self::$context, $param);
        $messages = $sink->get_messages();
        $this->assertCount(1, $messages);
        $this->assertIsInt($result, $messages[0]->id);
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
     *
     */
    public static function dataprovider(): array {
        return [
                'admin' => ['{"title":"TEST","message":"Message body"}', 2],
                'user' => ['{"title":"TEST","message":"Message body"}', 0],
        ];
    }

    /**
     * Test for get_subtype method.
     *
     * @covers \notificationsaction_usermessageagent\usermessageagent::get_subtype
     */
    public function test_getsubtype(): void {
        $this->assertSame(self::$subtype, self::$subplugin->get_subtype());
    }

    /**
     * Test for is_generic method.
     *
     * @covers \notificationsaction_usermessageagent\usermessageagent::is_generic
     */
    public function test_isgeneric(): void {
        $this->assertFalse(self::$subplugin->is_generic());
    }

    /**
     * Test for get_elements method.
     *
     * @covers \notificationsaction_usermessageagent\usermessageagent::get_elements
     */
    public function test_getelements(): void {
        $this->assertSame(self::$elements, self::$subplugin->get_elements());
    }

    /**
     * Test for check_capability method.
     *
     * @covers \notificationsaction_usermessageagent\usermessageagent::check_capability
     */
    public function test_checkcapability(): void {
        $this->assertSame(
            has_capability('local/notificationsagent:' . self::$subtype, self::$coursecontext),
            self::$subplugin->check_capability(self::$coursecontext)
        );
    }

    /**
     * Test for convert_parameters method.
     *
     * @covers \notificationsaction_usermessageagent\usermessageagent::convert_parameters
     */
    public function test_convertparameters(): void {
        $id = self::$subplugin->get_id();
        $params = [
                $id . "_usermessageagent_title" => "Test title", $id . "_usermessageagent_message" => ['text' => "Message body"],
                $id . "_usermessageagent_user" => 5,
        ];
        $expected = '{"title":"Test title","message":{"text":"Message body"},"user":5}';
        $method = phpunitutil::get_method(self::$subplugin, 'convert_parameters');
        $result = $method->invoke(self::$subplugin, $params);
        $this->assertSame($expected, $result);
    }

    /**
     * Test for get_title method.
     *
     * @covers \notificationsaction_usermessageagent\usermessageagent::get_title
     */
    public function test_gettitle(): void {
        $this->assertNotNull(self::$subplugin->get_title());
        foreach (self::$elements as $element) {
            $this->assertStringContainsString($element, self::$subplugin->get_title());
        }
    }

    /**
     * Test for get_description method
     *
     * @covers \notificationsaction_usermessageagent\usermessageagent::get_description
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
     * Test for get_ui method.
     *
     * @covers \notificationsaction_usermessageagent\usermessageagent::get_ui
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
        $uiusername = $method->invoke(self::$subplugin, self::$subplugin::UI_USER);

        $this->assertTrue($mform->elementExists($uititlename));
        $this->assertTrue($mform->elementExists($uiamessagename));
        $this->assertTrue($mform->elementExists($uiusername));
    }

    /**
     * Test for the get_parameters_placeholders method.
     *
     * @dataProvider dataprovidergetparametersplaceholders
     * @covers       \notificationsaction_usermessageagent\usermessageagent::get_parameters_placeholders
     *
     * @param string $param JSON encoded parameters.
     */
    public function test_getparametersplaceholders($param): void {
        $auxarray = json_decode($param, true);
        // Add cmid.
        $auxarray[self::$subplugin::UI_USER] = self::$user->id;
        $param = json_encode($auxarray);

        // Format message text // delete ['text'].
        $auxarray['message'] = $auxarray['message']['text'];

        self::$subplugin->set_parameters($param);
        $actual = self::$subplugin->get_parameters_placeholders();

        $this->assertSame(json_encode($auxarray), $actual);
    }

    /**
     * Data provider for test_getparametersplaceholders method.
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
     * Test for process_markups method.
     *
     * @covers \notificationsaction_usermessageagent\usermessageagent::process_markups
     *
     */
    public function test_processmarkups(): void {
        $uititle = 'test title';
        $uimessage = 'test message';

        $params[self::$subplugin::UI_USER] = self::$user->id;
        $params[self::$subplugin::UI_TITLE] = $uititle;
        $params[self::$subplugin::UI_MESSAGE]['text'] = $uimessage;
        $params = json_encode($params);

        $paramstoreplace = [
                shorten_text(str_replace('{' . rule::SEPARATOR . '}', ' ', $uititle)),
                shorten_text(format_string(str_replace('{' . rule::SEPARATOR . '}', ' ', $uimessage))),
                shorten_text(self::$user->firstname . " " . self::$user->lastname),
        ];
        $expected = str_replace(self::$subplugin->get_elements(), $paramstoreplace, self::$subplugin->get_title());

        self::$subplugin->set_parameters($params);
        $content = [];
        self::$subplugin->process_markups($content, self::$coursetest->id);
        $this->assertSame([$expected], $content);
    }

    /**
     * Test for is_send_once method.
     *
     * @covers \notificationsaction_usermessageagent\usermessageagent::is_send_once
     */
    public function test_issendonce(): void {
        $this->assertTrue(self::$subplugin->is_send_once(self::$user->id));
    }

    /**
     * Test update after restore method
     *
     * @return void
     * @covers \notificationsaction_usermessageagent\usermessageagent::update_after_restore
     */
    public function test_update_after_restore(): void {
        $logger = new mock_base_logger(0);
        $this->assertFalse(self::$subplugin->update_after_restore('restoreid', self::$coursecontext->id, $logger));
    }
}
