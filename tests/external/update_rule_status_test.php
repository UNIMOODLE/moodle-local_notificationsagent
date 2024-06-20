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
 * @package    local_notificationsagent
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_notificationsagent\external;

use local_notificationsagent\rule;
use external_api;
use local_notificationsagent\external\update_rule_status;

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once("{$CFG->libdir}/externallib.php");

/**
 * Testing external update rule status
 *
 * @group notificationsagent
 */
class update_rule_status_test extends \advanced_testcase {
    /**
     * @var rule
     */
    private static $rule;
    /**
     * @var \stdClass
     */
    /**
     * @var \stdClass
     */
    private static $user;

    /**
     * @var \stdClass
     */
    private static $course;

    /**
     * @var \stdClass
     */
    private static $cmteste;
    /**
     *
     */
    /**
     * Date start for the course
     */
    public const COURSE_DATESTART = 1704099600; // 01/01/2024 10:00:00.
    /**
     *
     */
    /**
     * Date end for the course
     */
    public const COURSE_DATEEND = 1706605200; // 30/01/2024 10:00:00,
    /**
     *
     */
    /**
     * Activity date start
     */
    public const CM_DATESTART = 1704099600; // 01/01/2024 10:00:00,
    /**
     *
     */
    /**
     * Activity date end
     */
    public const CM_DATEEND = 1705741200; // 20/01/2024 10:00:00,
    /**
     *
     */
    /**
     * User first access to a course
     */
    public const USER_FIRSTACCESS = 1704099600; // 30/01/2024 10:00:00,
    /**
     *
     */
    /**
     * User last access to a course
     */
    public const USER_LASTACCESS = 1704099600; // 01/01/2024 10:00:00.
    /**
     *
     */
    public const CMID = 246000;

    /**
     * Settin up test context
     *
     * @return void
     */
    final public function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
        $rule = new rule();
        self::$rule = $rule;
        self::$user = self::getDataGenerator()->create_user();
        self::$course = self::getDataGenerator()->create_course(
            ([
                'startdate' => self::COURSE_DATESTART,
                'enddate' => self::COURSE_DATEEND,
            ])
        );
    }

    /**
     *  Testing execute update rule status test
     *
     * @covers       \local_notificationsagent\external\update_rule_status::execute
     * @covers       \local_notificationsagent\external\update_rule_status::execute_returns
     * @covers       \local_notificationsagent\external\update_rule_status::execute_parameters
     * @dataProvider dataprovider
     *
     * @param int    $user
     * @param int    $useinstance
     * @param string $expected
     * @param int    $status
     *
     * @return void
     */
    public function test_execute($user, $useinstance, $expected, $status) {
        global $DB;
        $coursecontext = \context_course::instance(self::$course->id);
        self::$user = self::getDataGenerator()->create_and_enrol($coursecontext, 'manager');
        self::setUser($user === 0 ? self::$user->id : 2);
        $dataform = new \StdClass();
        $dataform->title = "Rule Test";
        $dataform->type = 1;
        $dataform->courseid = self::$course->id;
        $dataform->timesfired = 2;
        $dataform->runtime_group = ['runtime_days' => 5, 'runtime_hours' => 0, 'runtime_minutes' => 0];

        $ruleid = self::$rule->create($dataform);
        self::$rule->set_id($ruleid);

        $courseid = self::$course->id;

        // Conditions.
        $objdb = new \stdClass();
        $objdb->ruleid = $ruleid;
        $objdb->courseid = $courseid;
        $objdb->type = 'condition';
        $objdb->pluginname = 'sesssionstart';
        $objdb->parameters = '{"time":84600}';
        // Insert.
        $conditionid = $DB->insert_record('notificationsagent_condition', $objdb);
        $this->assertIsNumeric($conditionid);
        // Conditions.
        $objdb = new \stdClass();
        $objdb->ruleid = $ruleid;
        $objdb->courseid = $courseid;
        $objdb->type = 'action';
        $objdb->pluginname = 'addusergroup';
        $objdb->parameters = '{"cmid":"0"}';
        // Insert.
        $actionid = $DB->insert_record('notificationsagent_action', $objdb);
        $this->assertIsNumeric($actionid);

        $instance = self::$rule::create_instance($ruleid);

        $result = update_rule_status::execute(
            $useinstance == 0 ? $useinstance : $instance->get_id(),
            $status
        );
        $result = external_api::clean_returnvalue(update_rule_status::execute_returns(), $result);

        if ($user == 2) {
            $this->assertEmpty($result['warnings']);
        } else {
            $this->assertEquals($expected, $result['warnings'][0]['warningcode']);
        }
    }

    /**
     * Data provider for execute
     *
     * @return array[]
     */
    public static function dataprovider(): array {
        return [
            'No instance' => [1, 0, 'nosuchinstance', rule::RESUME_RULE],
            'Permissions resume' => [2, 1, '', rule::RESUME_RULE],
            'Permissions pause' => [2, 1, '', rule::PAUSE_RULE],
            'No Permissions' => [0, 1, 'nopermissions', rule::RESUME_RULE],
        ];
    }
}
