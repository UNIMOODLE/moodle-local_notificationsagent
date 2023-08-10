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
        $context = \context_course::instance($courseid);
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
            'select', 'action' . $id . '_element' . '6' . '_user',
            get_string(
                'editrule_action_element_user', 'notificationsaction_removeusergroup',
                array('typeelement' => '[UUUU]')
            ), $listusers
        );

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
            'select', 'action' . $id . '_element' . '6' . '_group',
            get_string(
                'editrule_action_element_group', 'notificationsaction_removeusergroup',
                array('typeelement' => '[GGGG]')
            ), $listgroups
        );

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
        // TODO: Implement get_parameters() method.
        return '{"time:1111 }';
    }
}
