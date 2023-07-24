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
require_once('usermessage_action.php');
class notificationsaction_usermessageagent_observer {

    public static function particular_notification(\notificationsaction_usermessageagent\event\particular_notification_event $event) {
        // Send notification to a particular user enrolled on the received course in the event.

        $message = new Usermessageagent_action($event->courseid, $event->relateduserid);
        $message->send_notification();

    }
}
