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
use local_notificationsagent\external\manage_sessions;

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once("{$CFG->libdir}/externallib.php");

/**
 * Testing external manage sessions
 *
 * @group notificationsagent
 */
class manage_sessions_test extends \advanced_testcase {
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
     * Date start for the course
     */
    public const COURSE_DATESTART = 1704099600; // 01/01/2024 10:00:00.
    /**
     * Date end for the course
     */
    public const COURSE_DATEEND = 1706605200; // 30/01/2024 10:00:00,

    /**
     * Activity date start
     */
    public const CM_DATESTART = 1704099600; // 01/01/2024 10:00:00,

    /**
     * Activity date end
     */
    public const CM_DATEEND = 1705741200; // 20/01/2024 10:00:00,

    /**
     * User first access to a course
     */
    public const USER_FIRSTACCESS = 1704099600; // 30/01/2024 10:00:00,

    /**
     * User last access to a course
     */
    public const USER_LASTACCESS = 1704099600; // 01/01/2024 10:00:00.

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
     *  Testing execute manage sesisons.
     *
     * @covers       \local_notificationsagent\external\manage_sessions::execute
     * @covers       \local_notificationsagent\external\manage_sessions::execute_returns
     * @covers       \local_notificationsagent\external\manage_sessions::execute_parameters
     * @dataProvider dataprovider
     *
     * @param int $user
     * @param int $useinstance
     * @param int $orderid
     * @param string $expected
     *
     * @return void
     */
    public function test_execute($user, $useinstance, $orderid, $expected) {
        global $DB;
        $coursecontext = \context_course::instance(self::$course->id);
        self::$user = self::getDataGenerator()->create_and_enrol($coursecontext, 'manager');
        self::setUser($user === 0 ? self::$user->id : 2);
        $courseid = self::$course->id;

        if ($orderid == -1) {
            $courseid = null;
        }
        $result = manage_sessions::execute(
            'sessionname',
            $orderid,
            $courseid
        );
        // Where user_preference was saved.
        $result = manage_sessions::execute(
            'sessionname',
            $orderid,
            $courseid
        );
        $result = external_api::clean_returnvalue(manage_sessions::execute_returns(), $result);

        $this->assertEquals($expected, $result['orderid']);
    }

    /**
     * Data provider for execute
     *
     * @return array[]
     */
    public static function dataprovider(): array {
        return [
                'Course valid' => [1, 0, 1, 1],
                'Admin view' => [2, 1, -1, -1],
        ];
    }
}
