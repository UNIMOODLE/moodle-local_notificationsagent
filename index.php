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

use local_notificationsagent\helper\helper;
use local_notificationsagent\rule;
use local_notificationsagent\notificationplugin;

$isroleadmin = false;
if (is_siteadmin() || !empty($PAGE->settingsnav)) {
    if (is_siteadmin()
        || ($PAGE->settingsnav->find('siteadministration', navigation_node::TYPE_SITE_ADMIN)
            || $PAGE->settingsnav->find('root', navigation_node::TYPE_SITE_ADMIN))
    ) {
        $isroleadmin = true;
    }
}

require_login();

$courseidparam = optional_param('courseid', 0, PARAM_INT);

if ($courseidparam) {
    $course = $DB->get_record('course', ['id' => $courseidparam], '*', MUST_EXIST);
    $PAGE->set_course($course);
    $context = $PAGE->context;
} else {
    $context = context_system::instance();
    $PAGE->set_context($context);
}

$courseid = $COURSE->id;

$context = context_course::instance($courseid);
if (get_config('local_notificationsagent', 'disable_user_use')) {
    if (!has_capability('local/notificationsagent:managecourserule', $context)) {
        throw new \moodle_exception(
            'nopermissions', '', '',
            get_capability_string('local/notificationsagent:managecourserule')
        );
    }
}

$PAGE->set_url(new moodle_url('/local/notificationsagent/index.php', ['courseid' => $courseid]));
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('heading', 'local_notificationsagent'));
$PAGE->set_heading(get_string('heading', 'local_notificationsagent'));
$PAGE->navbar->add(get_string('heading', 'local_notificationsagent'));
$PAGE->navbar->ignore_active();
if ($isroleadmin && $courseid == SITEID) {
    $PAGE->navbar->add(
        $SITE->fullname,
        new moodle_url('/')
    );
    $PAGE->navbar->add(
        get_string('admin_breadcrumb', 'local_notificationsagent'),
        new moodle_url('/local/notificationsagent/index.php')
    );
} else {
    $PAGE->navbar->add(
        $COURSE->fullname,
        new moodle_url('/course/view.php', ['id' => $courseid])
    );
    $PAGE->navbar->add(
        get_string('course_breadcrumb', 'local_notificationsagent'),
        new moodle_url('/local/notificationsagent/index.php', ['courseid' => $courseid])
    );
}
$PAGE->requires->js_call_amd('local_notificationsagent/notification_assigntemplate', 'init');
$PAGE->requires->js_call_amd('local_notificationsagent/rule/update_status', 'init');
$PAGE->requires->js_call_amd('local_notificationsagent/rule/delete', 'init');
$PAGE->requires->js_call_amd('local_notificationsagent/rule/share', 'init');
$PAGE->requires->js_call_amd('local_notificationsagent/rule/shareall', 'init');
$PAGE->requires->js_call_amd('local_notificationsagent/rule/unshareall', 'init');
$PAGE->requires->js_call_amd('local_notificationsagent/rule/sort_rule_cards', 'init');
$output = $PAGE->get_renderer('local_notificationsagent');

echo $output->header();

$renderer = $PAGE->get_renderer('core');
$templatecontext = [
    "courseid" => $courseid,
];

$templatecontext['iscontextsite'] = false;
if ($isroleadmin && $courseid == SITEID) {
    $templatecontext['iscontextsite'] = true;
}

$importruleurl = new moodle_url("/local/notificationsagent/importrule.php");
$templatecontext['importruleurl'] = $importruleurl;
$newruleurl = new moodle_url("/local/notificationsagent/editrule.php", [
    'courseid' => $courseid, 'action' => 'add', 'type' => rule::RULE_TYPE,
]);
$addruleurl = new moodle_url("/local/notificationsagent/assign.php", [
    'courseid' => $courseid,
]);
$addtemplate = new moodle_url("/local/notificationsagent/editrule.php", [
    'action' => 'add', 'type' => rule::TEMPLATE_TYPE,
]);
if ($templatecontext['iscontextsite']) {
    $reporturl = new moodle_url("/local/notificationsagent/report.php");
} else {
    $reporturl = new moodle_url("/local/notificationsagent/report.php", [
        'courseid' => $courseid,
    ]);
}

$templatecontext['url'] = [
    'newrule' => $newruleurl,
    'addrule' => $addruleurl,
    'addtemplate' => $addtemplate,
    'reporturl' => $reporturl,
];
$pregerencesorderid = get_user_preferences('orderid');
!empty($pregerencesorderid) ? $orderid = $pregerencesorderid : $orderid = null;
$rules = rule::get_rules_index($context, $courseid, $orderid);
$rulecontent = [];

