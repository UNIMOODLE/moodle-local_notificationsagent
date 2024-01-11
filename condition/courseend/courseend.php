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
use local_notificationsagent\EvaluationContext;
class notificationsagent_condition_courseend extends notification_activityconditionplugin {

    public function get_description() {
        return [
            'title' => $this->get_title(),
            'elements' => $this->get_elements(),
            'name' => $this->get_subtype(),
        ];
    }

    /** Returns the name of the plugin.
     *
     * @return string
     */
    public function get_name() {
        return get_string('pluginname', 'notificationscondition_courseend');
    }

    public function get_title() {
        return get_string('conditiontext', 'notificationscondition_courseend');
    }

    public function get_elements() {
        return ['[TTTT]'];
    }

    public function get_subtype() {
        return get_string('subtype', 'notificationscondition_courseend');
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
        $params = json_decode($context->get_params());
        $meetcondition = false;
        $conditionid = $this->get_id();

        $timeaccess = $context->get_timeaccess();

        $timeend = $DB->get_field(
            'notificationsagent_cache',
            'timestart',
            ['conditionid' => $conditionid, 'courseid' => $courseid, 'userid' => $userid, 'pluginname' => $pluginname],
        );
        $course = get_course($courseid);
        if (empty($timeend)) {
            $timeend = $course->enddate - $params->time;
        }

        ($timeaccess > $timeend) && ($timeaccess < $course->enddate) ? $meetcondition = true : $meetcondition = false;
        return $meetcondition;
    }


    /** Estimate next time when this condition will be true. */
    public function estimate_next_time(EvaluationContext $context) {
        $timeend = null;
        if (!$context->is_complementary()) {
            $params = json_decode($context->get_params());
            $courseid = $context->get_courseid();
            $course = get_course($courseid);
            $timeend = $course->enddate - $params->time;
        }
        return $timeend;
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

    public function check_capability($context) {
        return has_capability('local/notificationsagent:courseend', $context);
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
