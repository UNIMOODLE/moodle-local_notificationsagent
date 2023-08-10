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
 * Index
 *
 * @package    local_notificationsagent
 * @copyright  2023 UNIMOODLE
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("../../config.php");
require_once('renderer.php');
require_once("../../lib/modinfolib.php");
require_once("lib.php");
require_once("classes/rule.php");
use local_notificationsagent\Rule;

global $CFG, $DB, $PAGE;



// $PAGE->set_context(context_system::instance()).
$courseid = required_param('courseid', PARAM_INT);
// Limpiar session notificaciones.
unset($SESSION->NOTIFICATIONS);

if (!$courseid) {
    require_login();
    throw new \moodle_exception('needcourseid');
}


if (!$course = $DB->get_record('course', array('id'=>$courseid))) {
    throw new \moodle_exception('invalidcourseid');
}
require_login($course);
$context = context_course::instance($course->id);

$PAGE->set_course($course);
$PAGE->set_url(new moodle_url('/local/notificationsagent/index.php', array('courseid' => $course->id)));
$PAGE->set_title($course->shortname);
$PAGE->set_heading($course->fullname);
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('heading', 'local_notificationsagent'));
$PAGE->set_heading(get_string('heading', 'local_notificationsagent'));
$PAGE->navbar->add(get_string('heading', 'local_notificationsagent'));
$PAGE->requires->js_call_amd('local_notificationsagent/notification_assigntemplate', 'init');
$output = $PAGE->get_renderer('local_notificationsagent');

echo $output->header();

$renderer = $PAGE->get_renderer('core');
$templatecontext = [
    "courseid" => $course->id
];

$rules = Rule::get_rules();
$cardscontent=array();
foreach ($rules as $rule){
    $cardscontent[] = array(
        'id' => $rule->get_id(),
        'name' => $rule->get_name(),
        'type' => $rule->get_template() == 1 ? 'template' : 'rule',
        'type_lang' => $rule->get_template() == 1 ?
            get_string('type_template', 'local_notificationsagent') :
            get_string('type_rule', 'local_notificationsagent'),
    );
}

$templatecontext['cardscontent'] = $cardscontent;

/* Assign Templates */
$assigntemplatebutton = [
    'action' => get_string('assign', 'local_notificationsagent')
];
$templatecontext['assigntemplatebutton'] = $assigntemplatebutton;

$categories_all = core_course_category::top()->get_children();
$category_array = [];
//TODO check $ruleid.
$ruleid = "";
foreach ($categories_all as $cat) {
    /* $ruleid de build_category_array acabará desapareciendo 
    ya que el listado de cursos se obtendrá desde AJAX y la función getListOfCoursesAssigned en lib.php*/
    $category_array[] = build_category_array($cat, $ruleid);
}

if(!empty($category_array)){
    $outputcategories = html_writer::start_div("", ["class" => "course-category-listing"]);
        $outputcategories .= html_writer::start_div("", ["class" => "header-listing"]);
            $outputcategories .= html_writer::start_div("", ["class" => "d-flex"]);
                $outputcategories .= html_writer::start_div("", ["class" => "custom-control custom-checkbox mr-1"]);
                    $outputcategories .= html_writer::tag("input", "", ["id" => "course-category-select-all", "type" => "checkbox", "class" => "custom-control-input"]);
                    $outputcategories .= html_writer::tag("label", "", ["class" => "custom-control-label", "for" => "course-category-select-all"]);
                $outputcategories .= html_writer::end_div();//.custom-checkbox
                $outputcategories .= html_writer::start_div("", ["class" => "col px-0 d-flex"]);
                    $outputcategories .= html_writer::start_div("", ["class" => "header-categoryname"]);
                        $outputcategories .= get_string('name', 'core');
                    $outputcategories .= html_writer::end_div();//.header-categoryname
                $outputcategories .= html_writer::end_div();//.col
                $outputcategories .= html_writer::start_div("", ["class" => "col-auto px-0 d-flex"]);
                    $outputcategories .= html_writer::start_div("", ["class" => "header-countcourses"]);
                        $outputcategories .= get_string('courses', 'core');
                    $outputcategories .= html_writer::end_div();//.header-countcourses
                $outputcategories .= html_writer::end_div();//.col-auto
            $outputcategories .= html_writer::end_div();//.d-flex
        $outputcategories .= html_writer::end_div();//.header-listing
        $outputcategories .= html_writer::start_div("", ["class" => "category-listing"]);
            $outputcategories .= html_writer::start_tag("ul", ["id" => "category-listing-content-0", "class" => "m-0 pl-0"]);
                $outputcategories .= build_output_categories($category_array);
            $outputcategories .= html_writer::end_tag("ul");//#category-listing-content-0
        $outputcategories .= html_writer::end_div();//.category-listing
    $outputcategories .= html_writer::end_div();//.course-category-listing

    $templatecontext['output_categoriescourses'] = $outputcategories;
}

echo $renderer->render_from_template('local_notificationsagent/index', $templatecontext);

echo $output->footer();
