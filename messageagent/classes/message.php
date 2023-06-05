<?php

class Messageagent
{
    private int $courseid;
    private array $users;
    private $course_context;

    function __construct ($courseid){
        $this->courseid=$courseid;
        $course_context = context_course::instance($courseid);
        $this->course_context = $course_context;
        $this->users = $this->get_usersbycourse($course_context);

    }

    function send_notification(){

        $users = $this->users;

        foreach ($users as $user){

            $message = new \core\message\message();
            $message->component = 'local_messageagent'; // Your plugin's name
            $message->name = 'individual_message'; // Your notification name from message.php
            $message->userfrom = core_user::get_noreply_user(); // If the message is 'from' a specific user you can set them here
            $message->userto = $user;
            $message->subject = 'Recordatorio examen'; // Será nuestro TTTT
            $message->fullmessage = 'Hola {user_name}'; // Será nuestro BBBB
            $message->fullmessageformat = FORMAT_MARKDOWN;
            $message->fullmessagehtml = '<p>Hola {user_name}</p>';
            $message->smallmessage = 'small message'; //TODO
            $message->notification = 1; // Because this is a notification generated from Moodle, not a user-to-user message
            $message->contexturl = (new \moodle_url('/course/'))->out(false); // A relevant URL for the notification //TODO
            $message->contexturlname = 'Course list'; // Link title explaining where users get to for the contexturl

            // the integer ID of the new message or false if there was a problem (with submitted data or sending the message to the message processor).
            $messageid = message_send($message);
        }

    }

    /**
     * @param $context
     *
     * @return array
     */
    function get_usersbycourse($context): array {
        $enrolled_users = get_enrolled_users($context);
        $users = [];
        foreach ($enrolled_users as $user) {
            $users[] = $user->id;
        }
        return $users;
    }

}