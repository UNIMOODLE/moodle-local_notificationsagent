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
 * @package    local_notificationsagent
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_notificationsagent\helper;

use html_writer;
use moodle_url;
use local_notificationsagent\rule;

/**
 *  Helper class for plugin
 */
class helper {
    /**
     * Trace for scheduled tasks. Only meant for debugging
     * Disable in settings for producion environments.
     *
     * @param string $message
     *
     * @return void
     */
    public static function custom_mtrace($message) {
        $tracelog = get_config('local_notificationsagent', 'tracelog');
        if ($tracelog) {
            mtrace($message);
        }
    }

    /**
     * Returns seconds in human format
     *
     * @param integer $seconds Seconds
     * @param bool $toshow
     *
     * @return array|string $data Time in days, hours, minutes and seconds
     */
    public static function to_human_format($seconds, $toshow = false) {
        $dft = new \DateTime('@0');
        $dtt = new \DateTime("@$seconds");

        $stringtoshow = [];
        if ($a = $dft->diff($dtt)->format('%a')) {
            $stringtoshow[] = "$a " . get_string($a > 1 ? 'card_day_plural' : 'card_day', 'local_notificationsagent');
        }
        if ($h = $dft->diff($dtt)->format('%h')) {
            $stringtoshow[] = "$h " . get_string($h > 1 ? 'card_hour_plural' : 'card_hour', 'local_notificationsagent');
        }
        if ($i = $dft->diff($dtt)->format('%i')) {
            $stringtoshow[] = "$i " . get_string($i > 1 ? 'card_minute_plural' : 'card_minute', 'local_notificationsagent');
        }
        if ($s = $dft->diff($dtt)->format('%s')) {
            $stringtoshow[] = "$s " . get_string($s > 1 ? 'card_second_plural' : 'card_second', 'local_notificationsagent');
        }

        if (empty($stringtoshow)) {
            $stringtoshow[] = "0 " . get_string('card_second', 'local_notificationsagent');
        }

        if ($toshow) {
            return implode(",", $stringtoshow);
        }

        return ['days' => $a, 'hours' => $h, 'minutes' => $i, 'seconds' => $s];
    }

    /**
     * Get the URL used to access the course that the instance is in.
     *
     * @param int $id
     *
     * @return moodle_url
     */
    public static function get_course_url($id) {
        return new moodle_url('/course/view.php', ['id' => $id]);
    }

