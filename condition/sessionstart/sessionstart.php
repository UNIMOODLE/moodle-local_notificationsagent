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
use local_notificationsagent\EvaluationContext;
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
     * @param EvaluationContext $context |null collection of variables to evaluate the condition.
     *                                    If null the system's state is used.
     *
     * @return bool true if the condition is true, false otherwise.
     */
    public function evaluate(EvaluationContext $context): bool {

        // Miramos mdl_notificationsagent_cache si hay registro, comprobar
        // si no hay registro comprobar en la tabla del plugin
        global $DB;
        $courseid = $context->get_courseid();
        $userid = $context->get_userid();
        $pluginname = $this->get_subtype();
        $conditionid = $this->get_id();
        $timeaccess = $context->get_timeaccess();
        $params = json_decode($context->get_params());
        $meetcondition = false;

        // Timestart es el tiempo de primer acceso más time
        $timestart = $DB->get_field('notificationsagent_cache',
            'timestart', ['conditionid' => $conditionid,'courseid'=> $courseid, 'userid' => $userid, 'pluginname' => $pluginname],
        );


        if (!empty($timestart)) {
            ($timeaccess > $timestart) ? $meetcondition = true : $meetcondition = false;
            // La regla se hizo después de que el usuario entrara en el curso.
        } else {
            // Check own plugin table
            $firstacces = $DB->get_field('notifications_sessionaccess',
                'firstaccess', ['courseid'=> $courseid, 'userid' => $userid],
            );
            // El usuario no ha desencadenado nunca un course_view
            if (empty($firstacces)) {
                return $meetcondition;
            }

            ($timeaccess  > $firstacces + $params->time) ? $meetcondition = true : $meetcondition = false;

        }

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

        $timegroup = array();
        // Days.
        $timegroup[] =& $mform->createElement('float', 'condition'.$exception.$id.'_days', '',
            array('class' => 'mr-2', 'size' => '7', 'maxlength' => '3',
                   'placeholder' => get_string('condition_days', 'local_notificationsagent'),
                   'oninput' => 'this.value = this.value.replace(/[^0-9]/g, "").replace(/(\..*)\./g, "$1")',
                   'value' => $SESSION->NOTIFICATIONS['FORMDEFAULT'][$valuesession . '_days'] ?? null));
        // Hours.
        $timegroup[] =& $mform->createElement('float', 'condition'.$exception.$id.'_hours', '',
            array('class' => 'mr-2', 'size' => '7', 'maxlength' => '3', '
                   placeholder' => get_string('condition_hours', 'local_notificationsagent'),
                   'oninput' => 'this.value = this.value.replace(/[^0-9]/g, "").replace(/(\..*)\./g, "$1")',
                   'value' => $SESSION->NOTIFICATIONS['FORMDEFAULT'][$valuesession . '_hours'] ?? null));
        // Minutes.
        $timegroup[] =& $mform->createElement('float', 'condition'.$exception.$id.'_minutes', '',
            array('class' => 'mr-2', 'size' => '7', 'maxlength' => '2',
                'placeholder' => get_string('condition_minutes', 'local_notificationsagent'),
                'oninput' => 'this.value = this.value.replace(/[^0-9]/g, "").replace(/(\..*)\./g, "$1")',
                'value' => $SESSION->NOTIFICATIONS['FORMDEFAULT'][$valuesession . '_minutes'] ?? null));
        // Seconds.
        $timegroup[] =& $mform->createElement('float', 'condition'.$exception.$id.'_seconds', '',
            array('class' => 'mr-2', 'size' => '7', 'maxlength' => '2',
                'placeholder' => get_string('condition_seconds', 'local_notificationsagent'),
                'oninput' => 'this.value = this.value.replace(/[^0-9]/g, "").replace(/(\..*)\./g, "$1")',
                'value' => $SESSION->NOTIFICATIONS['FORMDEFAULT'][$valuesession . '_seconds'] ?? null));
        // GroupTime.
        $mform->addGroup($timegroup, $this->get_subtype().'_condition'.$exception.$id.'_time',
            get_string('editrule_condition_element_time', 'notificationscondition_sessionstart',
                array('typeelement' => '[TTTT]')));
        $mform->addGroupRule($this->get_subtype().'_condition'.$exception.$id.'_time', '- You must supply a value here.','required');
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
    public function convert_parameters($params) {
        // Receive an array like
        // [condition3_time_days] =>
        // [condition3_time_hours] =>
        // [condition3_time_minutes] =>
        // [condition3_time_seconds] =>
        // Return a json with '{"time:86400 }'.
        $timeunits = array('days', 'hours', 'minutes', 'seconds');
        $timevalues = array(
            'days' => 0,
            'hours' => 0,
            'minutes' => 0,
            'seconds' => 0
        );
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

         return json_encode(array('time' => $timeinseconds));
    }

    public function process_markups($params, $courseid) {
        $jsonparams = json_decode($params);

        $paramsToReplace = [$this->get_human_time($jsonparams->time)];

        $humanValue = str_replace($this->get_elements(), $paramsToReplace, $this->get_title());

        return $humanValue;
    }
}
