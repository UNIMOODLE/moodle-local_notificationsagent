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
require_once(__DIR__ . '/../../../../calendar/lib.php');
use local_notificationsagent\notification_activityconditionplugin;
use local_notificationsagent\EvaluationContext;
class notificationsagent_condition_calendarstart extends notification_activityconditionplugin {

    public function get_description() {
        return [
            'title' => self::get_title(),
            'elements' => self::get_elements(),
            'name' => self::get_subtype(),
        ];
    }

    public function get_mod_name() {
        return get_string('modname', 'notificationscondition_calendarstart');
    }

    /** Returns the name of the plugin.
     *
     * @return string
     */
    public function get_name() {
        return get_string('pluginname', 'notificationscondition_calendarstart');
    }

    public function get_title() {
        return get_string('conditiontext', 'notificationscondition_calendarstart');
    }

    public function get_elements() {
        return ['[TTTT]', '[CCCC]'];
    }

    public function get_subtype() {
        return get_string('subtype', 'notificationscondition_calendarstart');
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
        $radio = $params->radio;
        $meetcondition = false;
        $conditionid = $this->get_id();

        $timeaccess = $context->get_timeaccess();

        $timestart = $DB->get_field(
            'notificationsagent_cache',
            'timestart',
            ['conditionid' => $conditionid, 'courseid' => $courseid, 'userid' => $userid, 'pluginname' => $pluginname],
        );

        if (empty($timestart)) {
            $event = calendar_get_events_by_id([$params->calendar]);
            if ($radio == 1) {
                $timestart = $event[$params->calendar]->timestart + $params->time;
            } else {
                $timestart = $event[$params->calendar]->timestart + $event[$params->calendar]->timeduration + $params->time;
            }

        }

        ($timeaccess > $timestart) ? $meetcondition = true : $meetcondition = false;

        return $meetcondition;
    }


    /** Estimate next time when this condition will be true. */
    public function estimate_next_time(EvaluationContext $context) {
        global $DB;
        $params = json_decode($context->get_params());
        $radio = $params->radio;
        $event = calendar_get_events_by_id([$params->calendar]);
        if ($radio == 1) {
            $timestart = $event[$params->calendar]->timestart + $params->time;
        } else {
            $timestart = $event[$params->calendar]->timestart + $event[$params->calendar]->timeduration + $params->time;
        }
        return $timestart;
    }

    public function get_ui($mform, $id, $courseid, $exception) {
        global $SESSION, $DB;
        $valuesession = 'id_'.$this->get_subtype().'_'.$this->get_type().$exception.$id.'_time_'.$this->get_type().$exception.$id;

        $mform->addElement('hidden', 'pluginname'.$this->get_type().$exception.$id, $this->get_subtype());
        $mform->setType('pluginname'.$this->get_type().$exception.$id, PARAM_RAW );
        $mform->addElement('hidden', 'type'.$this->get_type().$exception.$id, $this->get_type().$id);
        $mform->setType('type'.$this->get_type().$exception.$id, PARAM_RAW );

        $timegroup = [];
        // Days.
        $timegroup[] =& $mform->createElement('float', 'condition'.$exception.$id.'_days', '',
            ['class' => 'mr-2', 'size' => '7', 'maxlength' => '3', '
                   placeholder' => get_string('condition_days', 'local_notificationsagent'),
                'oninput' => 'this.value = this.value.replace(/[^0-9]/g, "").replace(/(\..*)\./g, "$1")',
                'value' => $SESSION->NOTIFICATIONS['FORMDEFAULT'][$valuesession . '_days'] ?? null, ]);
        // Hours.
        $timegroup[] =& $mform->createElement('float', 'condition'.$exception.$id.'_hours', '',
            ['class' => 'mr-2', 'size' => '7', 'maxlength' => '3', '
                   placeholder' => get_string('condition_hours', 'local_notificationsagent'),
                   'oninput' => 'this.value = this.value.replace(/[^0-9]/g, "").replace(/(\..*)\./g, "$1")',
                   'value' => $SESSION->NOTIFICATIONS['FORMDEFAULT'][$valuesession . '_hours'] ?? null, ]);
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
            ['class' => 'mr-2', 'size' => '7', 'maxlength' => '2',
                'placeholder' => get_string('condition_seconds', 'local_notificationsagent'),
                'oninput' => 'this.value = this.value.replace(/[^0-9]/g, "").replace(/(\..*)\./g, "$1")',
                'value' => $SESSION->NOTIFICATIONS['FORMDEFAULT'][$valuesession . '_seconds'] ?? null, ]);
        // GroupTime.
        $mform->addGroup($timegroup, $this->get_subtype().'_condition'.$exception.$id.'_time',
            get_string('editrule_condition_element_time', 'notificationscondition_sessionstart',
                ['typeelement' => '[TTTT]']));
        $mform->addGroupRule(
            $this->get_subtype() . '_condition' . $exception . $id . '_time', '- You must supply a value here.', 'required'
        );

