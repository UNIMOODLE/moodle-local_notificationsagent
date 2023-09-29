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
class Messageagent_action {
    private int $courseid;
    private array $users;


    public function __construct ($courseid, $relateduserid) {
        $this->courseid = $courseid;
        if(empty($relateduserid)){
            $coursecontext = context_course::instance($courseid);
            $this->users = $this->get_usersbycourse($coursecontext);
        }else{
            $this->users = [$relateduserid];
        }
    }

    public function send_notification() {

        $users = $this->users;

        foreach ($users as $user) {

            $message = new \core\message\message();
            $message->component = 'notificationsaction_messageagent'; // Your plugin's name.
            $message->name = 'individual_message'; // Your notification name from message.php.
            $message->userfrom = core_user::get_noreply_user(); // If the message is 'from' a specific user you can set them here.
            $message->userto = $user;
            $message->subject = 'Recordatorio usuarios curso ' . date('H:i:s', time()); // Será nuestro TTTT.
            $message->fullmessage = 'Hola {user_name} ' . $user; // Será nuestro BBBB.
            $message->fullmessageformat = FORMAT_MARKDOWN;
            $message->fullmessagehtml = '<p>Hola {user_name}  </p>' . $user;
            $message->smallmessage = 'small message'; // TODO.
            $message->notification = 1; // Because this is a notification generated from Moodle, not a user-to-user message.
            $message->contexturl = (new \moodle_url('/course/'))->out(false); // A relevant URL for the notification. // TODO.
            $message->contexturlname = 'Course list'; // Link title explaining where users get to for the contexturl.

            // The integer ID of the new message or false if there was a problem
            // ... (with submitted data or sending the message to the message processor).
            $messageid = message_send($message);
        }
    }

    /**
     * @param $context
     *
     * @return array
     */
    public function get_usersbycourse($context): array {
        $enrolledusers = get_enrolled_users($context);
        $users = [];
        foreach ($enrolledusers as $user) {
            $users[] = $user->id;
        }
        return $users;
    }
}
