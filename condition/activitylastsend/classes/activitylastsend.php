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
 * @package    notificationscondition_activitylastsend
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace notificationscondition_activitylastsend;

use local_notificationsagent\evaluationcontext;
use local_notificationsagent\form\editrule_form;
use local_notificationsagent\notificationconditionplugin;
use local_notificationsagent\rule;

class activitylastsend extends notificationconditionplugin {

    /**
     * Subplugin name
     */
    public const NAME = 'activitylastsend';

    /**
     * Subplugin title
     *
     * @return \lang_string|string
     */
    public function get_title() {
        return get_string('conditiontext', 'notificationscondition_activitylastsend');
    }

    /**
     *  Subplugins elements
     *
     * @return string[]
     */
    public function get_elements() {
        return ['[TTTT]', '[AAAA]'];
    }

    public function get_subtype() {
        return get_string('subtype', 'notificationscondition_activitylastsend');
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
        $pluginname = $this->get_subtype();
        $timeaccess = $context->get_timeaccess();
        $conditionid = $this->get_id();

        $timelastsend = $DB->get_field(
            'notificationsagent_cache',
            'timestart',
            ['conditionid' => $conditionid, 'courseid' => $courseid, 'userid' => $userid, 'pluginname' => $pluginname],
        );

        if (!empty($timelastsend)) {
            ($timeaccess >= $timelastsend) ? $meetcondition = true : $meetcondition = false;
        }
        return $meetcondition;
    }

    /** Estimate next time when this condition will be true. */
    public function estimate_next_time(evaluationcontext $context) {
        return null;
    }

    public function get_ui($mform, $id, $courseid, $type) {
        $this->get_ui_title($mform, $id, $type);

        // Activity.
        $listactivities = [];
        $modinfo = get_fast_modinfo($courseid);
        foreach ($modinfo->get_cms() as $cm) {
            $listactivities[$cm->id] = format_string($cm->name);
        }
        // Only is template
        if ($this->rule->get_template() == rule::TEMPLATE_TYPE) {
            $listactivities['0'] = 'AAAA';
        }
        
        asort($listactivities);

        $element = $mform->createElement(
            'select',
            $this->get_name_ui($id, self::UI_ACTIVITY),
            get_string(
                'editrule_condition_activity', 'notificationscondition_activitysinceend',
                ['typeelement' => '[AAAA]']
            ),
            $listactivities
        );

        $this->get_ui_select_date($mform, $id, $type);
        $mform->insertElementBefore($element, 'new' . $type . '_group');
        $mform->addRule($this->get_name_ui($id, self::UI_ACTIVITY), get_string('editrule_required_error', 'local_notificationsagent'), 'required');
    }

    public function check_capability($context) {
        return has_capability('local/notificationsagent:activitylastsend', $context);
    }

    /**
     * @param array $params
     *
     * @return mixed
     */
    protected function convert_parameters($id, $params) {
        $params = (array) $params;
        $activity = $params[$this->get_name_ui($id, self::UI_ACTIVITY)] ?? 0;
        $timevalues = [
            'days' => $params[$this->get_name_ui($id, self::UI_DAYS)] ?? 0,
            'hours' => $params[$this->get_name_ui($id, self::UI_HOURS)] ?? 0,
            'minutes' => $params[$this->get_name_ui($id, self::UI_MINUTES)] ?? 0,
            'seconds' => $params[$this->get_name_ui($id, self::UI_SECONDS)] ?? 0,
        ];
        $timeinseconds = ($timevalues['days'] * 24 * 60 * 60) + ($timevalues['hours'] * 60 * 60)
            + ($timevalues['minutes'] * 60) + $timevalues['seconds'];
        $this->set_parameters(json_encode([self::UI_TIME => $timeinseconds, self::UI_ACTIVITY => (int) $activity]));
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
        if ($cmid && $mod = get_fast_modinfo($courseid)->get_cm($cmid)) {
            $activityname = $mod->name;
        }

        $paramstoreplace = [to_human_format($jsonparams->{self::UI_TIME}, true), $activityname];
        $humanvalue = str_replace($this->get_elements(), $paramstoreplace, $this->get_title());

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

    /**
     * Get the files that have not been modified by the user in the activity for a specified time.
     *
     * @param int $cmid          The course module id.
     * @param int $userid        The user id.
     * @param int $conditiontime The end time for the course module.
     * @param int $crontasktime  The time of the cron task.
     *
     * @return object The file that has not been modified for the specified time
     */
    public static function get_cmidfiles($cmid, $userid, $conditiontime, $crontasktime) {
        global $DB;

        $data = [];

        $sql = '
            SELECT f.id, f.userid, f.timemodified
              FROM {context} ctx
              JOIN {files} f
                ON ctx.id = f.contextid
               AND ctx.instanceid = :cmid
               AND ctx.contextlevel = :contextlevel
             WHERE f.filesize <> 0
               AND f.timemodified < :now + :time
               AND f.userid = :userid
        ';

        $data = $DB->get_record_sql(
            $sql,
            [
                'cmid' => $cmid,
                'contextlevel' => CONTEXT_MODULE,
                'userid' => $userid,
                'time' => $conditiontime,
                'now' => $crontasktime,
            ]
        );

        return $data;
    }
}
