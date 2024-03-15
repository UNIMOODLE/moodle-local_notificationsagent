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
 * @package    notificationsaction_forummessage
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace notificationsaction_forummessage;

use Generator;
use local_notificationsagent\evaluationcontext;
use local_notificationsagent\form\editrule_form;
use local_notificationsagent\notificationplugin;
use local_notificationsagent\helper\test\phpunitutil;
use local_notificationsagent\rule;
use notificationsaction_forummessage\forummessage;

/**
 * @group notificationsagent
 */
class forummessage_test extends \advanced_testcase {

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
        $this->resetAfterTest(true);
        self::$rule = new rule();

        self::$subplugin = new forummessage(self::$rule);
        self::$coursetest = self::getDataGenerator()->create_course(
            ['startdate' => self::COURSE_DATESTART, 'enddate' => self::COURSE_DATEEND]
        );
        self::$coursecontext = \context_course::instance(self::$coursetest->id);
        self::$user = self::getDataGenerator()->create_user();
        self::$context = new evaluationcontext();
        self::$context->set_userid(self::$user->id);
        self::$context->set_courseid(self::$coursetest->id);
        self::$subtype = 'forummessage';
        self::$elements = ['[FFFF]', '[TTTT]', '[BBBB]'];
    }

    /**
     *
     * @param int    $timeaccess
     * @param string $param
     * @param bool   $expected
     *
     * @covers       \notificationsaction_forummessage\forummessage::execute_action
     *
     * @dataProvider dataprovider
     */
    public function test_execute_action($param) {
        global $DB;
        $cmgenerator = self::getDataGenerator()->get_plugin_generator('mod_forum');
        $cmtestaf = $cmgenerator->create_instance([
            'course' => self::$coursetest->id,
        ]);

        $auxarray = json_decode($param, true);
        $auxarray['cmid'] = $cmtestaf->cmid;
        $param = json_encode($auxarray);

        self::$context->set_params($param);
        self::$context->set_userid(self::$user->id);
        self::$subplugin->set_id(self::CONDITIONID);
        self::$context->set_rule(self::$rule);

        // Test action.
        $result = self::$subplugin->execute_action(self::$context, $param);
        $expected = $DB->get_record('forum_discussions', ['id' => $result]);
        $this->assertEquals($expected->id, $result);
        $this->assertEquals($expected->forum, $cmtestaf->id);
        $this->assertEquals($expected->name, $auxarray['title']);
    }

    public static function dataprovider(): array {
        return [
            ['{"title":"TEST","message":"Message body"}'],
            ['{"title":"TEST2","message":"Message body"}'],
        ];
    }

    /**
     * @covers \notificationsaction_forummessage\forummessage::get_subtype
     */
    public function test_getsubtype() {
        $this->assertSame(self::$subtype, self::$subplugin->get_subtype());
    }

    /**
     * @covers \notificationsaction_forummessage\forummessage::is_generic
     */
    public function test_isgeneric() {
        $this->assertTrue(self::$subplugin->is_generic());
    }

    /**
     * @covers \notificationsaction_forummessage\forummessage::get_elements
     */
    public function test_getelements() {
        $this->assertSame(self::$elements, self::$subplugin->get_elements());
    }

    /**
     * @covers \notificationsaction_forummessage\forummessage::check_capability
     */
    public function test_checkcapability() {
        $this->assertSame(
            has_capability('local/notificationsagent:' . self::$subtype, self::$coursecontext),
            self::$subplugin->check_capability(self::$coursecontext)
        );
    }

    /**
     * @covers \notificationsaction_forummessage\forummessage::get_title
     */
    public function test_gettitle() {
        $this->assertNotNull(self::$subplugin->get_title());
        foreach (self::$elements as $element) {
            $this->assertStringContainsString($element, self::$subplugin->get_title());
        }
    }

    /**
     * @covers \notificationsaction_forummessage\forummessage::convert_parameters
     */
    public function test_convert_parameters() {
        $params = [
            "5_forummessage_title" => "Test title", "5_forummessage_message" => ['text' => "Message body"],
            "5_forummessage_cmid" => "5",
        ];
        $expected = '{"title":"Test title","message":{"text":"Message body"},"cmid":"5"}';
        $method = phpunitutil::get_method(self::$subplugin, 'convert_parameters');
        $result = $method->invoke(self::$subplugin, 5, $params);
        $this->assertSame($expected, $result);
    }

    /**
     * @covers \notificationsaction_forummessage\forummessage::get_ui
     */
    public function test_getui() {
        $courseid = self::$coursetest->id;
        $ruletype = rule::RULE_TYPE;
        $typeaction = "add";
        $customdata = [
            'ruleid' => self::$rule->get_id(),
            notificationplugin::TYPE_CONDITION => self::$rule->get_conditions(),
            notificationplugin::TYPE_EXCEPTION => self::$rule->get_exceptions(),
            notificationplugin::TYPE_ACTION => self::$rule->get_actions(),
            'type' => $ruletype,
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
        $uiactivityname = $method->invoke(self::$subplugin, $id, self::$subplugin::UI_ACTIVITY);

        $this->assertTrue($mform->elementExists($uititlename));
        $this->assertTrue($mform->elementExists($uiamessagename));
        $this->assertTrue($mform->elementExists($uiactivityname));
    }

    /**
     * @covers \notificationsaction_forummessage\forummessage::is_send_once
     */
    public function test_is_send_once() {
        $this->assertTrue(self::$subplugin->is_send_once(self::$user->id));
    }

    /**
     * @covers \notificationsaction_forummessage\forummessage::process_markups
     */
    public function test_processmarkups() {
        $UI_TITLE = 'test title';
        $UI_MESSAGE = 'test message';

        $forumgenerator = self::getDataGenerator()->get_plugin_generator('mod_forum');
        $cmgen = $forumgenerator->create_instance([
            'course' => self::$coursetest->id,
        ]);

        $paramstoreplace = [
            shorten_text($cmgen->name),
            shorten_text(str_replace('{' . rule::SEPARATOR . '}', ' ', $UI_TITLE)),
            shorten_text(format_string(str_replace('{' . rule::SEPARATOR . '}', ' ', $UI_MESSAGE))),
        ];
        $expected = str_replace(self::$subplugin->get_elements(), $paramstoreplace, self::$subplugin->get_title());

        $params[self::$subplugin::UI_ACTIVITY] = $cmgen->cmid;
        $params[self::$subplugin::UI_TITLE] = $UI_TITLE;
        $params[self::$subplugin::UI_MESSAGE]['text'] = $UI_MESSAGE;
        $params = json_encode($params);
        self::$subplugin->set_parameters($params);
        $content = [];
        self::$subplugin->process_markups($content, self::$coursetest->id);
        $this->assertSame([$expected], $content);
    }

    /**
     * @covers       \notificationsaction_forummessage\forummessage::get_parameters_placeholders
     *
     * @dataProvider dataprovidergetparametersplaceholders
     */
    public function test_getparametersplaceholders($param) {
        $cmgenerator = self::getDataGenerator()->get_plugin_generator('mod_forum');
        $cmtestaf = $cmgenerator->create_instance([
            'course' => self::$coursetest->id,
        ]);

        $auxarray = json_decode($param, true);
        // Add cmid.
        $auxarray['cmid'] = $cmtestaf->cmid;
        $param = json_encode($auxarray);

        // Format message text // delete ['text'].
        $auxarray['message'] = $auxarray['message']['text'];

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

