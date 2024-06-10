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
 * @package    local_notificationsagent
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 *
 * Menu element
 *
 * @param navigation_node $parentnode
 * @param stdClass $course
 * @param context_course $context
 *
 * @return void
 */
function local_notificationsagent_extend_navigation_course(navigation_node $parentnode, stdClass $course, context_course $context) {
    if (get_config('local_notificationsagent', 'disable_user_use')) {
        if (!has_capability('local/notificationsagent:managecourserule', $context)) {
            return;
        }
    }

    $menuentrytext = get_string('menu', 'local_notificationsagent');
    $courseid = $course->id;
    $url = '/local/notificationsagent/index.php?courseid=' . $courseid;
    $parentnode->add(
            $menuentrytext,
            new moodle_url($url),
            navigation_node::TYPE_SETTING,
            null,
            "notificationsagent"
    );
    // Add report navigation node.
    $reportnode = $parentnode->get('coursereports');
    if (isset($reportnode) && $reportnode !== false) {
        $reporturl = '/local/notificationsagent/report.php?courseid=' . $courseid;
        $reportnode->add(
                get_string('pluginname', 'local_notificationsagent'), new moodle_url($reporturl), navigation_node::TYPE_SETTING
        );
    }
}
