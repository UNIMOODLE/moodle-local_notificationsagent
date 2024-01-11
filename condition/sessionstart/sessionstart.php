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
use local_notificationsagent\notification_activityconditionplugin;
use local_notificationsagent\EvaluationContext;
class notificationsagent_condition_sessionstart extends notification_activityconditionplugin {

    public function get_description() {
        return [
            'title' => $this->get_title(),
            'elements' => $this->get_elements(),
            'name' => $this->get_subtype(),
        ];
    }

    public function get_title() {
        return get_string('conditiontext', 'notificationscondition_sessionstart');
    }

    public function get_elements() {
        return ['[TTTT]'];
    }

    public function get_subtype() {
        return get_string('subtype', 'notificationscondition_sessionstart');
    }

    /** Evaluates this condition using the context variables or the system's state and the complementary flag.
     *
     * @param EvaluationContext $context |null collection of variables to evaluate the condition.
     *                                    If null the system's state is used.
     *
     * @return bool true if the condition is true, false otherwise.
     */
    public function evaluate(EvaluationContext $context): bool {

        // Miramos mdl_notificationsagent_cache si hay registro, comprobar.
        // si no hay registro comprobar en la tabla del plugin.
        global $DB;
        $courseid = $context->get_courseid();
        $userid = $context->get_userid();
        $pluginname = $this->get_subtype();
        $conditionid = $this->get_id();
        $timeaccess = $context->get_timeaccess();
        $params = json_decode($context->get_params());
        $meetcondition = false;

        // Timestart es el tiempo de primer acceso más time.
        $timestart = $DB->get_field('notificationsagent_cache',
            'timestart', ['conditionid' => $conditionid, 'courseid' => $courseid, 'userid' => $userid, 'pluginname' => $pluginname],
        );

        if (empty($timestart)) {
            // Check own plugin table.
            $firstacces = get_first_course_access($userid, $courseid);

            if (empty($firstacces)) {
                return $meetcondition;
            }
            $timestart = $firstacces + $params->time;
        }

        ($timeaccess > $timestart) ? $meetcondition = true : $meetcondition = false;

        return $meetcondition;

    }

    /** Returns the name of the plugin
     *
     * @return string
     */
    public function get_name() {
        return get_string('pluginname', 'notificationscondition_sessionstart');
    }

    public function get_ui($mform, $id, $courseid, $exception) {
        global $SESSION;
        $valuesession = 'id_'.$this->get_subtype().'_'.$this->get_type().$exception.$id.'_time_'.$this->get_type().$exception.$id;

        $mform->addElement('hidden', 'pluginname'.$this->get_type().$exception.$id, $this->get_subtype());
        $mform->setType('pluginname'.$this->get_type().$exception.$id, PARAM_RAW );
        $mform->addElement('hidden', 'type'.$this->get_type().$exception.$id, $this->get_type().$id);
        $mform->setType('type'.$this->get_type().$exception.$id, PARAM_RAW );

        $timegroup = [];
        // Days.
        $timegroup[] =& $mform->createElement('float', 'condition'.$exception.$id.'_days', '',
            [
                'class' => 'mr-2', 'size' => '7', 'maxlength' => '3',
                'placeholder' => get_string('condition_days', 'local_notificationsagent'),
                'oninput' => 'this.value = this.value.replace(/[^0-9]/g, "").replace(/(\..*)\./g, "$1")',
                'value' => $SESSION->NOTIFICATIONS['FORMDEFAULT'][$valuesession . '_days'] ?? 0,
            ]
        );

        // Hours.
        $timegroup[] =& $mform->createElement('float', 'condition'.$exception.$id.'_hours', '',
            [
                'class' => 'mr-2', 'size' => '7', 'maxlength' => '3',
                   'placeholder' => get_string('condition_hours', 'local_notificationsagent'),
                   'oninput' => 'this.value = this.value.replace(/[^0-9]/g, "").replace(/(\..*)\./g, "$1")',
                   'value' => $SESSION->NOTIFICATIONS['FORMDEFAULT'][$valuesession . '_hours'] ?? 0,
            ]
        );
        // Minutes.
        $timegroup[] =& $mform->createElement('float', 'condition'.$exception.$id.'_minutes', '',
            [
                'class' => 'mr-2', 'size' => '7', 'maxlength' => '2',
                'placeholder' => get_string('condition_minutes', 'local_notificationsagent'),
            'oninput' => 'this.value = this.value.replace(/[^0-9]/g, "").replace(/(\..*)\./g, "$1")',
            'value' => $SESSION->NOTIFICATIONS['FORMDEFAULT'][$valuesession . '_minutes'] ?? 0,
            ]
        );
        // Seconds.
        $timegroup[] =& $mform->createElement('float', 'condition'.$exception.$id.'_seconds', '',
            [
                'class' => 'mr-2', 'size' => '7', 'maxlength' => '2',
                'placeholder' => get_string('condition_seconds', 'local_notificationsagent'),
                'oninput' => 'this.value = this.value.replace(/[^0-9]/g, "").replace(/(\..*)\./g, "$1")',
                'value' => $SESSION->NOTIFICATIONS['FORMDEFAULT'][$valuesession . '_seconds'] ?? 0,
            ]
        );
        // GroupTime.
        $mform->addGroup($timegroup, $this->get_subtype().'_condition'.$exception.$id.'_time',
            get_string('editrule_condition_element_time', 'notificationscondition_sessionstart',
                ['typeelement' => '[TTTT]']
            ));
        $mform->addGroupRule(
            $this->get_subtype() . '_condition' . $exception . $id . '_time', '- You must supply a value here.', 'required'
        );
    }

    /** Estimate next time when this condition will be true. */
    public function estimate_next_time(EvaluationContext $context) {
        // No devolvemos fecha en los subplugins que responden a un evento core de moodle.
        return null;
    }

    public function check_capability($context) {
        return has_capability('local/notificationsagent:sessionstart', $context);
    }

    /**
     * @param array $params
     *
     * @return mixed
     */
    public function convert_parameters($params) {
        // Receive an array like
        // [condition3_time_days] =>
        // [condition3_time_hours] =>
        // [condition3_time_minutes] =>
        // [condition3_time_seconds] =>
        // Return a json with '{"time:86400 }'.
        $timeunits = ['days', 'hours', 'minutes', 'seconds'];
        $timevalues = [
            'days' => 0,
            'hours' => 0,
            'minutes' => 0,
            'seconds' => 0,
        ];
        foreach ($params as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $subkey => $value) {
                    foreach ($timeunits as $unit) {
                        if (strpos($subkey, $unit) !== false) {
                            $timevalues[$unit] = $value;
                        }
                    }
                }
            }
        }
         $timeinseconds = ($timevalues['days'] * 24 * 60 * 60) + ($timevalues['hours'] * 60 * 60)
             + ($timevalues['minutes'] * 60) + $timevalues['seconds'];

         return json_encode(['time' => $timeinseconds]);
    }

    public function process_markups(&$content, $params, $courseid, $complementary=null) {
        $jsonparams = json_decode($params);

        $paramstoteplace = [$this->get_human_time($jsonparams->time)];

        $humanvalue = str_replace($this->get_elements(), $paramstoteplace, $this->get_title());

        array_push($content, $humanvalue);
    }

    public function is_generic() {
        return false;
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
