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
 *  lib.php for sessionstart subplugin.
 *
 * @package
 * @copyright  2023 fernando
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * @throws dml_exception
 */
function set_first_course_access($userid, $courseid, $timeaccess) {
    global $DB;
    $exists = $DB->record_exists('notifications_sessionaccess', array('userid' => $userid, 'courseid' => $courseid));
    if (!$exists) {
        $objdb = new stdClass();
        $objdb->userid = $userid;
        $objdb->courseid = $courseid;
        $objdb->firstaccess = $timeaccess;
        $DB->insert_record('notifications_sessionaccess', $objdb);
    }
}
