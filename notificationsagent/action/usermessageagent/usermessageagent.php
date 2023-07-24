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

class notificationsagent_action_usermessageagent extends notificationactionplugin {

    public function get_description() {
        return array(
            'title' => get_string('usermessageagent_action', 'notificationsaction_usermessageagent'),
            'elements' => self::get_elements(),
            'name' => self::get_subtype()
        );
    }

    public function get_ui($mform, $id, $courseid) {

        $mform->addElement(
            'text', 'action'.$id.'_element'.'3'.'_title',
            get_string(
                'editrule_action_element_title', 'notificationsaction_usermessageagent',
                array('typeelement' => '[TTTT]')
            ), array('size' => '64')
        );

        $mform->setType('action'.$id.'_element'.'3'.'_title', PARAM_TEXT);

        $editoroptions = array(
            'maxfiles' => EDITOR_UNLIMITED_FILES,
            'trusttext' => true
        );
        $mform->addElement(
            'editor', 'action'.$id.'_element'.'3'.'_message',
            get_string(
                'editrule_action_element_message', 'notificationsaction_usermessageagent',
                array('typeelement' => '[BBBB]')
            ),
            ['class' => 'fitem_id_templatevars_editor'], $editoroptions
        );
        $mform->setType('action'.$id.'_message', PARAM_RAW);
        // TODO.
        $context = \context_course::instance($courseid);
        $enrolledusers = get_enrolled_users($context);
        $listusers = array();
        foreach ($enrolledusers as $uservalue) {
            $listusers["user-" . $uservalue->id] = format_string(
                $uservalue->firstname . " " . $uservalue->lastname . " [" . $uservalue->email . "]", true
            );
        }
        self::placeholders($mform,'action'.$id);
        $mform->addElement(
            'select', 'action' . $id . '_element' . '3' . '_user',
            get_string('editrule_action_element_user', 'notificationsaction_usermessageagent', array('typeelement' => '[UUUU]')), $listusers
        );

        return $mform;

    }

    /**
     * @return lang_string|string
     */
    public function get_subtype() {
        return get_string('subtype', 'notificationsaction_usermessageagent');
    }

    /**
     * @return lang_string|string
     */
    public function get_name() {
        return get_string('pluginname', 'notificationsaction_usermessageagent');
    }

    public function get_title() {
        // TODO: Implement get_title() method.
    }

    public function get_elements() {
        return array('[TTTT]', '[BBBB]', '[UUUU]');
    }

    public function check_capability() {
        // TODO: Implement check_capability() method.
    }
}
