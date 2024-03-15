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
    public function get_elements() {
    }

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

    /** Estimate next time when this condition will be true. */
    public function estimate_next_time(evaluationcontext $context) {
        return null;
    }

    public function get_ui($mform, $id, $courseid, $exception) {
        return '';
    }

    // Load capabilities from CORE.
    public function check_capability($context) {
        return false;
    }

    /**
     * @param array $params
     *
     * @return mixed
     */
    protected function convert_parameters($id, $params) {
        $params = (array) $params;
        $this->set_parameters($params[editrule_form::FORM_JSON_AC]);
        return $this->get_parameters();
    }

    public function process_markups(&$content, $courseid, $complementary = null) {
        $info = new mod_ac_availability_info($courseid, $this->get_parameters());
        $html = $info->get_full_information_format($complementary);
        if (!empty($html)) {
            $content = array_merge($content, $html);
        }
    }

    public function is_generic() {
        return false;
    }

    /**
     * @return mixed
     */
    public function load_dataform() {
        return [editrule_form::FORM_JSON_AC => $this->get_parameters()];
    }

    public function save($action, $idname, $data, $complementary, $students = [], &$timer = 0) {
        // Get data from form.
        $this->convert_parameters($idname, $data);
        // If availability json is empty and row exists (UPDATE) then $action = delete
        if (mod_ac_availability_info::is_empty($this->get_parameters()) && $action == editrule_form::FORM_JSON_ACTION_UPDATE) {
            $action = editrule_form::FORM_JSON_ACTION_DELETE;
            parent::save($action, $idname, $data, $complementary, $students, $timer);
        }else if(!mod_ac_availability_info::is_empty($this->get_parameters())){
            parent::save($action, $idname, $data, $complementary, $students, $timer);
        }
    }

}

