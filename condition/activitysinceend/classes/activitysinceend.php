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
 * @package    notificationscondition_activitysinceend
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace notificationscondition_activitysinceend;

use local_notificationsagent\evaluationcontext;
use local_notificationsagent\form\editrule_form;
use local_notificationsagent\notificationconditionplugin;
use local_notificationsagent\rule;

/**
 * Class activitysinceend.
 */
class activitysinceend extends notificationconditionplugin {

    /**
     * Subplugin name
     */
    public const NAME = 'activitysinceend';

    /**
     * Subplugin title
     *
     * @return \lang_string|string
     */
    public function get_title() {
        return get_string('conditiontext', 'notificationscondition_activitysinceend');
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
     * @param evaluationcontext $context  |null collection of variables to evaluate the condition.
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
        $pluginname = $this->get_subtype();
        $timeaccess = $context->get_timeaccess();
        $conditionid = $this->get_id();
        $timeend = $DB->get_field(
            'notificationsagent_cache',
            'startdate',
            ['conditionid' => $conditionid, 'courseid' => $courseid, 'userid' => $userid, 'pluginname' => $pluginname],
        );

        if (empty($timeend)) {
            $timeend = self::get_timecompletion($cmid, $userid)->timemodified ?? null;;
            if (empty($timeend)) {
                return $meetcondition;
            }
        }
        ($timeaccess >= $timeend) ? $meetcondition = true : $meetcondition = false;
        return $meetcondition;
    }

    /** Estimate next time when this condition will be true.
     *
     * @param evaluationcontext $context |null collection of variables to evaluate the condition.
     */
    public function estimate_next_time(evaluationcontext $context) {
        $timereturn = null;
        $userid = $context->get_userid();
        $params = json_decode($context->get_params(), false);
        $cmid = $params->{self::UI_ACTIVITY};
        $timecompletion = self::get_timecompletion($cmid, $userid)->timemodified ?? $timereturn;
        $timeaccess = $context->get_timeaccess();

        if (empty($timecompletion)) {
            return null;
        }

        // Condition.
        if (!$context->is_complementary()) {
            if ($timeaccess >= $timecompletion && $timeaccess <= $timecompletion + $params->{self::UI_TIME}) {
                $timereturn = $timecompletion + $params->{self::UI_TIME};
            } else if ($timeaccess > $timecompletion + $params->{self::UI_TIME}) {
                $timereturn = time();
            }
        }

        // Exception.
        if ($timeaccess >= $timecompletion
            && $timeaccess < $timecompletion + $params->{self::UI_TIME}
            && $context->is_complementary()
        ) {
            $timereturn = time();
        }

        return $timereturn;
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
                'editrule_condition_activity', 'notificationscondition_activitysinceend',
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
     * Sublugin capability
     *
     * @param \context $context
     *
     * @return bool
     */
    public function check_capability($context) {
        return has_capability('local/notificationsagent:activitysinceend', $context);
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
        $timevalues = [
            'days' => $params[$this->get_name_ui(self::UI_DAYS)] ?? 0,
            'hours' => $params[$this->get_name_ui(self::UI_HOURS)] ?? 0,
            'minutes' => $params[$this->get_name_ui(self::UI_MINUTES)] ?? 0,
            'seconds' => $params[$this->get_name_ui(self::UI_SECONDS)] ?? 0,
        ];
        $timeinseconds = ($timevalues['days'] * 24 * 60 * 60) + ($timevalues['hours'] * 60 * 60)
            + ($timevalues['minutes'] * 60) + $timevalues['seconds'];
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
     * @param array $content  The content to be processed, passed by reference.
     * @param int   $courseid The ID of the course related to the content.
     * @param mixed $options  Additional options if any, null by default.
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

        $paramstoreplace = [to_human_format($jsonparams->{self::UI_TIME}, true), $activityname];
        $humanvalue = str_replace($this->get_elements(), $paramstoreplace, $this->get_title());

        $content[] = $humanvalue;
    }

    /**
     * Whether a subluplugin is generic
     *
     * @return bool
     */
    public function is_generic() {
        return false;
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
     * Get time completion
     *
     * @param int $cmid
     * @param int $userid
     */
    public static function get_timecompletion($cmid, $userid) {
        global $DB;
        $endtimequery = "
                     SELECT userid ,timemodified
                       FROM {course_modules_completion}
                      WHERE coursemoduleid = :cmid
                        AND completionstate > 0
                        AND userid = :userid";

        $completion = $DB->get_record_sql(
            $endtimequery,
            [
                'cmid' => $cmid,
                'userid' => $userid,
            ]
        );
        return $completion;
    }
}
