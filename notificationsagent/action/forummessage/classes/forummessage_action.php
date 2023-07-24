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
class Forummessage_action {

    private int $forumid;
    private int $courseid;

    public function __construct($courseid, $forumid) {
        $this->courseid = $courseid;
        $this->forumid = $forumid;
    }

    public function post_forum($forumid) {

        global $CFG;
        // TODO Get parameters.
        $postsubject = "Post general";
        $postmessage = "Mensaje del post";

        try {
            // Set up the Moodle Web Services client.
            $token = get_config('notificationsaction_forummessage', 'token_name');
            $domain = $CFG->wwwroot;
            $restformat = 'json';

            // Call the Moodle Web Services API to post the message.
            $params = array(
                'forumid' => $forumid,
                'subject' => $postsubject,
                'message' => $postmessage,
                'moodlewsrestformat' => $restformat
            );
            $function = 'mod_forum_add_discussion';
            $serverurl = $domain . '/webservice/rest/server.php' . '?wstoken=' . $token . '&wsfunction='.$function;

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $serverurl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => http_build_query($params, '', '&'),
                /*CURLOPT_HTTPHEADER => [
                    "Content-Type: application/json"
                ],*/
            ));

            $response = curl_exec($curl);

            curl_close($curl);

            // Check the response for errors.
            if (!$response) {
                echo 'Error: No response received.';
            } else {
                $response = json_decode($response, true);
                if (isset($response['exception'])) {
                    echo 'Error: ' . $response['message'];
                } else {
                    echo 'Message posted successfully.';
                }
            }

        } catch (moodle_exception $e) {
            echo $e;
        }
    }

    public function set_log() {
        // TODO.
    }



}
