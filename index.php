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

require_once("../../config.php");
require_once('renderer.php');
require_once("../../lib/modinfolib.php");
require_once("lib.php");
require_once("classes/rule.php");
use local_notificationsagent\Rule;

global $CFG, $DB, $PAGE;

$isroleadmin = false;
if (is_siteadmin() || !empty($PAGE->settingsnav)) {
    if (is_siteadmin() || ($PAGE->settingsnav->find('siteadministration', navigation_node::TYPE_SITE_ADMIN)
        || $PAGE->settingsnav->find('root', navigation_node::TYPE_SITE_ADMIN))) {
            $isroleadmin = true;
    }
}
$courseid = required_param('courseid', PARAM_INT);
// Limpiar session notificaciones.
unset($SESSION->NOTIFICATIONS);

if (!$courseid) {
    require_login();
    throw new \moodle_exception('needcourseid');
}

if ((!$isroleadmin && $courseid == SITEID) || (!$course = $DB->get_record('course', ['id' => $courseid]))) {
    throw new \moodle_exception('invalidcourseid');
}
require_login($course);
$context = context_course::instance($course->id);
if (get_config('local_notificationsagent', 'disable_user_use')) {
    if (!has_capability('local/notificationsagent:managecourserule', $context)) {
        throw new \moodle_exception('nopermissions', '', '',
            get_capability_string('local/notificationsagent:managecourserule')
        );
    }
}
$PAGE->set_course($course);
$PAGE->set_url(new moodle_url('/local/notificationsagent/index.php', ['courseid' => $course->id]));
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('heading', 'local_notificationsagent'));
$PAGE->set_heading(get_string('heading', 'local_notificationsagent'));
$PAGE->navbar->add(get_string('heading', 'local_notificationsagent'));
$PAGE->requires->js_call_amd('local_notificationsagent/notification_assigntemplate', 'init');
$PAGE->requires->js_call_amd('local_notificationsagent/notification_statusrule', 'init');
$PAGE->requires->js_call_amd('local_notificationsagent/rule/delete', 'init');
$PAGE->requires->js_call_amd('local_notificationsagent/rule/share', 'init');
$PAGE->requires->js_call_amd('local_notificationsagent/rule/shareall', 'init');
$output = $PAGE->get_renderer('local_notificationsagent');

echo $output->header();

$renderer = $PAGE->get_renderer('core');
$templatecontext = [
    "courseid" => $course->id,
];

$templatecontext['iscontextsite'] = false;
if ($isroleadmin && $courseid == SITEID) {
    $templatecontext['iscontextsite'] = true;
}

$importruleurl = new moodle_url("/local/notificationsagent/importrule.php");
$templatecontext['importruleurl'] = $importruleurl;
$addruleurl = new moodle_url("/local/notificationsagent/editrule.php", [
    'courseid' => $course->id, 'action' => 'add', 'type' => Rule::RULE_TYPE,
]);
$addtemplate = new moodle_url("/local/notificationsagent/editrule.php", [
    'courseid' => $course->id, 'action' => 'add', 'type' => Rule::TEMPLATE_TYPE,
]);
$reporturl = new moodle_url("/local/notificationsagent/report.php", [
    'courseid' => $course->id,
]);
$templatecontext['url'] = [
    'addrule' => $addruleurl,
    'addtemplate' => $addtemplate,
    'reporturl' => $reporturl,
];

$rules = Rule::get_rules($context, $courseid);
$rulecontent = [];

$conditionsarray = [];
$exceptionsarray = [];
$actionsarray = [];

