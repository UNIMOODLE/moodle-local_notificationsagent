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
 * @package    notificationsaction_removeusergroup
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace notificationsaction_removeusergroup;

use Generator;
use local_notificationsagent\evaluationcontext;
use local_notificationsagent\form\editrule_form;
use local_notificationsagent\notificationplugin;
use local_notificationsagent\helper\test\phpunitutil;
use local_notificationsagent\rule;
use notificationsaction_removeusergroup\removeusergroup;

/**
 * Test for removeusergroup class.
 *
 * @group notificationsagent
 */
final class removeusergroup_test extends \advanced_testcase {
    /**
     * @var rule
     */
    private static $rule;

    /**
     * @var removeusergroup
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

    public function setUp(): void {
        parent::setUp();

        $creategroup = true;
        $arguments = $this->getProvidedData();
        foreach ($arguments as $key => $value) {
            $$key = $value;
        }

        $this->resetAfterTest(true);
        self::$rule = new rule();

        self::$subplugin = new removeusergroup(self::$rule->to_record());
        self::$subplugin->set_id(5);
        self::$coursetest = self::getDataGenerator()->create_course(
            ['startdate' => self::COURSE_DATESTART, 'enddate' => self::COURSE_DATEEND]
        );
        self::$coursecontext = \context_course::instance(self::$coursetest->id);
        self::$user = self::getDataGenerator()->create_user();
        self::getDataGenerator()->enrol_user(self::$user->id, self::$coursetest->id);
        if ($creategroup) {
            self::$group = self::getDataGenerator()->create_group(['courseid' => self::$coursetest->id]);
            self::getDataGenerator()->create_group_member(['userid' => self::$user->id, 'groupid' => self::$group->id]);
        }
        self::$context = new evaluationcontext();
        self::$context->set_userid(self::$user->id);
        self::$context->set_courseid(self::$coursetest->id);
        self::$subtype = 'removeusergroup';
        self::$elements = ['[GGGG]'];
    }

    /**
     * Test for execute_action method.
     *
     * @covers \notificationsaction_removeusergroup\removeusergroup::execute_action
     */
    public function test_execute_action(): void {
        $auxarray['user'] = self::$user->id;
        $auxarray['cmid'] = self::$group->id;
        $param = json_encode($auxarray);
        self::$context->set_params($param);
        self::$context->set_userid(self::$user->id);
        self::$subplugin->set_id(self::CONDITIONID);
        // Test action.
        $result = self::$subplugin->execute_action(self::$context, $param);
        $this->assertTrue($result);
    }

    /**
     * Test get subtype method.
     *
     * @covers \notificationsaction_removeusergroup\removeusergroup::get_subtype
     */
    public function test_getsubtype(): void {
        $this->assertSame(self::$subtype, self::$subplugin->get_subtype());
    }

    /**
     * Test is generic method.
     *
     * @covers \notificationsaction_removeusergroup\removeusergroup::is_generic
     */
    public function test_isgeneric(): void {
        $this->assertTrue(self::$subplugin->is_generic());
    }

    /**
     * Test get elements method.
     *
     * @covers \notificationsaction_removeusergroup\removeusergroup::get_elements
     */
    public function test_getelements(): void {
        $this->assertSame(self::$elements, self::$subplugin->get_elements());
    }

    /**
     * Test check capability method.
     *
     * @covers \notificationsaction_removeusergroup\removeusergroup::check_capability
     */
    public function test_checkcapability(): void {
        $this->assertSame(
            has_capability('local/notificationsagent:' . self::$subtype, self::$coursecontext),
            self::$subplugin->check_capability(self::$coursecontext)
        );
    }

    /**
     * Test convert parameters
     *
     * @covers \notificationsaction_removeusergroup\removeusergroup::convert_parameters
     */
    public function test_convertparameters(): void {
        $id = self::$subplugin->get_id();
        $params = ["type" => "5", $id . "_removeusergroup_cmid" => "3"];
        $expected = '{"cmid":"3"}';
        $method = phpunitutil::get_method(self::$subplugin, 'convert_parameters');
        $result = $method->invoke(self::$subplugin, $params);

        $this->assertSame($expected, $result);
    }

    /**
     * Test get title method
     *
     * @covers \notificationsaction_removeusergroup\removeusergroup::get_title
     */
    public function test_gettitle(): void {
        $this->assertNotNull(self::$subplugin->get_title());
        foreach (self::$elements as $element) {
            $this->assertStringContainsString($element, self::$subplugin->get_title());
        }
    }

    /**
     * Test get description method
     *
     * @covers \notificationsaction_removeusergroup\removeusergroup::get_description
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
     * Test get ui.
     *
     * @covers       \notificationsaction_removeusergroup\removeusergroup::get_ui
     *
     * @dataProvider dataprovidergetui
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
        $uiactivityname = $method->invoke(self::$subplugin, self::$subplugin::UI_ACTIVITY);

        $this->assertTrue($mform->elementExists($uiactivityname));
    }

    /**
     * Data provider for get ui.
     */
    public static function dataprovidergetui(): Generator {
        yield ['creategroup' => false];
        yield ['creategroup' => true];
    }

    /**
     * Test process markups.
     *
     * @covers \notificationsaction_removeusergroup\removeusergroup::process_markups
     */
    public function test_processmarkups(): void {
        $expected = str_replace(self::$subplugin->get_elements(), [self::$group->name], self::$subplugin->get_title());
        $params[self::$subplugin::UI_ACTIVITY] = self::$group->id;
        $params = json_encode($params);
        self::$subplugin->set_parameters($params);
        $content = [];
        self::$subplugin->process_markups($content, self::$coursetest->id);
        $this->assertSame([$expected], $content);
    }

    /**
     * Test validation.
     *
     * @covers       \notificationsaction_removeusergroup\removeusergroup::validation
     */
    public function test_validation(): void {
        $objparameters = new \stdClass();
        $objparameters->cmid = self::$group->id;

        self::$subplugin->set_parameters(json_encode($objparameters));
        $this->assertTrue(self::$subplugin->validation(self::$coursetest->id));
    }
}
