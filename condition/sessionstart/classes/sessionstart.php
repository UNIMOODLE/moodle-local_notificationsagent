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

namespace notificationscondition_sessionstart;

use local_notificationsagent\evaluationcontext;
use local_notificationsagent\notificationconditionplugin;

class sessionstart extends notificationconditionplugin {

    /** @var UI ELEMENTS */
    public const NAME = 'sessionstart';

    public function get_title() {
        return get_string('conditiontext', 'notificationscondition_sessionstart');
    }

    public function get_elements() {
        return ['[TTTT]'];
    }

    public function get_subtype() {
        return get_string('subtype', 'notificationscondition_sessionstart');
    }

    /** Evaluates this condition using the context variables or the system's state and the complementary flag.
     *
     * @param evaluationcontext $context  |null collection of variables to evaluate the condition.
     *                                    If null the system's state is used.
     *
     * @return bool true if the condition is true, false otherwise.
     */
    public function evaluate(evaluationcontext $context): bool {
        // Miramos mdl_notificationsagent_cache si hay registro, comprobar.
        // si no hay registro comprobar en la tabla del plugin.
        global $DB;
        $courseid = $context->get_courseid();
        $userid = $context->get_userid();
        $pluginname = $this->get_subtype();
        $conditionid = $this->get_id();
        $timeaccess = $context->get_timeaccess();
        $params = json_decode($context->get_params());
        $meetcondition = false;

        // Timestart es el tiempo de primer acceso más time.
        $timestart = $DB->get_field(
            'notificationsagent_cache',
            'timestart', ['conditionid' => $conditionid, 'courseid' => $courseid, 'userid' => $userid, 'pluginname' => $pluginname],
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

    public function get_ui($mform, $id, $courseid, $type) {
        $this->get_ui_title($mform, $id, $type);
        $this->get_ui_select_date($mform, $id, $type);
    }

    /** Estimate next time when this condition will be true. */
    public function estimate_next_time(evaluationcontext $context) {
        // No devolvemos fecha en los subplugins que responden a un evento core de moodle.
        return null;
    }

    public function check_capability($context) {
        return has_capability('local/notificationsagent:sessionstart', $context);
    }

    /**
     * @param array $params
     *
     * @return mixed
     */
    protected function convert_parameters($id, $params) {
        $params = (array) $params;
        $timevalues = [
            'days' => $params[$this->get_name_ui($id, self::UI_DAYS)] ?? 0,
            'hours' => $params[$this->get_name_ui($id, self::UI_HOURS)] ?? 0,
            'minutes' => $params[$this->get_name_ui($id, self::UI_MINUTES)] ?? 0,
            'seconds' => $params[$this->get_name_ui($id, self::UI_SECONDS)] ?? 0,
        ];
        $timeinseconds = ($timevalues['days'] * 24 * 60 * 60) + ($timevalues['hours'] * 60 * 60)
            + ($timevalues['minutes'] * 60) + $timevalues['seconds'];
        $this->set_parameters(json_encode([self::UI_TIME => $timeinseconds]));
        return $this->get_parameters();
    }

    public function process_markups(&$content, $courseid, $options = null) {
        $jsonparams = json_decode($this->get_parameters());

        $paramstoteplace = [to_human_format($jsonparams->{self::UI_TIME}, true)];

        $humanvalue = str_replace($this->get_elements(), $paramstoteplace, $this->get_title());

        array_push($content, $humanvalue);
    }

    public function is_generic() {
        return false;
    }

    public function set_default($form, $id) {
        $params = $this->set_default_select_date($id);
        $form->set_data($params);
    }

    /**
     * @param $userid
     * @param $courseid
     * @param $timeaccess
     *
     * @return void
     * @throws \dml_exception
     */
    public static function set_first_course_access($userid, $courseid, $timeaccess) {
        global $DB;
        $exists = $DB->record_exists('notificationsagent_crseview', ['userid' => $userid, 'courseid' => $courseid]);
        if (!$exists) {
            $objdb = new \stdClass();
            $objdb->userid = $userid;
            $objdb->courseid = $courseid;
            $objdb->firstaccess = $timeaccess;
            $DB->insert_record('notificationsagent_crseview', $objdb);
        }
    }

    /**
     * @param $userid
     * @param $courseid
     *
     * @return false|mixed
     * @throws dml_exception
     */
    public static function get_first_course_access($userid, $courseid) {
        global $DB;
        $firstacces = $DB->get_field(
            'notificationsagent_crseview', 'firstaccess',
            ['userid' => $userid, 'courseid' => $courseid]
        );

        if (empty($firstacces)) {
            $query = 'SELECT timecreated
                    FROM {logstore_standard_log}
                   WHERE courseid = :courseid
                    AND userid = :userid
                    AND eventname = :eventname
               ORDER BY timecreated
                  LIMIT 1';

            $result = $DB->get_record_sql(
                $query, [
                    'courseid' => $courseid,
                    'userid' => $userid,
                    'eventname' => '\\core\\event\\course_viewed',
                ]
            );

            if (!$result) {
                $firstacces = null;
            } else {
                $firstacces = $result->timecreated;
            }
        }

        return $firstacces;
    }

}