foreach ($rules as $rule) {
    $conditions = $rule->get_conditions();
    $exceptions = $rule->get_exceptions($showac = true);
    $actions = $rule->get_actions();
    $conditionscontent = [];
    $exceptionscontent = [];
    $actionscontent = [];

    // Conditions.
    foreach ($conditions as $key => $value) {
        require_once($CFG->dirroot . '/local/notificationsagent/' . $value->get_type() . '/' . $value->get_pluginname() . '/'
            . $value->get_pluginname() . '.php');
        $pluginclass = 'notificationsagent_' . $value->get_type() . '_' . $value->get_pluginname();
        $pluginobj = new $pluginclass($rule);
        $pluginobj->process_markups(
            $conditionscontent, $value->get_parameters(), $courseid, notificationplugin::CAT_CONDITION_CHILDREN
        );
    }
    $conditionsarray = [
        'hascontent' => !empty($conditionscontent),
        'content' => $conditionscontent,
    ];

    // Exceptions.
    foreach ($exceptions as $key => $value) {
        require_once($CFG->dirroot . '/local/notificationsagent/' . $value->get_type() . '/' . $value->get_pluginname() . '/'
            . $value->get_pluginname() . '.php');
        $pluginclass = 'notificationsagent_' . $value->get_type() . '_' . $value->get_pluginname();
        $pluginobj = new $pluginclass($rule);
        $pluginobj->process_markups(
            $exceptionscontent, $value->get_parameters(), $courseid, notificationplugin::CAT_EXCEPTION_CHILDREN
        );
    }
    $exceptionsarray = [
        'hascontent' => !empty($exceptionscontent),
        'content' => $exceptionscontent,
    ];

    // Actions.
    foreach ($actions as $key => $value) {
        require_once($CFG->dirroot . '/local/notificationsagent/' . $value->get_type() . '/' . $value->get_pluginname() . '/'
            . $value->get_pluginname() . '.php');
        $pluginclass = 'notificationsagent_' . $value->get_type() . '_' . $value->get_pluginname();
        $pluginobj = new $pluginclass($rule);
        $parameters = $rule->replace_placeholders($value->get_parameters(), $courseid, $USER->id, $rule);
        $pluginobj->process_markups($actionscontent, $parameters, $courseid);
    }

    $actionsarray = [
        'hascontent' => !empty($actionscontent),
        'content' => $actionscontent,
    ];

    $rulecontent[] = [
        'id' => $rule->get_id(),
        'name' => format_text($rule->get_name()),
        'status' => $rule->get_status(),
        'status_lang' => $rule->get_forced() ?
            ($rule->get_status() ? get_string('status_paused', 'local_notificationsagent')
                : get_string('status_active', 'local_notificationsagent')
            ) : get_string('status_required', 'local_notificationsagent'),
        'conditions' => $conditionsarray,
        'exceptions' => $exceptionsarray,
        'actions' => $actionsarray,
        'type' => $rule->get_type(),
        'isrule' => $rule->get_template(),
        'forced' => $rule->get_forced(),
        'shared' => $rule->get_shared(),
        'canshare' => $rule->can_share(),
        'candelete' => $rule->can_delete(),
        'isallshared' => $rule->get_defaultrule(),
        'type_lang' => $rule->get_template() ?
            get_string('type_rule', 'local_notificationsagent') :
            get_string('type_template', 'local_notificationsagent'),
        'editurl' => new moodle_url(
            "/local/notificationsagent/editrule.php", ['courseid' => $course->id, 'action' => 'edit', 'ruleid' => $rule->get_id()]
        ),
        'reporturl' => new moodle_url(
            "/local/notificationsagent/report.php", ['courseid' => $course->id, 'ruleid' => $rule->get_id()]
        ),
        'exporturl' => new moodle_url(
            "/local/notificationsagent/exportrule.php", ['courseid' => $course->id, 'ruleid' => $rule->get_id()]
        ),
        'capabilities' => [
            'edit' => has_capability('local/notificationsagent:editrule', $context),
            'delete' => has_capability('local/notificationsagent:deleterule', $context),
            'resume' => has_capability('local/notificationsagent:updaterulestatus', $context),
            'assign' => has_capability('local/notificationsagent:assignrule', $context),
            'export' => has_capability('local/notificationsagent:exportrule', $context),
            'force' => has_capability('local/notificationsagent:forcerule', $context),
            'share' => has_capability('local/notificationsagent:updateruleshare', $context),
            'report' => has_capability('local/notificationsagent:viewassistantreport', $context),
        ],
    ];
}

$templatecontext['rulecontent'] = $rulecontent;
$templatecontext['capabilities'] = [
    'import' => has_capability('local/notificationsagent:importrule', $context),
    'create' => has_capability('local/notificationsagent:createrule', $context),
    'report' => has_capability('local/notificationsagent:viewassistantreport', $context),
];

$categoriesall = core_course_category::top()->get_children();
$categoryarray = [];
$ruleid = "";
foreach ($categoriesall as $cat) {
    $categoryarray[] = build_category_array($cat, $ruleid);
}

if (!empty($categoryarray)) {
    $outputcategories = html_writer::start_div("", ["class" => "course-category-listing"]);
    $outputcategories .= html_writer::start_div("", ["class" => "header-listing"]);
    $outputcategories .= html_writer::start_div("", ["class" => "d-flex"]);
    $outputcategories .= html_writer::start_div("", ["class" => "custom-control custom-checkbox mr-1"]);
    $outputcategories .= html_writer::tag(
        "input", "", ["id" => "course-category-select-all", "type" => "checkbox", "class" => "custom-control-input"]
    );
    $outputcategories .= html_writer::tag("label", "", ["class" => "custom-control-label", "for" => "course-category-select-all"]);
    $outputcategories .= html_writer::end_div(); // ... .custom-checkbox
    $outputcategories .= html_writer::start_div("", ["class" => "col px-0 d-flex"]);
    $outputcategories .= html_writer::start_div("", ["class" => "header-categoryname"]);
    $outputcategories .= get_string('name', 'core');
    $outputcategories .= html_writer::end_div(); // ...... .header-categoryname
    $outputcategories .= html_writer::end_div(); // ... .col
    $outputcategories .= html_writer::start_div("", ["class" => "col-auto px-0 d-flex"]);
    $outputcategories .= html_writer::start_div("", ["class" => "header-countcourses"]);
    $outputcategories .= get_string('courses', 'core');
    $outputcategories .= html_writer::end_div(); // ... .header-countcourses
    $outputcategories .= html_writer::end_div(); // ... .col-auto
    $outputcategories .= html_writer::end_div(); // ... .d-flex
    $outputcategories .= html_writer::end_div(); // ... .header-listing
    $outputcategories .= html_writer::start_div("", ["class" => "category-listing"]);
    $outputcategories .= html_writer::start_tag("ul", ["id" => "category-listing-content-0", "class" => "m-0 pl-0"]);
    $outputcategories .= build_output_categories($categoryarray);
    $outputcategories .= html_writer::end_tag("ul"); // ... #category-listing-content-0
    $outputcategories .= html_writer::end_div(); // ... .category-listing
    $outputcategories .= html_writer::end_div(); // ... .course-category-listing

    $templatecontext['output_categoriescourses'] = $outputcategories;
}

echo $renderer->render_from_template('local_notificationsagent/index', $templatecontext);

echo $output->footer();
