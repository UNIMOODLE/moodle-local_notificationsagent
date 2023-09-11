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
defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->dirroot . "/local/notificationsagent/classes/notificationactivityconditionplugin.php");
use local_notificationsagent\notification_activityconditionplugin;
class notificationsagent_condition_coursestart extends notification_activityconditionplugin {

    public function get_description() {
        return array(
            'title' => self::get_title(),
            'elements' => self::get_elements(),
            'name' => self::get_subtype()
        );
    }

    public function get_mod_name() {
        return get_string('modname', 'notificationscondition_coursestart');
    }

    /** Returns the name of the plugin.
     *
     * @return string
     */
    public function get_name() {
        return get_string('pluginname', 'notificationscondition_coursestart');
    }

    public function get_title() {
        return get_string('conditiontext', 'notificationscondition_coursestart');
    }

    public function get_elements() {
        return array('[TTTT]');
    }

    public function get_subtype() {
        return get_string('subtype', 'notificationscondition_coursestart');
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
        // Needed courseid and date to evaluate condition.
        return false;
    }

    public function get_ui($mform, $id, $courseid, $exception) {
        global $SESSION;
        $valuesession = 'id_'.$this->get_subtype().'_'.$this->get_type().$exception.$id.'_time_'.$this->get_type().$exception.$id;

        $mform->addElement('hidden', 'pluginname'.$this->get_type().$exception.$id,$this->get_subtype());
        $mform->setType('pluginname'.$this->get_type().$exception.$id,PARAM_RAW );
        $mform->addElement('hidden', 'type'.$this->get_type().$exception.$id,$this->get_type().$id);
        $mform->setType('type'.$this->get_type().$exception.$id, PARAM_RAW );

        $timegroup = array();
        //Days.
        $timegroup[] =& $mform->createElement('float', 'condition'.$exception.$id.'_days', '',
            array('class' => 'mr-2', 'size' => '7', 'maxlength' => '3', '
                   placeholder' => get_string('condition_days', 'local_notificationsagent'),
                'oninput' => 'this.value = this.value.replace(/[^0-9]/g, "").replace(/(\..*)\./g, "$1")', 
                'value' => !empty($SESSION->NOTIFICATIONS['FORMDEFAULT'][$valuesession.'_days']) 
                ? $SESSION->NOTIFICATIONS['FORMDEFAULT'][$valuesession.'_days'] 
                : null, 'required' => true ));
        //Hours.      
        $timegroup[] =& $mform->createElement('float', 'condition'.$exception.$id.'_hours', '',
            array('class' => 'mr-2', 'size' => '7', 'maxlength' => '3', '
                   placeholder' => get_string('condition_hours', 'local_notificationsagent'),
                   'oninput' => 'this.value = this.value.replace(/[^0-9]/g, "").replace(/(\..*)\./g, "$1")', 
                   'value' => !empty($SESSION->NOTIFICATIONS['FORMDEFAULT'][$valuesession.'_hours']) 
                   ? $SESSION->NOTIFICATIONS['FORMDEFAULT'][$valuesession.'_hours'] 
                   : null, 'required' => true ));
        //Minutes.
        $timegroup[] =& $mform->createElement('float', 'condition'.$exception.$id.'_minutes', '',
            array('class' => 'mr-2', 'size' => '7', 'maxlength' => '2', 'placeholder' => get_string('condition_minutes', 'local_notificationsagent'),
            'oninput' => 'this.value = this.value.replace(/[^0-9]/g, "").replace(/(\..*)\./g, "$1")', 
            'value' => !empty($SESSION->NOTIFICATIONS['FORMDEFAULT'][$valuesession.'_minutes']) 
            ? $SESSION->NOTIFICATIONS['FORMDEFAULT'][$valuesession.'_minutes'] 
            : null, 'required' => true ));
        //Seconds.
        $timegroup[] =& $mform->createElement('float', 'condition'.$exception.$id.'_seconds', '',
            array('class' => 'mr-2', 'size' => '7', 'maxlength' => '2',
                'placeholder' => get_string('condition_seconds', 'local_notificationsagent'),
                'oninput' => 'this.value = this.value.replace(/[^0-9]/g, "").replace(/(\..*)\./g, "$1")', 
                'value' => !empty($SESSION->NOTIFICATIONS['FORMDEFAULT'][$valuesession.'_seconds']) 
                ? $SESSION->NOTIFICATIONS['FORMDEFAULT'][$valuesession.'_seconds'] 
                : null, 'required' => true ));
        //GroupTime.
        $mform->addGroup($timegroup, $this->get_subtype().'_condition'.$exception.$id.'_time',
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
