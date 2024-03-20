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
 * @package    notificationsaction_addusergroup
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace notificationsaction_addusergroup;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . "/group/lib.php");

use local_notificationsagent\notificationactionplugin;
use local_notificationsagent\rule;

class addusergroup extends notificationactionplugin {

    /** @var UI ELEMENTS */
    public const NAME = 'addusergroup';

    public function get_ui($mform, $id, $courseid, $type) {
        $this->get_ui_title($mform, $id, $type);

        // Groups.
        $groups = groups_get_all_groups($courseid, null, null, 'id, name');
        $listgroups = [];

        foreach ($groups as $item) {
            $listgroups[$item->id] = format_string(
                $item->name, true
            );
        }
        // Only is template
        if ($this->rule->get_template() == rule::TEMPLATE_TYPE) {
            $listgroups['0'] = 'GGGG';
        }

        asort($listgroups);
        
        $group = $mform->createElement(
            'select', $this->get_name_ui($id, self::UI_ACTIVITY),
            get_string(
                'editrule_action_element_group', 'notificationsaction_addusergroup',
                ['typeelement' => '[GGGG]']
            ),
            $listgroups
        );
        
        $mform->insertElementBefore($group, 'new' . $type . '_group');

        $mform->addRule($this->get_name_ui($id, self::UI_ACTIVITY), get_string('editrule_required_error', 'local_notificationsagent'), 'required');
    }

    /**
     * @return lang_string|string
     */
    public function get_subtype() {
        return get_string('subtype', 'notificationsaction_addusergroup');
    }

    /**
     * Subplugin title
     *
     * @return \lang_string|string
     */
    public function get_title() {
        return get_string('addusergroup_action', 'notificationsaction_addusergroup');
    }

    /**
     *  Subplugins elements
     *
     * @return string[]
     */
    public function get_elements() {
        return ['[GGGG]'];
    }

    public function check_capability($context) {
        return has_capability('local/notificationsagent:addusergroup', $context);
    }

    /**
     * @param array $params
     *
     * @return mixed
     */
    protected function convert_parameters($id, $params) {
        $params = (array) $params;
        $group = $params[$this->get_name_ui($id, self::UI_ACTIVITY)] ?? 0;
        $this->set_parameters(json_encode([self::UI_ACTIVITY => $group]));
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

        $group = groups_get_group_name($jsonparams->{self::UI_ACTIVITY});

        $paramstoteplace = [shorten_text($group)];

        $humanvalue = str_replace($this->get_elements(), $paramstoteplace, $this->get_title());

        $content[] = $humanvalue;
    }

    public function execute_action($context, $params) {
        // Add user to a specified group.
        $placeholdershuman = json_decode($params);
        $user = $context->get_userid();
        return groups_add_member($placeholdershuman->{self::UI_ACTIVITY}, $user);
    }

    public function is_generic() {
        return true;
    }
}
