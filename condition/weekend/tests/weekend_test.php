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
 * @package    notificationscondition_weekend
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace notificationscondition_weekend;

use local_notificationsagent\evaluationcontext;
use local_notificationsagent\form\editrule_form;
use local_notificationsagent\notificationplugin;
use local_notificationsagent\helper\test\phpunitutil;
use local_notificationsagent\rule;
use notificationscondition_weekend\weekend;

/**
 * @group notificationsagent
 */
class weekend_test extends \advanced_testcase {

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

        self::$subplugin = new weekend(self::$rule);
        self::$coursetest = self::getDataGenerator()->create_course(
            ['startdate' => self::COURSE_DATESTART, 'enddate' => self::COURSE_DATEEND]
        );
        self::$coursecontext = \context_course::instance(self::$coursetest->id);
        self::$user = self::getDataGenerator()->create_user();
        self::$context = new evaluationcontext();
        self::$context->set_userid(self::$user->id);
        self::$context->set_courseid(self::$coursetest->id);
        self::$subtype = 'weekend';
        self::$elements = [];

    }

    /**
     *
     * @param int  $timeaccess
     * @param bool $expected
     *
     * @covers       \notificationscondition_weekend\weekend::evaluate
     *
     * @dataProvider dataprovider
     */
    public function test_evaluate($timeaccess, $usecache, $complementary, $expected) {
        self::$context->set_timeaccess($timeaccess);
        self::$context->set_complementary($complementary);
        self::$subplugin->set_id(self::CONDITIONID);

        if ($usecache) {
            global $DB;
            $objdb = new \stdClass();
            $objdb->userid = self::$user->id;
            $objdb->courseid = self::$coursetest->id;
            $objdb->timestart = $timeaccess;
            $objdb->pluginname = self::$subtype;
            $objdb->conditionid = self::CONDITIONID;
            // Insert.
            $DB->insert_record('notificationsagent_cache', $objdb);
        }
        // Insert.
        self::$subplugin->set_id(self::CONDITIONID);

        $result = self::$subplugin->evaluate(self::$context);

        $this->assertSame($expected, $result);
        self::assertNull(self::$subplugin->estimate_next_time(self::$context));
    }

    public static function dataprovider(): array {
        return [
            [1701598161, false, notificationplugin::COMPLEMENTARY_CONDITION, true],
            [1701511761, false, notificationplugin::COMPLEMENTARY_CONDITION, true],
            [1701691707, false, notificationplugin::COMPLEMENTARY_CONDITION, false],
            [1703498961, false, notificationplugin::COMPLEMENTARY_CONDITION, false],
            [1701511761, true, notificationplugin::COMPLEMENTARY_CONDITION, true],
            [1701691707, true, notificationplugin::COMPLEMENTARY_CONDITION, false],
        ];
    }

    /**
     * @covers \notificationscondition_weekend\weekend::get_subtype
     */
    public function test_getsubtype() {
        $this->assertSame(self::$subtype, self::$subplugin->get_subtype());
    }

    /**
     * @covers \notificationscondition_weekend\weekend::is_generic
     */
    public function test_isgeneric() {
        $this->assertTrue(self::$subplugin->is_generic());
    }

    /**
     * @covers \notificationscondition_weekend\weekend::get_elements
     */
    public function test_getelements() {
        $this->assertSame(self::$elements, self::$subplugin->get_elements());
    }

    /**
     * @covers \notificationscondition_weekend\weekend::check_capability
     */
    public function test_checkcapability() {
        $this->assertSame(
            has_capability('local/notificationsagent:' . self::$subtype, self::$coursecontext),
            self::$subplugin->check_capability(self::$coursecontext)
        );
    }

    /**
     * @covers \notificationscondition_weekend\weekend::estimate_next_time
     */
    public function test_estimatenexttime() {
        // Test estimate next time.
        $this->assertNull(self::$subplugin->estimate_next_time(self::$context));
    }

    /**
     * @covers \notificationscondition_weekend\weekend::get_cmid
     */
    public function test_getcmid() {
        // Test estimate next time.
        $this->assertNull(self::$subplugin->get_cmid(self::$context));
    }

    /**
     * @covers \notificationscondition_weekend\weekend::get_title
     */
    public function test_gettitle() {
        $this->assertNotNull(self::$subplugin->get_title());
        foreach (self::$elements as $element) {
            $this->assertStringContainsString($element, self::$subplugin->get_title());
        }
    }

    /**
     * @covers \notificationscondition_weekend\weekend::get_description
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
     * @covers \notificationscondition_weekend\weekend::convert_parameters
     */
    public function test_convertparameters() {
        $id = 0;
        $params = [];

        $method = phpunitutil::get_method(self::$subplugin, 'convert_parameters');
        $result = $method->invoke(self::$subplugin, $id, $params);

        $this->assertNull($result);
    }

    /**
     * @covers \notificationscondition_weekend\weekend::process_markups
     */
    public function test_processmarkups() {
        $content = [];
        self::$subplugin->process_markups($content, self::$coursetest->id);
        $this->assertSame([self::$subplugin->get_title()], $content);
    }

    /**
     * @throws \coding_exception
     * @throws \dml_exception
     * @covers       \notificationscondition_weekend\weekend::is_weekend()
     * @dataProvider dataproviderwe
     */
    final public function test_isweekend(int $time, bool $expected) {
        $this->assertSame($expected, weekend::is_weekend($time));

    }

    public static function dataproviderwe(): array {
        // Only valid for a saturday, sunday configuration.
        return [
            [1706182049, false],
            [1705741200, true],
            [1705827600, true],
        ];
    }

    /**
     * @covers \notificationscondition_weekend\weekend::get_ui
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
        $uidescriptionname = $method->invoke(self::$subplugin, $id, self::$subplugin::UI_DESCRIPTION);

        $this->assertTrue($mform->elementExists($uidescriptionname));
    }

}
