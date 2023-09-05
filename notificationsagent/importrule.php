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
    // read json file
    $data = file_get_contents($_FILES['userfile']['tmp_name']);
    $data = json_decode($data);

    // Check if file uploaded is a JSON
    if ($data === null) {
        $message = get_string('no_json_file', 'local_notificationsagent');
        echo \core\notification::error($message);
    } else {
        $sqlValue = new \stdClass();
        $sqlValue->courseid = $courseid;
        $sqlValue->name = $data->name;
        $sqlValue->description = $data->description;
        $sqlValue->createdby = $data->createdby;
        $sqlValue->createdat = $data->createdat;

        $id = $DB->insert_record('notificationsagent_rule', $sqlValue);

        if (is_numeric($id)) {
            $message = get_string('import_success', 'local_notificationsagent');
            echo \core\notification::success($message);
        } else {
            $message = get_string('import_error', 'local_notificationsagent');
            echo \core\notification::error($message);
        }
    }
 }

header('Location: index.php?courseid=' . $courseid);
