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

class notificationsagent_action_bootstrapnotifications extends notificationactionplugin {

    public function get_description() {
        return array(
            'title' => get_string('bootstrapnotifications_action', 'notificationsaction_bootstrapnotifications'),
            'elements' => self::get_elements(),
            'name' => self::get_subtype()
        );
    }

    public function get_ui($mform, $id, $courseid, $exception) {
        $mform->addElement(
            'text', 'action' . $id . '_element' . '6' . '_text',
            get_string(
                'editrule_action_element_text', 'notificationsaction_bootstrapnotifications',
                array('typeelement' => '[TTTT]')
            ));

        $mform->setType('action' . $id . '_element' . '6' . '_text', PARAM_TEXT);

        return $mform;
    }

    /**
     * @return lang_string|string
     */
    public function get_subtype() {
        return get_string('subtype', 'notificationsaction_bootstrapnotifications');
    }

    /**
     * @return lang_string|string
     */
    public function get_name() {
        return get_string('pluginname', 'notificationsaction_bootstrapnotifications');
    }

    public function get_title() {
        // TODO: Implement get_title() method.
    }

    public function get_elements() {
        return array('[TTTT]');
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
    }
}