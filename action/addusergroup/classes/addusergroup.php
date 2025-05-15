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

// Project implemented by the "Recovery, Transformation and Resilience Plan.
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
use local_notificationsagent\evaluationcontext;

/**
 * Class representing the addusergroup action plugin.
 */
class addusergroup extends notificationactionplugin {
    /** @var UI ELEMENTS */
    public const NAME = 'addusergroup';

    /**
     * Get the elements for the addusergroup plugin.
     *
     * @param \moodleform $mform
     * @param int $courseid
     * @param int $type
     */
    public function get_ui($mform, $courseid, $type) {
        $this->get_ui_title($mform, $type);

        // Groups.
        $groups = groups_get_all_groups($courseid, null, null, 'g.id, g.name');
        $listgroups = [];

        foreach ($groups as $item) {
            $listgroups[$item->id] = format_string(
                $item->name,
                true
            );
        }
        // Only is template.
        if ($this->rule->template == rule::TEMPLATE_TYPE) {
            $listgroups['0'] = 'GGGG';
        }

        asort($listgroups);

        $group = $mform->createElement(
            'select',
            $this->get_name_ui(self::UI_ACTIVITY),
            get_string(
                'editrule_action_element_group',
                'notificationsaction_addusergroup',
                ['typeelement' => '[GGGG]']
            ),
            $listgroups
        );

        $mform->insertElementBefore($group, 'new' . $type . '_group');

        $mform->addRule(
            $this->get_name_ui(self::UI_ACTIVITY),
            get_string('editrule_required_error', 'local_notificationsagent'),
            'required'
        );
    }

    /**
     * Get the title of the addusergroup action plugin.
     *
     * @return string Title of the plugin.
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

    /**
     * Sublugin capability
     *
     * @param \context $context
     *
     * @return bool
     */
    public function check_capability($context) {
        return has_capability('notificationsaction/addusergroup:addusergroup', $context);
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
        $this->set_parameters(json_encode([self::UI_ACTIVITY => $group]));
        return $this->get_parameters();
    }

    /**
     * Process and replace markups in the supplied content.
     *
     * This function should handle any markup logic specific to a notification plugin,
     * such as replacing placeholders with dynamic data, formatting content, etc.
     *
     * @param array $content The content to be processed, passed by reference.
     * @param int $courseid The ID of the course related to the content.
     * @param mixed $options Additional options if any, null by default.
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

    /**
     * Execute an action with the given parameters in the specified context.
     *
     * @param evaluationcontext $context The context in which the action is executed.
     * @param string $params An associative array of parameters for the action.
     *
     * @return mixed The result of the action execution.
     */
    public function execute_action($context, $params) {
        // Add user to a specified group.
        $placeholdershuman = json_decode($params);
        $user = $context->get_userid();
        return groups_add_member($placeholdershuman->{self::UI_ACTIVITY}, $user);
    }

    /**
     * Check if the action is generic or not.
     *
     * @return bool
     */
    public function is_generic() {
        return true;
    }

    /**
     * Validation subplugin
     *
     * @param int $courseid Course id
     * @param array $array The array to be modified by reference. If is null, validation is not being called from the form
     *                                  and return directly
     * @param bool $onlyverifysiteid Default true
     *
     * @return bool
     */
    public function validation($courseid, &$array = null, $onlyverifysiteid = true) {
        if (($validation = parent::validation($courseid, $array, $onlyverifysiteid)) === 'break') {
            return true;
        }

        // If  it is false from parent and $array is null, return.
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
     * Check event observer
     *
     * @param array $params
     *
     * @return bool
     */
    public function check_event_observer($params) {
        $check = false;

        $groupid = $params[self::UI_ACTIVITY];
        $data = json_decode($this->get_parameters(), true);
        $datagroupid = $data[self::UI_ACTIVITY] ?? 0;
        if ($datagroupid == $groupid) {
            $check = true;
        }

        return $check;
    }

    /**
     * Update any necessary ids and json parameters in the database.
     * It is called near the completion of course restoration.
     *
     * @param string $restoreid Restore identifier
     * @param int $courseid Course identifier
     * @param \base_logger $logger Logger if any warnings
     *
     * @return bool|void False if restore is not required
     */
    public function update_after_restore($restoreid, $courseid, \base_logger $logger) {
        global $DB;

        $oldgroupid = json_decode($this->get_parameters())->{self::UI_ACTIVITY};
        $rec = \restore_dbops::get_backup_ids_record($restoreid, 'group', $oldgroupid);

        if (!$rec || !$rec->newitemid) {
            // If we are on the same course (e.g. duplicate) then we can just
            // use the existing one.
            if ($DB->record_exists('groups', ['id' => $oldgroupid, 'course' => $courseid])) {
                return false;
            }
            // Otherwise it's a warning.
            $logger->process(
                'Restored item (' . $this->get_pluginname() . ')
                has groupid on action that was not restored',
                \backup::LOG_WARNING
            );
        } else {
            $newparameters = json_decode($this->get_parameters());
            $newparameters->{self::UI_ACTIVITY} = $rec->newitemid;
            $newparameters = json_encode($newparameters);

            $record = new \stdClass();
            $record->id = $this->get_id();
            $record->parameters = $newparameters;

            $DB->update_record('notificationsagent_action', $record);
        }
    }
}
