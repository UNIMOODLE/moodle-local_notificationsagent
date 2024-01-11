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
// Project implemented by the \"Recovery, Transformation and Resilience Plan.
// Funded by the European Union - Next GenerationEU\".
//
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
require_once($CFG->dirroot . "/local/notificationsagent/classes/notificationactivityconditionplugin.php");
require_once($CFG->dirroot . "/local/notificationsagent/notificationsagent.php");
use local_notificationsagent\notification_activityconditionplugin;
use local_notificationsagent\EvaluationContext;
use notificationsagent\notificationsagent;
class notificationsagent_condition_activityend extends notification_activityconditionplugin {

    public function get_description() {
        return [
            'title' => $this->get_title(),
            'elements' => $this->get_elements(),
            'name' => $this->get_subtype(),
        ];
    }

    public function get_title() {
        return get_string('conditiontext', 'notificationscondition_activityend');
    }

    public function get_elements() {
        return ['[TTTT]', '[AAAA]'];
    }

    public function get_subtype() {
        return get_string('subtype', 'notificationscondition_activityend');
    }

    /** Evaluates this condition using the context variables or the system's state and the complementary flag.
     *
     * @param EvaluationContext $context |null collection of variables to evaluate the condition.
     *                                    If null the system's state is used.
     *
     * @return bool true if the condition is true, false otherwise.
     */
    public function evaluate(EvaluationContext $context): bool {
        // Params being like "time": "activity":"" .
        global $DB;
        $meetcondition = false;
        $courseid = $context->get_courseid();
        $userid = $context->get_userid();
        $params = json_decode($context->get_params());
        $cmid = $params->activity;
        $coursecontext = context_course::instance($courseid);
        $pluginname = $this->get_subtype();
        $timeaccess = $context->get_timeaccess();
        $conditionid = $this->get_id();

        $timeend = $DB->get_field(
            'notificationsagent_cache',
            'timestart',
            ['conditionid' => $conditionid, 'courseid' => $courseid, 'userid' => $userid, 'pluginname' => $pluginname],
        );

        if (empty($timeend)) {
            $timeend = notificationsagent::notificationsagent_condition_get_cm_dates($cmid)->timeend - $params->time;
        }
        ($timeaccess > $timeend) ? $meetcondition = true : $meetcondition = false;

        return $meetcondition;
    }

    /** Estimate next time when this condition will be true. */
    public function estimate_next_time(EvaluationContext $context) {
        // Get activity from context.
        $params = json_decode($context->get_params());
        $cmid = $params->activity;
        $time = $params->time;

        if ($context->is_complementary()) {
            $timeend = notificationsagent::notificationsagent_condition_get_cm_dates($cmid)->timeend;
            return $timeend;
        } else {
            $timeend = notificationsagent::notificationsagent_condition_get_cm_dates($cmid)->timeend;
            return $timeend - $time;
        }
    }

    /** Returns the name of the plugin
     *
     * @return string
     */
    public function get_name() {
        return get_string('pluginname', 'notificationscondition_activityend');
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
        // Activity.
        $listactivities = [];
        $modinfo = get_fast_modinfo($courseid);
        foreach ($modinfo->get_cms() as $cm) {
            $listactivities[$cm->id] = format_string($cm->name);
        }
        if (empty($listactivities)) {
            $listactivities['0'] = 'AAAA';
        }
        asort($listactivities);
        $mform->addElement(
            'select', $this->get_subtype().'_' .$this->get_type() .$exception.$id.'_activity',
            get_string(
                'editrule_condition_activity', 'notificationscondition_activityend',
                ['typeelement' => '[AAAA]']
            ),
            $listactivities
        );
        if (!empty($SESSION->NOTIFICATIONS['FORMDEFAULT']['id_'.$this->get_subtype().'_'
                   .$this->get_type().$exception.$id.'_activity'])) {
            $mform->setDefault($this->get_subtype().'_' .$this->get_type() .$exception.$id.'_activity',
            $SESSION->NOTIFICATIONS['FORMDEFAULT']['id_'.$this->get_subtype().'_'.$this->get_type().$exception.$id.'_activity']);
        }
    }

    public function check_capability($context) {
        return has_capability('local/notificationsagent:activityend', $context);
    }

    /**
     * @param array $params
     *
     * @return mixed
     */
    public function convert_parameters($params) {
        $timeunits = ['days', 'hours', 'minutes', 'seconds'];
        $timevalues = [
            'days' => 0,
            'hours' => 0,
            'minutes' => 0,
            'seconds' => 0,
        ];
        $activity = 0;
        foreach ($params as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $subkey => $subvalue) {
                    foreach ($timeunits as $unit) {
                        if (strpos($subkey, $unit) !== false) {
                            $timevalues[$unit] = $subvalue;
                        }
                    }
                }
            } else if (strpos($key, "activity") !== false) {
                $activity = $value;
            }
        }
        $timeinseconds = ($timevalues['days'] * 24 * 60 * 60) + ($timevalues['hours'] * 60 * 60)
            + ($timevalues['minutes'] * 60) + $timevalues['seconds'];
        return json_encode(['time' => $timeinseconds, 'activity' => (int) $activity]);
    }

    public function process_markups(&$content, $params, $courseid, $complementary=null) {
        $activityincourse = false;
        $jsonparams = json_decode($params);

        $modinfo = get_fast_modinfo($courseid);
        $types = $modinfo->get_cms();

        foreach ($types as $type) {
            if ($type->id == $jsonparams->activity) {
                $activityname = $type->name;
                $activityincourse = true;
            }
        }

        // Check if activity is found in course, if is not, return [AAAA].
        if (!$activityincourse) {
            $activityname = '[AAAA]';
        }

        $paramstoreplace = [$this->get_human_time($jsonparams->time), $activityname];
        $humanvalue = str_replace($this->get_elements(), $paramstoreplace, $this->get_title());

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
        return $parameters->activity;
    }
}
