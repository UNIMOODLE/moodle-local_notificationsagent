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
// Project implemented by the \"Recovery, Transformation and Resilience Plan.
// Funded by the European Union - Next GenerationEU\".
//
// Produced by the UNIMOODLE University Group: Universities of
// Valladolid, Complutense de Madrid, UPV/EHU, León, Salamanca,
// Illes Balears, Valencia, Rey Juan Carlos, La Laguna, Zaragoza, Málaga,
// Córdoba, Extremadura, Vigo, Las Palmas de Gran Canaria y Burgos.

/**
 * Version details
 *
 * @package    notificationsaction_privateforummessage
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace notificationsaction_privateforummessage;

use local_notificationsagent\evaluationcontext;
use local_notificationsagent\rule;
use local_notificationsagent\notificationactionplugin;

/**
 * Class representing privateforummessage action plugin.
 *
 * This class provides the necessary structure and methods that all notification action plugins should inherit and implement
 * according to their specific needs.
 */
class privateforummessage extends notificationactionplugin {

    /** @var UI ELEMENTS */
    public const NAME = 'privateforummessage';

    /**
     * Subplugin title
     *
     * @return \lang_string|string
     */
    public function get_title() {
        return get_string('privateforummessage_action', 'notificationsaction_privateforummessage');
    }

    /**
     *  Subplugins elements
     *
     * @return string[]
     */
    public function get_elements() {
        return ['[FFFF]', '[TTTT]', '[BBBB]'];
    }

    /**
     * Get the elements for the privateforummessage plugin.
     *
     * @param \moodleform $mform
     * @param int $courseid
     * @param int $type
     */
    public function get_ui($mform, $courseid, $type) {
        $forumname = [];
        $this->get_ui_title($mform, $type);

        // Title.
        $title = $mform->createElement(
                'text', $this->get_name_ui(self::UI_TITLE),
                get_string(
                        'editrule_action_element_title', 'notificationsaction_privateforummessage',
                        ['typeelement' => '[TTTT]']
                ), ['size' => '64']
        );

        $editoroptions = [
                'maxfiles' => EDITOR_UNLIMITED_FILES,
                'trusttext' => true,
        ];

        // Message.
        $message = $mform->createElement(
                'editor', $this->get_name_ui(self::UI_MESSAGE),
                get_string(
                        'editrule_action_element_message', 'notificationsaction_privateforummessage',
                        ['typeelement' => '[BBBB]']
                ),
                ['class' => 'fitem_id_templatevars_editor'], $editoroptions
        );
        // Forum.
        if ($this->rule->template == rule::TEMPLATE_TYPE) {
            $forumname['0'] = 'FFFF';
        } else {
            $forumlist = get_coursemodules_in_course('forum', $courseid);
            foreach ($forumlist as $forum) {
                $forumname[$forum->id] = $forum->name;
            }
        }

        asort($forumname);

        $cm = $mform->createElement(
                'select',
                $this->get_name_ui(self::UI_ACTIVITY),
                get_string(
                        'editrule_action_element_forum',
                        'notificationsaction_privateforummessage',
                        ['typeelement' => '[FFFF]']
                ),
                $forumname
        );

        $this->placeholders($mform, $type, $this->show_user_placeholders());
        $mform->insertElementBefore($title, 'new' . $type . '_group');
        $mform->insertElementBefore($message, 'new' . $type . '_group');
        $mform->insertElementBefore($cm, 'new' . $type . '_group');
        $mform->setType($this->get_name_ui(self::UI_TITLE), PARAM_TEXT);
        $mform->addRule($this->get_name_ui(self::UI_TITLE), ' ', 'required');
        $mform->setType($this->get_name_ui(self::UI_MESSAGE), PARAM_RAW);
        $mform->addRule($this->get_name_ui(self::UI_MESSAGE), ' ', 'required');
        $mform->addRule(
                $this->get_name_ui(self::UI_ACTIVITY), get_string('editrule_required_error', 'local_notificationsagent'),
                'required'
        );

    }

    /**
     * Sublugin capability
     *
     * @param \context $context
     *
     * @return bool
     */
    public function check_capability($context) {
        return has_capability('local/notificationsagent:privateforummessage', $context)
                && has_capability('mod/forum:addnews', $context)
                && has_capability('mod/forum:addquestion', $context)
                && has_capability('mod/forum:startdiscussion', $context);
    }

    /**
     * Convert parameters for the notification plugin.
     *
     * This method should take an identifier and parameters for a notification
     * and convert them into a format suitable for use by the plugin.
     *
     * @param mixed $params The parameters associated with the notification.
     *
     * @return mixed The converted parameters.
     */
    public function convert_parameters($params) {
        $params = (array) $params;
        $title = $params[$this->get_name_ui(self::UI_TITLE)] ?? 0;
        $message = $params[$this->get_name_ui(self::UI_MESSAGE)] ?? 0;
        $forum = $params[$this->get_name_ui(self::UI_ACTIVITY)] ?? 0;
        $this->set_parameters(
                json_encode([self::UI_TITLE => $title, self::UI_MESSAGE => $message, self::UI_ACTIVITY => $forum])
        );
        return $this->get_parameters();
    }

