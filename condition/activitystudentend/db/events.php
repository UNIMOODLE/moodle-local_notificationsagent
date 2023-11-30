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
 *  events.php description here.
 *
 * @package
 * @copyright  2023 fernando <>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();
// This event will listen the user's first session in a course.


global $observers, $DB;

// Your database query to get all modules.
$modules = $DB->get_records('modules');

// Check if there are any modules.
if ($modules) {
    foreach ($modules as $module) {
        // Create the event name dynamically based on the module name.
        $eventname = '\mod_' . $module->name .'\event\course_module_viewed';

        // Add the observer with the dynamically generated event name.
        $observers[] = [
            'eventname' => $eventname,
            'callback' => 'notificationscondition_activitystudentend_observer::course_module_viewed',
        ];
    }
}
