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

// Project implemented by the "Recovery, Transformation and Resilience Plan.
// Funded by the European Union - Next GenerationEU\".
//
// Produced by the UNIMOODLE University Group: Universities of
// Valladolid, Complutense de Madrid, UPV/EHU, León, Salamanca,
// Illes Balears, Valencia, Rey Juan Carlos, La Laguna, Zaragoza, Málaga,
// Córdoba, Extremadura, Vigo, Las Palmas de Gran Canaria y Burgos.

/**
 * Version details
 *
 * @package    notificationscondition_calendareventto
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace notificationscondition_calendareventto;
defined('MOODLE_INTERNAL') || die();

use local_notificationsagent\evaluationcontext;
use local_notificationsagent\form\editrule_form;
use local_notificationsagent\notificationconditionplugin;
use local_notificationsagent\notificationsagent;
use local_notificationsagent\rule;

global $CFG;
require_once($CFG->dirroot . '/calendar/lib.php');

/**
 * Calendarevetto class
 */
class calendareventto extends notificationconditionplugin {
    /**
     * Subplugin name
     */
    public const NAME = 'calendareventto';

    /**
     * Subplugin title
     *
     * @return \lang_string|string
     */
    public function get_title() {
        return get_string('conditiontext', 'notificationscondition_calendareventto');
    }

    /**
     *  Subplugins elements
     *
     * @return string[]
     */
    public function get_elements() {
        return ['[TTTT]', '[CCCC]'];
    }

    /** Evaluates this condition using the context variables or the system's state and the complementary flag.
     *
     * @param evaluationcontext $context |null collection of variables to evaluate the condition.
     *                                    If null the system's state is used.
     *
     * @return bool true if the condition is true, false otherwise.
     */
    public function evaluate(evaluationcontext $context): bool {
        global $DB;
        $courseid = $context->get_courseid();
        $userid = $context->get_userid();
        $pluginname = $this->get_subtype();
        $params = json_decode($context->get_params());
        $meetcondition = false;
        $conditionid = $this->get_id();

        $timeaccess = $context->get_timeaccess();

        $timestart = $DB->get_field(
            'notificationsagent_cache',
            'startdate',
            ['conditionid' => $conditionid, 'courseid' => $courseid, 'userid' => $userid, 'pluginname' => $pluginname],
        );
        $event = calendar_get_events_by_id([$params->{self::UI_ACTIVITY}]);
        if (empty($timestart)) {
            $timestart = $event[$params->{self::UI_ACTIVITY}]->timestart - $params->{self::UI_TIME};
        }
        ($timeaccess >= $timestart) && ($timeaccess <= $event[$params->{self::UI_ACTIVITY}]->timestart) ? $meetcondition = true
                : $meetcondition = false;

        return $meetcondition;
    }

    /**
     *  Estimate next time when this condition will be true.
     *
     * @param evaluationcontext $context
     *
     * @return mixed|null
     */
    public function estimate_next_time(evaluationcontext $context) {
        $timestart = null;
        $params = json_decode($context->get_params(), false);

        if ($event = calendar_get_events_by_id([$params->{self::UI_ACTIVITY}])) {
            $timeaccess = $context->get_timeaccess();
            $calendarstart = $event[$params->{self::UI_ACTIVITY}]->timestart;
            // Condition.
            if (!$context->is_complementary()) {
                if ($timeaccess < $calendarstart - $params->{self::UI_TIME}) {
                    $timestart = $calendarstart - $params->{self::UI_TIME};
                } else if ($timeaccess >= $calendarstart - $params->{self::UI_TIME} && $timeaccess <= $calendarstart) {
                    $timestart = time();
                }
            }
            // Exception.
            if ($context->is_complementary()) {
                if ($timeaccess >= $calendarstart - $params->{self::UI_TIME} && $timeaccess < $calendarstart) {
                    $timestart = $calendarstart;
                } else {
                    $timestart = time();
                }
            }
        }

        return $timestart;
    }

    /**
     * Get the UI elements for the subplugin.
     *
     * @param \MoodleQuickForm $mform The form to which the elements will be added.
     * @param int $courseid The course identifier.
     * @param string $type The type of the notification plugin.
     */
    public function get_ui($mform, $courseid, $type) {
        $this->get_ui_title($mform, $type);
        global $DB;

        $listevents = $DB->get_records_sql("SELECT * FROM {event} WHERE eventtype IN ('course')");

        $events = [];
        foreach ($listevents as $event) {
            $events[$event->id] = $event->name;
        }

        // Only is template.
        if ($this->rule->template == rule::TEMPLATE_TYPE) {
            $events['0'] = 'CCCC';
        }

        $element = $mform->createElement(
            'select',
            $this->get_name_ui(self::UI_ACTIVITY),
            get_string(
                'editrule_condition_calendar',
                'notificationscondition_calendareventto',
                ['typeelement' => '[CCCC]']
            ),
            $events
        );

        $this->get_ui_select_date($mform, $type);
        $mform->insertElementBefore($element, 'new' . $type . '_group');
        $mform->addRule(
            $this->get_name_ui(self::UI_ACTIVITY),
            get_string('editrule_required_error', 'local_notificationsagent'),
            'required'
        );
    }

