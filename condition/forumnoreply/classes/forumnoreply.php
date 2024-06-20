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
 * @package    notificationscondition_forumnoreply
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace notificationscondition_forumnoreply;

use local_notificationsagent\evaluationcontext;
use local_notificationsagent\form\editrule_form;
use local_notificationsagent\notificationconditionplugin;
use local_notificationsagent\rule;

/**
 * Forumnoreply subplugin class
 */
class forumnoreply extends notificationconditionplugin {
    /**
     * Subplugin name
     */
    public const NAME = 'forumnoreply';
    /**
     * Forum Announcements
     */
    public const UNUSED_FORUMS = ['Announcements'];

    /**
     * Subplugin title
     *
     * @return \lang_string|string
     */
    public function get_title() {
        return get_string('conditiontext', 'notificationscondition_forumnoreply');
    }

    /**
     *  Subplugins elements
     *
     * @return string[]
     */
    public function get_elements() {
        return ['[FFFF]', '[TTTT]'];
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
        $cmid = json_decode($context->get_params())->{self::UI_ACTIVITY};
        $timenowandtime = json_decode($context->get_params())->{self::UI_TIME};

        $meetcondition = false;
        $conditionid = $this->get_id();

        $timeaccess = $context->get_timeaccess();

        $time = $DB->get_field(
            'notificationsagent_cache',
            'startdate',
            ['conditionid' => $conditionid, 'courseid' => $courseid, 'userid' => $userid, 'pluginname' => $pluginname],
        );

        if (empty($time)) {
            return !empty(
                self::get_unanswered_threads(
                    $cmid,
                    $courseid,
                    $timeaccess,
                    $timenowandtime,
                    $userid
                )
            );
        }

        ($timeaccess >= $time) ? $meetcondition = true : $meetcondition = false;
        return $meetcondition;
    }

    /**
     * Estimate next time when this condition will be true.
     *
     * @param evaluationcontext $context
     *
     * @return null
     */
    public function estimate_next_time(evaluationcontext $context) {
        $estimate = null;

        $evaluate = $this->evaluate($context);

        // Condition.
        if ($evaluate && !$this->get_iscomplementary()) {
            return time();
        }

        // Exception.
        if ($evaluate && $this->get_iscomplementary()) {
            return time();
        }

        return $estimate;
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

        // ListForums.
        $listforums = get_coursemodules_in_course('forum', $courseid);

        $forums = [];

        foreach ($listforums as $forum) {
            $forumname = $forum->name;
            if (!in_array($forumname, self::UNUSED_FORUMS)) {
                $forums[$forum->id] = $forumname;
            }
        }

        // Only is template.
        if ($this->rule->template == rule::TEMPLATE_TYPE) {
            $forums['0'] = 'FFFF';
        }

        $element = $mform->createElement(
            'select',
            $this->get_name_ui(self::UI_ACTIVITY),
            get_string(
                'editrule_condition_activity',
                'notificationscondition_activitysinceend',
                ['typeelement' => '[FFFF]']
            ),
            $forums
        );

        $this->get_ui_select_date($mform, $type);
        $mform->insertElementBefore($element, 'new' . $type . '_group');
        $mform->addRule(
            $this->get_name_ui(self::UI_ACTIVITY),
            get_string('editrule_required_error', 'local_notificationsagent'),
            'required'
        );
    }

    /**
     * Check capability of subplugin use
     *
     * @param \context $context
     *
     * @return bool
     */
    public function check_capability($context) {
        return has_capability('local/notificationsagent:forumnoreply', $context);
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

        // Check if activity is found, if is not, return [FFFF].
        $activityname = '[FFFF]';
        $cmid = $jsonparams->{self::UI_ACTIVITY};
        $fastmodinfo = get_fast_modinfo($courseid);
        $activityname = isset($fastmodinfo->cms[$cmid]) ? $fastmodinfo->cms[$cmid]->name : $activityname;

        $paramstoteplace =
                [$activityname, \local_notificationsagent\helper\helper::to_human_format($jsonparams->{self::UI_TIME}, true)];

        $humanvalue = str_replace($this->get_elements(), $paramstoteplace, $this->get_title());

        $content[] = $humanvalue;
    }

    /**
     * Is subplugin generic
     *
     * @return false
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
     * Get unanswered threads in a forum, or by a specific user
     *
     * @param int $cmid The course module id.
     * @param int $courseid The course id.
     * @param int $timenow The time now.
     * @param int $timenowandtime The subplugin time.
     * @param int|null $userid The user id.
     *
     * @return object Unanswered threads.
     */
    public static function get_unanswered_threads($cmid, $courseid, $timenow, $timenowandtime, $userid = null) {
        global $DB;

        $modinfo = get_fast_modinfo($courseid);
        $forumid = $modinfo->get_cm($cmid)->instance;

        $whereuser = '';
        $params = [];
        if ($userid) {
            [$useridsql, $params] = $DB->get_in_or_equal($userid, SQL_PARAMS_NAMED);
            $whereuser = " AND fd.userid {$useridsql}";
        }

        $params = [
                        'forum' => $forumid,
                        'course' => $courseid,
                        'timestart' => 0,
                        'timeend' => 0,
                        'timenow' => $timenow,
                        'timenow2' => $timenow,
                        'timenowandtime' => $timenowandtime,
                ] + $params;

        $sql = "SELECT DISTINCT fd.id, fd.timemodified as timemodified, fd.userid
                    FROM {forum_discussions} fd
                    JOIN {forum_posts} fp ON fp.discussion=fd.id AND fp.parent = 0
               LEFT JOIN {forum_posts} fp2 ON fp.id = fp2.parent
                    WHERE fd.forum = :forum
                    AND fd.course = :course
                    AND fd.timestart >= :timestart
                    AND (fd.timeend = :timeend OR fd.timeend > :timenow)
                    AND :timenow2 >= fd.timemodified + " . $DB->sql_cast_char2int($timenowandtime) . "
                    AND fp2.id IS NULL
                    {$whereuser}
                ";

        return $DB->get_records_sql($sql, $params);
    }
}
