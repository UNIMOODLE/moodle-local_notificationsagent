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
 *
 * @package
 * @copyright  2023 ISYC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */



function notificationsagent_condition_activityopen_get_cm_starttime($cmid) {
    // Table :course modules.
    global $DB;
    $starttimequery = "
                    SELECT mcm.id,instance,module,mm.name, mcm.course
                      FROM {course_modules} mcm
                      JOIN {modules} mm ON mm.id = mcm.module
                    WHERE mcm.id = :cmid";

    $modtype = $DB->get_record_sql(
        $starttimequery,
        [
            'cmid' => $cmid,
        ]
    );
    // If the activity has not starttime ( url activity for instance) then return course startdate.
    switch ($modtype->name){
        case 'assign':
            $starttime = $DB->get_field('assign', 'duedate', ['id' => $modtype->instance]);
            break;
        case 'assignment':
            $starttime = $DB->get_field('assignment', 'timedue', ['id' => $modtype->instance]);
            break;
        case 'bigbluebuttonbn':
            $starttime = $DB->get_field('bigbluebuttonbn', 'openingtime', ['id' => $modtype->instance]);
            break;
        case 'chat':
            $starttime = $DB->get_field('chat', 'chattime', ['id' => $modtype->instance]);
            break;
        case 'choice':
            $starttime = $DB->get_field('choice', 'timeopen', ['id' => $modtype->instance]);
            break;
        case 'feedback':
            $starttime = $DB->get_field('feedback', 'timeopen', ['id' => $modtype->instance]);
            break;
        case 'forum':
            $starttime = $DB->get_field('forum', 'duedate', ['id' => $modtype->instance]);
            break;
        case 'glossary':
            $starttime = $DB->get_field('glossary', 'assesstimestart', ['id' => $modtype->instance]);
            break;
        case 'lesson':
            $starttime = $DB->get_field('lesson', 'available', ['id' => $modtype->instance]);
            break;
        case 'quiz':
            $starttime = $DB->get_field('quiz', 'timeopen', ['id' => $modtype->instance]);
            break;
        case 'scorm':
            $starttime = $DB->get_field('scorm', 'timeopen', ['id' => $modtype->instance]);
            break;
        case 'workshop':
            $starttime = $DB->get_field('workshop', 'submissionstart', ['id' => $modtype->instance]);
        break;
        default:
            $starttime = get_course($modtype->course)->stardate;

    }

    return $starttime;

}
