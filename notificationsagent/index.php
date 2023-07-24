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
$output = $PAGE->get_renderer('local_notificationsagent');

echo $output->header();

$renderer = $PAGE->get_renderer('core');
$templatecontext = [
    "courseid" => $course->id
];
echo $renderer->render_from_template('local_notificationsagent/index', $templatecontext);

echo $output->footer();
