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
defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->dirroot . "/local/notificationsagent/classes/notificationactionplugin.php");
require_once($CFG->dirroot . "/mod/forum/externallib.php");

class notificationsagent_action_forummessage extends notificationactionplugin {

    public function get_title() {
        return get_string('forummessage_action', 'notificationsaction_forummessage');

    }

    public function get_elements() {
        return array('[FFFF]', '[TTTT]', '[BBBB]');

    }

    public function get_description() {
        return array(
            'title' => self::get_title(),
            'elements' => self::get_elements(),
            'name' => self::get_subtype()
        );
    }

    public function get_ui($mform, $id, $courseid, $exception) {
        global $DB;
        $mform->addElement(
            'text', 'action'.$id.'_element'.'4'.'_title',
            get_string(
                'editrule_action_element_title', 'notificationsaction_messageagent',
                array('typeelement' => '[TTTT]')
            ), array('size' => '64')
        );

        $mform->setType('action'.$id.'_element'.'4'.'_title', PARAM_TEXT);

        $editoroptions = array(
            'maxfiles' => EDITOR_UNLIMITED_FILES,
            'trusttext' => true
        );
        $mform->addElement(
            'editor', 'action'.$id.'_element'.'4'.'_message',
            get_string(
                'editrule_action_element_message', 'notificationsaction_forummessage',
                array('typeelement' => '[BBBB]')
            ),
            ['class' => 'fitem_id_templatevars_editor'], $editoroptions
        );
        $mform->setType('action'.$id.'_message', PARAM_RAW);

        $forumname = array();
        $forumlist = mod_forum_external::get_forums_by_courses(array($courseid));
        foreach ($forumlist as $forum) {
            $forumname[$forum->id] = $forum->name;
        }
        asort($forumname);
        if (empty($forumname)) {
            $forumname['0'] = 'FFFF';
        }
        $mform->addElement('select', 'action'.$id.'_element'.'4'.'_forum', get_string('editrule_action_element_forum',
            'notificationsaction_forummessage', array('typeelement' => '[FFFF]')), $forumname);

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

    public function check_capability() {
        // TODO: Implement check_capability() method.
    }

    /**
     * @param array $params
     *
     * @return mixed
     */
    public function get_parameters($params) {
        // TODO: Implement get_parameters() method.
        return '{"time:77777 }';
    }
}
