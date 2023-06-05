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
//namespace classes;
require_once ('postforum.php');
class local_forummessage_observer {

    public static function general_post_forum(\local_notificationsagent\event\forum_post_event $event)
    {
        // Post a message on a forum.
        // Forumid to be sent in event
        $forumid=2;
        $post = new Postforum($event->courseid, $forumid);
        $post->post_forum($forumid);

    }
}