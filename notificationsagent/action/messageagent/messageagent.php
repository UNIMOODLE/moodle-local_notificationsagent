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

class notificationsagent_action_messageagent extends notificationactionplugin {
    public function get_description() {
        return array(
            'title' => self::get_title(),
            'elements' => self::get_elements(),
            'name' => self::get_subtype()
        );
    }

    public function get_ui($mform, $id, $courseid, $exception) {
        global $SESSION;
        $valuesession = 'id_'.$this->get_subtype().'_' .$this->get_type() .$exception.$id;
        
        $mform->addElement('hidden', 'pluginname'.$this->get_type().$id,$this->get_subtype());
        $mform->setType('pluginname'.$this->get_type().$id,PARAM_RAW );
        $mform->addElement('hidden', 'type'.$this->get_type().$id,$this->get_type().$id);
        $mform->setType('type'.$this->get_type().$id, PARAM_RAW );
        
        $mform->addElement(
            'text', $this->get_subtype().'_' .$this->get_type() .$exception.$id.'_title',
            get_string(
                'editrule_action_element_title', 'notificationsaction_forummessage',
                array('typeelement' => '[TTTT]')
            ), array('size' => '64')
        );

        $mform->setType($this->get_subtype().'_' .$this->get_type() .$exception.$id.'_title', PARAM_TEXT);

        if(!empty($SESSION->NOTIFICATIONS['FORMDEFAULT'][$valuesession.'_title'])){
            $mform->setDefault($this->get_subtype().'_' .$this->get_type() .$exception.$id.'_title',
            $SESSION->NOTIFICATIONS['FORMDEFAULT'][$valuesession.'_title']);
        }

        $editoroptions = array(
            'maxfiles' => EDITOR_UNLIMITED_FILES,
            'trusttext' => true
        );

        $mform->addElement(
            'editor', $this->get_subtype().'_' .$this->get_type() .$exception.$id.'_message',
            get_string(
                'editrule_action_element_message', 'notificationsaction_forummessage',
                array('typeelement' => '[BBBB]')
            ),
            ['class' => 'fitem_id_templatevars_editor'], $editoroptions
        )->setValue(!empty($SESSION->NOTIFICATIONS['FORMDEFAULT'][$valuesession.'_message']) 
        ? array('text' => $SESSION->NOTIFICATIONS['FORMDEFAULT'][$valuesession.'_message'])
        : null);
        $mform->setType($this->get_subtype().'_' .$this->get_type() .$exception.$id.'_message', PARAM_RAW);

        self::placeholders($mform, 'action'.$id);

        return $mform;
    }

    /**
     * @return lang_string|string
     */
    public function get_subtype() {
        return get_string('subtype', 'notificationsaction_messageagent');
    }

    /**
     * @return lang_string|string
     */
    public function get_name() {
        return get_string('pluginname', 'notificationsaction_messageagent');
    }

    public function get_title() {
        return get_string('messageagent_action', 'notificationsaction_messageagent');
    }

    public function get_elements() {
        return array('[TTTT]', '[BBBB]');

    }

    public function check_capability() {
        // TODO: Implement check_capability() method.
        return false;
    }

    /**
     * @param array $params
     *
     * @return mixed
     */
    public function get_parameters($params) {
        $title = "";
        $message = "";
        
        foreach ($params as $key => $value) {
            if (strpos($key, "title") !== false) {
                $title = $value;
            } elseif (strpos($key, "message") !== false) {
                $message = $value;
            }
        }

    return json_encode(array('title' => $title, 'message' => $message));
    }
}
