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
 * @package    local_notificationsagent
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->dirroot . "/local/notificationsagent/classes/notificationactionplugin.php");

class notificationsagent_action_addusergroup extends notificationactionplugin {

    public function get_description() {
        return [
            'title' => $this->get_title(),
            'elements' => $this->get_elements(),
            'name' => $this->get_subtype(),
        ];
    }

    public function get_ui($mform, $id, $courseid, $exception) {
        global $SESSION;
        $valuesession = 'id_' . $this->get_subtype() . '_' . $this->get_type() . $exception . $id;

        $mform->addElement('hidden', 'pluginname' . $this->get_type() . $exception . $id, $this->get_subtype());
        $mform->setType('pluginname' . $this->get_type() . $exception . $id, PARAM_RAW);
        $mform->addElement('hidden', 'type' . $this->get_type() . $exception . $id, $this->get_type() . $id);
        $mform->setType('type' . $this->get_type() . $exception . $id, PARAM_RAW);

        $context = \context_course::instance($courseid);

        // Users.
        $enrolledusers = get_enrolled_users($context);
        $listusers = [];
        foreach ($enrolledusers as $uservalue) {
            $listusers[$uservalue->id] = format_string(
                $uservalue->firstname . " " . $uservalue->lastname . " [" . $uservalue->email . "]", true
            );
        }
        if (empty($listusers)) {
            $listusers['0'] = 'UUUU';
        }
        asort($listusers);
        $mform->addElement(
            'select', $this->get_subtype() . '_' . $this->get_type() . $exception . $id . '_user',
            get_string(
                'editrule_action_element_user', 'notificationsaction_addusergroup',
                ['typeelement' => '[UUUU]']
            ),
            $listusers
        );
        if (!empty($SESSION->NOTIFICATIONS['FORMDEFAULT'][$valuesession.'_user'])) {
            $mform->setDefault($this->get_subtype() . '_' . $this->get_type() . $exception . $id . '_user',
            $SESSION->NOTIFICATIONS['FORMDEFAULT'][$valuesession . '_user']);
        }

        // Groups.
        $groups = groups_get_all_groups($courseid, null, null, 'id, name');
        $listgroups = [];

        foreach ($groups as $item) {
            $listgroups[$item->id] = format_string(
                $item->name, true
            );
        }
        if (empty($listgroups)) {
            $listgroups['0'] = 'GGGG';
        }
        asort($listgroups);
        $mform->addElement(
            'select', $this->get_subtype().'_' .$this->get_type() .$exception.$id.'_group',
            get_string(
                'editrule_action_element_group', 'notificationsaction_addusergroup',
                ['typeelement' => '[GGGG]']
            ),
            $listgroups
        );
        if (!empty($SESSION->NOTIFICATIONS['FORMDEFAULT'][$valuesession.'_group'])) {
            $mform->setDefault($this->get_subtype() . '_' . $this->get_type() . $exception . $id . '_group',
            $SESSION->NOTIFICATIONS['FORMDEFAULT'][$valuesession . '_group']);
        }

        return $mform;
    }

    /**
     * @return lang_string|string
     */
    public function get_subtype() {
        return get_string('subtype', 'notificationsaction_addusergroup');
    }

    /**
     * @return lang_string|string
     */
    public function get_name() {
        return get_string('pluginname', 'notificationsaction_addusergroup');
    }

    public function get_title() {
        return get_string('addusergroup_action', 'notificationsaction_addusergroup');
    }

    public function get_elements() {
        return ['[UUUU]', '[GGGG]'];
    }

    public function check_capability($context) {
        return has_capability('local/notificationsagent:addusergroup', $context);
    }

    /**
     * @param array $params
     *
     * @return mixed
     */
    public function convert_parameters($params) {
        $user = "";
        $group = "";
        foreach ($params as $key => $value) {
            if (strpos($key, "user") !== false) {
                $user = $value;
            } else if (strpos($key, $params["type"] . "_" . "group") !== false) {
                $group = $value;
            }
        }

        return json_encode(['user' => $user, 'group' => $group]);
    }

    public function process_markups(&$content, $params, $courseid, $complementary=null) {
        $jsonparams = json_decode($params);

        $user = \core_user::get_user($jsonparams->user, '*', MUST_EXIST);

        $group = groups_get_group_name($jsonparams->group);

        $paramstoteplace = [shorten_text($user->firstname . " " . $user->lastname), shorten_text($group)];

        $humanvalue = str_replace($this->get_elements(), $paramstoteplace, $this->get_title());

        array_push($content, $humanvalue);
    }

    public function execute_action($context, $params) {
         // Add user to a specified group.
         $placeholdershuman = json_decode($params);
         groups_add_member($placeholdershuman->group, $placeholdershuman->user);
    }

    public function is_generic() {
        return false;
    }
}
