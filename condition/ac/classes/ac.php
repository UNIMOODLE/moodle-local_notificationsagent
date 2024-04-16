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
 * @package    notificationscondition_ac
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace notificationscondition_ac;

use local_notificationsagent\evaluationcontext;
use local_notificationsagent\notificationconditionplugin;
use notificationscondition_ac\mod_ac_availability_info;
use local_notificationsagent\form\editrule_form;

/**
 * Class representing the availability condition plugin.
 */
class ac extends notificationconditionplugin {

    /**
     * Subplugin name
     */
    public const NAME = 'ac';

    /**
     * Subplugin title
     *
     * @return \lang_string|string
     */
    public function get_title() {
        return get_string('conditiontext', 'notificationscondition_ac');
    }

    /**
     *  Subplugins elements
     *
     * @return string[]
     */
    public function get_elements() {}

    /**
     * Get the subtype of the condition.
     *
     * @return string The subtype of the condition.
     */
    public function get_subtype() {
        return get_string('subtype', 'notificationscondition_ac');
    }

    /** Evaluates this condition using the context variables or the system's state and the complementary flag.
     *
     * @param evaluationcontext $context  |null collection of variables to evaluate the condition.
     *                                    If null the system's state is used.
     *
     * @return bool true if the condition is true, false otherwise.
     */
    public function evaluate(evaluationcontext $context): bool {
        global $DB;

        $meetcondition = false;

        $courseid = $context->get_courseid();
        $params = $context->get_params();
        $pluginname = $this->get_subtype();
        $conditionid = $this->get_id();
        $userid = $context->get_userid();

        $cacheexists = $DB->record_exists('notificationsagent_cache', [
            'pluginname' => $pluginname, 'conditionid' => $conditionid,
            'courseid' => $courseid, 'userid' => $userid,
        ]);

        if ($cacheexists) {
            $meetcondition = true;
        }

        if (!$cacheexists) {
            $info = new mod_ac_availability_info($courseid, $params);
            $information = "";
            $meetcondition = $info->is_available($information, false, $userid);
        }

        return $meetcondition;
    }

    /** Estimate next time when this condition will be true.
     *
     * @param evaluationcontext $context
     */
    public function estimate_next_time(evaluationcontext $context) {
        return null;
    }

    /**
     * Get the UI for the condition.
     *
     * @param \moodleform $mform
     * @param int         $id
     * @param int         $courseid
     * @param string      $exception
     */
    public function get_ui($mform, $id, $courseid, $exception) {
        return '';
    }

    /**
     * Sublugin capability
     *
     * @param \context $context
     *
     * @return bool
     */
    public function check_capability($context) {
        return false;
    }

    /**
     * Convert parameters for the notification plugin.
     *
     * This method should take an identifier and parameters for a notification
     * and convert them into a format suitable for use by the plugin.
     *
     * @param int   $id     The identifier for the notification.
     * @param mixed $params The parameters associated with the notification.
     *
     * @return mixed The converted parameters.
     */
    protected function convert_parameters($id, $params) {
        $params = (array) $params;
        $this->set_parameters($params[editrule_form::FORM_JSON_AC]);
        return $this->get_parameters();
    }

    /**
     * This function should handle any markup logic specific to a notification plugin, such as replacing placeholders with dynamic
     * data, formatting content, etc.
     *
     * @param string $content  — The content to be processed, passed by reference.
     * @param int    $courseid — The ID of the course related to the content.
     * @param mixed  $options  — Additional options if any, null by default.
     * @param mixed  $complementary
     *
     */
    public function process_markups(&$content, $courseid, $complementary = null) {
        $info = new mod_ac_availability_info($courseid, $this->get_parameters());
        $html = $info->get_full_information_format($complementary);
        if (!empty($html)) {
            $content = array_merge($content, $html);
        }
    }

    /**
     * Whether a subluplugin is generic
     *
     * @return bool
     */
    public function is_generic() {
        return false;
    }

    /**
     * Loads the data from the database.
     *
     * @return mixed
     */
    public function load_dataform() {
        return [editrule_form::FORM_JSON_AC => $this->get_parameters()];
    }

    /**
     * Saves the data to the database.
     *
     * @param string $action        The action to perform (insert, update, delete).
     * @param string $idname        The ID name of the data.
     * @param mixed  $data          The data to be saved.
     * @param mixed  $complementary Additional complementary data.
     * @param int    $timer         (Optional) The timer. Default is 0.
     * @param array  $students      (Optional) The students. Default is an empty array.
     */
    public function save($action, $idname, $data, $complementary, &$timer = 0, $students = []) {
        // Get data from form.
        $this->convert_parameters($idname, $data);
        // If availability json is empty and row exists (UPDATE) then $action = delete
        if (mod_ac_availability_info::is_empty($this->get_parameters()) && $action == editrule_form::FORM_JSON_ACTION_UPDATE) {
            $action = editrule_form::FORM_JSON_ACTION_DELETE;
            parent::save($action, $idname, $data, $complementary, $timer, $students);
        } else if (!mod_ac_availability_info::is_empty($this->get_parameters())) {
            parent::save($action, $idname, $data, $complementary, $timer, $students);
        }
    }

    /**
     * Update any necessary ids and json parameters in the database.
     * It is called near the completion of course restoration.
     *
     * @param string       $restoreid Restore identifier
     * @param integer      $courseid  Course identifier
     * @param \base_logger $logger    Logger if any warnings
     *
     * @return bool|void False if restore is not required
     */
    public function update_after_restore($restoreid, $courseid, \base_logger $logger) {
        // TODO
        return false;
    }
}
