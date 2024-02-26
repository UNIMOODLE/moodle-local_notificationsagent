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
}

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
    $coursesarry = [];
    foreach ($courses as $course) {
        $coursesarry[] = [
            'id' => $course->id,
            'name' => format_text($course->fullname),
        ];
    }

    $categoryarray = [
        'id' => $category->id,
        'name' => format_text($category->name),
        'categories' => [],
        'courses' => $coursesarry,
        'count' => $count,
    ];

    $categoryarray['countsubcategoriescourses'] = count_category_courses($category);

    $subcategories = $category->get_children();
    foreach ($subcategories as $subcategory) {
        $hascourses = count_category_courses($subcategory);
        if ($hascourses > 0) {
            $subcategoryarray = build_category_array($subcategory, $ruleid);
            $categoryarray['categories'][] = $subcategoryarray;
        }
    }

    return $categoryarray;
}

/**
 * Count courses under category parent
 *
 * @param $category
 *
 * @return array
 */

function count_category_courses($category) {
    $countcategorycourses = $category->coursecount;

    $subcategories = $category->get_children();
    foreach ($subcategories as $subcategory) {
        $countsuncategorycourses = count_category_courses($subcategory);
        $countcategorycourses += $countsuncategorycourses;
    }
    return $countcategorycourses;
}

/**
 * Retrieve output for modal window
 *
 * @param     $arraycategories
 * @param int $categoryid
 *
 * @return string
 */

function build_output_categories($arraycategories, $categoryid = 0) {
    $output = "";
    foreach ($arraycategories as $key => $category) {
        $output .= html_writer::start_tag("li", [
            "id" => "listitem-category-" . $category["id"],
            "class" => "listitem listitem-category list-group-item list-group-item-action collapsed",
        ]);
        $output .= html_writer::start_div("", ["class" => "category-listing-header d-flex"]);
        $output .= html_writer::start_div("", ["class" => "custom-control custom-checkbox mr-1"]);
        $output .= html_writer::tag("input", "", [
            "id" => "checkboxcategory-" . $category["id"],
            "type" => "checkbox", "class" => "custom-control-input",
            "data-parent" => "#category-listing-content-" . $categoryid,
        ]);
        $output .= html_writer::tag(
            "label", "",
            ["class" => "custom-control-label", "for" => "checkboxcategory-" . $category["id"]]
        );
        $output .= html_writer::end_div();// ... .custom-checkbox
        $output .= html_writer::start_div("", [
            "class" => "d-flex px-0", "data-toggle" => "collapse",
            "data-target" => "#category-listing-content-" . $category["id"],
            "aria-controls" => "category-listing-content-" . $category["id"],
        ]);
        $output .= html_writer::start_div("", ["class" => "categoryname d-flex align-items-center"]);
        $output .= $category["name"];
        $output .= html_writer::tag("i", "", ["class" => "fa fa-angle-down ml-2"]);
        $output .= html_writer::end_div();// ....categoryname
        $output .= html_writer::end_div();// ... .data-toggle
        $output .= html_writer::start_div("", ["class" => "ml-auto px-0"]);
        $output .= html_writer::start_tag("span", ["class" => "course-count text-muted"]);
        $output .= $category["countsubcategoriescourses"];
        $output .= html_writer::tag("i", "", ["class" => "fa fa-graduation-cap fa-fw ml-2"]);
        $output .= html_writer::end_tag("span");// ... .course-count
        $output .= html_writer::end_div();// ... .col-auto
        $output .= html_writer::end_div();// ... .d-flex
        $output .= html_writer::start_tag("ul", [
            "id" => "category-listing-content-" . $category["id"],
            "class" => "collapse", "data-parent" => "#category-listing-content-" . $categoryid,
        ]);
        if (!empty($category["categories"])) {
            $output .= build_output_categories($category["categories"], $category["id"]);
        }
        if (!empty($category["courses"])) {
            foreach ($category["courses"] as $key => $course) {
                $output .= html_writer::start_tag("li", [
                    "id" => "listitem-course-" . $course["id"],
                    "class" => "listitem listitem-course list-group-item list-group-item-action",
                ]);
                $output .= html_writer::start_div("", ["class" => "d-flex"]);
                $output .= html_writer::start_div("", ["class" => "custom-control custom-checkbox mr-1"]);
                $output .= html_writer::tag(
                    "input", "",
                    [
                        "id" => "checkboxcourse-" . $course["id"],
                        "type" => "checkbox", "class" => "custom-control-input",
                        "data-parent" => "#category-listing-content-" . $category["id"],
                    ]
                );
                $output .= html_writer::tag(
                    "label", "",
                    ["class" => "custom-control-label", "for" => "checkboxcourse-" . $course["id"]]
                );
                $output .= html_writer::end_div();// ... .custom-checkbox
                $output .= html_writer::start_div("", ["class" => "coursename"]);
                $output .= $course["name"];
                $output .= html_writer::end_div();// ... .coursename
                $output .= html_writer::end_div();// ... .d-flex
                $output .= html_writer::end_tag("li");
                // ... .listitem.listitem-course.list-group-item.list-group-item-action
            }
        }
        $output .= html_writer::end_tag("ul");// ... #category-listing-content-x
        $output .= html_writer::end_tag("li");// ... .listitem.listitem-category.list-group-item
    }
    return $output;
}

function get_rulesbytimeinterval($timestarted, $tasklastrunttime) {
    global $DB;
    $rulesidquery = "
                    SELECT nt.id, nt.ruleid, nt.conditionid, nt.courseid, nt.userid
                      FROM {notificationsagent_triggers} nt
                      JOIN {notificationsagent_rule} nr ON nr.id = nt.ruleid
                     WHERE startdate
                       AND nr.status = 0 
                   BETWEEN :tasklastrunttime AND :timestarted
                     ";

    $rulesid = $DB->get_records_sql(
        $rulesidquery,
        [
            'tasklastrunttime' => $tasklastrunttime,
            'timestarted' => $timestarted,
        ]
    );
    return $rulesid;
}

/**
 * Returns seconds in human format
 *
 * @param integer $seconds Seconds
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

    if(empty($stringtoshow)){
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
 * @return moodle_url
 */
function get_course_url($id) {
    return new moodle_url('/course/view.php', ['id' => $id]);
}

/**
 * Get the URL for a specific module in a course.
 *
 * @param int $courseid The ID of the course.
 * @param int $cmid The ID of the course module.
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
 * @throws moodle_exception When there is an error with the condition ID
 * @return string The URL of the module or course
 */
function get_follow_link($context) {
    $conditions = $context->get_rule()->get_conditions();
    $condition = $conditions[$context->get_triggercondition()] ?? '';
    $cmid = !empty($condition) ? (json_decode($condition->get_parameters()))->cmid : '';

    return !empty($cmid) ? get_module_url($context->get_courseid(), $cmid) : get_course_url($context->get_courseid());
}
