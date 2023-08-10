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
 * @param $category
 * @param $ruleid
 *
 * @return array
 * @throws dml_exception
 *
 */

/**
 * Retrieve data for modal window
 *
 * @param $category
 * @param $ruleid
 *
 * @return array
 */

function build_category_array($category, $ruleid) {
    global $DB;
    $courses = $category->get_courses();
    $count = $category->coursecount;
    $courses_arry=array();
    foreach ($courses as $course){
        $assigned = $DB->get_field(
            'notifications_rule',
            'assigned',
            array('ruleid'=>$ruleid, 'courseid'=>$course->id));
        $courseid= $course->id;
        $fullname= $course->fullname;
        $courses_arry[]= array(
            'id' => $courseid,
            'name' => $fullname,
            'assigned' => $assigned,
        );
    }

    $categoryArray = array(
        'id' => $category->id,
        'name' => $category->name,
        'categories' => array(),
        'courses' => $courses_arry,
        'count' => $count
    );

    $categoryArray['countsubcategoriescourses'] = count_category_courses($category);

    $subcategories = $category->get_children();
    foreach ($subcategories as $subcategory) {
        $hascourses = count_category_courses($subcategory);
        if($hascourses > 0){
            $subcategoryArray = build_category_array($subcategory, $ruleid);
            $categoryArray['categories'][] = $subcategoryArray;
        }
    }

    return $categoryArray;
}

/**
 * Count courses under category parent
 *
 * @param $category
 *
 * @return array
 */

function count_category_courses($category){
    $countcategorycourses = $category->coursecount;

    $subcategories = $category->get_children();
    foreach ($subcategories as $subcategory) {
        $countsuncategorycourses = count_category_courses($subcategory);
        $countcategorycourses += $countsuncategorycourses;
    }
    return $countcategorycourses;
}

/**
 * Get list of courses assigned to rule 
 *
 * @param $category
 *
 * @return array
 */

if(!empty($_POST['idRule'])){
    echo json_encode(getListOfCoursesAssigned($_POST['idRule']));
}

function getListOfCoursesAssigned($idRule){
    global $DB;
    /* Select tabla de assingados pasándole el idRule y recibiendo un listado de ids de cursos */
    $listofCoursesAssigned = [11, 2, 10, 17, 5];

    return $listofCoursesAssigned;
}

/**
 * Retrieve output for modal window
 *
 * @param $arraycategories
 *
 * @return array
 */

function build_output_categories($arraycategories, $categoryid = 0) {
    $output = "";
    foreach ($arraycategories as $key => $category) {
        $output .= html_writer::start_tag("li", ["id" => "listitem-category-".$category["id"], "class" => "listitem listitem-category list-group-item list-group-item-action collapsed"]);
            $output .= html_writer::start_div("", ["class" => "category-listing-header d-flex"]);
                $output .= html_writer::start_div("", ["class" => "custom-control custom-checkbox mr-1"]);
                    $output .= html_writer::tag("input", "", ["id" => "checkboxcategory-".$category["id"], "type" => "checkbox", "class" => "custom-control-input", "data-parent" => "#category-listing-content-".$categoryid]);
                    $output .= html_writer::tag("label", "", ["class" => "custom-control-label", "for" => "checkboxcategory-".$category["id"]]);
                $output .= html_writer::end_div();//.custom-checkbox
                $output .= html_writer::start_div("", ["class" => "d-flex px-0", "data-toggle" => "collapse", "data-target" => "#category-listing-content-".$category["id"], "aria-controls" => "category-listing-content-".$category["id"]]);
                    $output .= html_writer::start_div("", ["class" => "categoryname"]);
                        $output .= $category["name"];
                        $output .= html_writer::tag("i", "", ["class" => "fa fa-angle-down ml-3"]);
                    $output .= html_writer::end_div();//.categoryname
                $output .= html_writer::end_div();//.data-toggle
                $output .= html_writer::start_div("", ["class" => "ml-auto px-0"]);
                    $output .= html_writer::start_tag("span", ["class" => "course-count text-muted"]);
                        $output .= $category["countsubcategoriescourses"];
                        $output .= html_writer::tag("i", "", ["class" => "fa fa-graduation-cap fa-fw ml-2"]);
                    $output .= html_writer::end_tag("span");//.course-count
                $output .= html_writer::end_div();//.col-auto
            $output .= html_writer::end_div();//.d-flex
            $output .= html_writer::start_tag("ul", ["id" => "category-listing-content-".$category["id"], "class" => "collapse", "data-parent" => "#category-listing-content-".$categoryid]);
                if(!empty($category["categories"])){
                    $output .= build_output_categories($category["categories"], $category["id"]);
                }
                if(!empty($category["courses"])){
                    foreach ($category["courses"] as $key => $course) {
                        $output .= html_writer::start_tag("li", ["id" => "listitem-course-".$course["id"], "class" => "listitem listitem-course list-group-item list-group-item-action"]);
                            $output .= html_writer::start_div("", ["class" => "d-flex"]);
                                $output .= html_writer::start_div("", ["class" => "custom-control custom-checkbox mr-1"]);
                                    $output .= html_writer::tag("input", "", ["id" => "checkboxcourse-".$course["id"], "type" => "checkbox", "class" => "custom-control-input", "data-parent" => "#category-listing-content-".$category["id"]]);
                                    $output .= html_writer::tag("label", "", ["class" => "custom-control-label", "for" => "checkboxcourse-".$course["id"]]);
                                $output .= html_writer::end_div();//.custom-checkbox
                                $output .= html_writer::start_div("", ["class" => "coursename"]);
                                    $output .= $course["name"];
                                $output .= html_writer::end_div();//.coursename
                            $output .= html_writer::end_div();//.d-flex
                        $output .= html_writer::end_tag("li");//.listitem.listitem-course.list-group-item.list-group-item-action
                    }
                }
            $output .= html_writer::end_tag("ul");//#category-listing-content-x
        $output .= html_writer::end_tag("li");//.listitem.listitem-category.list-group-item
    }
    
    return $output;
}



