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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.
defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->dirroot . "/local/notificationsagent/classes/notificationactionplugin.php");
require_once($CFG->dirroot . "/mod/forum/externallib.php");

class notificationsagent_action_forummessage extends notificationactionplugin {

    public function get_title() {
        return get_string('forummessage_action', 'notificationsaction_forummessage');
    }

    public function get_elements() {
        return ['[FFFF]', '[TTTT]', '[BBBB]'];
    }

    public function get_description() {
        return [
            'title' => self::get_title(),
            'elements' => self::get_elements(),
            'name' => self::get_subtype(),
        ];
    }

    public function get_ui($mform, $id, $courseid, $exception) {
        global $SESSION;
        $valuesession = 'id_' . $this->get_subtype() . '_' . $this->get_type() . $exception . $id;

        $mform->addElement('hidden', 'pluginname' . $this->get_type() . $exception . $id, $this->get_subtype());
        $mform->setType('pluginname' . $this->get_type() . $exception . $id, PARAM_RAW);
        $mform->addElement('hidden', 'type' . $this->get_type() . $exception . $id, $this->get_type() . $id);
        $mform->setType('type' . $this->get_type() . $exception . $id, PARAM_RAW);

        self::placeholders($mform, 'action' . $id, 'message');

        // Title.
        $mform->addElement(
            'text', $this->get_subtype() . '_' . $this->get_type() . $exception . $id . '_title',
            get_string(
                'editrule_action_element_title', 'notificationsaction_forummessage',
                ['typeelement' => '[TTTT]']
            ), ['size' => '64']
        );
        $mform->addRule(
            $this->get_subtype() . '_' . $this->get_type() . $exception . $id . '_title', null, 'required', null, 'client'
        );

        $mform->setType($this->get_subtype() . '_' . $this->get_type() . $exception . $id . '_title', PARAM_TEXT);

        if (!empty($SESSION->NOTIFICATIONS['FORMDEFAULT'][$valuesession . '_title'])) {
            $mform->setDefault($this->get_subtype() . '_' . $this->get_type() . $exception . $id . '_title',
            $SESSION->NOTIFICATIONS['FORMDEFAULT'][$valuesession . '_title']);
        }

        $editoroptions = [
            'maxfiles' => EDITOR_UNLIMITED_FILES,
            'trusttext' => true,
        ];

        // Message.
        $mform->addElement(
            'editor', $this->get_subtype() . '_' . $this->get_type() . $exception . $id . '_message',
            get_string(
                'editrule_action_element_message', 'notificationsaction_forummessage',
                ['typeelement' => '[BBBB]']
            ),
            ['class' => 'fitem_id_templatevars_editor'], $editoroptions
        )->setValue(!empty($SESSION->NOTIFICATIONS['FORMDEFAULT'][$valuesession . '_message'])
        ? ['text' => $SESSION->NOTIFICATIONS['FORMDEFAULT'][$valuesession . '_message']]
        : null);
        $mform->setType($this->get_subtype() . '_' . $this->get_type() . $exception . $id . '_message', PARAM_RAW);
        $mform->addRule($this->get_subtype() . '_' . $this->get_type() . $exception . $id . '_message',
        null, 'required', null, 'client');

        // Forum.
        $forumname = [];
        $forumlist = mod_forum_external::get_forums_by_courses([$courseid]);
        foreach ($forumlist as $forum) {
            $forumname[$forum->id] = $forum->name;
        }
        asort($forumname);
        if (empty($forumname)) {
            $forumname['0'] = 'FFFF';
            $forumname['-1'] = 'Announcements';
        }
        $mform->addElement(
            'select',
            $this->get_subtype() . '_' . $this->get_type() . $exception . $id . '_forum',
            get_string(
                'editrule_action_element_forum',
                'notificationsaction_forummessage',
                ['typeelement' => '[FFFF]']
            ),
            $forumname
        );

        if (!empty($SESSION->NOTIFICATIONS['FORMDEFAULT'][$valuesession . '_forum'])) {
            $mform->setDefault($this->get_subtype() . '_' . $this->get_type() . $exception . $id . '_forum',
            $SESSION->NOTIFICATIONS['FORMDEFAULT'][$valuesession . '_forum']);
        }

        return $mform;
    }

