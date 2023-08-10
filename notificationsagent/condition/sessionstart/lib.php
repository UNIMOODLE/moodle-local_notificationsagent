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
 * @copyright  2023 Isyc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// TODO Refactor to plugin instead of subplugin.
function sessionstart_set_timer_cache($userid, $courseid, $time, $pluginname) {
    global $DB;
    $exists = $DB->record_exists('notifications_timercache',
        array('userid' => $userid,
            'courseid' => $courseid,
            'pluginname' => $pluginname));
    // First course viewed for a user does not change, so no need for update
    if (!$exists) {
        $objdb = new stdClass();
        $objdb->userid = $userid;
        $objdb->courseid = $courseid;
        $objdb->timestart = $time;
        $objdb->pluginname = $pluginname;
        $DB->insert_record('notifications_timercache', $objdb);
    }
}
