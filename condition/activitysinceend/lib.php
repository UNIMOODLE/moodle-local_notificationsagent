<?php
// This file is part of Moodle - https://moodle.org/
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
 *
 * @package
 * @copyright  2023 ISYC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */



function notificationsagent_condition_activitysinceend_get_cm_endtime($cmid) {
    // Table :course modules.
    global $DB;
    $endtimequery = "
                     SELECT id, coursemoduleid, userid, completionstate, timemodified
                     FROM {course_modules_completion}
                     WHERE coursemoduleid = :cmid AND completionstate > 0";

    $completion = $DB->get_records_sql(
        $endtimequery,
        [
            'cmid' => $cmid,
        ]
    );

    return $completion;

}
