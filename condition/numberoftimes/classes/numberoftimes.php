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
 * @package    notificationscondition_numberoftimes
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace notificationscondition_numberoftimes;

use local_notificationsagent\evaluationcontext;
use local_notificationsagent\form\editrule_form;
use local_notificationsagent\notificationconditionplugin;

class numberoftimes extends notificationconditionplugin {

    /**
     * Subplugin name
     */
    public const NAME = 'numberoftimes';
    public const N_TIMES = 'times';

    /**
     * Subplugin title
     *
     * @return \lang_string|string
     */
    public function get_title() {
        return get_string('conditiontext', 'notificationscondition_numberoftimes');
    }

    /**
     *  Subplugins elements
     *
     * @return string[]
     */
    public function get_elements() {
        return ['[TTTT]', '[N]'];
    }

    public function get_subtype() {
        return get_string('subtype', 'notificationscondition_numberoftimes');
    }

    /** Evaluates this condition using the context variables or the system's state and the complementary flag.
     *
     * @param evaluationcontext $context  |null collection of variables to evaluate the condition.
     *                                    If null the system's state is used.
     *
     * @return bool true if the condition is true, false otherwise.
     */
    public function evaluate(evaluationcontext $context): bool {
        global $DB;
        $courseid = $context->get_courseid();
        $userid = $context->get_userid();
        $ruleid = $context->get_rule()->get_id();
        $timeaccess = $context->get_timeaccess();
        $params = json_decode($context->get_params(), false);
        $meetcondition = false;
        $time = $params->{self::UI_TIME};
        $numbertimes = $params->{self::N_TIMES};

        $launched = $DB->get_record(
            'notificationsagent_launched',
            ['ruleid' => $ruleid, 'userid' => $userid, 'courseid' => $courseid]
        );

        if ($launched === false) {
            return true;
        }

        ($timeaccess >= $launched->timemodified + $time && $launched->timesfired < $numbertimes)
            ? $meetcondition = true : $meetcondition = false;

        return $meetcondition;
    }

    /** Estimate next time when this condition will be true. */
    public function estimate_next_time(evaluationcontext $context) {
        return null;
    }

    public function get_ui($mform, $id, $courseid, $type) {
        $this->get_ui_title($mform, $id, $type);

        // Number of times.
        $element = $mform->createElement(
            'float', $this->get_name_ui($id, self::N_TIMES),
            get_string('condition_number_times', 'notificationscondition_numberoftimes', ['typeelement' => '[N]']),
            [
                'class' => 'mr-2', 'size' => '7', 'maxlength' => '3',
                'placeholder' => get_string('condition_number', 'notificationscondition_numberoftimes'),
                'oninput' => 'this.value = this.value.replace(/[^0-9]/g, "").replace(/(\..*)\./g, "$1")',
            ]
        );

        $this->get_ui_select_date($mform, $id, $type);
        $mform->insertElementBefore($element, 'new' . $type . '_group');
        $mform->addRule($this->get_name_ui($id, self::N_TIMES), get_string('editrule_required_error', 'local_notificationsagent'), 'required');
    }

    public function check_capability($context) {
        return has_capability('local/notificationsagent:numberoftimes', $context);
    }

    /**
     * @param array $params
     *
     * @return mixed
     */
    protected function convert_parameters($id, $params) {
        $params = (array) $params;
        $numbertimes = $params[$this->get_name_ui($id, self::N_TIMES)];
        $timevalues = [
            'days' => $params[$this->get_name_ui($id, self::UI_DAYS)] ?? 0,
            'hours' => $params[$this->get_name_ui($id, self::UI_HOURS)] ?? 0,
            'minutes' => $params[$this->get_name_ui($id, self::UI_MINUTES)] ?? 0,
            'seconds' => $params[$this->get_name_ui($id, self::UI_SECONDS)] ?? 0,
        ];
        $timeinseconds = ($timevalues['days'] * 24 * 60 * 60) + ($timevalues['hours'] * 60 * 60)
            + ($timevalues['minutes'] * 60) + $timevalues['seconds'];
        $this->set_parameters(json_encode([self::UI_TIME => $timeinseconds, self::N_TIMES => (int) $numbertimes]));
        return $this->get_parameters();
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

        $paramstoteplace = [to_human_format($jsonparams->{self::UI_TIME}, true), $jsonparams->{self::N_TIMES}];

        $humanvalue = str_replace($this->get_elements(), $paramstoteplace, $this->get_title());

        $content[] = $humanvalue;
    }

    public function is_generic() {
        return false;
    }

    /**
     * Set the defalut values
     *
     * @param editrule_form $form
     * @param int           $id
     *
     * @return void
     */
    public function set_default($form, $id) {
        $params = $this->set_default_select_date($id);
        $form->set_data($params);
    }

}
