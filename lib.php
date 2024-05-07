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
 * Trace for scheduled tasks. Only meant for debugging
 * Disable in settings for producion environments.
 *
 * @param string $message
 *
 * @return void
 */
function custom_mtrace($message) {
    $tracelog = get_config('local_notificationsagent', 'tracelog');
    if ($tracelog) {
        mtrace($message);
    }
}

/**
 *
 * Menu element
 *
 * @param navigation_node $parentnode
 * @param stdClass        $course
 * @param context_course  $context
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

/**
 * Returns seconds in human format
 *
 * @param integer $seconds Seconds
 * @param bool    $toshow
 *
 * @return array|string $data Time in days, hours, minutes and seconds
 */
function to_human_format($seconds, $toshow = false) {
    $dtF = new \DateTime('@0');
    $dtT = new \DateTime("@$seconds");

    $stringtoshow = [];
    $a = $dtF->diff($dtT)->format('%a') and
    $stringtoshow[] = "$a " . get_string($a > 1 ? 'card_day_plural' : 'card_day', 'local_notificationsagent');
    $h = $dtF->diff($dtT)->format('%h') and
    $stringtoshow[] = "$h " . get_string($h > 1 ? 'card_hour_plural' : 'card_hour', 'local_notificationsagent');
    $i = $dtF->diff($dtT)->format('%i') and
    $stringtoshow[] = "$i " . get_string($i > 1 ? 'card_minute_plural' : 'card_minute', 'local_notificationsagent');
    $s = $dtF->diff($dtT)->format('%s') and
    $stringtoshow[] = "$s " . get_string($s > 1 ? 'card_second_plural' : 'card_second', 'local_notificationsagent');

    if (empty($stringtoshow)) {
        $stringtoshow[] = "0 " . get_string('card_second', 'local_notificationsagent');
    }

    if ($toshow) {
        return implode(",", $stringtoshow);
    }

    return ['days' => $a, 'hours' => $h, 'minutes' => $i, 'seconds' => $s];
}

/**
 * Returns human format in seconds
 *
 * @param array $time Time in days, hours, minutes and seconds
 *
 * @return integer $seconds Seconds
 */
function to_seconds_format($time) {
    $seconds = 0;

    if (isset($time['days']) && $time['days'] != "") {
        $seconds = $time['days'] * 24 * 60 * 60;
    }
    if (isset($time['hours']) && $time['hours'] != "") {
        $seconds += $time['hours'] * 60 * 60;
    }
    if (isset($time['minutes']) && $time['minutes'] != "") {
        $seconds += $time['minutes'] * 60;
    }
    if (isset($time['seconds']) && $time['seconds'] != "") {
        $seconds += $time['seconds'];
    }

    return $seconds;
}

/**
 * Get the URL used to access the course that the instance is in.
 *
 * @param int $id
 *
 * @return moodle_url
 */
function get_course_url($id) {
    return new moodle_url('/course/view.php', ['id' => $id]);
}

/**
 * Get the URL for a specific module in a course.
 *
 * @param int $courseid The ID of the course.
 * @param int $cmid     The ID of the course module.
 *
 * @return moodle_url The URL of the course module.
 */
function get_module_url($courseid, $cmid) {
    return new moodle_url(
        get_fast_modinfo($courseid)->get_cm($cmid)->url->get_path(), ['id' => $cmid]
    );
}

/**
 * Get the direct link to the course or module based on the trigger condition.
 *
 * @param object $context The Evaluation context object
 *
 * @return string The URL of the module or course
 * @throws moodle_exception When there is an error with the condition ID
 */
function get_follow_link($context) {
    $conditions = $context->get_rule()->get_conditions();
    $condition = $conditions[$context->get_triggercondition()] ?? '';
    $cmid = !empty($condition) ? ((json_decode($condition->get_parameters()))->cmid ?? '') : '';

    return !empty($cmid) ? get_module_url($context->get_courseid(), $cmid) : get_course_url($context->get_courseid());
}
