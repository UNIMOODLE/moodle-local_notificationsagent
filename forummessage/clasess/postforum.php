<?php


class Postforum
{
    private int $forumid;
    private int $courseid;

    function __construct($courseid, $forumid)    {
        $this->courseid = $courseid;
        $this->forumid = $forumid;
    }

    function post_forum($forumid){

        $post = new \mod_forum_external();
        $post_subject = "Post general";
        $post_message = "Mensaje del post";
        // TODO. Use webservice to post on forum
        try {
            $post::add_discussion($forumid, $post_subject, $post_message);

        } catch (moodle_exception $e) {
            echo $e;
        }
    }

    function set_log(){
        // TODO
        //
    }



}