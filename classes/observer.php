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
 * @package    local_notificationsagent
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\event\config_log_created;
use core\event\course_deleted;
use core\event\course_module_deleted;
use local_notificationsagent\external\update_rule_status;
use local_notificationsagent\helper\helper;
use local_notificationsagent\notificationplugin;
use local_notificationsagent\notificationsagent;
use local_notificationsagent\rule;
use notificationscondition_ac\ac;

/**
 * Observer for the notificationscondition_sessionstart plugin.
 */
class local_notificationsagent_observer {
    /**
     * A function to handle the course_module_deleted event.
     *
     * @param course_module_deleted $event The course module event object
     */
    public static function course_module_deleted(course_module_deleted $event) {
        global $DB;

        $cmid = $event->contextinstanceid;

        // Get rules with conditions with cmid.
        $sql = 'SELECT mnc.id, mnc.ruleid AS ruleid, mnc.pluginname
                  FROM {notificationsagent_condition} mnc
                 WHERE (mnc.cmid = :cmid) OR (pluginname = :acname)';

        $dataobj = $DB->get_records_sql($sql, [
                'cmid' => $cmid,
                'acname' => ac::NAME,
        ]);

        foreach ($dataobj as $data) {
            $subplugin = notificationplugin::create_instance(
                $data->id,
                notificationplugin::TYPE_CONDITION,
                $data->pluginname,
                $data->ruleid
            );
            $result = $subplugin->validation($event->courseid);
            if (!$result) {
                update_rule_status::execute(
                    $data->ruleid,
                    rule::PAUSE_RULE,
                );
                helper::broken_rule_notify($event->courseid, $data->ruleid);
            }
        }
    }

    /**
     * A function to handle the config_log_created event.
     *
     * @param config_log_created $event The course module event object
     */
    public static function config_log_created(config_log_created $event) {
        $modifieditem = $event->other['plugin'];
        $notificationscondition = preg_filter(
            '/^/',
            'notificationscondition_',
            array_keys(core_plugin_manager::instance()->get_installed_plugins('notificationscondition'))
        );
        $notificationsaction = preg_filter(
            '/^/',
            'notificationsaction_',
            array_keys(core_plugin_manager::instance()->get_installed_plugins('notificationsaction'))
        );
        if (in_array($modifieditem, $notificationscondition, false) || in_array($modifieditem, $notificationsaction, false)) {
            \cache::make('local_notificationsagent', notificationplugin::TYPE_CONDITION)->purge();
            \cache::make('local_notificationsagent', notificationplugin::TYPE_ACTION)->purge();
        }
    }

    /**
     *  Function for course deleted listener
     *  Delete all reports records from a course when a course delete event is triggered
     *
     * @param course_deleted $event
     *
     * @return void
     */
    public static function course_deleted(course_deleted $event) {
        notificationsagent::delete_all_by_course($event->courseid);
    }
}
