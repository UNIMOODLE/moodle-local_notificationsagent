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

use core\event\course_viewed;
use local_notificationsagent\evaluationcontext;
use local_notificationsagent\notificationsagent;
use notificationscondition_sessionstart\sessionstart;

/**
 * Observer for the notificationscondition_sessionstart plugin.
 */
class notificationscondition_sessionstart_observer {
    /**
     * A function to handle the course_viewed event.
     *
     * @param course_viewed $event The course_viewed event object
     */
    public static function course_viewed(course_viewed $event) {
        $pluginname = sessionstart::NAME;
        // Bypass the event hadler if the plugin is disabled.
        if (! local_notificationsagent\plugininfo\notificationscondition::is_plugin_enabled($pluginname)) {
            return;
        }
        if ($event->courseid == SITEID || !isloggedin()) {
            return;
        }

        $userid = $event->userid;
        $courseid = $event->courseid;
        $timeaccess = $event->timecreated;
        // Only triggered if is the first access to a course, otherwise return.
        $firstaccess = sessionstart::get_first_course_access($userid, $courseid);
        if (!empty($firstaccess)) {
            return null;
        }

        // We use this event to avoid querying the log_standard_log for a course firstaccess.
        sessionstart::set_first_course_access($userid, $courseid, $timeaccess);

        $conditions = notificationsagent::get_conditions_by_course($pluginname, $courseid);
        foreach ($conditions as $condition) {
            $subplugin = new sessionstart($condition->ruleid, $condition->id);
            $context = new evaluationcontext();
            $context->set_params($subplugin->get_parameters());
            $context->set_complementary($subplugin->get_iscomplementary());
            $context->set_timeaccess($timeaccess);
            $context->set_courseid($courseid);
            $context->set_userid($userid);

            notificationsagent::generate_cache_triggers($subplugin, $context);
        }
    }
}
