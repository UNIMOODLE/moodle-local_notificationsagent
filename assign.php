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
 * Version details
 *
 * @package    local_notificationsagent
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/renderer.php');
require_once(__DIR__ ."/../../lib/modinfolib.php");
require_once(__DIR__ ."/lib.php");

use local_notificationsagent\rule;
use local_notificationsagent\notificationplugin;

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
            'nopermissions',
            '',
            '',
            get_capability_string('local/notificationsagent:managecourserule')
        );
    }
}

$PAGE->set_url(new moodle_url('/local/notificationsagent/assign.php', ['courseid' => $courseid]));
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('heading', 'local_notificationsagent'));
$PAGE->set_heading(get_string('heading', 'local_notificationsagent'));
$PAGE->navbar->add(get_string('heading', 'local_notificationsagent'));
$PAGE->navbar->ignore_active();
$PAGE->navbar->add(
    $COURSE->fullname,
    new moodle_url('/course/view.php', ['id' => $courseid])
);
$PAGE->navbar->add(
    get_string('course_breadcrumb', 'local_notificationsagent'),
    new moodle_url('/local/notificationsagent/index.php', ['courseid' => $courseid])
);
$PAGE->navbar->add(
    get_string('assign', 'local_notificationsagent'),
    new moodle_url('/local/notificationsagent/assign.php', ['courseid' => $courseid])
);
$output = $PAGE->get_renderer('local_notificationsagent');

echo $output->header();

$renderer = $PAGE->get_renderer('core');
$templatecontext = [
    "courseid" => $courseid,
];

$templatecontext['url']['addrule'] = new moodle_url("/local/notificationsagent/editrule.php", [
    'courseid' => $courseid, 'action' => 'add', 'type' => rule::RULE_TYPE,
]);

$rules = rule::get_rules_assign($context, $courseid);
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

    $rulecontent[] = [
        'id' => $rule->get_id(),
        'name' => format_text($rule->get_name()),
        'owner' => $rule->get_owner(),
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
        'isallshared' => $rule->get_defaultrule(),
        'type_lang' => $rule->get_template()
            ?
            ($rule->get_shared() == 0
                ?
                get_string('type_sharedrule', 'local_notificationsagent')
                :
                get_string('type_rule', 'local_notificationsagent'))
            :
            get_string('type_template', 'local_notificationsagent'),
        'editurl' => new moodle_url(
            "/local/notificationsagent/editrule.php",
            ['courseid' => $courseid, 'action' => 'clone', 'ruleid' => $rule->get_id()]
        ),
    ];
}

$templatecontext['rulecontent'] = $rulecontent;
$templatecontext['capabilities']['create'] = has_capability('local/notificationsagent:createrule', $context);

echo $renderer->render_from_template('local_notificationsagent/assign', $templatecontext);

echo $output->footer();
