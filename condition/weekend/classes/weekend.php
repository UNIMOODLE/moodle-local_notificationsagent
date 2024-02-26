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
 * @package    local_notificationsagent
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace notificationscondition_weekend;

use local_notificationsagent\evaluationcontext;
use local_notificationsagent\notificationconditionplugin;
use core_calendar\type_factory;

class weekend extends notificationconditionplugin {

    /** @var UI ELEMENTS */
    public const NAME = 'weekend';
    public const UI_DESCRIPTION = 'description';

    /**
     * @return \lang_string|string
     * @throws \coding_exception
     */
    public function get_title() {
        return get_string('conditiontext', 'notificationscondition_weekend');
    }

    /**
     * @return array
     */
    public function get_elements() {
        return [];
    }

    /**
     * @return \lang_string|string
     * @throws \coding_exception
     */
    public function get_subtype() {
        return get_string('subtype', 'notificationscondition_weekend');
    }

    /** Evaluates this condition using the context variables or the system's state and the complementary flag.
     *
     * @param evaluationcontext $context  |null collection of variables to evaluate the condition.
     *                                    If null the system's state is used.
     *
     * @return bool true if the condition is true, false otherwise.
     */
    public function evaluate(evaluationcontext $context): bool {
        return self::is_weekend($context->get_timeaccess());
    }

    /** Estimate next time when this condition will be true. */
    public function estimate_next_time(evaluationcontext $context) {
        return null;
    }

    /**
     * @param $mform
     * @param $id
     * @param $courseid
     * @param $type
     *
     * @return void
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function get_ui($mform, $id, $courseid, $type) {
        $this->get_ui_title($mform, $id, $type);

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
            'static', $this->get_name_ui($id, self::UI_DESCRIPTION), get_string('weekendtag', 'notificationscondition_weekend'),
            get_string('weekendtext', 'notificationscondition_weekend', ['weekend' => $concatenatedtoday])
        );
        $mform->insertElementBefore($element, 'new' . $type . '_group');

    }

    /**
     * @param $context
     *
     * @return bool
     * @throws \coding_exception
     */
    public function check_capability($context) {
        return has_capability('local/notificationsagent:weekend', $context);
    }

    /**
     * @param array $params
     *
     * @return mixed
     */
    protected function convert_parameters($id, $params) {
        return null;
    }

    /**
     * @param $content
     * @param $courseid
     * @param $options
     *
     * @return string|void
     * @throws \coding_exception
     */
    public function process_markups(&$content, $courseid, $options = null) {
        $content[] = $this->get_title();
    }

    public function is_generic() {
        return true;
    }

    /**
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function is_weekend($time): bool {
        $isweekend = false;
        $today = date('l', $time);
        // Get weekend core configuration.
        $weekendconfig = get_config(null, 'calendar_weekend');
        $calendar = type_factory::get_calendar_instance();

        $params = $calendar->get_weekdays();
        foreach ($params as $key => $value) {
            if (($weekendconfig & (1 << $key)) && $value['fullname'] === $today) {
                $isweekend = true;
            }
        }
        return $isweekend;
    }

}
