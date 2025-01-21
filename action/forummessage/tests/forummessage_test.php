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
use local_notificationsagent\helper\test\phpunitutil;
use local_notificationsagent\notificationactionplugin;
use local_notificationsagent\notificationplugin;
use local_notificationsagent\notificationsagent;
use local_notificationsagent\rule;

/**
 * Test for forummessage.
 *
 * @group notificationsagent
 */
final class forummessage_test extends \advanced_testcase {
    /**
     * @var rule
     */
    private static $rule;

    /**
     * @var forummessage
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

        self::$subplugin = new forummessage(self::$rule->to_record());
        self::$subplugin->set_id(5);
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
     * Test case for execute_action method.
     *
     * @dataProvider dataprovider
     *
     * @param string $param Parameter to be passed to the method
     *
     * @return void
     * @covers       \notificationsaction_forummessage\forummessage::execute_action
     */
    public function test_execute_action($param): void {
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
        self::$context->set_usertimesfired(1);
        // Test action.
        $result = self::$subplugin->execute_action(self::$context, $param);
        $expected = $DB->get_record('forum_discussions', ['id' => $result]);
        $this->assertEquals($expected->id, $result);
        $this->assertEquals($expected->forum, $cmtestaf->id);
        $this->assertEquals($expected->name, $auxarray['title']);
    }

    /**
     * Data provider for test_execute_action.
     */
    public static function dataprovider(): array {
        return [
            ['{"title":"TEST","message":"Message body"}'],
            ['{"title":"TEST2","message":"Message body"}'],
        ];
    }

    /**
     * Test get_subtype.
     *
     * @covers \notificationsaction_forummessage\forummessage::get_subtype
     */
    public function test_getsubtype(): void {
        $this->assertSame(self::$subtype, self::$subplugin->get_subtype());
    }

    /**
     * Test is_generic.
     *
     * @covers \notificationsaction_forummessage\forummessage::is_generic
     */
    public function test_isgeneric(): void {
        $this->assertTrue(self::$subplugin->is_generic());
    }

    /**
     * Test get_elements.
     *
     * @covers \notificationsaction_forummessage\forummessage::get_elements
     */
    public function test_getelements(): void {
        $this->assertSame(self::$elements, self::$subplugin->get_elements());
    }

    /**
     * Test check_capability.
     *
     * @covers \notificationsaction_forummessage\forummessage::check_capability
     */
    public function test_checkcapability(): void {
        $this->assertSame(
            has_capability('local/notificationsagent:' . self::$subtype, self::$coursecontext),
            self::$subplugin->check_capability(self::$coursecontext)
        );
    }

    /**
     * Test get_title.
     *
     * @covers \notificationsaction_forummessage\forummessage::get_title
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
     * @covers \notificationsaction_forummessage\forummessage::convert_parameters
     */
    public function test_convertparameters(): void {
        $id = self::$subplugin->get_id();
        $params = [
            $id . "_forummessage_title" => "Test title", $id . "_forummessage_message" => ['text' => "Message body"],
            $id . "_forummessage_cmid" => "5",
        ];
        $expected = '{"title":"Test title","message":{"text":"Message body"},"cmid":"5"}';
        $method = phpunitutil::get_method(self::$subplugin, 'convert_parameters');
        $result = $method->invoke(self::$subplugin, $params);
        $this->assertSame($expected, $result);
    }

    /**
     * Test get ui.
     *
     * @covers \notificationsaction_forummessage\forummessage::get_ui
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
        $uiactivityname = $method->invoke(self::$subplugin, self::$subplugin::UI_ACTIVITY);

        $this->assertTrue($mform->elementExists($uititlename));
        $this->assertTrue($mform->elementExists($uiamessagename));
        $this->assertTrue($mform->elementExists($uiactivityname));
    }

    /**
     * Test is_send_once.
     *
     * @covers \notificationsaction_forummessage\forummessage::is_send_once
     */
    public function test_is_send_once(): void {
        $this->assertTrue(self::$subplugin->is_send_once(self::$user->id));
    }

    /**
     * Test process_markups.
     *
     * @covers \notificationsaction_forummessage\forummessage::process_markups
     */
    public function test_processmarkups(): void {
        $uititle = 'test title';
        $uimessage = 'test message';

        $forumgenerator = self::getDataGenerator()->get_plugin_generator('mod_forum');
        $cmgen = $forumgenerator->create_instance([
            'course' => self::$coursetest->id,
        ]);

        $paramstoreplace = [
            shorten_text($cmgen->name),
            shorten_text(str_replace('{' . rule::SEPARATOR . '}', ' ', $uititle)),
            shorten_text(format_string(str_replace('{' . rule::SEPARATOR . '}', ' ', $uimessage))),
        ];
        $expected = str_replace(self::$subplugin->get_elements(), $paramstoreplace, self::$subplugin->get_title());

        $params[self::$subplugin::UI_ACTIVITY] = $cmgen->cmid;
        $params[self::$subplugin::UI_TITLE] = $uititle;
        $params[self::$subplugin::UI_MESSAGE]['text'] = $uimessage;
        $params = json_encode($params);
        self::$subplugin->set_parameters($params);
        $content = [];
        self::$subplugin->process_markups($content, self::$coursetest->id);
        $this->assertSame([$expected], $content);
    }

    /**
     *
     * This method tests the process_markups method of the forummessage class.
     *
     * @param mixed $param
     *
     * @return void
     * @covers       \notificationsaction_forummessage\forummessage::get_parameters_placeholders
     * @dataProvider dataprovidergetparametersplaceholders
     */
    public function test_getparametersplaceholders($param): void {
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

    /**
     * Data provider for get_parameters_placeholders.
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
     * Test showuserplaceholders
     *
     * @covers \notificationsaction_forummessage\forummessage::show_user_placeholders
     * @return void
     */
    public function test_showuserplaceholders(): void {
        $this->assertFalse(self::$subplugin->show_user_placeholders());
    }

    /**
     * Test validation.
     *
     * @covers       \notificationsaction_forummessage\forummessage::validation
     */
    public function test_validation(): void {
        $quizgenerator = self::getDataGenerator()->get_plugin_generator('mod_forum');
        $cmgen = $quizgenerator->create_instance([
            'course' => self::$coursetest->id,
        ]);
        $objparameters = new \stdClass();
        $objparameters->cmid = $cmgen->cmid;

        self::$subplugin->set_parameters(json_encode($objparameters));
        $this->assertTrue(self::$subplugin->validation(self::$coursetest->id));
    }

    /**
     * Testing get_activity_cmid
     *
     * @param int $cmid
     * @return void
     * @dataProvider provideractivitycmid
     * @covers       \notificationsaction_forummessage\forummessage::get_activity_cmid
     */
    public function test_get_activity_cmid(int $cmid): void {

        $data = ['cmid' => $cmid];

        $result = self::$subplugin->get_activity_cmid($data, self::$coursetest->id);

        if ($cmid === notificationsagent::FORUM_NEWS_CMID) {
            $instance = notificationactionplugin::get_news_forum(self::$coursetest->id);
            $fastmodinfo = get_fast_modinfo(self::$coursetest->id);
            $cmid = $fastmodinfo->get_instances_of('forum')[$instance]->id;
        }
        $this->assertEquals($cmid, $result);
    }

    /**
     * Dataprovider
     *
     * @return \int[][]
     */
    public static function provideractivitycmid(): array {
        return [
            [3],
            [notificationsagent::FORUM_NEWS_CMID],
        ];
    }
}
