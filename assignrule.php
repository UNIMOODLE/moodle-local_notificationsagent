<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
// Project implemented by the \"Recovery, Transformation and Resilience Plan.
// Funded by the European Union - Next GenerationEU\".
//
// Produced by the UNIMOODLE University Group: Universities of
// Valladolid, Complutense de Madrid, UPV/EHU, León, Salamanca,
// Illes Balears, Valencia, Rey Juan Carlos, La Laguna, Zaragoza, Málaga,
// Córdoba, Extremadura, Vigo, Las Palmas de Gran Canaria y Burgos

/**
 * Version details
 *
 * @package    local_notificationsagent
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once("../../config.php");
require_once("classes/rule.php");

use local_notificationsagent\Rule;

$idrule = optional_param('ruleid', null, PARAM_INT);
$arraycategories = optional_param_array('category', null, PARAM_RAW);
$arraycourses = optional_param_array('course', null, PARAM_RAW);
 
if (!empty($idrule)) {
    echo json_encode(get_list_assigned_context($idrule));
} 

if (!empty($idrule) && (isset($arraycourses) || isset($arraycategories))) {
    add_list_courses_assigned($idrule, $arraycategories, $arraycourses);
} 

 function get_list_assigned_context($idrule) {
    $rule = Rule::create_instance($idrule);
    $listofcoursesassigned = $rule->get_assignedcontext();
    return $listofcoursesassigned;
}

function add_list_courses_assigned($idrule, $categories = [], $courses = []) {
    global $DB;

    $DB->delete_records('notificationsagent_context', ['ruleid' => $idrule]);

    if (!empty($categories)) {
        foreach ($categories as $category) {
            $paramscat = array (
                'ruleid' => $idrule,
                'contextid' => CONTEXT_COURSECAT,
                'objectid' => $category
            );
            $DB->insert_record('notificationsagent_context', $paramscat);
        }
    }

    if (!empty($courses)) {
        $rule = Rule::create_instance($idrule);
        foreach ($courses as $course) { 
            if ($course != $rule->get_courseid()) {
                $paramscourse = array (
                    'ruleid' => $idrule,
                    'contextid' => CONTEXT_COURSE,
                    'objectid' => $course
                );
                $DB->insert_record('notificationsagent_context', $paramscourse);
            }
        }
    }
}
