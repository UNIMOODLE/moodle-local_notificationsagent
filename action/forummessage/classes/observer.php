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
require_once('forummessage_action.php');

class notificationsaction_forummessage_observer {

    public static function general_post_forum(\notificationsaction_forummessage\event\notificationsagent_forummessage_event $event) {
        // Post a message on a forum.
        // Forumid to be sent in event.
        $forumid = 1; // TODO.
        $post = new Forummessage_action($event->courseid, $forumid);
        $post->post_forum($forumid);

    }
}
