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

namespace local_notificationsagent\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\userlist;
use local_notificationsagent\engine\notificationsagent_engine;
use local_notificationsagent\evaluationcontext;
use local_notificationsagent\notificationplugin;
use local_notificationsagent\rule;

/**
 * Testing privacy provider class
 *
 * @group notificationsagent
 */
class provider_test extends \advanced_testcase {
    /**
     * @var rule
     */
    private static $rule;
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
     *  Random id for activity
     */
    public const CMID = 246000;

    public const COMPONENT = 'local_notificationsagent';

    /**
     * Settin up test context
     *
     * @return void
     * @throws \coding_exception
     */
    final public function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
        global $DB;
        $rule = new rule();
        self::$rule = $rule;
        self::$user = self::getDataGenerator()->create_user();
        self::$course = self::getDataGenerator()->create_course();
        self::getDataGenerator()->enrol_user(self::$user->id, self::$course->id);

        $report = new \stdClass();
        $report->ruleid = 1;
        $report->userid = self::$user->id;
        $report->courseid = self::$course->id;
        $report->actionid = self::CMID;
        $report->actiondetail = '{"title":"Tïtulo","message":"mensaje"}';
        $report->timestamp = time();

        $DB->insert_record('notificationsagent_report', $report);

    }

    /**
     * Test provider metadata
     *
     * @covers \local_notificationsagent\privacy\provider::get_metadata
     * @return void
     */
    public function test_get_metadata() {
        $collection = new collection(self::COMPONENT);
        $result = provider::get_metadata($collection);
        $this->assertNotEmpty($collection);
        $this->assertSame($collection, $result);
        $this->assertInstanceOf(collection::class, $result);
    }

    /**
     * Test get context for user id
     *
     * @covers \local_notificationsagent\privacy\provider::get_contexts_for_userid
     * @return void
     */
    public function test_get_contexts_for_userid() {
        $context = \context_course::instance(self::$course->id);
        $contextlist = provider::get_contexts_for_userid(self::$user->id,);
        // Expect one item.
        $this->assertCount(1, $contextlist);
        // We should have the user context of our test user.
        $this->assertSame($context, $contextlist->current());

    }

    /**
     * Test get users in context
     *
     * @covers \local_notificationsagent\privacy\provider::get_users_in_context
     * @return void
     */
    public function test_get_users_in_context() {
        $context = \context_course::instance(self::$course->id);
        $userlist = new userlist($context, self::COMPONENT);
        provider::get_users_in_context($userlist);
        $this->assertEquals(self::$user->id, $userlist->get_userids()[0]);

        $context = \context_user::instance(self::$user->id);
        $userlist = new userlist($context, self::COMPONENT);
        provider::get_users_in_context($userlist);
        $this->assertEmpty($userlist->get_userids());
    }

    /**
     * Test delete users in context
     *
     * @covers \local_notificationsagent\privacy\provider::delete_data_for_all_users_in_context
     * @covers \local_notificationsagent\privacy\provider::delete_user_report
     * @return void
     */
    public function test_delete_data_for_all_users_in_contextt() {
        global $DB;
        $report = $DB->get_records('notificationsagent_report', ['courseid' => self::$course->id]);
        $this->assertNotEmpty($report);
        $context = \context_course::instance(self::$course->id);
        provider::delete_data_for_all_users_in_context($context);

        $deletereport = $DB->get_records('notificationsagent_report', ['courseid' => self::$course->id]);

        $this->assertEmpty($deletereport);
    }

    /**
     * @return void
     * @covers \local_notificationsagent\privacy\provider::delete_data_for_user
     */
    public function test_delete_data_for_user() {
        global $DB;
        $contextlist = provider::get_contexts_for_userid(self::$user->id,);
        $emptyapprvlist = new approved_contextlist(self::$user, 'mod_quiz', [$contextlist->get_contexts()[0]->id]);
        provider::delete_data_for_user($emptyapprvlist);
        $report = $DB->get_records('notificationsagent_report', ['courseid' => self::$course->id]);
        $this->assertNotEmpty($report);

        $this->assertNotEmpty($contextlist);
        $apprvlist = new approved_contextlist(self::$user, self::COMPONENT, [$contextlist->get_contexts()[0]->id]);
        $this->assertNotEmpty($apprvlist);
        provider::delete_data_for_user($apprvlist);
        $deletereport = $DB->get_records('notificationsagent_report', ['courseid' => self::$course->id]);
        $this->assertEmpty($deletereport);

    }
}
