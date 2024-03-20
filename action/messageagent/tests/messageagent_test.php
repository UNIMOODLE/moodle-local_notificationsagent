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
use notificationsaction_messageagent\messageagent;

/**
 * @group notificationsagent
 */
class messageagent_test extends \advanced_testcase {

    /**
     * @var rule
     */
    private static $rule;
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
        $this->resetAfterTest();
        self::$rule = new rule();

        self::$subplugin = new messageagent(self::$rule);
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
     *
     * @param string $param
     *
     * @covers       \notificationsaction_messageagent\messageagent::execute_action
     *
     * @dataProvider dataprovider
     */
    public function test_execute_action($param) {
        $auxarray = json_decode($param, true);
        self::$context->set_params($param);
        self::$context->set_rule(self::$rule);
        self::$subplugin->set_id(self::CONDITIONID);
        // Test action.
        unset_config('noemailever');
        $sink = $this->redirectEmails();
        $result = self::$subplugin->execute_action(self::$context, $param);
        $messages = $sink->get_messages();
        $this->assertCount(1, $messages);
        $this->assertIsInt($result);
        $this->assertSame(self::$user->email, $messages[0]->to);
        $this->assertStringContainsString($auxarray['title'], $messages[0]->subject);
        $this->assertStringContainsString($auxarray['message'], $messages[0]->body);
    }

    public static function dataprovider(): array {
        return [
            ['{"title":"TEST","message":"Message body"}'],
        ];
    }

    /**
     * @covers \notificationsaction_messageagent\messageagent::get_subtype
     */
    public function test_getsubtype() {
        $this->assertSame(self::$subtype, self::$subplugin->get_subtype());
    }

    /**
     * @covers \notificationsaction_messageagent\messageagent::is_generic
     */
    public function test_isgeneric() {
        $this->assertTrue(self::$subplugin->is_generic());
    }

    /**
     * @covers \notificationsaction_messageagent\messageagent::get_elements
     */
    public function test_getelements() {
        $this->assertSame(self::$elements, self::$subplugin->get_elements());
    }

    /**
     * @covers \notificationsaction_messageagent\messageagent::check_capability
     */
    public function test_checkcapability() {
        $this->assertSame(
            has_capability('local/notificationsagent:' . self::$subtype, self::$coursecontext),
            self::$subplugin->check_capability(self::$coursecontext)
        );
    }

    /**
     * @covers \notificationsaction_messageagent\messageagent::convert_parameters
     */
    public function test_convert_parameters() {
        $params = ["5_messageagent_title" => "Test title", "5_messageagent_message" => ['text' => "Message body"]];
        $expected = '{"title":"Test title","message":{"text":"Message body"}}';
        $method = phpunitutil::get_method(self::$subplugin, 'convert_parameters');
        $result = $method->invoke(self::$subplugin, 5, $params);
        $this->assertSame($expected, $result);
    }

    /**
     * @covers \notificationsaction_messageagent\messageagent::get_title
     */
    public function test_gettitle() {
        $this->assertNotNull(self::$subplugin->get_title());
        foreach (self::$elements as $element) {
            $this->assertStringContainsString($element, self::$subplugin->get_title());
        }
    }

    /**
     * @covers \notificationsaction_messageagent\messageagent::get_description
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
     * @covers \notificationsaction_messageagent\messageagent::get_ui
     */
    public function test_getui() {
        $courseid = self::$coursetest->id;
        $typeaction = "add";
        $customdata = [
            'rule' => self::$rule,
            'timesfired' => rule::MINIMUM_EXECUTION,
            'courseid' => $courseid,
            'getaction' => $typeaction,
        ];

        $form = new editrule_form(new \moodle_url('/'), $customdata);
        $form->definition();
        $form->definition_after_data();
        $mform = phpunitutil::get_property($form, '_form');
        $id = time();
        $subtype = notificationplugin::TYPE_CONDITION;
        self::$subplugin->get_ui($mform, $id, $courseid, $subtype);

        $method = phpunitutil::get_method(self::$subplugin, 'get_name_ui');
        $uititlename = $method->invoke(self::$subplugin, $id, self::$subplugin::UI_TITLE);
        $uiamessagename = $method->invoke(self::$subplugin, $id, self::$subplugin::UI_MESSAGE);

        $this->assertTrue($mform->elementExists($uititlename));
        $this->assertTrue($mform->elementExists($uiamessagename));
    }

    /**
     * @covers \notificationsaction_messageagent\messageagent::process_markups
     */
    public function test_processmarkups() {
        $UI_TITLE = 'test title';
        $UI_MESSAGE = 'test message';

        $paramstoreplace = [
            shorten_text(str_replace('{' . rule::SEPARATOR . '}', ' ', $UI_TITLE)),
            shorten_text(format_string(str_replace('{' . rule::SEPARATOR . '}', ' ', $UI_MESSAGE))),
        ];
        $expected = str_replace(self::$subplugin->get_elements(), $paramstoreplace, self::$subplugin->get_title());

        $params[self::$subplugin::UI_TITLE] = $UI_TITLE;
        $params[self::$subplugin::UI_MESSAGE]['text'] = $UI_MESSAGE;
        $params = json_encode($params);
        self::$subplugin->set_parameters($params);
        $content = [];
        self::$subplugin->process_markups($content, self::$coursetest->id);
        $this->assertSame([$expected], $content);
    }

    /**
     * @covers       \notificationsaction_messageagent\messageagent::get_parameters_placeholders
     *
     * @dataProvider dataprovidergetparametersplaceholders
     */
    public function test_getparametersplaceholders($param) {
        $auxarray = json_decode($param, true);

        // Format message text // delete ['text'].
        $auxarray['message'] = $auxarray[self::$subplugin::UI_MESSAGE]['text'];

        self::$subplugin->set_parameters($param);
        $actual = self::$subplugin->get_parameters_placeholders();

        $this->assertSame(json_encode($auxarray), $actual);
    }

    public static function dataprovidergetparametersplaceholders(): Generator {
        $data['title'] = 'TEST';
        $data['message']['text'] = 'Message body';
        yield [json_encode($data)];
        $data['title'] = 'TEST2';
        $data['message']['text'] = 'Message body';
        yield [json_encode($data)];
    }
}

