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

$json = array();

if (!$courseid) {
    require_login();
    throw new \moodle_exception('needcourseid');
}

$sql = 'SELECT *
 FROM {notificationsagent_rule} r
 inner join {notificationsagent_action} a on a.ruleid = r.id
 inner join {notificationsagent_condition} c on c.ruleid = r.id
 WHERE r.courseid = :courseid
 AND r.id = :ruleid';

$courseparams = array('courseid' => $courseid, 'id' => $ruleid);

$json["rule"] = $DB->get_record('notificationsagent_rule', $courseparams);
$json["actions"] = $DB->get_records('notificationsagent_action', array('ruleid' => $ruleid));
$json["conditions"] = $DB->get_records('notificationsagent_condition', array('ruleid' => $ruleid));

$rs = json_encode($json);

header('Content-disposition: attachment; filename=rule_' . $ruleid . '_export.json');
header('Content-type: application/json');
echo $rs;
