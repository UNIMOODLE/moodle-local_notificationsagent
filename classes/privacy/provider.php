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
// Produced by the UNIMOODLE University Group: Universities of
// Valladolid, Complutense de Madrid, UPV/EHU, León, Salamanca,
// Illes Balears, Valencia, Rey Juan Carlos, La Laguna, Zaragoza, Málaga,
// Córdoba, Extremadura, Vigo, Las Palmas de Gran Canaria y Burgos.

/**
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
use core_privacy\local\request\contextlist;
use core_privacy\local\request\userlist;
use core_privacy\local\request;

/**
 * Provider user data class
 */
class provider implements \core_privacy\local\metadata\provider,
                          \core_privacy\local\request\core_userlist_provider,
                          \core_privacy\local\request\subsystem\provider {
    /**
     * Privacy  notifications agent  provider.
     *
     * @param collection $collection Collection of the plugin
     *
     * @return collection Collection object
     */
    public static function get_metadata(collection $collection): collection {
        $reportdata = [
            'userid' => 'privacy:metadata:userid',
            'courseid' => 'privacy:metadata:courseid',
            'ruleid' => 'privacy:metadata:ruleid',
            'actionid' => 'privacy:metadata:actionid',
            'actiondetail' => 'privacy:metadata:actiondetail',
            'timestamp' => 'privacy:metadata:timestamp',
        ];

        $collection->add_database_table(
            'notificationsagent_report',
            $reportdata,
            'privacy:metadata:notificationsagentreport'
        );

        return $collection;
    }

    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * @param int $userid The user to search.
     *
     * @return  contextlist   $contextlist  The contextlist containing the list of contexts used in this plugin.
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $params = ['userid' => $userid, 'contextcourse' => CONTEXT_COURSE];
        $sql = "SELECT ctx.id
                       FROM {context} ctx
                       JOIN {notificationsagent_report} nar ON nar.courseid = ctx.instanceid AND nar.userid = :userid
                   WHERE ctx.contextlevel = :contextcourse";

        $contextlist = new contextlist();
        $contextlist->add_from_sql($sql, $params);
        return $contextlist;
    }

    /**
     * Export all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts to export information for.
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;
        if ($contextlist->get_component() != 'local_notificationsagent') {
            return;
        }
        $contexts = array_filter($contextlist->get_contexts(), function($context) {
            if ($context->contextlevel == CONTEXT_COURSE) {
                return $context;
            }
        });

        foreach ($contexts as $context) {
            $userid = $contextlist->get_user()->id;
            $exportdata = [];

            $records = $DB->get_records('notificationsagent_report', ['userid' => $userid]);
            foreach ($records as $record) {
                // Export only the data that does not expose internal information.
                $data = [];
                $data['ruleid'] = $record->ruleid;
                $data['userid'] = $record->userid;
                $data['courseid'] = $record->courseid;
                $data['actionid'] = $record->actionid;
                $data['actiondetail'] = $record->actiondetail;
                $data['timestamp'] = $record->timestamp;

                $exportdata[] = $data;
            }

            request\writer::with_context($context)->export_data(
                [get_string('privacy:metadata:localnotificationsagentreport', 'local_notificationsagent')],
                (object) $exportdata
            );
        }
    }

    /**
     * Delete all data for all users in the specified context.
     *
     * @param \context $context The specific context to delete data for.
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        if ($context->contextlevel == CONTEXT_COURSE) {
            static::delete_user_report($context->instanceid);
        }
    }

    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts and user information to delete information for.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        if ($contextlist->get_component() != 'local_notificationsagent') {
            return;
        }
        foreach ($contextlist->get_contexts() as $context) {
            if ($context->contextlevel == CONTEXT_COURSE) {
                static::delete_user_report($context->instanceid, $contextlist->get_user()->id);
            }
        }
    }

    /**
     * Get the list of users who have data within a context.
     *
     * @param userlist $userlist The userlist containing the list of users who have data in this context/plugin combination.
     */
    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();

        if (!$context instanceof \context_course) {
            return;
        }

        $params = ['courseid' => $context->instanceid];

        $sql = "SELECT userid FROM {notificationsagent_report} WHERE courseid = :courseid";
        $userlist->add_from_sql('userid', $sql, $params);
    }

    /**
     * Delete multiple users within a single context.
     *
     * @param approved_userlist $userlist The approved context and user information to delete information for.
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        global $DB;

        $context = $userlist->get_context();

        if ($context instanceof \context_course) {
            $userids = $userlist->get_userids();
            if (!empty($userids)) {
                list($usersql, $userparams) = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED);
                $select = "courseid = :courseid AND userid {$usersql}";
                $params = ['courseid' => $context->instanceid] + $userparams;
                $DB->delete_records_select('notificationsagent_report', $select, $params);
            }
        }
    }

    /**
     * Deletes notification report for a given user.
     *
     * @param int      $courseid
     * @param int|null $userid User id to delete..
     *
     */
    protected static function delete_user_report(int $courseid, int $userid = null) {
        global $DB;
        $params = (isset($userid)) ? ['courseid' => $courseid, 'userid' => $userid] : ['courseid' => $courseid];
        $DB->delete_records('notificationsagent_report', $params);
    }

}
