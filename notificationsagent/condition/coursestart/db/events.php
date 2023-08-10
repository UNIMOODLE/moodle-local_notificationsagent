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

// Events related to creating and updating a course.
// Used for updating temporary triggers in case of configuration changes in a course.
defined('MOODLE_INTERNAL') || die();
$observers[] = [
    'eventname'   => 'core\event\course_updated',
    'callback' => 'notificationscondition_coursestart_observer::course_updated',
];