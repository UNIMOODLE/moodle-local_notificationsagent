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
 * sessiostart numberoftimes.php description here.
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
class notificationsagent_condition_numberoftimes extends notification_activityconditionplugin {

    public function get_description() {
        return [
            'title' => self::get_title(),
            'elements' => self::get_elements(),
            'name' => self::get_subtype(),
        ];
    }

    public function get_title() {
        return get_string('conditiontext', 'notificationscondition_numberoftimes');
    }

    public function get_elements() {
        return ['[TTTT]', '[N]'];
    }

    public function get_subtype() {
        return get_string('subtype', 'notificationscondition_numberoftimes');
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
        $time = $params->time;
        $numbertimes = $params->numbertimes;

        $ruleid = $DB->get_field('notificationsagent_condition', 'ruleid', ['id' => $conditionid]);

        $launched = $DB->get_record('notificationsagent_launched',
        ['ruleid' => $ruleid, 'userid' => $userid, 'courseid' => $courseid]);

        if ($launched === false) {
            return true;
        }

        (time() > $launched->timemodified + $time && $launched->timesfired < $numbertimes)
        ? $meetcondition = true : $meetcondition = false;

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
        return get_string('pluginname', 'notificationscondition_numberoftimes');
    }

    public function get_ui($mform, $id, $courseid, $exception) {
        global $SESSION;
        $valuesession = 'id_'.$this->get_subtype().'_'.$this->get_type().$exception.$id.'_time_'.$this->get_type().$exception.$id;

        $mform->addElement('hidden', 'pluginname'.$this->get_type().$exception.$id, $this->get_subtype());
        $mform->setType('pluginname'.$this->get_type().$exception.$id, PARAM_RAW );
        $mform->addElement('hidden', 'type'.$this->get_type().$exception.$id, $this->get_type().$id);
        $mform->setType('type'.$this->get_type().$exception.$id, PARAM_RAW );

        // Number of times.
        $mform->addElement('float', $this->get_subtype() . '_' . $this->get_type() . $exception . $id . '_numbertimes',
        get_string('condition_number_times', 'notificationscondition_numberoftimes', ['typeelement' => '[N]']),
            [
                    'class' => 'mr-2', 'size' => '7', 'maxlength' => '3',
                    'placeholder' => get_string('condition_number', 'notificationscondition_numberoftimes'),
                    'oninput' => 'this.value = this.value.replace(/[^0-9]/g, "").replace(/(\..*)\./g, "$1")',
            ]
        );
        $mform->addRule(
            $this->get_subtype() . '_' . $this->get_type() . $exception . $id . '_numbertimes', null, 'required', null, 'client'
        );

        if (!empty
        ($SESSION->NOTIFICATIONS['FORMDEFAULT']['id_'.$this->get_subtype().'_'.$this->get_type().$exception.$id.'_numbertimes'])) {
            $mform->setDefault($this->get_subtype() . '_' . $this->get_type() . $exception . $id . '_numbertimes',
            $SESSION->NOTIFICATIONS['FORMDEFAULT']['id_'.$this->get_subtype().'_'.$this->get_type().$exception.$id.'_numbertimes']);
        }

        $timegroup = [];
        // Days.
        $timegroup[] =& $mform->createElement('float', 'condition'.$exception.$id.'_days', '',
            [
                'class' => 'mr-2', 'size' => '7', 'maxlength' => '3',
                   'placeholder' => get_string('condition_days', 'local_notificationsagent'),
                   'oninput' => 'this.value = this.value.replace(/[^0-9]/g, "").replace(/(\..*)\./g, "$1")',
                   'value' => $SESSION->NOTIFICATIONS['FORMDEFAULT'][$valuesession . '_days'] ?? null,
            ]
        );
        // Hours.
        $timegroup[] =& $mform->createElement('float', 'condition'.$exception.$id.'_hours', '',
            [
                'class' => 'mr-2', 'size' => '7', 'maxlength' => '3', '
                   placeholder' => get_string('condition_hours', 'local_notificationsagent'),
                   'oninput' => 'this.value = this.value.replace(/[^0-9]/g, "").replace(/(\..*)\./g, "$1")',
                   'value' => $SESSION->NOTIFICATIONS['FORMDEFAULT'][$valuesession . '_hours'] ?? null,
            ]
        );
        // Minutes.
        $timegroup[] =& $mform->createElement('float', 'condition'.$exception.$id.'_minutes', '',
            [
                'class' => 'mr-2', 'size' => '7', 'maxlength' => '2',
                'placeholder' => get_string('condition_minutes', 'local_notificationsagent'),
                'oninput' => 'this.value = this.value.replace(/[^0-9]/g, "").replace(/(\..*)\./g, "$1")',
                'value' => $SESSION->NOTIFICATIONS['FORMDEFAULT'][$valuesession . '_minutes'] ?? null,
            ]
        );
        // Seconds.
        $timegroup[] =& $mform->createElement('float', 'condition'.$exception.$id.'_seconds', '',
            [
                'class' => 'mr-2', 'size' => '7', 'maxlength' => '2',
                'placeholder' => get_string('condition_seconds', 'local_notificationsagent'),
                'oninput' => 'this.value = this.value.replace(/[^0-9]/g, "").replace(/(\..*)\./g, "$1")',
                'value' => $SESSION->NOTIFICATIONS['FORMDEFAULT'][$valuesession . '_seconds'] ?? null,
            ]
        );
        // GroupTime.
        $mform->addGroup($timegroup, $this->get_subtype().'_condition'.$exception.$id.'_time',
            get_string('editrule_condition_element_time', 'notificationscondition_numberoftimes',
                ['typeelement' => '[TTTT]']
            ));
        $mform->addGroupRule(
            $this->get_subtype() . '_condition' . $exception . $id . '_time', '- You must supply a value here.', 'required'
        );

    }



    public function check_capability($context) {
        return has_capability('local/notificationsagent:numberoftimes', $context);
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
        $numbertimes = 0;
        foreach ($params as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $subkey => $value) {
                    foreach ($timeunits as $unit) {
                        if (strpos($subkey, $unit) !== false) {
                            $timevalues[$unit] = $value;
                        }
                    }
                }
            } else if (strpos($key, "numbertimes") !== false) {
                $numbertimes = $value;
            }
        }
         $timeinseconds = ($timevalues['days'] * 24 * 60 * 60) + ($timevalues['hours'] * 60 * 60)
             + ($timevalues['minutes'] * 60) + $timevalues['seconds'];

         return json_encode(['time' => $timeinseconds, 'numbertimes' => (int) $numbertimes]);
    }

    public function process_markups(&$content, $params, $courseid, $complementary=null) {
        $jsonparams = json_decode($params);

        $paramstoteplace = [$this->get_human_time($jsonparams->time), $jsonparams->numbertimes];

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
