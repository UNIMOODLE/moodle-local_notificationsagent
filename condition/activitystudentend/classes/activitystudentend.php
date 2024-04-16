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
 * @package    notificationscondition_activitystudentend
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace notificationscondition_activitystudentend;

use local_notificationsagent\evaluationcontext;
use local_notificationsagent\form\editrule_form;
use local_notificationsagent\notificationconditionplugin;
use local_notificationsagent\rule;

class activitystudentend extends notificationconditionplugin {

    /**
     * Subplugin name
     */
    public const NAME = 'activitystudentend';

    /**
     * Subplugin title
     *
     * @return \lang_string|string
     */
    public function get_title() {
        return get_string('conditiontext', 'notificationscondition_activitystudentend');
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
        return get_string('subtype', 'notificationscondition_activitystudentend');
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
        $pluginname = $this->get_subtype();
        $conditionid = $this->get_id();
        $timeaccess = $context->get_timeaccess();
        $params = json_decode($context->get_params());
        $meetcondition = false;

        // Timestart es el tiempo de primer acceso más time.
        $lastaccess = $DB->get_field(
            'notificationsagent_cache',
            'timestart', ['conditionid' => $conditionid, 'courseid' => $courseid, 'userid' => $userid, 'pluginname' => $pluginname],
        );

        if (empty($lastaccess)) {
            // Check own plugin table.
            $lastaccess = self::get_cmlastaccess($userid, $courseid, $params->{self::UI_ACTIVITY});

            if (empty($lastaccess)) {
                return $meetcondition;
            }
            $lastaccess += $params->{self::UI_TIME};
        }

        ($timeaccess >= $lastaccess) ? $meetcondition = true : $meetcondition = false;
        return $meetcondition;
    }

    /**
     *  Estimate next time when this condition will be true.
     *
     * @param evaluationcontext $context
     *
     * @return int|mixed|null
     */
    public function estimate_next_time(evaluationcontext $context) {
        $timereturn = null;
        $userid = $context->get_userid();
        $courseid = $context->get_courseid();
        $params = json_decode($context->get_params(), false);
        $lastaccess = self::get_cmlastaccess($userid, $courseid, $params->{self::UI_ACTIVITY});
        $timeaccess = $context->get_timeaccess();

        if (empty($lastaccess)) {
            return null;
        }

        // Condition.
        if (!$context->is_complementary()) {
            if ($timeaccess >= $lastaccess && $timeaccess <= $lastaccess + $params->{self::UI_TIME}) {
                $timereturn = $lastaccess + $params->{self::UI_TIME};
            } else if ($timeaccess > $lastaccess + $params->{self::UI_TIME}) {
                $timereturn = time();
            }
        }

        //Exception.
        if ($timeaccess >= $lastaccess
            && $timeaccess <= $lastaccess + $params->{self::UI_TIME}
            && $context->is_complementary()
        ) {
            $timereturn = time();
        }

        return $timereturn;
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
                'editrule_condition_activity', 'notificationscondition_activitystudentend',
                ['typeelement' => '[AAAA]']
            ),
            $listactivities
        );

        $this->get_ui_select_date($mform, $id, $type);
        $mform->insertElementBefore($element, 'new' . $type . '_group');
        $mform->addRule(
            $this->get_name_ui($id, self::UI_ACTIVITY), get_string('editrule_required_error', 'local_notificationsagent'),
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
        return has_capability('local/notificationsagent:activitystudentend', $context);
    }

    /**
     * Convert parameters for the notification plugin.
     *
     * This method should take an identifier and parameters for a notification
     * and convert them into a format suitable for use by the plugin.
     *
     * @param int   $id     The identifier for the notification.
     * @param mixed $params The parameters associated with the notification.
     *
     * @return mixed The converted parameters.
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
     * Set user's access time to an activity
     *
     * @param $userid
     * @param $courseid
     * @param $idactivity
     * @param $timecreated
     *
     * @throws \dml_exception
     */
    public static function set_activity_access($userid, $courseid, $idactivity, $timecreated) {
        global $DB;
        $exists = $DB->record_exists(
            'notificationsagent_cmview',
            ['userid' => $userid, 'courseid' => $courseid, 'idactivity' => $idactivity]
        );

        $objdb = new \stdClass();
        $objdb->userid = $userid;
        $objdb->courseid = $courseid;
        $objdb->idactivity = $idactivity;
        $objdb->firstaccess = $timecreated;

        if (!$exists) {
            // Si el registro no existe, inserta uno nuevo.
            $DB->insert_record('notificationsagent_cmview', $objdb);
        } else {
            // Si el registro existe, obtén la ID y actualiza el registro.
            $existingrecord = $DB->get_record(
                'notificationsagent_cmview',
                ['userid' => $userid, 'courseid' => $courseid, 'idactivity' => $idactivity]
            );
            $objdb->id = $existingrecord->id;
            $DB->update_record('notificationsagent_cmview', $objdb);
        }
    }

    /**
     * Get user's access time to an activity
     *
     * @param $userid
     * @param $courseid
     * @param $cmid
     *
     * @return false|mixed|null
     * @throws \dml_exception
     */
    public static function get_cmlastaccess($userid, $courseid, $cmid) {
        global $DB;
        $lastaccess = $DB->get_field(
            'notificationsagent_cmview',
            'firstaccess', ['courseid' => $courseid, 'userid' => $userid, 'idactivity' => $cmid],
        );

        if (empty($lastaccess)) {
            $query = "SELECT timecreated
                FROM {logstore_standard_log} mlsl
                JOIN {course_modules} mcm ON mcm.id = mlsl.contextinstanceid
                 AND mlsl.courseid = :courseid
                 AND mlsl.contextinstanceid = :cmid
                 AND mlsl.userid = :userid
                JOIN {modules} mm ON mcm.module = mm.id
               WHERE eventname = CONCAT('\\mod_',mm.name,'\\event\\course_module_viewed')
            ORDER BY timecreated
               LIMIT 1";

            $result = $DB->get_record_sql(
                $query, [
                    'courseid' => $courseid,
                    'userid' => $userid,
                    'cmid' => $cmid,
                ]
            );

            if (!$result) {
                $lastaccess = null;
            } else {
                $lastaccess = $result->timecreated;
            }
        }

        return $lastaccess;
    }
}
