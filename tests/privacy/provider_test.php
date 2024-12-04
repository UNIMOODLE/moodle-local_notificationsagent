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

namespace local_notificationsagent\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;
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
     *  Random id for activity
     */
    public const CMID = 246000;
    /**
     * Component name
     */
    public const COMPONENT = 'local_notificationsagent';

    /**
     * Settin up test context
     *
     * @return void
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

        $result = $DB->insert_record('notificationsagent_report', $report);
        $this->assertIsInt($result);

        $rule = new \stdClass();
        $rule->ruleid = 1;
        $rule->createdby = self::$user->id;
        $rule->createdat = time();
        $result = $ruleid = $DB->insert_record('notificationsagent_rule', $rule);
        $this->assertIsInt($result);

        $launched = new \stdClass();
        $launched->ruleid = $ruleid;
        $launched->courseid = self::$course->id;
        $launched->userid = self::$user->id;
        $launched->timesfired = 3;
        $launched->timecreated = time();
        $launched->timemodified = time();
        $result = $DB->insert_record('notificationsagent_launched', $launched);
        $this->assertIsInt($result);

        $cache = new \stdClass();
        $cache->userid = self::$user->id;
        $result = $DB->insert_record('notificationsagent_cache', $cache);
        $this->assertIsInt($result);

        $triggers = new \stdClass();
        $triggers->userid = self::$user->id;
        $triggers->ruleid = $ruleid;
        $triggers->conditionid = 2;
        $triggers->courseid = self::$course->id;
        $result = $DB->insert_record('notificationsagent_triggers', $triggers);
        $this->assertIsInt($result);

        $context = new \stdClass();
        $context->ruleid = $ruleid;
        $context->contextid = CONTEXT_COURSE;
        $context->objectid = self::$course->id;
        $result = $DB->insert_record('notificationsagent_context', $context);
        $this->assertIsInt($result);

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
        $contextlist = provider::get_contexts_for_userid(self::$user->id);
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
    public function test_delete_data_for_all_users_in_context() {
        global $DB;
        $report = $DB->get_records('notificationsagent_report', ['courseid' => self::$course->id]);
        $launched = $DB->get_records('notificationsagent_launched', ['userid' => self::$user->id]);
        $triggers = $DB->get_records('notificationsagent_triggers', ['userid' => self::$user->id]);
        $params = ['objectid' => self::$course->id, 'contextid' => CONTEXT_COURSE];
        $contexts = $DB->get_records('notificationsagent_context' , $params, '', 'ruleid');

        foreach ($contexts as $context) {
            $getrule = $DB->get_records('notificationsagent_rule', ['id' => $context->ruleid, 'createdby' => self::$user->id]);
            $this->assertNotEmpty($getrule);
        }

        $this->assertNotEmpty($report);
        $this->assertNotEmpty($launched);
        $this->assertNotEmpty($triggers);
        $context = \context_course::instance(self::$course->id);
        provider::delete_data_for_all_users_in_context($context);

        $deletereport = $DB->get_records('notificationsagent_report', ['courseid' => self::$course->id]);
        $deletelaunched = $DB->get_records('notificationsagent_launched', ['userid' => self::$user->id]);
        $deletetriggers = $DB->get_records('notificationsagent_triggers', ['userid' => self::$user->id]);
        $rulestodelete = $DB->get_records('notificationsagent_context' , $params, '', 'id');

        foreach ($rulestodelete as $ruletodelete) {
            $deleterule = $DB->get_records('notificationsagent_rule', ['id' => $ruletodelete->id]);
            $this->assertEmpty($deleterule);
        }

        $this->assertEmpty($deletereport);
        $this->assertEmpty($deletelaunched);
        $this->assertEmpty($deletetriggers);

    }

    /**
     * Test for deleting data of user
     *
     * @return void
     * @covers \local_notificationsagent\privacy\provider::delete_data_for_user
     */
    public function test_delete_data_for_user() {
        global $DB;
        $contextlist = provider::get_contexts_for_userid(self::$user->id);
        $emptyapprvlist = new approved_contextlist(self::$user, 'mod_quiz', [$contextlist->get_contexts()[0]->id]);
        provider::delete_data_for_user($emptyapprvlist);
        $report = $DB->get_records('notificationsagent_report', ['courseid' => self::$course->id]);
        $launched = $DB->get_records('notificationsagent_launched', ['userid' => self::$user->id]);
        $triggers = $DB->get_records('notificationsagent_triggers', ['userid' => self::$user->id]);
        $params = ['objectid' => self::$course->id, 'contextid' => CONTEXT_COURSE];
        $contexts = $DB->get_records('notificationsagent_context' , $params, '', 'ruleid');
        foreach ($contexts as $context) {
            $getrule = $DB->get_records('notificationsagent_rule', ['id' => $context->ruleid, 'createdby' => self::$user->id]);
            $this->assertNotEmpty($getrule);
        }

        $this->assertNotEmpty($report);
        $this->assertNotEmpty($report);
        $this->assertNotEmpty($launched);
        $this->assertNotEmpty($triggers);
        $this->assertNotEmpty($contextlist);
        $apprvlist = new approved_contextlist(self::$user, self::COMPONENT, [$contextlist->get_contexts()[0]->id]);
        $this->assertNotEmpty($apprvlist);
        provider::delete_data_for_user($apprvlist);
        $deletereport = $DB->get_records('notificationsagent_report', ['courseid' => self::$course->id]);
        $deletedlaunched = $DB->get_records('notificationsagent_launched', ['userid' => self::$user->id]);
        $deletedtriggers = $DB->get_records('notificationsagent_triggers', ['userid' => self::$user->id]);
        foreach ($contexts as $context) {
            $deletedrule = $DB->get_records('notificationsagent_rule', ['id' => $context->ruleid, 'createdby' => self::$user->id]);
            $this->assertEmpty($deletedrule);
        }
        $this->assertEmpty($deletereport);
        $this->assertEmpty($deletedtriggers);
        $this->assertEmpty($deletedlaunched);
    }

    /**
     * Test for deleting data of users
     *
     * @covers \local_notificationsagent\privacy\provider::delete_data_for_users
     * @return void
     */
    public function test_delete_data_for_users() {
        global $DB;
        $context = \context_course::instance(self::$course->id);
        $apprvlist = new approved_userlist($context, self::COMPONENT, [self::$user->id]);
        $report = $DB->get_records('notificationsagent_report', ['courseid' => self::$course->id]);
        $launched = $DB->get_records('notificationsagent_launched', ['userid' => self::$user->id]);
        $triggers = $DB->get_records('notificationsagent_triggers', ['userid' => self::$user->id]);
        $params = ['objectid' => self::$course->id, 'contextid' => CONTEXT_COURSE];
        $contexts = $DB->get_records('notificationsagent_context' , $params, '', 'ruleid');
        foreach ($contexts as $context) {
            $getrule = $DB->get_records('notificationsagent_rule', ['id' => $context->ruleid, 'createdby' => self::$user->id]);
            $this->assertNotEmpty($getrule);
        }

        $this->assertNotEmpty($report);
        $this->assertNotEmpty($report);
        $this->assertNotEmpty($launched);
        $this->assertNotEmpty($triggers);

        provider::delete_data_for_users($apprvlist);
        $deletereport = $DB->get_records('notificationsagent_report', ['courseid' => self::$course->id]);
        $deletedlaunched = $DB->get_records('notificationsagent_launched', ['userid' => self::$user->id]);
        $deletedtriggers = $DB->get_records('notificationsagent_triggers', ['userid' => self::$user->id]);
        $contexts = $DB->get_records('notificationsagent_context' , $params, '', 'ruleid');
        foreach ($contexts as $context) {
            $deletedrule = $DB->get_records('notificationsagent_rule', ['id' => $context->ruleid, 'createdby' => self::$user->id]);
            $this->assertEmpty($deletedrule);
        }
        $this->assertEmpty($deletereport);
        $this->assertEmpty($deletedtriggers);
        $this->assertEmpty($deletedlaunched);
    }

    /**
     * Test for exporting data of user
     *
     * @covers \local_notificationsagent\privacy\provider::export_user_data
     * @return void
     */
    public function test_export_user_data() {
        $contextlist = provider::get_contexts_for_userid(self::$user->id);
        $apprvlist = new approved_contextlist(self::$user, self::COMPONENT, [$contextlist->get_contexts()[0]->id]);
        $this->assertNotEmpty($apprvlist);
        $this->assertEquals(self::COMPONENT, $apprvlist->get_component());

        provider::export_user_data($apprvlist);

        $tables = [
            'notificationsagent_rule',
            'notificationsagent_launched' ,
            'notificationsagent_cache' ,
            'notificationsagent_triggers' ,
            'notificationsagent_report' ,
        ];
        foreach ($tables as $table) {
            foreach ($contextlist as $context) {
                $data = writer::with_context($context)->get_data(
                    [$table]
                );
                $this->assertNotEmpty($data);
            }
        }

        $delapprvlist = new approved_contextlist(self::$user, 'mod_quiz', [$contextlist->get_contexts()[0]->id]);
        provider::export_user_data($delapprvlist);
        $this->assertEquals('mod_quiz', $delapprvlist->get_component());
        $this->assertNotEmpty($delapprvlist);
    }
}