    /**
     * Process and replace markups in the supplied content.
     *
     * This function should handle any markup logic specific to a notification plugin,
     * such as replacing placeholders with dynamic data, formatting content, etc.
     *
     * @param array $content The content to be processed, passed by reference.
     * @param int $courseid The ID of the course related to the content.
     * @param mixed $options Additional options if any, null by default.
     *
     * @return void Processed content with markups handled.
     */
    public function process_markups(&$content, $courseid, $options = null) {
        $jsonparams = json_decode($this->get_parameters(), false);

        // Check if activity is found, if is not, return [FFFF].
        $activityname = '[FFFF]';
        $cmid = $jsonparams->{self::UI_ACTIVITY};

        $fastmodinfo = get_fast_modinfo($courseid);
        $activityname = isset($fastmodinfo->cms[$cmid]) ? $fastmodinfo->cms[$cmid]->name : $activityname;

        $message = $jsonparams->{self::UI_MESSAGE}->text ?? '';
        $paramstoteplace = [
                $activityname,
                shorten_text(str_replace('{' . rule::SEPARATOR . '}', ' ', $jsonparams->{self::UI_TITLE})),
                shorten_text(format_string(str_replace('{' . rule::SEPARATOR . '}', ' ', $message))),
        ];

        $humanvalue = str_replace($this->get_elements(), $paramstoteplace, $this->get_title());

        $content[] = $humanvalue;
    }

    /**
     * Execute an action with the given parameters in the specified context.
     *
     * @param evaluationcontext $context The context in which the action is executed.
     * @param string $params An associative array of parameters for the action.
     *
     * @return mixed The result of the action execution.
     */
    public function execute_action($context, $params) {
        // Post a message on a forum.

        global $DB;
        $placeholdershuman = json_decode($params);
        $postsubject = format_text($placeholdershuman->{self::UI_TITLE}, FORMAT_PLAIN);
        $postmessage = notificationactionplugin::get_message_by_timesfired($context, $placeholdershuman->{self::UI_MESSAGE});
        $time = 0;
        $modinfo = get_fast_modinfo($context->get_courseid(), -1);
        $forumid = $modinfo->get_cm($placeholdershuman->{self::UI_ACTIVITY})->instance;
        $userid = $context->get_userid();
        $courseid = $context->get_courseid();
        // Set up the Moodle Web Services client.

        $timenow = time();
        $pluginname = self::NAME;
        $condition = $context->get_rule()->get_condition($pluginname);
        if ($condition) {
            $decode = $condition->get_parameters();
            $param = json_decode($decode);
            $time = $param->{self::UI_TIME};
        }

        $sqlthreads = "SELECT DISTINCT fd.id AS discussionid, fp.id AS postid
                      FROM {forum_discussions} fd
                      JOIN {forum_posts} fp ON fp.discussion=fd.id AND fp.parent = 0
                 LEFT JOIN {forum_posts} fp2 ON fp.id = fp2.parent
                     WHERE fd.forum = :forum
                       AND fd.course = :course
                       AND fd.timestart >= :timestart
                       AND (fd.timeend = :timeend OR fd.timeend > :timenow)
                       AND timemodified < :timenowandtime
                       AND fp2.id IS NULL
                       AND fd.userid = :usertrigger
            ";

        $threads = $DB->get_records_sql($sqlthreads, [
                'forum' => $forumid,
                'course' => $courseid,
                'timestart' => 0,
                'timeend' => 0,
                'timenow' => $timenow,
                'timenowandtime' => $timenow + $time,
                'usertrigger' => $userid,
        ]);

        if (empty($threads)) {
            return false;
        }

        $result = false;
        foreach ($threads as $thread) {
            $obj = new \stdClass();
            $obj->discussion = $thread->discussionid;
            $obj->parent = $thread->postid;
            $obj->privatereplyto = $userid;
            $obj->userid = get_admin()->id;
            $obj->created = $timenow;
            $obj->modified = $timenow;
            $obj->mailed = FORUM_MAILED_PENDING;
            $obj->subject = $postsubject;
            $obj->message = $postmessage;
            $obj->forum = $forumid;
            $obj->course = $courseid;

            $result = self::forum_add_post($obj);
        }

        return $result;
    }

    /**
     * Whether a subluplugin is generic
     *
     * @return bool
     */
    public function is_generic() {
        return false;
    }

    /**
     * Returns the parameters to be replaced in the placeholders
     *
     * @return string $json Parameters
     */
    public function get_parameters_placeholders() {
        $parameters = json_decode($this->get_parameters());

        return json_encode([
                'title' => $parameters->{self::UI_TITLE},
                'message' => $parameters->{self::UI_MESSAGE}->text,
                'cmid' => $parameters->{self::UI_ACTIVITY},
        ]);
    }

    /**
     * Replying private to a post.
     *
     * @param \stdClass $obj
     *
     * @return bool|int
     * @throws \dml_exception
     */
    public static function forum_add_post($obj) {
        global $DB;

        $timenow = $obj->timenow ?? time();

        $forum = $DB->get_record('forum', ['id' => $obj->forum]);

        $post = new \stdClass();
        $post->discussion = $obj->discussion;
        $post->parent = $obj->parent;
        $post->privatereplyto = $obj->privatereplyto; // User we are replying to.
        $post->userid = $obj->userid; // Admin in replying.
        $post->created = $timenow;
        $post->modified = $timenow;
        $post->mailed = FORUM_MAILED_PENDING;
        $post->subject = $obj->subject;
        $post->message = $obj->message;
        $post->messageformat = FORMAT_HTML;
        $post->forum = $forum->id;
        $post->course = $forum->course;

        \mod_forum\local\entities\post::add_message_counts($post);
        $post->id = $DB->insert_record("forum_posts", $post);

        return $post->id;
    }
}
