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

class notificationsagent_action_removeusergroup extends notificationactionplugin {

    public function get_description() {
        return array(
            'title' => get_string('removeusergroup_action', 'notificationsaction_removeusergroup'),
            'elements' => self::get_elements(),
            'name' => self::get_subtype()
        );
    }

    public function get_ui($mform, $id, $courseid, $exception) {
        global $SESSION;
        $valuesession = 'id_'.$this->get_subtype().'_' .$this->get_type() .$exception.$id;

        $mform->addElement('hidden', 'pluginname'.$this->get_type().$exception.$id,$this->get_subtype());
        $mform->setType('pluginname'.$this->get_type().$exception.$id,PARAM_RAW );
        $mform->addElement('hidden', 'type'.$this->get_type().$exception.$id,$this->get_type().$id);
        $mform->setType('type'.$this->get_type().$exception.$id, PARAM_RAW );

        $context = \context_course::instance($courseid);

        //Users.
        $enrolledusers = get_enrolled_users($context);
        $listusers = array();
        foreach ($enrolledusers as $uservalue) {
            $listusers["user-" . $uservalue->id] = format_string(
                $uservalue->firstname . " " . $uservalue->lastname . " [" . $uservalue->email . "]", true
            );
        }
        if(empty($listusers)){
            $listusers['user-0'] = 'UUUU';
        }
        asort($listusers);
        $mform->addElement(
            'select', $this->get_subtype().'_' .$this->get_type() .$exception.$id.'_user',
            get_string(
                'editrule_action_element_user', 'notificationsaction_addusergroup',
                array('typeelement' => '[UUUU]')
            ),
            $listusers
        );
        if(!empty($SESSION->NOTIFICATIONS['FORMDEFAULT'][$valuesession.'_user'])){
            $mform->setDefault($this->get_subtype().'_' .$this->get_type() .$exception.$id.'_user',
            $SESSION->NOTIFICATIONS['FORMDEFAULT'][$valuesession.'_user']);
        }

        //Groups.
        $groups = groups_get_all_groups($courseid, null, null, 'id, name');
        $listgroups = array();

        foreach ($groups as $item) {
            $listgroups["group-" . $item->id] = format_string(
                $item->name, true
            );
        }
        if (empty($listgroups)) {
            $listgroups['group-0'] = 'GGGG';
        }
        asort($listgroups);
        $mform->addElement(
            'select', $this->get_subtype().'_' .$this->get_type() .$exception.$id.'_group',
            get_string(
                'editrule_action_element_group', 'notificationsaction_addusergroup',
                array('typeelement' => '[GGGG]')
            ),
            $listgroups
        );
        if(!empty($SESSION->NOTIFICATIONS['FORMDEFAULT'][$valuesession.'_group'])){
            $mform->setDefault($this->get_subtype().'_' .$this->get_type() .$exception.$id.'_group',
            $SESSION->NOTIFICATIONS['FORMDEFAULT'][$valuesession.'_group']);
        }

        return $mform;

    }

    /**
     * @return lang_string|string
     */
    public function get_subtype() {
        return get_string('subtype', 'notificationsaction_removeusergroup');
    }

    /**
     * @return lang_string|string
     */
    public function get_name() {
        return get_string('pluginname', 'notificationsaction_removeusergroup');
    }

    public function get_title() {
        // TODO: Implement get_title() method.
    }

    public function get_elements() {
        return array('[GGGG]', '[UUUU]');
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
        $user = "";
        $group = "";
    
        foreach ($params as $key => $value) {
            if (strpos($key, "user") !== false){
                $user = $value;

            } elseif (strpos($key, "group") !== false) {
                $group = $value;
            }
        }
    
        return json_encode(array('user' => $user, 'group' => $group));
    }
}
