<?php
// This file is part of the Notifications Agent plugin for Moodle - http://moodle.org/
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
class Usermessageagent_action {
    private int $courseid, $user;
    private array $users;
    private string $placeholders;

    public function __construct ($courseid, $user, $other) {
        $this->courseid = $courseid;
        $this->user = $user;
        $this->placeholders = $other;
    }

    public function send_notification() {

        $placeholdershuman = json_decode($this->placeholders);

        $message = new \core\message\message();
        $message->component = 'notificationsaction_usermessageagent'; // Your plugin's name.
        $message->name = 'particular_message'; // Your notification name from message.php.
        $message->userfrom = core_user::get_noreply_user(); // If the message is 'from' a specific user you can set them here.
        $message->userto = $placeholdershuman->user;
        $message->subject = $placeholdershuman->title; // Será nuestro TTTT.
        $message->fullmessage = $placeholdershuman->message; // Será nuestro BBBB.
        $message->fullmessageformat = FORMAT_MARKDOWN;
        $message->fullmessagehtml = '<p>' . $placeholdershuman->message . '</p>';
        $message->smallmessage = 'small message'; // TODO.
        $message->notification = 1; // Because this is a notification generated from Moodle, not a user-to-user message.
        $message->contexturl = (new \moodle_url('/course/'))->out(false); // A relevant URL for the notification //TODO.
        $message->contexturlname = 'Course list'; // Link title explaining where users get to for the contexturl.
        // The integer ID of the new message or false if there was a problem (with submitted data or sending the message to
        // the message processor).
        $messageid = message_send($message);
    }

}
