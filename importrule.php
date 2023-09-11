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
 * Import rules
 *
 * @package    local_notificationsagent
 * @copyright  2023 UNIMOODLE
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("../../config.php");
global $DB;

$courseid = required_param('courseid', PARAM_INT);

if (!$courseid) {
    require_login();
    throw new \moodle_exception('needcourseid');
}

if (!isset($_FILES['userfile']) || $_FILES['userfile']['error'] == UPLOAD_ERR_NO_FILE) {
    $message = get_string('no_file_selected', 'local_notificationsagent');
    echo \core\notification::error($message);
} else {
    // Read json file.
    $data = file_get_contents($_FILES['userfile']['tmp_name']);
    $data = json_decode($data, true);

    // Check if file uploaded is a JSON.
    if (!$data) {
        $message = get_string('no_json_file', 'local_notificationsagent');
        echo \core\notification::error($message);
    } else {
        $sqlrule = new \stdClass();
        $sqlrule->courseid = $courseid;
        $sqlrule->name = $data['rule']['name'];
        $sqlrule->description = $data['rule']['description'];
        $sqlrule->createdby = $data['rule']['createdby'];
        $sqlrule->createdat = $data['rule']['createdat'];

        $idrule = $DB->insert_record('notificationsagent_rule', $sqlrule);

        if ($data['actions']) {
            $sqlactions = new \stdClass();

            for ($i = 1; $i <= count($data['actions']); $i++) {
                foreach ($data['actions'][$i] as $key => $value) {
                    $sqlactions->$key = $value;
                }

                $idactions = $DB->insert_record('notificationsagent_action', $sqlactions);
            }
        }

        if ($data['conditions']) {
            $sqlconditions = new \stdClass();

            for ($i = 1; $i <= count($data['conditions']); $i++) {
                foreach ($data['conditions'][$i] as $key => $value) {
                    $sqlconditions->$key = $value;
                }

                $idconditions = $DB->insert_record('notificationsagent_condition', $sqlconditions);
            }
        }

        if ($idrule && (is_null($idactions) || is_null($idconditions))) {
            $message = get_string('import_success', 'local_notificationsagent');
            echo \core\notification::success($message);
        } else {
            $message = get_string('import_error', 'local_notificationsagent');
            echo \core\notification::error($message);
        }
    }
}

header('Location: index.php?courseid=' . $courseid);