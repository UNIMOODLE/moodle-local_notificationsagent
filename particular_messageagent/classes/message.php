<?php

class Particularmessageagent
{
    private int $courseid, $user;
    private array $users;
    private $course_context;

    function __construct ($courseid, $user){
        $this->courseid=$courseid;
        $this->user = $user;
    }

    function send_notification(){

        $message = new \core\message\message();
        $message->component = 'local_particular_messageagent'; // Your plugin's name
        $message->name = 'particular_message'; // Your notification name from message.php
        $message->userfrom = core_user::get_noreply_user(); // If the message is 'from' a specific user you can set them here
        $message->userto = $this->user;
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