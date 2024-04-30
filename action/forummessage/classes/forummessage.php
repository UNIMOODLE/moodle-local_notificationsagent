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
 * @package    notificationsaction_forummessage
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace notificationsaction_forummessage;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . "/mod/forum/externallib.php");

use local_notificationsagent\rule;
use local_notificationsagent\notificationsagent;
use local_notificationsagent\notificationactionplugin;

/**
 * Class representing the forummessage action plugin.
 */
class forummessage extends notificationactionplugin {
    /** @var UI ELEMENTS */
    public const NAME = 'forummessage';

    /**
     * Subplugin title
     *
     * @return \lang_string|string
     */
    public function get_title() {
        return get_string('forummessage_action', 'notificationsaction_forummessage');
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
     * Get the elements for the forummessage plugin.
     *
     * @param \moodleform $mform
     * @param int         $courseid
     * @param int         $type
     */
    public function get_ui($mform, $courseid, $type) {
        $this->get_ui_title($mform, $type);
        // Title.
        $title = $mform->createElement(
            'text', $this->get_name_ui(self::UI_TITLE),
            get_string(
                'editrule_action_element_title', 'notificationsaction_forummessage',
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
                'editrule_action_element_message', 'notificationsaction_forummessage',
                ['typeelement' => '[BBBB]']
            ),
            ['class' => 'fitem_id_templatevars_editor'], $editoroptions
        );
        // Forum.
        $forumname = [];

        if ($this->rule->template == rule::TEMPLATE_TYPE) {
            $forumname['0'] = 'FFFF';
        } else {
            if ($courseid == SITEID) {
                $forumname[notificationsagent::FORUM_NEWS_CMID] = get_string('frontpagenews');
            } else {
                $forumlist = get_coursemodules_in_course('forum', $courseid);
                foreach ($forumlist as $forum) {
                    $forumname[$forum->id] = $forum->name;
                }
            }
        }

        asort($forumname);

        $cm = $mform->createElement(
            'select',
            $this->get_name_ui(self::UI_ACTIVITY),
            get_string(
                'editrule_action_element_forum',
                'notificationsaction_forummessage',
                ['typeelement' => '[FFFF]']
            ),
            $forumname
        );
        $this->placeholders($mform, $type, $this->show_user_placeholders());
        $mform->insertElementBefore($title, 'new' . $type . '_group');
        $mform->insertElementBefore($message, 'new' . $type . '_group');
        $mform->insertElementBefore($cm, 'new' . $type . '_group');
        $mform->setType($this->get_name_ui(self::UI_TITLE), PARAM_TEXT);
        $mform->addRule($this->get_name_ui(self::UI_TITLE), null, 'required', null, 'client');
        $mform->setType($this->get_name_ui(self::UI_MESSAGE), PARAM_RAW);
        $mform->addRule($this->get_name_ui(self::UI_MESSAGE), null, 'required', null, 'client');
        $mform->addRule(
            $this->get_name_ui(self::UI_ACTIVITY), get_string('editrule_required_error', 'local_notificationsagent'), 'required'
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
        return has_capability('local/notificationsagent:forummessage', $context)
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
     * @param array $content  The content to be processed, passed by reference.
     * @param int   $courseid The ID of the course related to the content.
     * @param mixed $options  Additional options if any, null by default.
     *
     * @return void Processed content with markups handled.
     */
    public function process_markups(&$content, $courseid, $options = null) {
        $jsonparams = json_decode($this->get_parameters());

        // Check if activity is found, if is not, return [FFFF].
        $activityname = '[FFFF]';
        $cmid = $jsonparams->{self::UI_ACTIVITY};
        if ($cmid == notificationsagent::FORUM_NEWS_CMID) {
            $activityname = get_string('frontpagenews');
        } else {
            $fastmodinfo = get_fast_modinfo($courseid);
            $activityname = isset($fastmodinfo->cms[$cmid]) ? $fastmodinfo->cms[$cmid]->name : $activityname;
        }

        $message = $jsonparams->{self::UI_MESSAGE}->text ?? '';
        $paramstoteplace = [
            shorten_text($activityname),
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
     * @param string            $params  An associative array of parameters for the action.
     *
     * @return mixed The result of the action execution.
     */
    public function execute_action($context, $params) {
        // Post a message on a forum.

        $placeholdershuman = json_decode($params);
        $postsubject = format_text($placeholdershuman->{self::UI_TITLE}, FORMAT_PLAIN);
        $postmessage = notificationactionplugin::get_message_by_timesfired($context, $placeholdershuman->{self::UI_MESSAGE});

        $modinfo = get_fast_modinfo($context->get_courseid());

        if ($placeholdershuman->{self::UI_ACTIVITY} == notificationsagent::FORUM_NEWS_CMID) {
            $forumid = notificationactionplugin::get_news_forum($context->get_courseid());
        } else {
            $forumid = $modinfo->get_cm($placeholdershuman->{self::UI_ACTIVITY})->instance;
        }

        $discussion = new \stdClass();
        $discussion->forum = $forumid;
        $discussion->course = $context->get_courseid();
        $discussion->name = $postsubject;
        $discussion->message = format_text($postmessage);
        $discussion->messageformat = FORMAT_HTML;
        $discussion->pinned = FORUM_DISCUSSION_UNPINNED;
        $discussion->messagetrust = 0;
        $discussion->attatchment = null;
        $discussion->timelocked = 0;
        $discussion->mailnow = true;
        $discussion->groupid = -1;
        $discussion->timestart = 0;
        $discussion->timeend = 0;

        if (!$discussion->id = forum_add_discussion($discussion, null, null, $context->get_rule()->get_createdby())) {
            throw new \moodle_exception('couldnotadd', 'forum', '', $discussion->name);
        }
        return $discussion->id;
    }

    /**
     * Whether a subluplugin is generic
     *
     * @return bool
     */
    public function is_generic() {
        return true;
    }

    /**
     * Check if the action will be sent once or not
     *
     * @param integer $userid User id
     *
     * @return bool $sendonce Will the action be sent once?
     */
    public function is_send_once($userid) {
        return true;
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
     *  Show placeholders relatives to user fields.
     *
     * @return bool
     */
    public function show_user_placeholders() {
        return false;
    }
}
