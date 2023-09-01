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
 * Export rules
 *
 * @package    local_notificationsagent
 * @copyright  2023 UNIMOODLE
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("../../config.php");
global $DB;

$courseid = required_param('courseid', PARAM_INT);
$ruleid = required_param('ruleid', PARAM_INT);

if (!$courseid) {
    require_login();
    throw new \moodle_exception('needcourseid');
}

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    throw new \moodle_exception('invalidcourseid');
}
require_login($course);

$context = context_course::instance($course->id);

// TODO: Rewrite query to export conditions / actions.
/*$sql = 'SELECT *
        FROM {notifications_rule}
        WHERE courseid = :courseid
        AND ruleid = :ruleid';*/

$courseparams = array('courseid' => $courseid, 'ruleid' => $ruleid);

$rs = $DB->get_record('notifications_rule', $courseparams);

$rs = json_encode($rs);

header('Content-disposition: attachment; filename=rule_' . $ruleid . '_export.json');
header('Content-type: application/json');
echo $rs;
