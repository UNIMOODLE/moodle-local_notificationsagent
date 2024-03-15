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
 * @package    notificationscondition_calendareventto
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_notificationsagent\notificationsagent;
use local_notificationsagent\notificationplugin;

/**
 * Observer for calendareventto condition
 */
class notificationscondition_calendareventto_observer {

    /**
     * Listen calendar updated event
     *
     * @param \core\event\calendar_event_updated $event
     *
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     */
    public static function calendar_updated(core\event\calendar_event_updated $event) {
        global $DB;
        if (!isloggedin() || $event->courseid == 1) {
            return;
        }

        $other = $event->other;
        $courseid = $event->courseid;

        // If stardate is not set in other array then the startdate setting has not been modified.
        if (isset($other["timestart"])) {
            $startdate = $other["timestart"];
        } else {
            return;
        }

        $pluginname = get_string('subtype', 'notificationscondition_calendareventto');
        $conditions = notificationsagent::get_conditions_by_course($pluginname, $courseid);

        foreach ($conditions as $condition) {
            $decode = $condition->parameters;
            $pluginname = $condition->pluginname;
            $condtionid = $condition->id;
            $param = json_decode($decode, true);
            $cache = $startdate - $param[notificationplugin::UI_TIME];

            if (!notificationsagent::was_launched_indicated_times(
                $condition->ruleid, $condition->ruletimesfired, $courseid, notificationsagent::GENERIC_USERID
            )
            ) {
                notificationsagent::set_timer_cache(
                    notificationsagent::GENERIC_USERID, $courseid, $cache, $pluginname, $condtionid, true
                );
                notificationsagent::set_time_trigger(
                    $condition->ruleid, $condtionid, notificationsagent::GENERIC_USERID, $courseid, $cache
                );
            }
        }
    }
}
