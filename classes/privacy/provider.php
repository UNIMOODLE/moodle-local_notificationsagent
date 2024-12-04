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
use core_privacy\local\request\contextlist;
use core_privacy\local\request\userlist;
use core_privacy\local\request;

/**
 * Provider user data class
 */
class provider implements
    \core_privacy\local\request\core_userlist_provider,
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\subsystem\provider {
    /**
     * Privacy  notifications agent  provider.
     *
     * @param collection $collection Collection of the plugin
     *
     * @return collection Collection object
     */
    public static function get_metadata(collection $collection): collection {

        $collection->add_database_table(
            'notificationsagent_rule',
            [
                'createdby' => 'privacy:metadata:createdby',
                'createdat' => 'privacy:metadata:createdat',
            ],
            'privacy:metadata:notificationsagentrule'
        );

        $collection->add_database_table(
            'notificationsagent_launched',
            [
                'userid' => 'privacy:metadata:notificationsagent_launched:userid',
                'timesfired' => 'privacy:metadata:notificationsagent_launched:timesfired',
                'timecreated' => 'privacy:metadata:notificationsagent_launched:timecreated',
                'timemodified' => 'privacy:metadata:notificationsagent_launched:timemodified',
            ],
            'privacy:metadata:notificationsagent_launched'
        );

        $collection->add_database_table(
            'notificationsagent_cache',
            [
                'userid' => 'privacy:metadata:notificationsagent_cache:userid',
                'startdate' => 'privacy:metadata:notificationsagent_cache:startdate',
                'cache' => 'privacy:metadata:notificationsagent_cache:cache',
            ],
            'privacy:metadata:notificationsagent_cache'
        );

        $collection->add_database_table(
            'notificationsagent_triggers',
            [
                'userid' => 'privacy:metadata:notificationsagent_triggers:userid',
                'startdate' => 'privacy:metadata:notificationsagent_triggers:startdate',
                'ruleoff' => 'privacy:metadata:notificationsagent_triggers:ruleoff',
            ],
            'privacy:metadata:notificationsagent_triggers'
        );

        $collection->add_database_table(
            'notificationsagent_report',
            [
                'userid' => 'privacy:metadata:userid',
                'courseid' => 'privacy:metadata:courseid',
                'ruleid' => 'privacy:metadata:ruleid',
                'actionid' => 'privacy:metadata:actionid',
                'actiondetail' => 'privacy:metadata:actiondetail',
                'timestamp' => 'privacy:metadata:timestamp',
            ],
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
        $contextlist = new contextlist();
        $params = ['userid' => $userid];
        $sql = "SELECT ctx.id
                  FROM {notificationsagent_rule} nar
                  JOIN {context} ctx ON ctx.instanceid = nar.id
                 WHERE nar.createdby = :userid";

        $contextlist->add_from_sql($sql, $params);

        $params = ['userid' => $userid, 'contextcourse' => CONTEXT_COURSE];
        $sql = "SELECT ctx.id
                       FROM {context} ctx
                       JOIN {notificationsagent_report} nar ON nar.courseid = ctx.instanceid AND nar.userid = :userid
                   WHERE ctx.contextlevel = :contextcourse";

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
        $contexts = array_filter($contextlist->get_contexts(), function ($context) {
            if ($context->contextlevel == CONTEXT_COURSE) {
                return $context;
            }
        });
        $userid = $contextlist->get_user()->id;
        $tables = [
            'notificationsagent_rule' => ['createdby' => $userid],
            'notificationsagent_launched' => ['userid' => $userid],
            'notificationsagent_cache' => ['userid' => $userid],
            'notificationsagent_triggers' => ['userid' => $userid],
            'notificationsagent_report' => ['userid' => $userid],
        ];

        foreach ($tables as $table => $conditions) {
            $records = $DB->get_records($table, $conditions);
            foreach ($records as $record) {
                foreach ($contexts as $context) {
                    request\writer::with_context($context)->export_data( [$table], $record);
                }
            }
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
                [$usersql, $userparams] = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED);
                $select = "courseid = :courseid AND userid {$usersql}";
                $params = ['courseid' => $context->instanceid] + $userparams;
                $DB->delete_records_select('notificationsagent_report', $select, $params);
                $DB->delete_records_select('notificationsagent_launched', $select, $params);
                $DB->delete_records_select('notificationsagent_triggers', $select, $params);
                $params = ['objectid' => $context->instanceid, 'contextid' => $context->contextlevel];
                $rulestodelete = $DB->get_records('notificationsagent_context' , $params, '', 'ruleid');
                foreach ($rulestodelete as $ruletodelete) {
                    foreach ($userids as $userid) {
                        $DB->delete_records('notificationsagent_rule', ['id' => $ruletodelete->ruleid, 'createdby' => $userid]);
                    }
                }
            }
        }
    }

    /**
     * Deletes notification report for a given user.
     *
     * @param int $courseid
     * @param int|null $userid User id to delete..
     *
     */
    protected static function delete_user_report(int $courseid, ?int $userid = null) {
        global $DB;
        $params = (isset($userid)) ? ['courseid' => $courseid, 'userid' => $userid] : ['courseid' => $courseid];
        $DB->delete_records('notificationsagent_report', $params);
        $DB->delete_records('notificationsagent_launched', $params);
        $DB->delete_records('notificationsagent_triggers', $params);

            $params = ['objectid' => $courseid, 'contextid' => CONTEXT_COURSE];
            $rulestodelete = $DB->get_records('notificationsagent_context' , $params, '', 'ruleid');
        foreach ($rulestodelete as $ruletodelete) {
            if (!isset($userid)) {
                $DB->delete_records('notificationsagent_rule', ['id' => $ruletodelete->ruleid]);
            } else {
                $DB->delete_records('notificationsagent_rule', ['id' => $ruletodelete->ruleid, 'createdby' => $userid]);
            }
        }
    }


}
