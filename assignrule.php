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
 *  Version details
 *
 * @package    local_notificationsagent
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("../../config.php");

use local_notificationsagent\rule;

defined('MOODLE_INTERNAL') || die();
require_login();
$idrule = optional_param('ruleid', null, PARAM_INT);
$arraycategories = optional_param_array('category', null, PARAM_RAW);
$arraycourses = optional_param_array('course', null, PARAM_RAW);
$forced = optional_param('forced', null, PARAM_INT);
$action = optional_param('action', null, PARAM_TEXT);
$data = [];

if (!empty($idrule)) {
    if ($action == 'SHOW_CONTEXT') {
        $data = get_list_assigned_context($idrule);
    } else if ($action == 'SET_CONTEXT') {
        add_list_courses_assigned($idrule, $arraycategories, $arraycourses);
        set_forced_rule($idrule, $forced);
    }
}

/**
 * List of courses assigned to a context
 *
 * @param int $idrule
 *
 * @return array|array[]
 * @package local_notificationsagent
 */
function get_list_assigned_context($idrule) {
    $rule = rule::create_instance($idrule);
    $listofcoursesassigned = $rule->get_assignedcontext();
    return $listofcoursesassigned;
}

/**
 * Add list of courses assigned
 *
 * @param int $idrule
 * @param array $categories
 * @param array $courses
 *
 * @return void
 * @package local_notificationsagent
 */
function add_list_courses_assigned($idrule, $categories = [], $courses = []) {
    global $DB;

    $instance = rule::create_instance($idrule);
    $DB->delete_records('notificationsagent_context', ['ruleid' => $idrule]);
    $instance->set_default_context(SITEID);

    if (!empty($categories)) {
        foreach ($categories as $category) {
            $paramscat = [
                    'ruleid' => $idrule,
                    'contextid' => CONTEXT_COURSECAT,
                    'objectid' => $category,
            ];
            $DB->insert_record('notificationsagent_context', $paramscat);
        }
    }

    if (!empty($courses)) {
        foreach ($courses as $course) {
            $paramscourse = [
                    'ruleid' => $idrule,
                    'contextid' => CONTEXT_COURSE,
                    'objectid' => $course,
            ];
            $DB->insert_record('notificationsagent_context', $paramscourse);
        }
    }
}

/**
 * Set forced field of rule
 *
 * @param int $idrule
 * @param int $forced
 *
 * @return void
 * @package local_notificationsagent
 */
function set_forced_rule($idrule, $forced) {
    global $DB;

    $instance = rule::create_instance($idrule);
    $context = \context_course::instance(SITEID);
    if (has_capability('local/notificationsagent:forcerule', $context)) {
        $request = new \stdClass();
        $request->id = $instance->get_id();
        $request->forced = !$forced ? rule::FORCED_RULE : rule::NONFORCED_RULE;

        $DB->update_record('notificationsagent_rule', $request);
    }
}

echo json_encode($data);
exit();
