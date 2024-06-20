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
// Project implemented by the \"Recovery, Transformation and Resilience Plan.
// Funded by the European Union - Next GenerationEU\".
//
// Produced by the UNIMOODLE University Group: Universities of
// Valladolid, Complutense de Madrid, UPV/EHU, León, Salamanca,
// Illes Balears, Valencia, Rey Juan Carlos, La Laguna, Zaragoza, Málaga,
// Córdoba, Extremadura, Vigo, Las Palmas de Gran Canaria y Burgos.

/**
 * Version details
 *
 * @package    notificationscondition_usergroupadd
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace notificationscondition_usergroupadd;

use local_notificationsagent\evaluationcontext;
use local_notificationsagent\form\editrule_form;
use local_notificationsagent\helper\test\mock_base_logger;
use local_notificationsagent\helper\test\phpunitutil;
use local_notificationsagent\notificationplugin;
use local_notificationsagent\rule;

/**
 * Tests for the usergroupadd condition.
 *
 * @group notificationsagent
 */
class usergroupadd_test extends \advanced_testcase {
    /**
     * @var rule
     */
    private static $rule;
    /**
     * @var usergroupadd
     */
    private static $subplugin;
    /**
     * @var \stdClass
     */
    private static $course;
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
     * @var \stdClass
     */
    private static $group;
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

