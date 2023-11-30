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

/**
 *  lib.php for activitystudentend subplugin.
 *
 * @package
 * @copyright  2023 Isyc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * @throws dml_exception
 */
function set_activity_access($userid, $courseid, $idactivity, $timecreated) {
    global $DB;
    $exists = $DB->record_exists('notificationsagent_cmview',
    ['userid' => $userid, 'courseid' => $courseid, 'idactivity' => $idactivity]);

    $objdb = new stdClass();
    $objdb->userid = $userid;
    $objdb->courseid = $courseid;
    $objdb->idactivity = $idactivity;
    $objdb->firstaccess = $timecreated;

    if (!$exists) {
        // Si el registro no existe, inserta uno nuevo.
        $DB->insert_record('notificationsagent_cmview', $objdb);
    } else {
        // Si el registro existe, obtén la ID y actualiza el registro.
        $existingrecord = $DB->get_record('notificationsagent_cmview',
        ['userid' => $userid, 'courseid' => $courseid, 'idactivity' => $idactivity]);
        $objdb->id = $existingrecord->id;
        $DB->update_record('notificationsagent_cmview', $objdb);
    }
}

