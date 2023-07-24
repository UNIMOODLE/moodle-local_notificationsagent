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
 * sessionstart observer.php .
 *
 * @package    sessionstart
 * @copyright  2023 fernando
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once (__DIR__ . '/../lib.php');
class notificationscondition_sessionstart_observer {

    /**
     * @throws \dml_exception
     */
    public static function course_viewed(\core\event\course_viewed $event) {
        // We use this event to avoid querying the log_standard_log for a course firstaccess.
        if (!isloggedin() || $event->courseid == 1) {
            return;
        }
        $userid = $event->userid;
        $courseid = $event->courseid;
        $timeaccess = $event->timecreated;
        set_first_course_access($userid, $courseid, $timeaccess);
    }

}