        self::$subplugin = new usergroupadd(self::$rule->to_record());
        self::$subplugin->set_id(5);
        self::$course = self::getDataGenerator()->create_course(
            ['startdate' => self::COURSE_DATESTART, 'enddate' => self::COURSE_DATEEND]
        );
        self::$coursecontext = \context_course::instance(self::$course->id);
        self::$user = self::getDataGenerator()->create_user();
        self::$context = new evaluationcontext();
        self::$context->set_userid(self::$user->id);
        self::$context->set_courseid(self::$course->id);
        self::$subtype = 'usergroupadd';
        self::$elements = ['GGGG'];
        self::$group = self::getDataGenerator()->create_group(['courseid' => self::$course->id]);
        self::getDataGenerator()->enrol_user(self::$user->id, self::$course->id);
    }

    /**
     * Tests the evaluate method.
     *
     * @param int $timeaccess
     * @param bool $usecache
     * @param bool $complementary
     * @param int $addingroup
     * @param bool $expected
     *
     * @covers       \notificationscondition_usergroupadd\usergroupadd::evaluate
     *
     * @dataProvider dataprovider
     */
    public function test_evaluate($timeaccess, $usecache, $complementary, $addingroup, $expected) {
        global $DB;
        self::setUser(self::$user->id);
        if ($addingroup) {
            self::getDataGenerator()->create_group_member([
                    'userid' => self::$user->id, 'groupid' => self::$group->id,
            ]);
        }
        self::$context->set_complementary($complementary);
        self::$context->set_params(
            json_encode(
                ['cmid' => self::$group->id],
            )
        );
        self::$context->set_timeaccess($timeaccess);
        self::$subplugin->set_id(self::CONDITIONID);

        if ($usecache) {
            $objdb = new \stdClass();
            $objdb->userid = self::$user->id;
            $objdb->courseid = self::$course->id;
            $objdb->startdate = self::COURSE_DATESTART + 864000;
            $objdb->pluginname = self::$subtype;
            $objdb->conditionid = self::CONDITIONID;
            $DB->insert_record('notificationsagent_cache', $objdb);
        }

        $result = self::$subplugin->evaluate(self::$context);
        $this->assertSame($expected, $result);
    }

    /**
     * Data provider for test_evaluate.
     */
    public static function dataprovider(): array {
        return [
                'Without cache or group' => [1701598161, false, notificationplugin::COMPLEMENTARY_CONDITION, false, false],
                'In group but without cache' => [1701511761, false, notificationplugin::COMPLEMENTARY_CONDITION, true, true],
                'Cached but without group' => [1701691707, true, notificationplugin::COMPLEMENTARY_CONDITION, false, true],
                'Cached and in group' => [1703498961, true, notificationplugin::COMPLEMENTARY_CONDITION, true, true],
        ];
    }

    /**
     * Test get subtype.
     *
     * @covers \notificationscondition_usergroupadd\usergroupadd::get_subtype
     */
    public function test_getsubtype() {
        $this->assertSame(self::$subtype, self::$subplugin->get_subtype());
    }

    /**
     * Test is generic.
     *
     * @covers \notificationscondition_usergroupadd\usergroupadd::is_generic
     */
    public function test_isgeneric() {
        $this->assertFalse(self::$subplugin->is_generic());
    }

    /**
     * Test get elements.s
     *
     * @covers \notificationscondition_usergroupadd\usergroupadd::get_elements
     */
    public function test_getelements() {
        $this->assertSame(self::$elements, self::$subplugin->get_elements());
    }

    /**
     * Test check capability.
     *
     * @covers \notificationscondition_usergroupadd\usergroupadd::check_capability
     */
    public function test_checkcapability() {
        $this->assertSame(
            has_capability('local/notificationsagent:' . self::$subtype, self::$coursecontext),
            self::$subplugin->check_capability(self::$coursecontext)
        );
    }

    /**
     * Test weeekend estimate next time
     *
     * @covers       \notificationscondition_usergroupadd\usergroupadd::estimate_next_time
     * @dataProvider datausergroupadd
     *
     * @param int $timeaccess Time access
     * @param int $expected Expected result
     * @param bool $complementary Complementary
     * @param bool $addingroup Add group
     *
     * @return void
     */
    public function test_estimatenexttime($timeaccess, $expected, $complementary, $addingroup) {
        \uopz_set_return('time', $timeaccess);
        // Saturday, Sunday configuration.
        self::$context->set_complementary($complementary);
        set_config('calendar_usergroupadd', 65);
        date_default_timezone_set('Europe/Madrid');
        self::$context->set_params(
            json_encode(
                ['cmid' => self::$group->id],
            )
        );
        if ($addingroup) {
            self::getDataGenerator()->create_group_member([
                    'userid' => self::$user->id, 'groupid' => self::$group->id,
            ]);
        }
        self::$context->set_timeaccess($timeaccess);
        // Test estimate next time.
        $this->assertEquals($expected, self::$subplugin->estimate_next_time(self::$context));
        \uopz_unset_return('time');
    }

    /**
     * Dataprovider for estimatenext time
     *
     * @return array[]
     */
    public static function datausergroupadd(): array {
        return [
                'Condition Is in group' => [1704074700, 1704074700, 0, true],
                'Condition Is not in group' => [1704495600, null, 0, false],
                'Exception Is in group' => [1704074700, null, 1, true],
                'Exception Is not in group' => [1704495600, 1704495600, 1, false],
        ];
    }

    /**
     * Test get cmid.
     *
     * @covers \notificationscondition_usergroupadd\usergroupadd::get_cmid
     */
    public function test_getcmid() {
        // Test estimate next time.
        $this->assertNull(self::$subplugin->get_cmid(self::$context));
    }

    /**
     * Test get title.
     *
     * @covers \notificationscondition_usergroupadd\usergroupadd::get_title
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
     * @covers \notificationscondition_usergroupadd\usergroupadd::get_description
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
     * @covers \notificationscondition_usergroupadd\usergroupadd::convert_parameters
     */
    public function test_convertparameters() {
        $id = self::$subplugin->get_id();
        $params = [$id . "_" . self::$subplugin::NAME . "_cmid" => "5"];
        $expected = '{"cmid":5}';
        $method = phpunitutil::get_method(self::$subplugin, 'convert_parameters');
        $result = $method->invoke(self::$subplugin, $params);
        $this->assertSame($expected, $result);
    }

    /**
     * Test process markups.
     *
     * @covers \notificationscondition_usergroupadd\usergroupadd::process_markups
     */
    public function test_processmarkups() {
        $expected = str_replace(self::$subplugin->get_elements(), [self::$group->name], self::$subplugin->get_title());
        $params[self::$subplugin::UI_ACTIVITY] = self::$group->id;
        $params = json_encode($params);
        self::$subplugin->set_parameters($params);
        $content = [];
        self::$subplugin->process_markups($content, self::$course->id);
        $this->assertSame([$expected], $content);
    }

    /**
     * Test get ui.
     *
     * @covers \notificationscondition_usergroupadd\usergroupadd::get_ui
     */
    public function test_getui() {
        $courseid = self::$course->id;
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
        $uinameselector = $method->invoke(self::$subplugin, self::$subplugin::UI_ACTIVITY);
        $this->assertTrue($mform->elementExists($uinameselector));
    }

    /**
     * Test update after restore method
     *
     * @return void
     * @covers \notificationscondition_usergroupadd\usergroupadd::update_after_restore
     */
    public function test_update_after_restore() {
        $logger = new mock_base_logger(0);
        $this->assertFalse(self::$subplugin->update_after_restore('restoreid', self::$coursecontext->id, $logger));
    }

    /**
     * Test validation.
     *
     * @covers       \notificationscondition_usergroupadd\usergroupadd::validation
     */
    public function test_validation() {
        $objparameters = new \stdClass();
        $objparameters->cmid = self::$group->id;

        self::$subplugin->set_parameters(json_encode($objparameters));
        $this->assertTrue(self::$subplugin->validation(self::$course->id));
    }
}
