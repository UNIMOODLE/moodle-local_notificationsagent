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

use core_reportbuilder\system_report_factory;
use core_reportbuilder\local\filters\text;
use local_notificationsagent\reportbuilder\local\systemreports;
use local_notificationsagent\Rule;

require('../../config.php');
global $PAGE, $CFG, $OUTPUT;
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/local/notificationsagent/classes/rule.php');


require_login();



$ruleid = optional_param('ruleid', '', PARAM_INT);
$courseid = optional_param('courseid', '', PARAM_INT);
$filters = [];
if ($courseid) {
    $context = context_course::instance($courseid);
    $coursefilter = get_course($courseid)->fullname;
    $filters['course:fullname_operator'] = text::IS_EQUAL_TO;
    $filters['course:fullname_value'] = $coursefilter;

} else {
    $context = context_system::instance();
}

if ($ruleid) {
    $rule = Rule::create_instance($ruleid);
    $filter = $rule->get_name();
    $filters['rule:rulename_operator'] = text::IS_EQUAL_TO;
    $filters['rule:rulename_value'] = $filter;
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

$output = $PAGE->get_renderer('local_notificationsagent');

echo $OUTPUT->header();

$report = system_report_factory::create(systemreports\rules::class, $context);

$report->set_filter_values($filters);

echo $report->output();
echo $output->footer();

