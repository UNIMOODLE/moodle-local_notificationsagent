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

/**
 * Assign rule
 *
 * @package    local_notificationsagent
 * @copyright  2023 UNIMOODLE
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 require_once("../../config.php");

 $idrule = optional_param('idRule', null, PARAM_INT);
 $arraycourses = optional_param_array('arrayCourses', null, PARAM_RAW);
 

 if (isset($idrule) && !isset($arraycourses)) {
     echo json_encode(get_list_courses_assigned($idrule));
 } 
 if (isset($arraycourses)) {
    echo json_encode(add_list_courses_assigned($idrule, $arraycourses));

 } 


 function get_list_courses_assigned($idrule) {
    global $DB;
    $courseid = $DB->get_field('notificationsagent_rule', 'courseid', array('id' => $idrule));
    $listofcoursesassigned = [$courseid];
    return $listofcoursesassigned;
}



function add_list_courses_assigned($idrule, $arraycourses) {
   global $DB, $USER;
    $rule = $DB->get_record('notificationsagent_rule', array('id' => $idrule));
    $conditions = $DB->get_records('notificationsagent_condition', array('id' => $idrule));
    $actions = $DB->get_records('notificationsagent_action', array('id' => $idrule));

    foreach($arraycourses as $array){
        if($array !== $rule->courseid){
            $data = new stdClass;
            $data->courseid = $array;
            $data->name = $rule->name;
            $data->createdat = time();
            $data->createdby = $USER->id;
            $id = $DB->insert_record('notificationsagent_rule', $data);

            foreach($conditions as $condition){
                $condition->ruleid = $id;
                $DB->insert_record('notificationsagent_condition', $condition);
            }
            foreach($actions as $action){
                $action->ruleid = $id;
                $DB->insert_record('notificationsagent_action', $action);
            }
        }
    }

   $addlistofcoursesassigned = $id; 
   return $addlistofcoursesassigned;


}