    public function get_subtype() {
        return get_string('subtype', 'notificationsaction_forummessage');
    }

    /**
     * @inheritDoc
     */
    public function get_name() {
        return get_string('pluginname', 'notificationsaction_forummessage');
    }

    public function check_capability($context) {
        if (has_capability('local/notificationsagent:forummessage', $context) &&
            has_capability('mod/forum:addnews', $context) &&
            has_capability('mod/forum:addquestion', $context) &&
            has_capability('mod/forum:startdiscussion', $context)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param array $params
     *
     * @return mixed
     */
    public function convert_parameters($params) {
        $title = "";
        $message = "";
        $forum = "";
        foreach ($params as $key => $value) {
            if (strpos($key, "title") !== false) {
                $title = $value;
            } else if (strpos($key, "message") !== false) {
                $message = $value["text"];
            } else if (strpos($key, "forum") !== false) {
                $forum = $value;
            }
        }

        return json_encode(['title' => $title, 'message' => $message, 'forum' => $forum]);
    }

    public function process_markups(&$content, $params, $courseid, $complementary=null) {
        global $DB;

        $jsonparams = json_decode($params);

        $forum = new \stdClass();

        if ($jsonparams->forum > 0) {
            $forum = $DB->get_record('forum', ['id' => $jsonparams->forum], 'name', MUST_EXIST);
        } else {
            if ($jsonparams->forum == 0) {
                $forum->name = 'FFFF';
            } else if ($jsonparams->forum == -1) {
                $forum->name = 'Announcements';
            }
        }

        $paramstoteplace = [
            shorten_text($forum->name), shorten_text($jsonparams->title), shorten_text(format_string($jsonparams->message)),
        ];

        $humanvalue = str_replace($this->get_elements(), $paramstoteplace, $this->get_title());

        array_push($content, $humanvalue);
    }

    public function execute_action($context, $params) {
        // Post a message on a forum.

        $placeholdershuman = json_decode($params);
        $postsubject = format_text($placeholdershuman->title, FORMAT_PLAIN);
        $postmessage = notificationactionplugin::get_message_by_timesfired($context, $placeholdershuman->message);

        $discussion = new stdClass();
        $discussion->forum = $placeholdershuman->forum;
        $discussion->course = $context->get_courseid();
        $discussion->name = $postsubject;
        $discussion->message = format_text($postmessage);
        $discussion->messageformat = FORMAT_HTML;
        $discussion->pinned = 1;
        $discussion->messagetrust = 0;
        $discussion->attatchment = null;
        $discussion->timelocked = 0;
        $discussion->mailnow = true;
        $discussion->groupid = -1;
        $discussion->timestart = 0;
        $discussion->timeend = 0;

        if ($discussion->id = forum_add_discussion($discussion, null, null, $context->get_rule()->get_createdby())) {
            $cm = get_coursemodule_from_instance('forum', $placeholdershuman->forum);
            $modulecontext = context_module::instance($cm->id);
            $forumparams = [
                'userid' => $context->get_rule()->get_createdby(),
                'context' => $modulecontext,
                'objectid' => $discussion->id,
                'other' => [
                    'forumid' => $placeholdershuman->forum,
                ],
            ];

            $event = \mod_forum\event\discussion_created::create($forumparams);
            $event->add_record_snapshot('forum_discussions', $discussion);
            $event->trigger();
        } else {
            throw new \moodle_exception('couldnotadd', 'forum', '', $discussion->name);
        }

        return true;
    }

    public function is_generic() {
        return true;
    }

    /**
     * Check if the action will be sent once or not
     * @param integer $userid User id
     *
     * @return bool $sendonce Will the action be sent once?
     */
    public function is_send_once($userid) {
        return true;
    }
}
