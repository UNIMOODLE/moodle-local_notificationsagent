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
defined('MOODLE_INTERNAL') || die();
$observers[] = [
    'eventname' => '\core\event\user_graded',
    'callback' => 'local_notificationsagent_observer::user_graded',
];

$observers[] = [
    'eventname' => '\core\event\course_module_completion_updated',
    'callback' => 'local_notificationsagent_observer::course_module_completion_updated',
];

$observers[] = [
    'eventname' => '\core\event\course_module_updated',
    'callback' => 'local_notificationsagent_observer::course_module_updated',
];

$observers[] = [
    'eventname' => '\core\event\group_member_added',
    'callback' => 'local_notificationsagent_observer::group_member_added',
];

$observers[] = [
    'eventname' => '\core\event\course_module_created',
    'callback' => 'local_notificationsagent_observer::course_module_created',
];

