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

        $mform->addElement('hidden', 'pluginname'.$id,$this->get_subtype());
        $mform->setType('pluginname'.$id, PARAM_RAW );
        $mform->addElement('hidden', 'type'.$id,$this->get_type());
        $mform->setType('type'.$id, PARAM_RAW );
        $timegroup = array();
        $timegroup[] =& $mform->createElement(
            'float', 'condition' . $exception . $id . '_time_days', '',
            array(
                'class' => 'mr-2', 'size' => '7', 'maxlength' => '3',
                'placeholder' => 'Días',
                'oninput' => 'this.value = this.value.replace(/[^0-9]/g, "").replace(/(\..*)\./g, "$1")'
            )
        );

        $timegroup[] =& $mform->createElement(
            'float', 'condition' . $exception . $id . '_time_hours', '',
            array(
                'class' => 'mr-2', 'size' => '7', 'maxlength' => '3',
                ' placeholder' => 'Horas',
                'oninput' => 'this.value = this.value.replace(/[^0-9]/g, "").replace(/(\..*)\./g, "$1")'
            )
        );
        $timegroup[] =& $mform->createElement(
            'float', 'condition' . $exception . $id . '_time_minutes', '',
            array(
                'class' => 'mr-2', 'size' => '7', 'maxlength' => '3', '
                 placeholder' => 'Minutos',
                'oninput' => 'this.value = this.value.replace(/[^0-9]/g, "").replace(/(\..*)\./g, "$1")'
            )
        );
        $timegroup[] =& $mform->createElement(
            'float', 'condition' . $exception . $id  . '_time_seconds', '',
            array(
                'class' => 'mr-2', 'size' => '7', 'maxlength' => '2', 'placeholder' => 'Segudos',
                'oninput' => 'this.value = this.value.replace(/[^0-9]/g, "").replace(/(\..*)\./g, "$1")'
            )
        );
        $mform->setDefault('condition'.$exception.$id.'_time_hours', '2'.'_time_seconds');

        // TODO Strings.
        $mform->addGroup($timegroup, $this->get_subtype().'_'.$this->get_type(). $exception.$id .'time',
            get_string('editrule_condition_element_time', 'notificationscondition_coursestart',
                array('typeelement' => '[TTTT]')));
        $mform->setDefault('condition'.$exception.$id.'_time_minutes', '_time_minutes');
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
        // TODO: Implement get_parameters() method.
        return '{"time:99899 }';
    }
}
