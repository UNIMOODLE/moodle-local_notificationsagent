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
// Funded by the European Union - Next GenerationEU".
//
// Produced by the UNIMOODLE University Group: Universities of
// Valladolid, Complutense de Madrid, UPV/EHU, León, Salamanca,
// Illes Balears, Valencia, Rey Juan Carlos, La Laguna, Zaragoza, Málaga,
// Córdoba, Extremadura, Vigo, Las Palmas de Gran Canaria y Burgos.

/**
 * Version details
 *
 * @package    notificationscondition_sessionstart
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace notificationscondition_sessionstart;

use local_notificationsagent\evaluationcontext;
use local_notificationsagent\form\editrule_form;
use local_notificationsagent\notificationconditionplugin;
use notificationscondition_sessionstart\persistent\coursefirstaccess;

/**
 * This class handles the condition of session start.
 */
class sessionstart extends notificationconditionplugin {
    /**
     * Subplugin name
     */
    public const NAME = 'sessionstart';

    /**
     * Subplugin title
     *
     * @return \lang_string|string
     */
    public function get_title() {
        return get_string('conditiontext', 'notificationscondition_sessionstart');
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
        global $DB;
        $courseid = $context->get_courseid();
        $userid = $context->get_userid();
        $pluginname = $this->get_subtype();
        $conditionid = $this->get_id();
        $timeaccess = $context->get_timeaccess();
        $params = json_decode($context->get_params());
        $meetcondition = false;

        $timestart = $DB->get_field(
            'notificationsagent_cache',
            'startdate',
            ['conditionid' => $conditionid, 'courseid' => $courseid, 'userid' => $userid, 'pluginname' => $pluginname],
        );

        if (empty($timestart)) {
            // Check own plugin table.
            $firstacces = self::get_first_course_access($userid, $courseid);

            if (empty($firstacces)) {
                return false;
            }
            $timestart = $firstacces + $params->{self::UI_TIME};
        }

        ($timeaccess >= $timestart) ? $meetcondition = true : $meetcondition = false;

        return $meetcondition;
    }

    /**
     * Get the UI elements for the subplugin.
     *
     * @param \MoodleQuickForm $mform The Moodle quick form object.
     * @param int $courseid The ID of the course.
     * @param string $type The type of the notification plugin.
     */
    public function get_ui($mform, $courseid, $type) {
        $this->get_ui_title($mform, $type);
        $this->get_ui_select_date($mform, $type);
    }

    /**
     * Estimate the next time the condition will be true.
     *
     * @param evaluationcontext $context Context for the condition evaluation.
     *
     * @return mixed Estimated time as a Unix timestamp or null if cannot be estimated.
     */
    public function estimate_next_time(evaluationcontext $context) {
        // Condition.
        $courseid = $context->get_courseid();
        $userid = $context->get_userid();
        $params = json_decode($context->get_params(), false);
        $firstacces = self::get_first_course_access($userid, $courseid);
        // The student never has view the course.
        if (empty($firstacces)) {
            // Return null as we cannot provide a estimated date.
            return null;
        }
        if ($context->is_complementary() && $context->get_timeaccess() >= $firstacces + $params->{self::UI_TIME}) {
            return null;
        }

        return max(time(), $firstacces + $params->{self::UI_TIME});
    }

    /**
     * Checks whether the user has the capability to use the condition within a given context.
     *
     * @param \context $context The context to check the capability in.
     *
     * @return bool True if the user has the capability, false otherwise.
     */
    public function check_capability($context) {
        return has_capability('local/notificationsagent:sessionstart', $context);
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
     * Get if it is generic.
     *
     * @return boolean
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
     * Set the first course access for a user.
     *
     * @param int $userid
     * @param int $courseid
     * @param int $timeaccess
     *
     */
    public static function set_first_course_access($userid, $courseid, $timeaccess) {

        $crsefirstaccess = coursefirstaccess::get_record(['courseid' => $courseid, 'userid' => $userid]);

        if (empty($crsefirstaccess)) {
            $crsefirstaccess = new coursefirstaccess();
            $crsefirstaccess->set('userid', $userid);
            $crsefirstaccess->set('courseid', $courseid);
        }
        $crsefirstaccess->set('firstaccess', $timeaccess);
        $crsefirstaccess->save();
    }

    /**
     * Get the first access time for a specific user and course.
     * It checks the custom table first and if not found, it queries the logstore_standard_log table.
     * It stores the first access time in the custom table for future fast access.
     * @param int $userid user id
     * @param int $courseid course id
     *
     * @return mixed|null  return firstacces to a course or null if not found.
     */
    public static function get_first_course_access(int $userid, int $courseid) {
        global $DB;
        $firstaccess = null;
        $crsefirstaccess = coursefirstaccess::get_record(['courseid' => $courseid, 'userid' => $userid]);
        if (!empty($crsefirstaccess)) {
            $firstaccess = $crsefirstaccess->get('firstaccess');
        } else {
            // If the first access is not set, we check the logstore_standard_log table.
            $query = 'SELECT timecreated
                               FROM {logstore_standard_log}
                            WHERE courseid = :courseid
                                 AND userid = :userid
                                AND eventname = :eventname
                      ORDER BY timecreated
                            LIMIT 1';

            $result = $DB->get_record_sql(
                $query,
                [
                    'courseid' => $courseid,
                    'userid' => $userid,
                    'eventname' => '\\core\\event\\course_viewed',
                ]
            );

            if (!$result) {
                return null;
            }
            $firstaccess = $result->timecreated;
            // We record this time in custom table to avoid querying the log_standard_log for a course firstaccess.
            sessionstart::set_first_course_access($userid, $courseid, $firstaccess);
        }

        return $firstaccess;
    }

    /**
     * Update any necessary ids and json parameters in the database.
     * It is called near the completion of course restoration.
     *
     * @param string $restoreid Restore identifier
     * @param integer $courseid Course identifier
     * @param \base_logger $logger Logger if any warnings
     *
     * @return bool False if restore is not required
     */
    public function update_after_restore($restoreid, $courseid, \base_logger $logger) {
        return false;
    }
}
