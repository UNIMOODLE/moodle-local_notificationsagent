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

require('../../config.php');
global $PAGE, $CFG, $OUTPUT, $COURSE, $SITE, $USER;
require_once($CFG->libdir . '/adminlib.php');

use core_reportbuilder\local\filters\number;
use core_reportbuilder\local\filters\select;
use core_reportbuilder\system_report_factory;
use local_notificationsagent\reportbuilder\local\systemreports;
use local_notificationsagent\rule;

require_login();

$ruleid = optional_param('ruleid', '', PARAM_INT);
$courseid = optional_param('courseid', '', PARAM_INT);
$filters = [];
if ($courseid) {
    global $DB;
    $course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
    $PAGE->set_course($course);
    $context = context_course::instance($course->id);
    $coursefilter = get_course($courseid)->id;
    $filters['rule:courseselector_operator'] = number::EQUAL_TO;
    $filters['rule:courseselector_values'] = $course->id;

} else {
    $context = context_system::instance();
}

if ($ruleid) {
    $rule = rule::create_instance($ruleid);
    $filter = $rule->get_id();
    $filters['rule:rulename_operator'] = select::EQUAL_TO;
    $filters['rule:rulename_values'] = $filter;
}

// Only show my own name.
if (!has_capability(
    'local/notificationsagent:viewcourserule',
    $context
)
) {
    $filters['rule:userfullname_operator'] = select::EQUAL_TO;
    $filters['rule:userfullname_values'] = $USER->id;
}

if (!has_capability('local/notificationsagent:viewassistantreport', $context)) {
    throw new \moodle_exception(
        'nopermissions', 'error', '', get_capability_string('local/notificationsagent:viewassistantreport')
    );
}

$PAGE->set_context($context);

$url = new moodle_url('/local/notificationsagent/report.php');

$PAGE->set_url($url);
$PAGE->set_pagelayout('report');
$title = get_string('report', 'local_notificationsagent');
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->navbar->ignore_active();
if (!$courseid) {
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
    get_string('editrule_reports', 'local_notificationsagent'),
    new moodle_url('/local/notificationsagent/report.php', ['courseid' => $courseid])
);

$output = $PAGE->get_renderer('local_notificationsagent');

echo $OUTPUT->header();

$report = system_report_factory::create(systemreports\rules::class, $context);

$report->set_filter_values($filters);
echo $report->output();
echo $output->footer();
