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
 * @package    notificationscondition_weekdays
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace notificationscondition_weekdays;

use local_notificationsagent\evaluationcontext;
use local_notificationsagent\notificationconditionplugin;
use core_calendar\type_factory;

/**
 * weekdays supluging class
 */
class weekdays extends notificationconditionplugin {

    /**
     * Subplugin name
     */
    public const NAME = 'weekdays';
    /**
     * Day of week name
     */
    public const UI_DAYOFWEEK = 'dayofweek';

    /**
     * Subplugin title
     *
     * @return \lang_string|string
     */
    public function get_title() {
        return get_string('conditiontext', 'notificationscondition_weekdays');
    }

    /**
     *  Subplugins elements
     *
     * @return string[]
     */
    public function get_elements() {
        return ['LMXJVSD'];
    }

    /**
     * Evaluates this condition using the context variables or the system's state and the complementary flag.
     *
     * @param evaluationcontext $context  |null collection of variables to evaluate the condition.
     *                                    If null the system's state is used.
     *
     * @return bool true if the condition is true, false otherwise.
     */
    public function evaluate(evaluationcontext $context): bool {
        $timeaccess = $context->get_timeaccess();
        $params = $context->get_params();
        $data = json_decode($params, true);
        $selecteddays = $data[self::NAME];
        $today = date("w", $timeaccess);
        return self::correct_weekday($today, $selecteddays);
    }

    /**
     * Estimate next time when this condition will be true.
     *
     * @param evaluationcontext $context
     *
     * @return int|null
     */
    public function estimate_next_time(evaluationcontext $context) {
        global $CFG;
        $timestamp = null;
        $params = $context->get_params();
        $data = json_decode($params, true);
        $selecteddays = $data[self::NAME];

        $calendar = type_factory::get_calendar_instance();
        [
            'year' => $year,
            'mon' => $month,
            'mday' => $day,
            'wday' => $dayofweek,
            'hours' => $hour,
            'minutes' => $minute,
        ]
            = usergetdate($context->get_timeaccess(), $CFG->timezone);
        // Condition.
        if (!$context->is_complementary()) {
            if (self::correct_weekday($dayofweek, $selecteddays)) {
                return (time());
            }

            $weekdays = $calendar->get_weekdays();
            foreach ($weekdays as $weekday => $value) {
                if (self::correct_weekday(
                    date('w', $timestamp = make_timestamp($year, $month, $day, $hour, $minute, 0, $CFG->timezone)), $selecteddays
                )
                ) {
                    return $timestamp;
                }
                $day++;
            }
            // Exception.
        } else {
            if (!self::correct_weekday($dayofweek, $selecteddays)) {
                return (time());
            }

            $weekdays = $calendar->get_weekdays();
            foreach ($weekdays as $weekday => $value) {
                if (!self::correct_weekday(
                    date('w', $timestamp = make_timestamp($year, $month, $day, $hour, $minute, 0, $CFG->timezone)),
                    $selecteddays
                )
                ) {
                    return $timestamp;
                }
                $day++;
            }
        }
        return $timestamp;
    }

    /**
     * Get the UI elements for the subplugin.
     *
     * @param \MoodleQuickForm $mform    The form to which the elements will be added.
     * @param int              $courseid The course identifier.
     * @param string           $type     The type of the notification plugin.
     */
    public function get_ui($mform, $courseid, $type) {
        $this->get_ui_title($mform, $type);

        $weekdays = $this->get_weekdays_list();
        // Example of checkbox id: 15_weekdays_dayofweek0 for sunday.
        foreach ($weekdays as $key => $value) {
            $radioarray[] = $mform->createElement(
                'advcheckbox',
                $this->get_name_ui(self::UI_DAYOFWEEK . $key),
                '', $value['fullname'], $key
            );
        }

        $radiogroup = $mform->createElement(
            'group', $this->get_name_ui($this->get_subtype()),
            '',
            $radioarray, null, false
        );

        $mform->insertElementBefore($radiogroup, 'new' . $type . '_group');
    }

