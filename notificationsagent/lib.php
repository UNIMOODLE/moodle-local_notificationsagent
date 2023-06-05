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
function custom_mtrace($message) {
    $tracelog = get_config('notificationsagent', 'tracelog');
    if ($tracelog) {
        mtrace($message);
    }
}

/**
 * @throws coding_exception
 * @throws moodle_exception
 */
function local_notificationsagent_extend_navigation_course(navigation_node $parentnode, stdClass $course, context_course $context) {
    $menu_entry_text = get_string('menu', 'local_notificationsagent');
    $courseid = $course->id;
    $url ='/local/notificationsagent/index.php?courseid='.$courseid;
    $parentnode->add(
        $menu_entry_text,
        new moodle_url($url),
        navigation_node::TYPE_SETTING,
        null,
        "notificationsagent"
    );
}


/**
 * @param $courseid
 *
 * @return array
 * @throws dml_exception
 */
function get_all_course_modules($courseid) {
    global $DB;
    $coursemods = get_course_mods($courseid);
    $result = array();
    if($coursemods) {
        foreach($coursemods as $coursemod) {
            $coursemod->course_module_instance = $DB->get_record($coursemod->modname, array('id' =>$coursemod->instance ));
            $result[$coursemod->id] = $coursemod;
        }
    }
    return $result;
}

/**
 * @return array[]
 */
function get_all_conditions($courseid){
    $listconditions = array();

    $hasModules = false;
    $list_modules = get_all_course_modules($courseid);
    if(!empty($list_modules)){
        $listconditions[] = array(
            'title'=> get_string('editrule_condition_title_tocloseactivity', 'local_notificationsagent'),
            'elements' => array('[TTTT]','[AAAA]')
        );
        $listconditions[] = array(
            'title'=> get_string('editrule_condition_title_usercompleteactivity', 'local_notificationsagent'),
            'elements' => array('[AAAA]')
        );
        $listconditions[] = array(
            'title'=> get_string('editrule_condition_title_activeactivity', 'local_notificationsagent'),
            'elements' => array('[AAAA]')
        );
        $listconditions[] = array(
            'title'=> get_string('editrule_condition_title_accessforumactivitiescomplete', 'local_notificationsagent'),
            'elements' => array('[FFFF]')
        );
    }
    $listconditions[] = array(
        'title'=> get_string('editrule_condition_title_forumnotanswer', 'local_notificationsagent'),
        'elements' => array('[FFFF]', '[TTTT]')
    );

    $listconditions[] = array(
        'title'=> get_string('editrule_condition_title_betweendates', 'local_notificationsagent'),
        'elements' => array('[FFFF]','[FFFF]')
    );
    
    return $listconditions;
}

/**
 * @return array[]
 */
function get_all_actions($courseid){
    global $DB, $USER, $COURSE;
    $listactions = array();

    $hasUsers = false;
    $context = \context_course::instance($courseid);
    $enrolledusers = get_enrolled_users($context);
    if(!empty($enrolledusers)){
        $hasUsers = true;
    }

    $hasForums = false;
    $modinfo = get_fast_modinfo($COURSE);
    $forums = $DB->get_records_sql("
        SELECT f.*,
            d.maildigest
        FROM {forum} f
        LEFT JOIN {forum_digests} d ON d.forum = f.id AND d.userid = ?
        WHERE f.course = ?
    ", array($USER->id, $courseid));
    if(!empty($modinfo->get_instances_of('forum'))){
        $hasForums = true;
    }

    $hasGroups = false;
    $list_groups  = groups_get_all_groups($courseid);
    if(!empty($list_groups)){
        $hasGroups = true;
    }

    $listactions[] = array(
        'title'=> get_string('editrule_action_title_individualnotification', 'local_notificationsagent'),
        'elements' => array('[TTTT]','[BBBB]')
    );

    if($hasUsers){
        $listactions[] = array(
            'title'=> get_string('editrule_action_title_notificationtouser', 'local_notificationsagent'),
            'elements' => array('[UUUU]','[TTTT]','[BBBB]')
        );
    }

    if($hasForums){
        $listactions[] = array(
            'title'=> get_string('editrule_action_title_postgeneralforum', 'local_notificationsagent'),
            'elements' => array('[FFFF]','[TTTT]','[BBBB]')
        );
        $listactions[] = array(
            'title'=> get_string('editrule_action_title_postprivateforum', 'local_notificationsagent'),
            'elements' => array('[FFFF]','[TTTT]','[BBBB]')
        );
    }

    if($hasGroups){
        $listactions[] = array(
            'title'=> get_string('editrule_action_title_addusertogroup', 'local_notificationsagent'),
            'elements' => array('[GGGG]')
        );
        $listactions[] = array(
            'title'=> get_string('editrule_action_title_removeuserfromgroup', 'local_notificationsagent'),
            'elements' => array('[GGGG]')
        );
    }

    $listactions[] = array(
        'title'=> get_string('editrule_action_title_bootstrapnotification', 'local_notificationsagent'),
        'elements' => array('[TTTT]','[BBBB]')
    );
    
    return $listactions;
}

/**
 * Cambiar orden elemento array
 */
/*function moveElementArray(&$array, $from, $to) {
    $out = array_splice($array, $from, 1);
    array_splice($array, $to, 0, $out);
    return $array;
}*/

/**
 * @throws dml_exception
 */
function set_first_course_access($userid, $courseid, $timeaccess) {
    global $DB;

    $exists =$DB->record_exists('notificationsagent_access', array('userid'=> $userid, 'courseid'=> $courseid));
    if(!$exists){
        $obj_db = new stdClass();
        $obj_db->userid = $userid;
        $obj_db->courseid = $courseid;
        $obj_db->firstaccess= $timeaccess;
        $DB->insert_record('notificationsagent_access',$obj_db );
    }
}


