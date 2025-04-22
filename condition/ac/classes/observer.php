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
 * @package    notificationscondition_ac
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_notificationsagent\external\update_rule_status;
use local_notificationsagent\helper\helper;
use local_notificationsagent\rule;
use notificationscondition_ac\ac;

/**
 * Event handler for ac subplugin.
 */
class notificationscondition_ac_observer {
    /**
     * Triggered when 'group_deleted' event is triggered.
     *
     * @param \core\event\group_deleted $event
     */
    public static function group_deleted(\core\event\group_deleted $event) {
        global $DB;
        // Bypass the event hadler if the plugin is disabled.
        if (! local_notificationsagent\plugininfo\notificationscondition::is_plugin_enabled(ac::NAME)) {
            return;
        }
        $sql = 'SELECT mnc.id, mnc.ruleid AS ruleid
                  FROM {notificationsagent_condition} mnc
                 WHERE mnc.pluginname = :name';

        $dataobj = $DB->get_records_sql($sql, [
                'name' => ac::NAME,
        ]);

        foreach ($dataobj as $data) {
            $subplugin = new ac($data->ruleid, $data->id);
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
     * Triggered when 'grouping_deleted' event is triggered.
     *
     * @param \core\event\grouping_deleted $event
     */
    public static function grouping_deleted(\core\event\grouping_deleted $event) {
        global $DB;

        $sql = 'SELECT mnc.id, mnc.ruleid AS ruleid
                  FROM {notificationsagent_condition} mnc
                 WHERE mnc.pluginname = :name';

        $dataobj = $DB->get_records_sql($sql, [
                'name' => ac::NAME,
        ]);

        foreach ($dataobj as $data) {
            $subplugin = new ac($data->ruleid, $data->id);
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
}