    /**
     * Sublugin capability
     *
     * @param \context $context
     *
     * @return bool
     */
    public function check_capability($context) {
        return has_capability('notificationscondition/calendareventto:calendareventto', $context);
    }

    /**
     * Convert parameters for the notification plugin.
     *
     * This method should take an identifier and parameters for a notification
     * and convert them into a format suitable for use by the plugin.
     *
     * @param mixed $params The parameters associated with the notification.
     *
     * @return mixed The converted parameters.
     */
    public function convert_parameters($params) {
        $params = (array) $params;
        $calendar = $params[$this->get_name_ui(self::UI_ACTIVITY)] ?? 0;
        $timeinseconds = $this->select_date_to_unix($params);
        $this->set_parameters(json_encode([self::UI_TIME => $timeinseconds, self::UI_ACTIVITY => (int) $calendar]));
        $this->set_cmid((int) $calendar);
        return $this->get_parameters();
    }

    /**
     * Process and replace markups in the supplied content.
     *
     * This function should handle any markup logic specific to a notification plugin,
     * such as replacing placeholders with dynamic data, formatting content, etc.
     *
     * @param array $content The content to be processed, passed by reference.
     * @param int $courseid The ID of the course related to the content.
     * @param mixed $options Additional options if any, null by default.
     *
     * @return void Processed content with markups handled.
     */
    public function process_markups(&$content, $courseid, $options = null) {
        $jsonparams = json_decode($this->get_parameters());

        $paramstoteplace = [\local_notificationsagent\helper\helper::to_human_format($jsonparams->{self::UI_TIME}, true)];

        $humanvalue = str_replace($this->get_elements(), $paramstoteplace, $this->get_title());

        $content[] = $humanvalue;
    }

    /**
     * Validation subplugin
     * If this method overrides, call to parent::validation
     *
     * @param int $courseid Course id
     * @param array $array The array to be modified by reference. If is null, validation is not being called from the form
     *                                  and return directly
     * @param bool $onlyverifysiteid Default TRUE
     *
     * @return bool
     */
    public function validation($courseid, &$array = null, $onlyverifysiteid = true) {
        if (($validation = parent::validation($courseid, $array, $onlyverifysiteid)) === 'break') {
            return true;
        }

        // If it false from parent and $array is null, return.
        if (is_null($array) && !$validation) {
            return $validation;
        }

        $data = json_decode($this->get_parameters(), true);

        if (!calendar_get_events_by_id([$data[self::UI_ACTIVITY]])) {
            $array[$this->get_name_ui(self::UI_ACTIVITY)] = get_string('editrule_required_error', 'local_notificationsagent');
            return $validation = false;
        }

        return $validation;
    }

    /**
     * Whether a condition is generic or not
     *
     * @return bool
     */
    public function is_generic() {
        return true;
    }

    /**
     * Set the default values
     *
     * @param editrule_form $form
     *
     * @return void
     */
    public function set_default($form) {
        $params = $this->set_default_select_date();
        $form->set_data($params);
    }

    /**
     * Update any necessary ids and json parameters in the database.
     * It is called near the completion of course restoration.
     *
     * @param string $restoreid Restore identifier
     * @param int $courseid Course identifier
     * @param \base_logger $logger Logger if any warnings
     *
     * @return bool|void False if restore is not required
     */
    public function update_after_restore($restoreid, $courseid, \base_logger $logger) {
        global $DB;

        $oldeventid = json_decode($this->get_parameters())->{self::UI_ACTIVITY};
        $rec = \restore_dbops::get_backup_ids_record($restoreid, 'event', $oldeventid);

        if (!$rec || !$rec->newitemid) {
            // If we are on the same course (e.g. duplicate) then we can just
            // use the existing one.
            if ($DB->record_exists('event', ['id' => $oldeventid, 'courseid' => $courseid])) {
                return false;
            }
            // Otherwise it's a warning.
            $logger->process(
                'Restored item (' . $this->get_pluginname() . ')
                has eventid on action that was not restored',
                \backup::LOG_WARNING
            );
        } else {
            $newparameters = json_decode($this->get_parameters());
            $newparameters->{self::UI_ACTIVITY} = $rec->newitemid;
            $newparameters = json_encode($newparameters);

            $record = new \stdClass();
            $record->id = $this->get_id();
            $record->parameters = $newparameters;
            $record->cmid = $rec->newitemid;

            $DB->update_record('notificationsagent_condition', $record);
        }
    }
}