    /**
     * Capability for subplugin
     *
     * @param \context $context
     *
     * @return bool
     */
    public function check_capability($context) {
        return has_capability('local/notificationsagent:weekdays', $context);
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

        $selected = [];
        $weekdays = self::get_weekdays_list();
        // Example of checkbox id: 15_weekdays_dayofweek0 for sunday.
        foreach ($weekdays as $key => $value) {
            if ($params[$this->get_name_ui(self::UI_DAYOFWEEK . $key)]) {
                $selected[] = $key;
            }
        }

        $this->set_parameters(json_encode([self::NAME => $selected]));
        return $this->get_parameters();
    }

    /**
     * Return array with weekdays
     *
     * @return array
     */
    private static function get_weekdays_list() {
        $calendar = \core_calendar\type_factory::get_calendar_instance();
        $calendar->get_weekdays();
        return $calendar->get_weekdays();
    }

    /**
     * Process and replace markups in the supplied content.
     *
     * This function should handle any markup logic specific to a notification plugin,
     * such as replacing placeholders with dynamic data, formatting content, etc.
     *
     * @param array $content  The content to be processed, passed by reference.
     * @param int   $courseid The ID of the course related to the content.
     * @param mixed $options  Additional options if any, null by default.
     *
     * @return void Processed content with markups handled.
     */
    public function process_markups(&$content, $courseid, $options = null) {
        $jsonparams = json_decode($this->get_parameters());
        $calendar = \core_calendar\type_factory::get_calendar_instance();
        $calendar->get_weekdays();
        $weekdays = $calendar->get_weekdays();

        foreach ($jsonparams->{self::NAME} as $day) {
            empty($contentdays) ? $contentdays = $weekdays[$day]['fullname']
                : $contentdays .= ', ' . $weekdays[$day]['fullname'];
        }
        $humanvalue = str_replace($this->get_elements(), $contentdays, $this->get_title());
        $content[] = $humanvalue;
    }

    /**
     * Whether a subplugins is generic
     *
     * @return true
     */
    public function is_generic() {
        return true;
    }

    /**
     * Check if today is marked as true.
     *
     * @param int   $today (0 => sunday, 1 => monday...)
     * @param array $days  weekdays selected
     *
     * @return boolean
     */
    public static function correct_weekday($today, $days): bool {
        return in_array($today, $days);
    }

    /**
     * Validation subplugin
     * If this method overrides, call to parent::validation
     *
     * @param int   $courseid           Course id
     * @param array $array              The array to be modified by reference. If is null, validation is not being called from the form
     *                                  and return directly
     * @param bool  $onlyverifysiteid   Default false. If true, only SITEID is verified
     * 
     * @return bool
     */
    public function validation($courseid, &$array = null, $onlyverifysiteid = false) {
        if (($validation = parent::validation($courseid, $array)) === 'break') {
            return true;
        }

        // If false from parent and $array is null, return
        if (is_null($array) && !$validation) {
            return $validation;
        }

        $data = json_decode($this->get_parameters(), true);

        $weekdaysselected = $data[self::NAME];
        $weekdays = self::get_weekdays_list();

        if (empty($weekdaysselected)) {
            $array[$this->get_name_ui($this->get_subtype())] = get_string('weekdaysrequired', 'notificationscondition_weekdays');
            return false;
        }

        // Example of checkbox id: 15_weekdays_dayofweek0 for sunday.
        foreach ($weekdaysselected as $day) {
            if (array_key_exists($day, $weekdays)) {
                $validation = true;
            }
        }

        return $validation;
    }

    /**
     * Loads the data from the database.
     *
     * @return mixed
     */
    public function load_dataform() {
        $params = json_decode($this->get_parameters(), false);
        $arrayparams = [];
        foreach ($params->{self::NAME} as $param) {
            $key = $this->get_name_ui(self::UI_DAYOFWEEK . $param);
            $arrayparams[$key] = true;

        }
        return $arrayparams;
    }

    /**
     * Update any necessary ids and json parameters in the database.
     * It is called near the completion of course restoration.
     *
     * @param string       $restoreid Restore identifier
     * @param integer      $courseid  Course identifier
     * @param \base_logger $logger    Logger if any warnings
     *
     * @return bool False if restore is not required
     */
    public function update_after_restore($restoreid, $courseid, \base_logger $logger) {
        return false;
    }
}
