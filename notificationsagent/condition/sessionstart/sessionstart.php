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

/**
 * sessiostart sessionstart.php description here.
 *
 * @package    sessiostart
 * @copyright  2023 fernando <fpano@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . "/local/notificationsagent/classes/notificationactivityconditionplugin.php");
use local_notificationsagent\notification_activityconditionplugin;
class notificationsagent_condition_sessionstart extends notification_activityconditionplugin {

    public function get_description() {
        return array(
            'title' => self::get_title(),
            'elements' => self::get_elements(),
            'name' => self::get_subtype()
        );
    }
    protected function get_mod_name() {
        return get_string('modname', 'notificationscondition_sessionstart');
    }

    public function get_title() {
        return get_string('conditiontext', 'notificationscondition_sessionstart');
    }

    public function get_elements() {
        return array('[TTTT]');
    }

    public function get_subtype() {
        return get_string('subtype', 'notificationscondition_sessionstart');
    }

    /** Evaluates this condition using the context variables or the system's state and the complementary flag.
     *
     * @param \EvaluationContext $context |null collection of variables to evaluate the condition.
     *                                    If null the system's state is used.
     *
     * @return bool true if the condition is true, false otherwise.
     */
    public function evaluate(EvaluationContext $context): bool {
        // TODO: Implement evaluate() method.
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
        $mform->addElement('hidden', 'pluginname'.$this->get_type().$id,$this->get_subtype());
        $mform->setType('pluginname'.$this->get_type().$id,PARAM_RAW );
        $mform->addElement('hidden', 'type'.$this->get_type().$id,$this->get_type().$id);
        $mform->setType('type'.$this->get_type().$id, PARAM_RAW );
        $timegroup = array();
        $timegroup[] =& $mform->createElement('float', 'condition'.$exception.$id.'_time_days', '',
            array('class' => 'mr-2', 'size' => '7', 'maxlength' => '3', '
                   placeholder' => 'Días',
                'oninput' => 'this.value = this.value.replace(/[^0-9]/g, "").replace(/(\..*)\./g, "$1")'));

        $timegroup[] =& $mform->createElement('float', 'condition'.$exception.$id.'_time_hours', '',
            array('class' => 'mr-2', 'size' => '7', 'maxlength' => '3', '
                   placeholder' => 'Horas',
                'oninput' => 'this.value = this.value.replace(/[^0-9]/g, "").replace(/(\..*)\./g, "$1")'));

        $timegroup[] =& $mform->createElement('float', 'condition'.$exception.$id.'_time_minutes', '',
            array('class' => 'mr-2', 'size' => '7', 'maxlength' => '2', 'placeholder' => 'Minutos',
                'oninput' => 'this.value = this.value.replace(/[^0-9]/g, "").replace(/(\..*)\./g, "$1")',
                'value' => $SESSION->NOTIFICATIONS['FORMDEFAULT']['id_condition'.'_group_time_condition'.$exception.$id.'_time_hours'] ?? null));

        $timegroup[] =& $mform->createElement('float', 'condition'.$exception.$id.'_time_seconds', '',
            array('class' => 'mr-2', 'size' => '7', 'maxlength' => '2',
                'placeholder' => 'Segundos',
                'oninput' => 'this.value = this.value.replace(/[^0-9]/g, "").replace(/(\..*)\./g, "$1")'));

        $mform->addGroup($timegroup, $this->get_subtype().'_condition'.$exception.'_time_'.$id,
            get_string('editrule_condition_element_time', 'notificationscondition_sessionstart',
                array('typeelement' => '[TTTT]')));
    }

    /** Estimate next time when this condition will be true. */
    public function estimate_next_time() {
        // TODO: Implement estimate_next_time() method.
    }

    /**
     * @param array $params
     *
     * @return mixed
     */
    public function get_parameters($params) {
        // Receive an array like [condition3_time_days] =>
        //                    [condition3_time_hours] =>
        //                    [condition3_time_minutes] =>
        //                    [condition3_time_seconds] =>
        // Return a json with '{"time:86400 }';
        $timeUnits = array('days', 'hours', 'minutes', 'seconds');
        $timeValues = array(
            'days' => 0,
            'hours' => 0,
            'minutes' => 0,
            'seconds' => 0
        );
    
        foreach ($params as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $subKey => $value) {
                    foreach ($timeUnits as $unit) {
                        if (strpos($subKey, $unit) !== false) {
                            $timeValues[$unit] = $value;
                        }
                    }
                }
            }/*elseif () {
                
            }*/
        }

    
         $timeInSeconds = ($timeValues['days'] * 24 * 60 * 60) + ($timeValues['hours'] * 60 * 60) + ($timeValues['minutes'] * 60) + $timeValues['seconds'];

         return json_encode(array('time' => $timeInSeconds));
    }
}