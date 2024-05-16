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

use local_notificationsagent\rule;
use local_notificationsagent\form\editrule_form;

global $DB, $PAGE, $COURSE;
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
$typeaction = required_param('action', PARAM_ALPHANUMEXT);
$ruletype = !is_null(optional_param('type', null, PARAM_INT)) ? optional_param('type', null, PARAM_INT) : rule::RULE_TYPE;

$cancel = optional_param('cancel', null, PARAM_TEXT);
$submitbutton = optional_param('submitbutton', null, PARAM_TEXT);
$keypost = optional_param('key', null, PARAM_TEXT);

if ($courseidparam) {
    $course = $DB->get_record('course', ['id' => $courseidparam], '*', MUST_EXIST);
    $PAGE->set_course($course);
    $context = $PAGE->context;
} else {
    $context = context_system::instance();
    $PAGE->set_context($context);
}

$courseid = $COURSE->id;

$url = new moodle_url('/local/notificationsagent/editrule.php');
$PAGE->set_url($url);
$PAGE->set_pagelayout('admin');
$heading = rule::RULE_TYPE === $ruletype
    ? get_string('editrule_newrule', 'local_notificationsagent')
    : get_string(
        'editrule_newtemplate', 'local_notificationsagent'
    );
$PAGE->set_title(
    $heading . " - " .
    get_string('heading', 'local_notificationsagent')
);

$PAGE->set_heading(
    ($typeaction == 'add' || $typeaction == 'clone'
        ? $heading
        : get_string('editrule_editrule', 'local_notificationsagent')) . " - " .
    get_string('heading', 'local_notificationsagent')
);
$PAGE->navbar->add(
    get_string('editrule_newrule', 'local_notificationsagent') . " - " .
    get_string('heading', 'local_notificationsagent')
);
$PAGE->navbar->ignore_active();
if ($isroleadmin && $courseid == SITEID) {
    $PAGE->navbar->add(
        $SITE->fullname,
        new moodle_url('/')
    );
    $PAGE->navbar->add(
        'Notification Agent Admin',
        new moodle_url('/local/notificationsagent/index.php')
    );
} else {
    $PAGE->navbar->add(
        $COURSE->fullname,
        new moodle_url('/course/view.php', ['id' => $courseid])
    );
    $PAGE->navbar->add(
        'Notification Agent',
        new moodle_url('/local/notificationsagent/index.php', ['courseid' => $courseid])
    );
}
$PAGE->navbar->add(
    ($typeaction == 'add' || $typeaction == 'clone' ? $heading : get_string('editrule_editrule', 'local_notificationsagent')),
    new moodle_url('/local/notificationsagent/editrule.php', ['courseid' => $courseid])
);
$PAGE->requires->js_call_amd('core/copy_to_clipboard');
$PAGE->requires->js_call_amd(
    'local_notificationsagent/notification_tabs', 'init',
    [editrule_form::FORM_NEW_CONDITION_BUTTON, editrule_form::FORM_NEW_CONDITION_SELECT]
);
$PAGE->requires->js_call_amd(
    'local_notificationsagent/notification_tabs', 'init',
    [editrule_form::FORM_NEW_EXCEPTION_BUTTON, editrule_form::FORM_NEW_EXCEPTION_SELECT]
);
$PAGE->requires->js_call_amd(
    'local_notificationsagent/notification_tabs', 'initRemove',
    [editrule_form::FORM_REMOVE_CONDITION_SPAN, editrule_form::FORM_REMOVE_CONDITION_BUTTON]
);
$PAGE->requires->js_call_amd(
    'local_notificationsagent/notification_tabs', 'initRemove',
    [editrule_form::FORM_REMOVE_EXCEPTION_SPAN, editrule_form::FORM_REMOVE_EXCEPTION_BUTTON]
);
$PAGE->requires->js_call_amd(
    'local_notificationsagent/notification_tabs', 'initRemove',
    [editrule_form::FORM_REMOVE_ACTION_SPAN, editrule_form::FORM_REMOVE_ACTION_BUTTON]
);
$PAGE->requires->js_call_amd('local_notificationsagent/notification_statusrule', 'init');

// LOAD RULE.
$ruleid = optional_param('ruleid', null, PARAM_INT);
$ruleid = empty($ruleid) ? null : $ruleid;
$rule = new rule($ruleid, $ruletype, $typeaction);
$customdata = [
    'rule' => $rule->to_record(),
    'timesfired' => rule::MINIMUM_EXECUTION,
    'courseid' => $courseid,
    'getaction' => $typeaction,
];

$mform = new editrule_form($PAGE->url->out(false), $customdata);
$mform->set_data($rule->get_dataform());

if ($mform->is_cancelled()) {
    $PAGE->set_url(new moodle_url('/local/notificationsagent/index.php', ['courseid' => $courseid]));
    redirect(
        new moodle_url('/local/notificationsagent/index.php', ['courseid' => $courseid]),
        get_string('rulecancelled', 'local_notificationsagent')
    );
} else if ($mform->no_submit_button_pressed()) {
    $mform->addorremovesubplugin();

} else if ($fromform = $mform->get_data()) {
    $rule->save_form($fromform);

    redirect(
        new moodle_url('/local/notificationsagent/index.php', ['courseid' => $courseid]),
        get_string('rulesaved', 'local_notificationsagent')
    );
}

$output = $PAGE->get_renderer('local_notificationsagent');

echo $output->header();

$mform->display();

echo $output->footer();
