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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.
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

global $CFG, $SESSION;
require_once($CFG->dirroot . '/local/notificationsagent/classes/notificationconditionplugin.php');
require_once($CFG->dirroot . '/local/notificationsagent/condition/ac/classes/mod_ac_availability_info.php');
use local_notificationsagent\EvaluationContext;

class notificationsagent_condition_ac extends \notificationconditionplugin {

    public function get_description() {
        return [
            'title' => self::get_title(),
            'elements' => self::get_elements(),
            'name' => self::get_subtype(),
        ];
    }

    protected function get_cmid($parameters) {
    }

    public function get_title() {
    }

    public function get_elements() {
    }


    public function get_subtype() {
        return get_string('subtype', 'notificationscondition_ac');
    }

    /** Evaluates this condition using the context variables or the system's state and the complementary flag.
     *
     * @param EvaluationContext $context |null collection of variables to evaluate the condition.
     *                                    If null the system's state is used.
     *
     * @return bool true if the condition is true, false otherwise.
     */
    public function evaluate(EvaluationContext $context): bool {
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
    public function estimate_next_time(EvaluationContext $context) {
        return null;
    }

    /** Returns the name of the plugin
     *
     * @return string
     */
    public function get_name() {
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
    public function convert_parameters($params) {
        return $params;
    }

    public function process_markups(&$content, $params, $courseid, $complementary=null) {
        $info = new mod_ac_availability_info($courseid, $params);
        $html = $info->get_full_information_format($complementary);
        if (!empty($html)) {
            $content = array_merge($content, $html);
        }
    }

    public function is_generic() {
        return false;
    }

}