$conditionsarray = [];
$exceptionsarray = [];
$actionsarray = [];

foreach ($rules as $rule) {
    $ac = $rule->get_ac();
    $conditions = $rule->get_conditions();
    $exceptions = $rule->get_exceptions();
    $actions = $rule->get_actions();
    $conditionscontent = [];
    $exceptionscontent = [];
    $actionscontent = [];

    // AC (conditions and exceptions).
    if ($ac) {
        $ac->process_markups($conditionscontent, $courseid, notificationplugin::COMPLEMENTARY_CONDITION);
        $ac->process_markups($exceptionscontent, $courseid, notificationplugin::COMPLEMENTARY_EXCEPTION);
    }

    // Conditions.
    if (!empty($conditions)) {
        foreach ($conditions as $subplugin) {
            $subplugin->process_markups($conditionscontent, $courseid);
        }
    }

    // Exceptions.
    if (!empty($exceptions)) {
        foreach ($exceptions as $subplugin) {
            $subplugin->process_markups($exceptionscontent, $courseid);
        }
    }

    $conditionsarray = [
        'hascontent' => !empty($conditionscontent),
        'content' => $conditionscontent,
    ];

    $exceptionsarray = [
        'hascontent' => !empty($exceptionscontent),
        'content' => $exceptionscontent,
    ];

    // Actions.
    if (!empty($actions)) {
        foreach ($actions as $subplugin) {
            $subplugin->process_markups($actionscontent, $courseid);
        }
    }
    $actionsarray = [
        'hascontent' => !empty($actionscontent),
        'content' => $actionscontent,
    ];

    $datanamerule = rule::get_coursename_and_username_by_rule_id($rule->get_id());
    $rulecontent[] = [
        'id' => $rule->get_id(),
        'name' => format_text($rule->get_name()),
        'status' => $rule->get_status(),
        'status_lang' => 
            !$rule->validation($courseid) ? get_string('status_broken', 'local_notificationsagent') :
            ($rule->get_forced() ?
            ($rule->get_status() ? get_string('status_paused', 'local_notificationsagent')
                : get_string('status_active', 'local_notificationsagent')
            ) : get_string('status_required', 'local_notificationsagent')),
        'conditions' => $conditionsarray,
        'exceptions' => $exceptionsarray,
        'actions' => $actionsarray,
        'type' => $rule->get_type(),
        'isrule' => $rule->get_template(),
        'forced' => $rule->get_forced(),
        'validation' => $rule->validation($courseid),
        'shared' => $rule->get_shared(),
        'canshare' => $rule->can_share(),
        'candelete' => $rule->can_delete(),
        'isallshared' => $rule->get_defaultrule(),
        'type_lang' => $rule->get_template()
            ? ($rule->get_shared() == 0
            ? ($courseid == 1 ? get_string('cardsharedby', 'local_notificationsagent', $datanamerule) : get_string('type_sharedrule','local_notificationsagent'))
            : get_string('type_rule', 'local_notificationsagent')
            )
            : get_string('type_template', 'local_notificationsagent'),
        'editurl' => new moodle_url(
            "/local/notificationsagent/editrule.php", ['courseid' => $courseid, 'action' => 'edit', 'ruleid' => $rule->get_id()]
        ),
        'reporturl' => new moodle_url(
            "/local/notificationsagent/report.php", $templatecontext['iscontextsite']
            ? ['ruleid' => $rule->get_id()]
            : [
                'courseid' => $courseid, 'ruleid' =>
                    $rule->get_id(),
            ],
        ),
        'exporturl' => new moodle_url(
            "/local/notificationsagent/exportrule.php", ['courseid' => $courseid, 'ruleid' => $rule->get_id()]
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
    $categoryarray[] = helper::build_category_array($cat, $ruleid);
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
    $outputcategories .= helper::build_output_categories($categoryarray);
    $outputcategories .= html_writer::end_tag("ul"); // ... #category-listing-content-0
    $outputcategories .= html_writer::end_div(); // ... .category-listing
    $outputcategories .= html_writer::end_div(); // ... .course-category-listing

    $templatecontext['output_categoriescourses'] = $outputcategories;
}
echo $renderer->render_from_template('local_notificationsagent/index', $templatecontext);

echo $output->footer();
