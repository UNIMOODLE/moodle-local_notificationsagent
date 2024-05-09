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
// Produced by the UNIMOODLE University Group: Universities of
// Valladolid, Complutense de Madrid, UPV/EHU, León, Salamanca,
// Illes Balears, Valencia, Rey Juan Carlos, La Laguna, Zaragoza, Málaga,
// Córdoba, Extremadura, Vigo, Las Palmas de Gran Canaria y Burgos.

/**
 * Version details
 *
 * @package    notificationscondition_usergroupadd
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace notificationscondition_usergroupadd;

use local_notificationsagent\evaluationcontext;
use local_notificationsagent\notificationconditionplugin;
use core_calendar\type_factory;
use local_notificationsagent\rule;

/**
 * usergroupadd supluging class
 */
class usergroupadd extends notificationconditionplugin {

    /**
     * Subplugin name
     */
    public const NAME = 'usergroupadd';
    /**
     * User interface description
     */
    public const UI_DESCRIPTION = 'description';

    /**
     * Subplugin title
     *
     * @return \lang_string|string
     */
    public function get_title() {
        return get_string('conditiontext', 'notificationscondition_usergroupadd');
    }

    /**
     *  Subplugins elements
     *
     * @return string[]
     */
    public function get_elements() {
        return ['GGGG'];
    }

    /**
     * Evaluates this condition using the context variables or the system's state and the complementary flag.
     *
     * @param evaluationcontext $context  |null collection of variables to evaluate the condition.
     *                                    If null the system's state is used.
     *
     * @return bool true if the condition is true, false otherwise.
     */
    public function evaluate(evaluationcontext $context): bool {
        global $DB, $USER;

        $condition = false;
        $pluginname = $this->get_subtype();
        $conditionid = $this->get_id();
        $courseid = $context->get_courseid();
        $userid = $context->get_userid();

        $params = json_decode($context->get_params());

        $cache = $DB->record_exists('notificationsagent_cache',
            ['pluginname' => $pluginname, 'conditionid' => $conditionid,
            'courseid' => $courseid, 'userid' => $userid, ]);

        if (!$cache) {
            if (isset($userid)) {
                $isingroup = $DB->record_exists('groups_members',
                ['groupid' => $params->cmid, 'userid' => $userid]);
            }
        }

        if ($cache || $isingroup) {
            $condition = true;
        }

        return $condition;
    }

    /**
     * Estimate next time when this condition will be true.
     *
     * @param evaluationcontext $context
     *
     * @return false|int|mixed|null
     */
    public function estimate_next_time(evaluationcontext $context) {
        $estimate = null;
        $isingroup = $this->evaluate($context);
        // Check if it's in group and condition/exception.
        if ($isingroup) {
            if (!$context->is_complementary()) {
                return time();
            } else {
                return $estimate;
            }
        } else {
            if (!$context->is_complementary()) {
                return $estimate;
            } else {
                return time();
            }
        }
        return $estimate;
    }

    /**
     * Get the UI elements for the subplugin.
     *
     * @param \MoodleQuickForm $mform    The form to which the elements will be added.
     * @param int              $courseid The course identifier.
     * @param string           $type     The type of the notification plugin.
     */
    public function get_ui($mform, $courseid, $type) {
        $this->get_ui_title($mform, $type);

        $groups = groups_get_all_groups($courseid, null, null, 'id, name');
        $listgroups = [];

        if ($this->rule->template == rule::TEMPLATE_TYPE) {
            $listgroups['0'] = 'GGGG';
        }

        foreach ($groups as $item) {
            $listgroups[$item->id] = format_string(
                $item->name, true
            );
        }
        asort($listgroups);

        $group = $mform->createElement(
            'select', $this->get_name_ui(self::UI_ACTIVITY),
            get_string(
                'editrule_action_element_group', 'notificationscondition_usergroupadd',
                ['typeelement' => '[GGGG]']
            ),
            $listgroups
        );

        $mform->insertElementBefore($group, 'new' . $type . '_group');
        $mform->addRule(
            $this->get_name_ui(self::UI_ACTIVITY), get_string('editrule_required_error', 'local_notificationsagent'),
            'required'
        );
    }

    /**
     * Capability for subplugin
     *
     * @param \context $context
     *
     * @return bool
     */
    public function check_capability($context) {
        return has_capability('local/notificationsagent:usergroupadd', $context);
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
        $group = $params[$this->get_name_ui(self::UI_ACTIVITY)] ?? 0;
        $this->set_parameters(json_encode([self::UI_ACTIVITY => (int) $group]));
        $this->set_cmid((int) $group);
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
        !empty(groups_get_group_name($jsonparams->{self::UI_ACTIVITY})) ?
        $group =  groups_get_group_name($jsonparams->{self::UI_ACTIVITY}):
        $group = self::get_elements()[0];
        $groupstring = str_replace($this->get_elements(), $group, $this->get_title());
        $content[] = $groupstring;
    }

    /**
     * Whether a subplugins is generic
     *
     * @return bool
     */
    public function is_generic() {
        return false;
    }

    /**
     * Validation subplugin
     * If this method overrides, call to parent::validation
     *
     * @param int   $courseid           Course id
     * @param array $array              The array to be modified by reference. If is null, validation is not being called from the form
     *                                  and return directly
     * @param bool  $onlyverifysiteid   Default true
     * 
     * @return bool
     */
    public function validation($courseid, &$array = null, $onlyverifysiteid = true) {
        if (($validation = parent::validation($courseid, $array, $onlyverifysiteid)) === 'break') {
            return true;
        }

        // If false from parent and $array is null, return
        if (is_null($array) && !$validation) {
            return $validation;
        }

        $data = json_decode($this->get_parameters(), true);

        if (!groups_group_exists($data[self::UI_ACTIVITY])) {
            $array[$this->get_name_ui(self::UI_ACTIVITY)] = get_string('editrule_required_error', 'local_notificationsagent');
            return $validation = false;
        }

        return $validation;
    }

    /**
     * Update any necessary ids and json parameters in the database.
     * It is called near the completion of course restoration.
     *
     * @param string       $restoreid Restore identifier
     * @param integer      $courseid  Course identifier
     * @param \base_logger $logger    Logger if any warnings
     *
     * @return bool False if restore is not required
     */
    public function update_after_restore($restoreid, $courseid, \base_logger $logger) {
        return false;
    }
}
