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
 * @package    local_notificationsagent
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->dirroot . "/local/notificationsagent/classes/notificationactivityconditionplugin.php");
require_once(__DIR__ . '/lib.php');

use core_calendar\type_factory;
use local_notificationsagent\notification_activityconditionplugin;
use local_notificationsagent\EvaluationContext;
class notificationsagent_condition_weekend extends notification_activityconditionplugin {

    public function get_description() {
        return [
            'title' => $this->get_title(),
            'elements' => $this->get_elements(),
            'name' => $this->get_subtype(),
        ];
    }

    public function get_mod_name() {
        return get_string('modname', 'notificationscondition_weekend');
    }

    /** Returns the name of the plugin.
     *
     * @return string
     */
    public function get_name() {
        return get_string('pluginname', 'notificationscondition_weekend');
    }

    public function get_title() {
        return get_string('conditiontext', 'notificationscondition_weekend');
    }

    public function get_elements() {
        return [];
    }

    public function get_subtype() {
        return get_string('subtype', 'notificationscondition_weekend');
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
        $courseid = $context->get_courseid();
        $userid = $context->get_userid();
        $pluginname = $this->get_subtype();
        $conditionid = $this->get_id();

        $timestart = $DB->get_field(
            'notificationsagent_cache',
            'timestart',
            ['conditionid' => $conditionid, 'courseid' => $courseid, 'userid' => $userid, 'pluginname' => $pluginname],
        );

        if (empty($timestart)) {
            $timestart = $context->get_timeaccess();
        }

        return is_weekend($timestart);

    }

    /** Estimate next time when this condition will be true. */
    public function estimate_next_time(EvaluationContext $context) {
        return null;
    }

    public function get_ui($mform, $id, $courseid, $exception) {

        $mform->addElement('hidden', 'pluginname'.$this->get_type().$exception.$id, $this->get_subtype());
        $mform->setType('pluginname'.$this->get_type().$exception.$id, PARAM_RAW );
        $mform->addElement('hidden', 'type'.$this->get_type().$exception.$id, $this->get_type().$id);
        $mform->setType('type'.$this->get_type().$exception.$id, PARAM_RAW );

        $weekendconfig = get_config(null, 'calendar_weekend');
        $calendar = \core_calendar\type_factory::get_calendar_instance();
        $params = $calendar->get_weekdays();
        $test = [];
        foreach ($params as $key => $value) {
            if ($weekendconfig & (1 << $key)) {

                    $test[] = $value['fullname'];
            }
        }
        $concatenatedtoday = implode(', ', $test);

        $mform->addElement('static', 'description', "Weekend",
        get_string('weekendtext', 'notificationscondition_weekend'). $concatenatedtoday);

    }

    public function check_capability($context) {
         return has_capability('local/notificationsagent:weekend', $context);
    }

    /**
     * @param array $params
     *
     * @return mixed
     */
    public function convert_parameters($params) {
        return null;
    }

    public function process_markups(&$content, $params, $courseid, $complementary=null) {
        $paramstoteplace = [];

        $humanvalue = str_replace($this->get_elements(), $paramstoteplace, $this->get_title());

        array_push($content, $humanvalue);
    }


    public function is_generic() {
        return true;
    }

    /**
     * Return the module identifier specified in the condition
     * @param object $parameters Plugin parameters
     *
     * @return int|null $cmid Course module id or null
     */
    public function get_cmid($parameters) {
        return null;
    }
}
