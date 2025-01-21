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
 * @package    notificationscondition_sessionstart
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace notificationscondition_sessionstart\privacy;

use core_privacy\local\metadata\collection;
use notificationscondition_sessionstart\persistent\coursefirstaccess;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\writer;
use core_privacy\local\request\userlist;
/**
 * Tests for the sessionstart condition.
 *
 * @group notificationsagent
 */
final class provider_test extends \advanced_testcase {

    /**
     * @var \stdClass
     */
    private static $user;

    /**
     * @var \stdClass
     */
    private static $course;

    protected function setUp(): void {
        $this->resetAfterTest();
        self::$user = self::getDataGenerator()->create_user();
        self::$course = self::getDataGenerator()->create_course();
        self::getDataGenerator()->enrol_user(self::$user->id, self::$course->id);
        self::getDataGenerator()->create_user_course_lastaccess(self::$user, self::$course, time());
        $firstaccess = new coursefirstaccess();
        $firstaccess->set('userid', self::$user->id);
        $firstaccess->set('courseid', self::$course->id);
        $firstaccess->set('firstaccess', time());
        $firstaccess->save();

    }
    /**
     *  Get metadata
     * @covers \notificationscondition_sessionstart\privacy\provider::get_metadata
     * @return void
     */
    public function test_get_metadata(): void {
        $collection = new collection('notificationscondition_sessionstart');
        $result = provider::get_metadata($collection);
        $this->assertNotEmpty($collection);
        $this->assertSame($collection, $result);
        $this->assertInstanceOf(collection::class, $result);
    }
    /**
     *  Get context for userid test
     * @covers \notificationscondition_sessionstart\privacy\provider::get_contexts_for_userid
     * @return void
     */
    public function test_get_contexts_for_userid(): void {

        $contextlist = provider::get_contexts_for_userid(self::$user->id);
        $contexts = $contextlist->get_contextids();
        $this->assertCount(1, $contexts);
        $this->assertEquals(\context_course::instance(self::$course->id)->id, reset($contexts));
    }
    /**
     *  Exportdata  test
     * @covers \notificationscondition_sessionstart\privacy\provider::export_user_data
     * @return void
     */
    public function test_export_user_data(): void {

        $contextlist = provider::get_contexts_for_userid(self::$user->id);
        $approvedcontextlist = new approved_contextlist(self::$user, 'notificationscondition_sessionstart',
            [$contextlist->get_contexts()[0]->id]);
        $this->assertNotEmpty($approvedcontextlist);
        $this->assertEquals('notificationscondition_sessionstart', $approvedcontextlist->get_component());

        provider::export_user_data($approvedcontextlist);

        foreach ($contextlist as $context) {
            $writer = writer::with_context($context);
            $this->assertTrue($writer->has_any_data());
            $exporteddata = $writer->get_data(['notificationsagent_crseview']);
            $this->assertNotEmpty($exporteddata);
            $this->assertEquals(self::$user->id, $exporteddata->userid);
            $this->assertEquals(self::$course->id, $exporteddata->courseid);
        }
    }
    /**
     *  Delete data for all users
     * @covers \notificationscondition_sessionstart\privacy\provider::delete_data_for_all_users_in_context
     * @return void
     */
    public function test_delete_data_for_all_users_in_context(): void {
        global $DB;
        $datapre = $DB->get_record('notificationsagent_crseview', ['courseid' => self::$course->id]);
        $this->assertNotEmpty($datapre);
        $context = \context_course::instance(self::$course->id);
        provider::delete_data_for_all_users_in_context($context);
        $this->assertFalse($DB->record_exists('notificationsagent_crseview', ['courseid' => self::$course->id]));
    }
    /**
     *  Delete data for user
     * @covers \notificationscondition_sessionstart\privacy\provider::delete_data_for_user
     * @return void
     */
    public function test_delete_data_for_user(): void {
        global $DB;
        $user2 = self::getDataGenerator()->create_user();
        $firstaccess = new coursefirstaccess();
        $firstaccess->set('userid', $user2->id);
        $firstaccess->set('courseid', self::$course->id);
        $firstaccess->set('firstaccess', time());
        $firstaccess->save();

        $context = \context_course::instance(self::$course->id);
        $approvedcontextlist = new approved_contextlist(self::$user, 'notificationscondition_sessionstart', [$context->id]);

        $datauser1 = $DB->get_record('notificationsagent_crseview', ['userid' => self::$user->id]);
        $datauser2 = $DB->get_record('notificationsagent_crseview', ['userid' => $user2->id]);

        $this->assertNotEmpty($datauser1);
        $this->assertNotEmpty($datauser2);

        provider::delete_data_for_user($approvedcontextlist);

        $this->assertFalse($DB->record_exists('notificationsagent_crseview', ['userid' => self::$user->id]));
        $this->assertTrue($DB->record_exists('notificationsagent_crseview', ['userid' => $user2->id]));
    }

    /**
     *  Get users in context
     * @covers \notificationscondition_sessionstart\privacy\provider::get_users_in_context
     * @return void
     */
    public function test_get_users_in_context(): void {

        $user2 = self::getDataGenerator()->create_user();
        $firstaccess = new coursefirstaccess();
        $firstaccess->set('userid', $user2->id);
        $firstaccess->set('courseid', self::$course->id);
        $firstaccess->set('firstaccess', time());
        $firstaccess->save();

        $context = \context_course::instance(self::$course->id);
        $userlist = new userlist($context, 'notificationscondition_sessionstart');

        provider::get_users_in_context($userlist);

        $this->assertCount(2, $userlist->get_userids());
        $this->assertTrue(in_array(self::$user->id, $userlist->get_userids()));
        $this->assertTrue(in_array($user2->id, $userlist->get_userids()));

    }
    /**
     *  Delete data for users
     * @covers \notificationscondition_sessionstart\privacy\provider::delete_data_for_users
     * @return void
     */
    public function test_delete_data_for_users(): void {
        global $DB;

        $user2 = self::getDataGenerator()->create_user();
        $firstaccess = new coursefirstaccess();
        $firstaccess->set('userid', $user2->id);
        $firstaccess->set('courseid', self::$course->id);
        $firstaccess->set('firstaccess', time());
        $firstaccess->save();
        $context = \context_course::instance(self::$course->id);
        $approveduserlist = new approved_userlist($context, 'notificationscondition_sessionstart', [self::$user->id]);
        provider::delete_data_for_users($approveduserlist);
        $this->assertFalse($DB->record_exists('notificationsagent_crseview', ['userid' => self::$user->id]));
        $this->assertTrue($DB->record_exists('notificationsagent_crseview', ['userid' => $user2->id]));
    }
}
