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
 * @package    notificationscondition_activitymodified
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_notificationsagent\notificationsagent;
use notificationscondition_activitymodified\activitymodified;

class notificationscondition_activitymodified_observer {
    public static function course_module_updated(\core\event\course_module_updated $event) {
        if (has_capability('moodle/course:managefiles', \context_course::instance($event->courseid), $event->userid)) {
            if (activitymodified::get_any_new_content(
                $event->objectid,
                $event->timecreated,
            )
            ) {
                $pluginname = 'activitymodified';
                $conditions = notificationsagent::get_conditions_by_cm($pluginname, $event->courseid, $event->objectid);
                foreach ($conditions as $condition) {
                    notificationsagent::set_timer_cache(
                        notificationsagent::GENERIC_USERID,
                        $event->courseid,
                        $event->timecreated,
                        $pluginname,
                        $condition->id,
                        true
                    );
                    notificationsagent::set_time_trigger(
                        $condition->ruleid,
                        $condition->id,
                        notificationsagent::GENERIC_USERID,
                        $event->courseid,
                        $event->timecreated
                    );
                }
            }
        }
    }
}