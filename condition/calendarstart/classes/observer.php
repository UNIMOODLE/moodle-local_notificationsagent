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
 * @package    notificationscondition_calendarstart
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\event\calendar_event_deleted;
use local_notificationsagent\evaluationcontext;
use local_notificationsagent\external\update_rule_status;
use local_notificationsagent\notificationsagent;
use local_notificationsagent\rule;
use notificationscondition_calendarstart\calendarstart;

/**
 * Class notificationscondition_calendarstart_observer
 */
class notificationscondition_calendarstart_observer {

    /**
     * Updates the calendar when a calendar event is updated.
     *
     * @param \core\event\calendar_event_updated $event The calendar event update event.
     */
    public static function calendar_updated(\core\event\calendar_event_updated $event) {
        $pluginname = calendarstart::NAME;
        $cmid = $event->objectid;
        $courseid = $event->courseid;

        $conditions = notificationsagent::get_conditions_by_cm($pluginname, $courseid, $cmid);
        foreach ($conditions as $condition) {
            $subplugin = new calendarstart($condition->ruleid, $condition->id);
            $context = new evaluationcontext();
            $context->set_params($subplugin->get_parameters());
            $context->set_complementary($subplugin->get_iscomplementary());
            $context->set_timeaccess($event->timecreated);
            $context->set_courseid($courseid);

            notificationsagent::generate_cache_triggers($subplugin, $context);
        }
    }

    /**
     * A function to handle the calendar_event_deleted event.
     *
     * @param calendar_event_deleted $event The course module event object
     */
    public static function calendar_event_deleted(calendar_event_deleted $event) {
        global $DB;

        $cmid = $event->objectid;

        //Get rules with conditions with cmid
        $sql = 'SELECT mnc.id, mnc.ruleid AS ruleid, mnc.pluginname
                  FROM {notificationsagent_condition} mnc
                 WHERE mnc.pluginname = :name
                   AND mnc.cmid = :cmid';

        $dataobj = $DB->get_records_sql($sql, [
            'name' => calendarstart::NAME,
            'cmid' => $cmid,
        ]);

        foreach ($dataobj as $data) {
            $subplugin = new calendarstart($data->ruleid, $data->id);
            $result = $subplugin->validation($event->courseid);
            if (!$result) {
                update_rule_status::execute(
                    $data->ruleid, rule::PAUSE_RULE,
                );
            }
        }
    }
}
