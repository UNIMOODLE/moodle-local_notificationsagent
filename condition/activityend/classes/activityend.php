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
 * @package    notificationscondition_activityend
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace notificationscondition_activityend;

use local_notificationsagent\evaluationcontext;
use local_notificationsagent\form\editrule_form;
use local_notificationsagent\notificationsagent;
use local_notificationsagent\notificationconditionplugin;
use local_notificationsagent\rule;

/**
 * This class handles the condition of activity end.
 */
class activityend extends notificationconditionplugin {

    /**
     * Subplugin name
     */
    public const NAME = 'activityend';

    /**
     * Subplugin title
     *
     * @return \lang_string|string
     */
    public function get_title() {
        return get_string('conditiontext', 'notificationscondition_activityend');
    }

    /**
     *  Subplugins elements
     *
     * @return string[]
     */
    public function get_elements() {
        return ['[TTTT]', '[AAAA]'];
    }

    /** Evaluates this condition using the context variables or the system's state and the complementary flag.
     *
     * @param evaluationcontext $context |null collection of variables to evaluate the condition.
     *                                    If null the system's state is used.
     *
     * @return bool true if the condition is true, false otherwise.
     */
    public function evaluate(evaluationcontext $context): bool {
        // Params being like "time": "cmid":"" .
        global $DB;
        $meetcondition = false;
        $courseid = $context->get_courseid();
        $userid = $context->get_userid();
        $params = json_decode($context->get_params());
        $cmid = $params->{self::UI_ACTIVITY};
        $coursecontext = \context_course::instance($courseid);
        $pluginname = $this->get_subtype();
        $timeaccess = $context->get_timeaccess();
        $conditionid = $this->get_id();
        $timeendactivity = notificationsagent::notificationsagent_condition_get_cm_dates($cmid)->timeend;

        $timeend = $DB->get_field(
                'notificationsagent_cache',
                'startdate',
                ['conditionid' => $conditionid, 'courseid' => $courseid, 'userid' => $userid, 'pluginname' => $pluginname],
        );

        if (empty($timeend)) {
            $timeend = $timeendactivity - $params->{self::UI_TIME};
        }

        ($timeaccess >= $timeend && $timeaccess < $timeendactivity) ? $meetcondition = true : $meetcondition = false;

        return $meetcondition;
    }

    /**
     * Estimate the next time when this condition will be true.
     *
     * @param evaluationcontext $context The evaluation context object.
     *
     * @return int|null The estimated time when the condition will be true, or null if it cannot be estimated.
     */
    public function estimate_next_time(evaluationcontext $context) {
        $timeend = null;
        $params = json_decode($context->get_params());
        $cmid = $params->{self::UI_ACTIVITY};
        $time = $params->{self::UI_TIME};
        $timeaccess = $context->get_timeaccess();
        $cmidend = notificationsagent::notificationsagent_condition_get_cm_dates($cmid)->timeend;

        // Condition.
        if (!$context->is_complementary()) {
            if ($timeaccess <= $cmidend - $time) {
                $timeend = $cmidend - $time;
            } else if ($timeaccess >= $cmidend - $time && $timeaccess < $cmidend) {
                $timeend = time();
            }
        }
        // Exception.
        if ($context->is_complementary()) {
            if ($timeaccess >= $cmidend - $time && $timeaccess < $cmidend) {
                $timeend = $cmidend;
            } else {
                $timeend = time();
            }
        }
        return $timeend;
    }

    /**
     * Get the UI elements for the subplugin.
     *
     * @param \MoodleQuickForm $mform The mform object to generate the UI for.
     * @param int $courseid The ID of the course.
     * @param string $type The type of the form element.
     */
    public function get_ui($mform, $courseid, $type) {
        $this->get_ui_title($mform, $type);

        // Activity.
        $listactivities = [];
        $modinfo = get_fast_modinfo($courseid);
        foreach ($modinfo->get_cms() as $cm) {
            $listactivities[$cm->id] = format_string($cm->name);
        }
        // Only is template.
        if ($this->rule->template == rule::TEMPLATE_TYPE) {
            $listactivities['0'] = 'AAAA';
        }

        asort($listactivities);

        $element = $mform->createElement(
                'select',
                $this->get_name_ui(self::UI_ACTIVITY),
                get_string(
                        'editrule_condition_activity', 'notificationscondition_activityend',
                        ['typeelement' => '[AAAA]']
                ),
                $listactivities
        );

        $this->get_ui_select_date($mform, $type);
        $mform->insertElementBefore($element, 'new' . $type . '_group');
        $mform->addRule(
                $this->get_name_ui(self::UI_ACTIVITY), get_string('editrule_required_error', 'local_notificationsagent'),
                'required'
        );
    }

    /**
     * Validation subplugin
     * If this method overrides, call to parent::validation
     *
     * @param int $courseid Course id
     * @param array $array The array to be modified by reference. If is null, validation is not being called from the form
     *                                  and return directly
     * @param bool $onlyverifysiteid Default false. If true, only SITEID is verified
     *
     * @return bool
     */
    public function validation($courseid, &$array = null, $onlyverifysiteid = false) {
        if (($validation = parent::validation($courseid, $array)) === 'break') {
            return true;
        }

        // If it is false from parent and $array is null, return.
        if (is_null($array) && !$validation) {
            return $validation;
        }

        // All parameters.
        $data = json_decode($this->get_parameters(), true);

        // Parameters to validate.
        $cmid = $data[self::UI_ACTIVITY];
        if (!$validation = notificationsagent::notificationsagent_condition_get_cm_dates($cmid)->timeend) {
            if (is_null($array)) {
                return false;
            }
            $array[$this->get_name_ui(self::UI_ACTIVITY)] = get_string(
                    'validation_editrule_form_dateend', 'notificationscondition_activityend'
            );
        }

        return true;
    }

    /**
     * Sublugin capability
     *
     * @param \context $context
     *
     * @return bool
     */
    public function check_capability($context) {
        return has_capability('local/notificationsagent:activityend', $context);
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
        $activity = $params[$this->get_name_ui(self::UI_ACTIVITY)] ?? 0;
        $timeinseconds = $this->select_date_to_unix($params);
        $this->set_parameters(json_encode([self::UI_TIME => $timeinseconds, self::UI_ACTIVITY => (int) $activity]));
        $this->set_cmid((int) $activity);
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

        // Check if activity is found in course, if is not, return [AAAA].
        $activityname = '[AAAA]';
        $cmid = $jsonparams->{self::UI_ACTIVITY};

        $fastmodinfo = get_fast_modinfo($courseid);
        $activityname = isset($fastmodinfo->cms[$cmid]) ? $fastmodinfo->cms[$cmid]->name : $activityname;

        $paramstoreplace =
                [\local_notificationsagent\helper\helper::to_human_format($jsonparams->{self::UI_TIME}, true), $activityname];
        $humanvalue = str_replace($this->get_elements(), $paramstoreplace, $this->get_title());

        $content[] = $humanvalue;
    }

    /**
     * Whether a subluplugin is generic
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
}