    /**
     * Get the URL for a specific module in a course.
     *
     * @param int $courseid The ID of the course.
     * @param int $cmid The ID of the course module.
     *
     * @return moodle_url The URL of the course module.
     */
    public static function get_module_url($courseid, $cmid) {
        return new moodle_url(
            get_fast_modinfo($courseid)->get_cm($cmid)->url->get_path(),
            ['id' => $cmid]
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
    public static function get_follow_link($context) {
        $conditions = $context->get_rule()->get_conditions();
        $condition = $conditions[$context->get_triggercondition()] ?? '';
        $cmid = !empty($condition) ? ((json_decode($condition->get_parameters()))->cmid ?? '') : '';

        return !empty($cmid)
                ? self::get_module_url($context->get_courseid(), $cmid)
                : self::get_course_url(
                    $context->get_courseid()
                );
    }

    /**
     * Returns human format in seconds
     *
     * @param array $time Time in days, hours, minutes and seconds
     *
     * @return integer $seconds Seconds
     */
    public static function to_seconds_format($time) {
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
     *
     * Retrieve data for modal window
     *
     * @param \core_course_category $category
     * @param int $ruleid
     *
     * @return array
     */
    public static function build_category_array($category, $ruleid) {
        global $DB;
        $courses = $category->get_courses();
        $count = $category->coursecount;
        $coursesarray = [];
        foreach ($courses as $course) {
            $coursesarray[] = [
                    'id' => $course->id,
                    'name' => format_text($course->fullname),
            ];
        }

        $categoryarray = [
                'id' => $category->id,
                'name' => format_text($category->name),
                'categories' => [],
                'courses' => $coursesarray,
                'count' => $count,
        ];

        $categoryarray['countsubcategoriescourses'] = self::count_category_courses($category);

        $subcategories = $category->get_children();
        foreach ($subcategories as $subcategory) {
            $hascourses = self::count_category_courses($subcategory);
            if ($hascourses > 0) {
                $subcategoryarray = self::build_category_array($subcategory, $ruleid);
                $categoryarray['categories'][] = $subcategoryarray;
            }
        }

        return $categoryarray;
    }

    /**
     * Count courses under category parent
     *
     * @param \core_course_category $category
     *
     * @return array
     */
    public static function count_category_courses($category) {
        $countcategorycourses = $category->coursecount;

        $subcategories = $category->get_children();
        foreach ($subcategories as $subcategory) {
            $countsuncategorycourses = self::count_category_courses($subcategory);
            $countcategorycourses += $countsuncategorycourses;
        }
        return $countcategorycourses;
    }

    /**
     * Retrieve output for modal window
     *
     * @param array $arraycategories
     * @param int $categoryid
     *
     * @return string
     */
    public static function build_output_categories($arraycategories, $categoryid = 0) {
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
                    "data-category" => $category["id"],
            ]);
            $output .= html_writer::tag(
                "label",
                "",
                ["class" => "custom-control-label", "for" => "checkboxcategory-" . $category["id"]]
            );
            $output .= html_writer::end_div(); // ... .custom-checkbox
            $output .= html_writer::start_div("", [
                    "class" => "d-flex px-0", "data-toggle" => "collapse",
                    "data-target" => "#category-listing-content-" . $category["id"],
                    "aria-controls" => "category-listing-content-" . $category["id"],
            ]);
            $output .= html_writer::start_div("", ["class" => "categoryname d-flex align-items-center"]);
            $output .= $category["name"];
            $output .= html_writer::tag("i", "", ["class" => "fa fa-angle-down ml-2"]);
            $output .= html_writer::end_div(); // ....categoryname
            $output .= html_writer::end_div(); // ... .data-toggle
            $output .= html_writer::span(
                "",
                "",
                ["id" => "selected-info-" . $category["id"], "class" => "bg-primary"]
            );
            $output .= html_writer::start_div("", ["class" => "ml-auto px-0"]);
            $output .= html_writer::start_tag("span", ["class" => "course-count text-muted"]);
            $output .= $category["countsubcategoriescourses"];
            $output .= html_writer::tag("i", "", ["class" => "fa fa-graduation-cap fa-fw ml-2"]);
            $output .= html_writer::end_tag("span"); // ... .course-count
            $output .= html_writer::end_div(); // ... .col-auto
            $output .= html_writer::end_div(); // ... .d-flex
            $output .= html_writer::start_tag("ul", [
                    "id" => "category-listing-content-" . $category["id"],
                    "class" => "collapse", "data-parent" => "#category-listing-content-" . $categoryid,
            ]);
            if (!empty($category['courses'])) {
                $output .= html_writer::link(
                    "#",
                    get_string("assignselectcourses", "local_notificationsagent"),
                    ["id" => "select-all-" . $category["id"],
                    "data-category" => $category["id"], "data-forceselected" => "false"]
                );
            }
            if (!empty($category["categories"])) {
                $output .= self::build_output_categories($category["categories"], $category["id"]);
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
                        "input",
                        "",
                        [
                                    "id" => "checkboxcourse-" . $course["id"],
                                    "type" => "checkbox", "class" => "custom-control-input",
                                    "data-parent" => "#category-listing-content-" . $category["id"],
                                    "data-category" => $category["id"],
                            ]
                    );
                    $output .= html_writer::tag(
                        "label",
                        "",
                        ["class" => "custom-control-label", "for" => "checkboxcourse-" . $course["id"]]
                    );
                    $output .= html_writer::end_div(); // ... .custom-checkbox
                    $output .= html_writer::start_div("", ["class" => "coursename"]);
                    $output .= $course["name"];
                    $output .= html_writer::end_div(); // ... .coursename
                    $output .= html_writer::end_div(); // ... .d-flex
                    $output .= html_writer::end_tag("li");
                    // ... .listitem.listitem-course.list-group-item.list-group-item-action
                }
            }
            $output .= html_writer::end_tag("ul"); // ... #category-listing-content-x
            $output .= html_writer::end_tag("li"); // ... .listitem.listitem-category.list-group-item
        }
        return $output;
    }

    /**
     *  Notify to rules owners of the rule broken status
     *
     * @param int $courseid
     * @param int $ruleid
     * @return false|int|mixed
     */
    public static function broken_rule_notify($courseid, $ruleid) {
        $rule = new rule($ruleid);
        $userid = $rule->get_createdby();
        $title = $rule->get_name() . " " . get_string('status_broken', 'local_notificationsagent');
        $text = get_string(
            'brokenrulebody',
            'local_notificationsagent',
            [
                        'course' => get_course($courseid)->fullname,
                        'rule' => $rule->get_name(),
                ]
        );
        $message = new \core\message\message();
        $message->component = 'local_notificationsagent'; // Your plugin's name.
        $message->name = 'notificationsagent_message'; // Your notification name from message.php.
        $message->userfrom = \core_user::get_noreply_user(); // If the message is 'from' a specific user you can set them here.
        $message->userto = $userid;
        $message->subject = $title;
        $message->fullmessage = format_text($text);
        $message->fullmessageformat = FORMAT_MOODLE;
        $message->fullmessagehtml = format_text('<p>' . $text . '</p>');
        $message->smallmessage = shorten_text(format_text($text));
        $message->notification = 1; // Because this is a notification generated from Moodle, not a user-to-user message.
        $message->contexturl = (new \moodle_url(
            '/local/notificationsagent/editrule.php?courseid=' . $courseid . '&action=edit&ruleid=' . $ruleid
        ))->out(
            false
        ); // A relevant URL for the notification.
        $message->contexturlname = get_string('fullrule', 'local_notificationsagent'); // Link title explaining where users get
        // to  for the contexturl.
        // The integer ID of the new message or false if there was a problem (with submitted data or sending the message to the
        // message processor).
        return message_send($message);
    }

    /**
     * Get default capabilities.
     *
     * @param \context $context The course context in which to check the capability.
     *
     * @return array $capabilities Default value for capabilities.
     */
    public static function get_default_capabilities($context) {
        return [
                'report' => has_capability('local/notificationsagent:viewassistantreport', $context),
                'export' => has_capability('local/notificationsagent:exportrule', $context),
        ];
    }

    /**
     * Get capabilities for a given context.
     *
     * @param \context $context The course context in which to check the capability.
     *
     * @return array $capabilities The capabilities of the rule.
     */
    public static function get_capabilities($context) {
        return [
                'resume' => has_capability('local/notificationsagent:updaterulestatus', $context),
                'edit' => has_capability('local/notificationsagent:editrule', $context),
                'delete' => has_capability('local/notificationsagent:deleterule', $context),
                'assign' => has_capability('local/notificationsagent:assignrule', $context),
                'export' => has_capability('local/notificationsagent:exportrule', $context),
                'force' => has_capability('local/notificationsagent:forcerule', $context),
                'share' => has_capability('local/notificationsagent:updateruleshare', $context),
                'report' => has_capability('local/notificationsagent:manageownrule', $context),
        ];
    }

    /**
     * Get parent categories id from course
     *
     * @param int $courseid
     *
     * @return array $parents categories ids.
     */
    public static function get_parents_categories_course($courseid) {
        $course = get_course($courseid);
        $categoryid = $course->category;
        try {
            $category = \core_course_category::get($categoryid);
            $parents = $category->get_parents();
            $parents[] = $course->category;
        } catch (\moodle_exception $e) {
            // If the category is hidden just return the course category.
            $parents = [ $categoryid ];
        }

        return $parents;
    }

    /**
     * Set error in report actiondetail
     *
     * @param string $parameters
     * @return false|string
     */
    public static function set_error($parameters) {
        $json = json_decode($parameters, true);
        $json['error'] = get_string('actionerror', 'local_notificationsagent');
        return json_encode($json);
    }

    /**
     * Get the course cache
     *
     * @param int $courseid The ID of the course.
     *
     * @return \stdClass A course object
     */
    public static function get_cache_course($courseid) {
        global $DB;
        $cache = \cache::make('local_notificationsagent', 'course');
        $coursecache = $cache->get($courseid) ? $cache->get($courseid) : null;
        if (is_null($coursecache)) {
            if ($coursecache = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST)) {
                $cache->set($courseid, $coursecache);
            }
        }

        return $coursecache;
    }

    /**
     * Set the course cache
     *
     * @param int $courseid The ID of the course.
     *
     * @return \stdClass A course object
     */
    public static function set_cache_course($courseid) {
        global $DB;
        $cache = \cache::make('local_notificationsagent', 'course');
        if ($coursecache = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST)) {
            $cache->set($courseid, $coursecache);
        }

        return $coursecache;
    }
}
