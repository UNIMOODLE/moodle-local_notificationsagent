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
 * @package    notificationsaction_bootstrapnotifications
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace notificationsaction_bootstrapnotifications\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\core_userlist_provider;
use core_privacy\local\request\userlist;
use core_privacy\local\request;
/**
 *  Privacy provider class
 */
class provider implements
    core_userlist_provider,
    \core_privacy\local\metadata\provider,
    request\subsystem\provider {

    /**
     * Privacy provider for bootstrapnotifications
     *
     * @param collection $collection
     * @return collection
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table(
            'notificationsagent_bootstrap',
            [
                'userid' => 'privacy:metadata:notificationsagent_bootstrap:userid',
                'courseid' => 'privacy:metadata:notificationsagent_bootstrap:courseid',
                'message' => 'privacy:metadata:notificationsagent_bootstrap:message',
            ],
            'privacy:metadata:notificationsagent_bootstrap'
        );

        return $collection;
    }

    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * @param int $userid El ID del usuario.
     * @return contextlist
     */
    public static function get_contexts_for_userid(int $userid): \core_privacy\local\request\contextlist {
        $contextlist = new contextlist();
        $sql = "SELECT ctx.id
                      FROM {context} ctx
                       JOIN {notificationsagent_bootstrap} t ON t.courseid = ctx.instanceid
                    WHERE t.userid = :userid";
        $params = ['userid' => $userid];

        return $contextlist->add_from_sql($sql, $params);
    }

    /**
     * Export user data
     *
     * @param approved_contextlist $contextlist La lista de contextos aprobados.
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;

        $userid = $contextlist->get_user()->id;
        $records = $DB->get_records('notificationsagent_bootstrap', ['userid' => $userid]);
        foreach ($contextlist->get_contexts() as $context) {

            foreach ($records as $record) {
                request\writer::with_context($context)
                    ->export_data(
                        ['notificationsagent_bootstrap'], $record
                    );
            }
        }
    }
    /**
     * Delete data from users in a context
     *
     * @param \context $context
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;

        if ($context instanceof \context_course) {
            $DB->delete_records('notificationsagent_bootstrap', ['courseid' => $context->instanceid]);
        }
    }

    /**
     * Delete data from user
     *
     * @param approved_contextlist $contextlist
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;

        $userid = $contextlist->get_user()->id;

        foreach ($contextlist->get_contexts() as $context) {
            if ($context instanceof \context_course) {
                $DB->delete_records('notificationsagent_bootstrap', [
                    'userid' => $userid,
                    'courseid' => $context->instanceid,
                ]);
            }
        }
    }

    /**
     * Get user list in a context
     *
     * @param userlist $userlist Userlist in a context.
     */
    public static function get_users_in_context(userlist $userlist) {
        global $DB;

        $context = $userlist->get_context();

        // Asegúrate de verificar si el contexto es del tipo que manejas (por ejemplo, un curso).
        if ($context instanceof \context_course) {
            $sql = "SELECT userid
                      FROM {notificationsagent_bootstrap}
                     WHERE courseid = :courseid";
            $params = ['courseid' => $context->instanceid];

            $users = $DB->get_records_sql($sql, $params);

            foreach ($users as $user) {
                $userlist->add_user($user->userid);
            }
        }
    }

    /**
     * Delete users data
     *
     * @param approved_userlist $userlist Lista aprobada de usuarios para eliminar datos.
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        global $DB;

        $context = $userlist->get_context();

        // Verifica si el contexto es del tipo que maneja este plugin.
        if ($context instanceof \context_course) {
            $courseid = $context->instanceid;

            foreach ($userlist->get_userids() as $userid) {
                // Elimina los registros del usuario en el contexto específico.
                $DB->delete_records('notificationsagent_bootstrap', [
                    'userid' => $userid,
                    'courseid' => $courseid,
                ]);
            }
        }
    }
}