        // Calendar.
        $listevents = $DB->get_records_sql("SELECT * FROM {event} WHERE eventtype IN ('course')");

        $events = [];
        foreach ($listevents as $event) {
            $events[$event->id] = format_text($event->name) . " - " . userdate($event->timestart) .
            " - " . userdate($event->timestart + $event->timeduration);
        }

        $mform->addElement(
            'select', $this->get_subtype().'_' .$this->get_type() .$exception.$id.'_calendar',
            get_string(
                'editrule_condition_calendar', 'notificationscondition_calendarstart',
                ['typeelement' => '[CCCC]']
            ),
            $events
        );
        if (!empty($SESSION->NOTIFICATIONS['FORMDEFAULT']['id_'.$this->get_subtype().'_'
                   .$this->get_type().$exception.$id.'_calendar'])) {
            $mform->setDefault($this->get_subtype().'_' .$this->get_type() .$exception.$id.'_calendar',
            $SESSION->NOTIFICATIONS['FORMDEFAULT']['id_'.$this->get_subtype().'_'.$this->get_type().$exception.$id.'_calendar']);
        }
        // Radio.
        $radioarray = [];
        $radioarray[] = $mform->createElement('radio', $this->get_subtype().'_' .$this->get_type() . $exception.$id.'_radio',
        '', get_string('afterstart', 'notificationscondition_calendarstart'), 1);
        $radioarray[] = $mform->createElement('radio', $this->get_subtype().'_' .$this->get_type() .$exception.$id.'_radio',
        '', get_string('afterend', 'notificationscondition_calendarstart'), 2);

        $mform->addGroup($radioarray, $this->get_subtype().'_condition'.$exception.$id.'_radio', '', [' '], false);
        $mform->addGroupRule(
            $this->get_subtype() . '_condition' . $exception . $id . '_radio', '- You must supply a value here.', 'required'
        );

        if (!empty($SESSION->NOTIFICATIONS['FORMDEFAULT']['id_'.$this->get_subtype().'_'
                   .$this->get_type().$exception.$id.'_radio'])) {
            $mform->setDefault($this->get_subtype().'_' .$this->get_type() .$exception.$id.'_radio',
            $SESSION->NOTIFICATIONS['FORMDEFAULT']['id_'.$this->get_subtype().'_'.$this->get_type().$exception.$id.'_radio']);
        }

    }

    public function check_capability($context) {
        return has_capability('local/notificationsagent:calendarstart', $context);
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
        $calendar = 0;
        $radio = 0;
        foreach ($params as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $subkey => $value) {
                    foreach ($timeunits as $unit) {
                        if (strpos($subkey, $unit) !== false) {
                            $timevalues[$unit] = $value;
                        }
                    }
                }
            } else if (strpos($key, "calendar") !== false) {
                $calendar = $value;
            } else if (strpos($key, "radio") !== false) {
                $radio = $value;
            }

        }
        $timeinseconds = ($timevalues['days'] * 24 * 60 * 60) + ($timevalues['hours'] * 60 * 60)
            + ($timevalues['minutes'] * 60) + $timevalues['seconds'];

        return json_encode(['time' => $timeinseconds, 'calendar' => (int) $calendar, 'radio' => $radio]);
    }

    public function process_markups($params, $courseid) {
        $jsonparams = json_decode($params);

        $paramstoteplace = [$this->get_human_time($jsonparams->time)];

        $humanvalue = str_replace($this->get_elements(), $paramstoteplace, $this->get_title());

        return $humanvalue;
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
