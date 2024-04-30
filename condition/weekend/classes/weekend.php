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
 * @package    notificationscondition_weekend
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace notificationscondition_weekend;

use local_notificationsagent\evaluationcontext;
use local_notificationsagent\notificationconditionplugin;
use core_calendar\type_factory;

/**
 * Weekend supluging class
 */
class weekend extends notificationconditionplugin {

    /**
     * Subplugin name
     */
    public const NAME = 'weekend';
    /**
     * User interface description
     */
    public const UI_DESCRIPTION = 'description';

    /**
     * Subplugin title
     *
     * @return \lang_string|string
     */
    public function get_title() {
        return get_string('conditiontext', 'notificationscondition_weekend');
    }

    /**
     *  Subplugins elements
     *
     * @return string[]
     */
    public function get_elements() {
        return [];
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
        return self::is_weekend($context->get_timeaccess());
    }

    /**
     * Estimate next time when this condition will be true.
     *
     * @param evaluationcontext $context
     *
     * @return false|int|mixed|null
     */
    public function estimate_next_time(evaluationcontext $context) {
        global $CFG;
        $weekendconfig = get_config('core', 'calendar_weekend');
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
        // Condición.
        if (!$context->is_complementary()) {
            if (self::is_weekend($context->get_timeaccess())) {
                return time();
            }
            $day += 1;

            while (!($weekendconfig & (1 << (++$dayofweek % $calendar->get_num_weekdays())))) {
                $day++;
            }
            // Exception.
        } else {
            if (!self::is_weekend($context->get_timeaccess())) {
                return time();
            }
            $day += 1;

            while (($weekendconfig & (1 << (++$dayofweek % $calendar->get_num_weekdays())))) {
                $day++;
            }
        }
        return make_timestamp($year, $month, $day, $hour, $minute, 0, $CFG->timezone);
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

        $weekendconfig = get_config(null, 'calendar_weekend');
        $calendar = \core_calendar\type_factory::get_calendar_instance();
        $params = $calendar->get_weekdays();
        $test = [];
        foreach ($params as $key => $value) {
            if ($weekendconfig & (1 << $key)) {
                $test[] = $value['fullname'];
            }
        }
        $concatenatedtoday = implode(', ', $test);

        $element = $mform->createElement(
            'static', $this->get_name_ui(self::UI_DESCRIPTION), get_string('weekendtag', 'notificationscondition_weekend'),
            get_string('weekendtext', 'notificationscondition_weekend', ['weekend' => $concatenatedtoday])
        );
        $mform->insertElementBefore($element, 'new' . $type . '_group');

    }

    /**
     * Capability for subplugin
     *
     * @param \context $context
     *
     * @return bool
     */
    public function check_capability($context) {
        return has_capability('local/notificationsagent:weekend', $context);
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
        return null;
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
        $content[] = $this->get_title();
    }

    /**
     * Whether a subplugins is generic
     *
     * @return bool
     */
    public function is_generic() {
        return true;
    }

    /**
     * Determine if is weekend given a time.
     *
     * @param int $time
     *
     * @return bool
     */
    public static function is_weekend($time): bool {
        $isweekend = false;
        $today = date("w", $time);
        // Get weekend core configuration.
        $weekendconfig = get_config('core', 'calendar_weekend');
        $calendar = type_factory::get_calendar_instance();

        $params = $calendar->get_weekdays();
        foreach ($params as $key => $value) {
            if (($weekendconfig & (1 << $key)) && $key == $today) {
                $isweekend = true;
            }
        }
        return $isweekend;
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
