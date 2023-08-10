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
require_once('removeusergroup_action.php');
class notificationsaction_removeusergroup_observer {

    public static function remove_user_group(\notificationsaction_removeusergroup\event\remove_user_group_event $event) {
        // Add user to a specified group.

        $message = new Removeusergroup_action($event->courseid, $event->relateduserid);
        $message->remove_user_group();

    }
}
