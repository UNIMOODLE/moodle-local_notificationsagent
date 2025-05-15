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
 * @package    notificationscondition_coursestart
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace notificationscondition_coursestart;

use local_notificationsagent\evaluationcontext;
use local_notificationsagent\form\editrule_form;
use local_notificationsagent\helper\helper;
use local_notificationsagent\notificationconditionplugin;

/**
 * Class representing the coursestart condition plugin.
 */
class coursestart extends notificationconditionplugin {
    /**
     * Subplugin name
     */
    public const NAME = 'coursestart';

    /**
     * Subplugin title
     *
     * @return \lang_string|string
     */
    public function get_title() {
        return get_string('conditiontext', 'notificationscondition_coursestart');
    }

    /**
     *  Subplugins elements
     *
     * @return string[]
     */
    public function get_elements() {
        return ['[TTTT]'];
    }

    /** Evaluates this condition using the context variables or the system's state and the complementary flag.
     *
     * @param evaluationcontext $context |null collection of variables to evaluate the condition.
     *                                    If null the system's state is used.
     *
     * @return bool true if the condition is true, false otherwise.
     */
    public function evaluate(evaluationcontext $context): bool {
        $courseid = $context->get_courseid();
        $params = json_decode($context->get_params());
        $meetcondition = false;
        $timeaccess = $context->get_timeaccess();

        if ($course = helper::get_cache_course($courseid)) {
            $timestart = $course->startdate + $params->{self::UI_TIME};
            $meetcondition = ($timeaccess >= $timestart);
        }

        return $meetcondition;
    }

    /**
     * Estimate next time when this condition will be true.
     *
     * @param evaluationcontext $context
     *
     * @return int|mixed|null
     */
    public function estimate_next_time(evaluationcontext $context) {
        $timestart = null;
        $params = json_decode($context->get_params());
        $courseid = $context->get_courseid();

        if ($course = helper::get_cache_course($courseid)) {
            $coursestart = $course->startdate;
            $timeaccess = $context->get_timeaccess();
            // Condition.
            if (!$context->is_complementary()) {
                if ($timeaccess <= ($coursestart + $params->{self::UI_TIME})) {
                    $timestart = max(time(), $coursestart + $params->{self::UI_TIME});
                } else {
                    return time();
                }
            }
            // Exception.
            if (
                    ($timeaccess <= $coursestart + $params->{self::UI_TIME} && $timeaccess >= $coursestart)
                    && $context->is_complementary()
            ) {
                return time();
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
        $this->get_ui_select_date($mform, $type);
    }

    /**
     *  Check subplugin capability
     *
     * @param \context $context
     *
     * @return bool
     */
    public function check_capability($context) {
        return has_capability('notificationscondition/coursestart:coursestart', $context);
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
        $timeinseconds = $this->select_date_to_unix($params);
        $this->set_parameters(json_encode([self::UI_TIME => $timeinseconds]));
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
     * Wether a subplugin is generic
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
        return false;
    }
}
