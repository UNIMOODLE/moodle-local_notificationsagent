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
 * @package    local_notificationsagent
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_notificationsagent;

use stdClass;

/**
 * Notifications agent main class
 */
class notificationsagent {

    /** @var int All users are affected by the action/condition */
    public const GENERIC_USERID = -1;
    /** @var string Condition Availability type */
    private const CONDITION_AVAILABILITY = 'ac';

    /**
     * Get the current conditions by plugin and course id
     *
     * @param string $pluginname Plugin name
     * @param int    $courseid   Course id
     *
     * @return array $data Plugin and course conditions
     */
    public static function get_conditions_by_course($pluginname, $courseid) {
        global $DB;

        $data = [];

        $conditionssql = 'SELECT DISTINCT nc.id, nr.id AS ruleid, nc.parameters, nc.pluginname
                                     FROM {notificationsagent_rule} nr
                                     JOIN {notificationsagent_condition} nc ON nr.id = nc.ruleid
                                      AND nr.status = 0 AND nr.template = 1
                                     JOIN {notificationsagent_context} nctx ON nctx.ruleid = nr.id
                                    WHERE nc.pluginname = :pluginname
                                      AND (nctx.contextid = :categorycontextid
                                       OR (nctx.contextid = :coursecontextid
                                      AND nctx.objectid != :siteid))
        ';
        $conditions = $DB->get_records_sql(
            $conditionssql, [
                'pluginname' => $pluginname,
                'categorycontextid' => CONTEXT_COURSECAT,
                'coursecontextid' => CONTEXT_COURSE,
                'siteid' => SITEID,
            ]
        );

        foreach ($conditions as $condition) {
            $coursesql = '';
            $categorysql = '';

            $coursecategories = self::get_course_category_context_byruleid($condition->ruleid);
            $uniqueidsql = $DB->sql_concat('nr.id', "'_'", 'nc.id', "'_'", 'nctx.objectid');
            $coursesql = "SELECT $uniqueidsql AS uniqueid, nc.id, nr.id AS ruleid, nc.parameters,
                                 nr.timesfired AS ruletimesfired,
                                 nc.pluginname, nctx.objectid AS courseid
                            FROM {notificationsagent_rule} nr
                            JOIN {notificationsagent_context} nctx ON nr.id = nctx.ruleid
                             AND nr.status = 0 AND nr.template = 1
                            JOIN {notificationsagent_condition} nc ON nr.id = nc.ruleid
                           WHERE nc.id = :courseconditionid
                             AND nctx.contextid = :coursecontextid
                             AND nctx.objectid = :coursecontext
            ";
            $params = [
                'courseconditionid' => $condition->id,
                'coursecontextid' => CONTEXT_COURSE,
                'coursecontext' => $courseid,
            ];

            if (in_array($courseid, $coursecategories)) {
                $uniqueidsql = $DB->sql_concat('nr.id', "'_'", 'nc.id', "'_'", 'data.courseid');
                $categorysql = "UNION
                               SELECT $uniqueidsql AS uniqueid, nc.id, nr.id AS ruleid, nc.parameters,
                                      nr.timesfired AS ruletimesfired,
                                      nc.pluginname, data.courseid AS courseid
                                 FROM {notificationsagent_rule} nr
                                 JOIN {notificationsagent_condition} nc ON nr.id = nc.ruleid
                           CROSS JOIN (
                               SELECT c.id AS courseid
                                 FROM {course} c
                                WHERE c.id = :categorycontext
                            ) AS data
                                WHERE nc.id = :categoryconditionid";
                $params['courseconditionid'] = $condition->id;
                $params['coursecontextid'] = CONTEXT_COURSE;
                $params['coursecontext'] = $courseid;
                $params['categorycontext'] = $courseid;
                $params['categoryconditionid'] = $condition->id;
            }
            $result = $DB->get_records_sql($coursesql . $categorysql, $params);

            $data = array_merge($data, $result);
        }

        return $data;
    }

    /**
     * Get the current conditions by plugin, course and cmid
     *
     * @param string $pluginname Plugin name
     * @param int    $courseid   Course id
     * @param int    $cmid       Course module id
     *
     * @return array $data Plugin, course and cmid conditions
     */
    public static function get_conditions_by_cm($pluginname, $courseid, $cmid) {
        global $DB;

        $conditionssql = 'SELECT nc.id, nc.ruleid, nr.timesfired AS ruletimesfired, nc.parameters, nc.pluginname, nc.cmid
                            FROM {notificationsagent_rule} nr
                            JOIN {notificationsagent_context} nctx ON nr.id = nctx.ruleid
                             AND nr.status = 0 AND nr.template = 1 AND nctx.contextid = :coursecontextid
                            JOIN {notificationsagent_condition} nc ON nr.id = nc.ruleid
                           WHERE pluginname = :pluginname
                             AND nctx.objectid = :courseid
                             AND nc.cmid = :cmid
        ';
        $conditions = $DB->get_records_sql(
            $conditionssql,
            [
                'coursecontextid' => CONTEXT_COURSE,
                'pluginname' => $pluginname,
                'courseid' => $courseid,
                'cmid' => $cmid,
            ]
        );

        return $conditions;
    }

    /**
     * Get the current plugin conditions
     *
     * @param string $pluginname Plugin name
     *
     * @return array $data Plugin conditions
     */
    public static function get_conditions_by_plugin($pluginname) {
        global $DB;

        $data = [];

        $conditionssql = 'SELECT DISTINCT nc.id, nr.id AS ruleid, nc.parameters, nc.pluginname
                                     FROM {notificationsagent_rule} nr
                                     JOIN {notificationsagent_condition} nc ON nr.id = nc.ruleid
                                      AND nr.status = 0 AND nr.template = 1
                                     JOIN {notificationsagent_context} nctx ON nctx.ruleid = nr.id
                                    WHERE nc.pluginname = :pluginname
                                      AND (nctx.contextid = :categorycontextid
                                       OR (nctx.contextid = :coursecontextid
                                      AND nctx.objectid != :siteid))
        ';
        $conditions = $DB->get_records_sql(
            $conditionssql, [
                'pluginname' => $pluginname,
                'categorycontextid' => CONTEXT_COURSECAT,
                'coursecontextid' => CONTEXT_COURSE,
                'siteid' => SITEID,
            ]
        );

        foreach ($conditions as $condition) {
            $coursesql = '';
            $categorysql = '';
            $coursecategories = self::get_course_category_context_byruleid($condition->ruleid);
            $uniqueidsql = $DB->sql_concat('nr.id', "'_'", 'nc.id', "'_'", 'nctx.objectid');
            $coursesql = "SELECT $uniqueidsql AS uniqueid, nc.id, nr.id AS ruleid,
                                 nr.timesfired AS ruletimesfired,
                                 nc.parameters, nc.pluginname, nctx.objectid AS courseid
                            FROM {notificationsagent_rule} nr
                            JOIN {notificationsagent_context} nctx ON nr.id = nctx.ruleid
                             AND nr.status = 0 AND nr.template = 1
                            JOIN {notificationsagent_condition} nc ON nr.id = nc.ruleid
                           WHERE nc.id = :courseconditionid
                             AND (nctx.contextid = :coursecontextid
                             AND nctx.objectid != :siteid)
            ";
            $params = [
                'courseconditionid' => $condition->id,
                'coursecontextid' => CONTEXT_COURSE,
                'siteid' => SITEID,
            ];

            if (!empty($coursecategories)) {
                [$incourses, $params] = $DB->get_in_or_equal($coursecategories, SQL_PARAMS_NAMED);
                $uniqueidsql = $DB->sql_concat('nr.id', "'_'", 'nc.id', "'_'", 'data.courseid');
                $categorysql = "UNION
                               SELECT $uniqueidsql AS uniqueid, nc.id, nr.id AS ruleid,
                                      nr.timesfired AS ruletimesfired,
                                      nc.parameters, nc.pluginname, data.courseid
                                 FROM {notificationsagent_rule} nr
                                 JOIN {notificationsagent_condition} nc ON nr.id = nc.ruleid
                           CROSS JOIN (
                               SELECT c.id AS courseid
                                 FROM {course} c
                                WHERE c.id $incourses
                            ) AS data
                                WHERE nc.id = :categoryconditionid";
                $params['courseconditionid'] = $condition->id;
                $params['coursecontextid'] = CONTEXT_COURSE;
                $params['siteid'] = SITEID;
                $params['categoryconditionid'] = $condition->id;
            }
            $result = $DB->get_records_sql($coursesql . $categorysql, $params);

            $data = array_merge($data, $result);
        }

        return $data;
    }

    /**
     * Get conditions of type availability condition
     *
     * @return array $data Availability conditions
     */
    public static function get_availability_conditions() {
        global $DB;

        $data = [];

        $conditionssql = 'SELECT DISTINCT nc.id, nr.id AS ruleid, nc.pluginname
                            FROM {notificationsagent_rule} nr
                            JOIN {notificationsagent_context} nctx ON nctx.ruleid = nr.id
                             AND nr.status = 0 AND nr.template = 1
                            JOIN {notificationsagent_condition} nc ON nr.id = nc.ruleid
                           WHERE (nctx.contextid = :categorycontextid
                              OR (nctx.contextid = :coursecontextid
                             AND nctx.objectid != :siteid))
                             AND nc.pluginname = :plugin
                             AND NOT EXISTS (
                                SELECT ncaux.id, nraux.id AS ruleid
                                FROM {notificationsagent_rule} nraux
                                JOIN {notificationsagent_condition} ncaux ON nraux.id = ncaux.ruleid
                                WHERE nraux.id = nr.id AND ncaux.pluginname != :pluginaux
                            )
        ';
        $conditions = $DB->get_records_sql(
            $conditionssql, [
                'categorycontextid' => CONTEXT_COURSECAT,
                'coursecontextid' => CONTEXT_COURSE,
                'siteid' => SITEID,
                'plugin' => self::CONDITION_AVAILABILITY,
                'pluginaux' => self::CONDITION_AVAILABILITY,
            ]
        );

        foreach ($conditions as $condition) {
            $coursesql = '';
            $categorysql = '';
            $coursecategories = self::get_course_category_context_byruleid($condition->ruleid);
            $uniqueidsql = $DB->sql_concat('nr.id', "'_'", 'nc.id', "'_'", 'nctx.objectid');
            $coursesql = "SELECT $uniqueidsql AS uniqueid, nc.id, nr.id AS ruleid,
                                 nr.timesfired AS ruletimesfired, nctx.objectid AS courseid
                            FROM {notificationsagent_rule} nr
                            JOIN {notificationsagent_context} nctx ON nr.id = nctx.ruleid
                             AND nr.status = 0 AND nr.template = 1
                            JOIN {notificationsagent_condition} nc ON nr.id = nc.ruleid
                           WHERE nc.id = :courseconditionid
                             AND (nctx.contextid = :coursecontextid
                             AND nctx.objectid != :siteid)
            ";
            $params = [
                'courseconditionid' => $condition->id,
                'coursecontextid' => CONTEXT_COURSE,
                'siteid' => SITEID,
            ];

            if (!empty($coursecategories)) {
                [$incourses, $params] = $DB->get_in_or_equal($coursecategories, SQL_PARAMS_NAMED);
                $uniqueidsql = $DB->sql_concat('nr.id', "'_'", 'nc.id', "'_'", 'data.courseid');
                $categorysql = "UNION
                               SELECT $uniqueidsql AS uniqueid, nc.id, nr.id AS ruleid,
                                      nr.timesfired AS ruletimesfired, data.courseid
                                 FROM {notificationsagent_rule} nr
                                 JOIN {notificationsagent_condition} nc ON nr.id = nc.ruleid
                           CROSS JOIN (
                               SELECT c.id AS courseid
                                 FROM {course} c
                                WHERE c.id $incourses
                            ) AS data
                                WHERE nc.id = :categoryconditionid";
                $params['courseconditionid'] = $condition->id;
                $params['coursecontextid'] = CONTEXT_COURSE;
                $params['siteid'] = SITEID;
                $params['categoryconditionid'] = $condition->id;
            }
            $result = $DB->get_records_sql($coursesql . $categorysql, $params);

            $data = array_merge($data, $result);
        }

        return $data;
    }

    /**
     * Get the courses associated with the category context given a ruleid
     *
     * @param int $id Rule id
     *
     * @return array $data Courses where the rule is applied
     */
    public static function get_course_category_context_byruleid($id) {
        global $DB;

        $data = [];

        $sqlcategoryctx = 'SELECT nctx.objectid AS id
                             FROM {notificationsagent_rule} nr
                             JOIN {notificationsagent_context} nctx ON nr.id = nctx.ruleid
                            WHERE nr.id = :ruleid
                              AND nctx.contextid = :categorycontextid
        ';
        $categories = $DB->get_records_sql($sqlcategoryctx, [
            'ruleid' => $id,
            'categorycontextid' => CONTEXT_COURSECAT,
        ]);

        if (!empty($categories)) {
            foreach ($categories as $category) {
                $coursecat = \core_course_category::get($category->id);
                $coursecategories = $coursecat->get_courses(['recursive' => 1]);
                $data = array_merge($data, array_column($coursecategories, 'id'));
            }
        }

        return $data;
    }

    /**
     * Retrieves users by course from the database.
     *
     * @param mixed $context
     *
     * @return array
     */
    public static function get_usersbycourse($context): array {
        return get_role_users(
            5, $context, false, 'u.*',
            '', true, '', '', '', 'u.suspended = 0', ''
        );
    }

    /**
     * Set timer cache in the notifications agent cache table.
     *
     * @param int    $userid             The user ID.
     * @param int    $courseid           The course ID.
     * @param int    $timer              The timer value.
     * @param string $pluginname         The name of the plugin.
     * @param int    $conditionid        The condition ID.
     * @param bool   $updatecacheifexist Whether to update the cache if it already exists.
     *
     * @return int|null The ID of the inserted or updated record, or null if no action was taken.
     */
    public static function set_timer_cache($userid, $courseid, $timer, $pluginname, $conditionid, $updatecacheifexist) {
        global $DB;
        $exists = $DB->get_field(
            'notificationsagent_cache', 'id',
            [
                'userid' => $userid,
                'courseid' => $courseid,
                'pluginname' => $pluginname,
                'conditionid' => $conditionid,
            ]
        );
        $objdb = new \stdClass();
        $objdb->userid = $userid;
        $objdb->courseid = $courseid;
        $objdb->timestart = $timer;
        $objdb->pluginname = $pluginname;
        $objdb->conditionid = $conditionid;
        // Insert.
        if (!$exists) {
            return $DB->insert_record('notificationsagent_cache', $objdb);
        }
        // Update.
        if ($updatecacheifexist) {
            $objdb->id = $exists;
            return $DB->update_record('notificationsagent_cache', $objdb);
        }
        return null;
    }

    /**
     * Set a time trigger for a specific rule, condition, user, and course.
     *
     * @param int $ruleid      Rule id
     * @param int $conditionid Condition id
     * @param int $userid      User id
     * @param int $courseid    Course id
     * @param int $timer       Timer
     * @param int $ruleoff     Is ruleoff
     */
    public static function set_time_trigger($ruleid, $conditionid, $userid, $courseid, $timer, $ruleoff = null) {
        global $DB;
        $exists = $DB->get_record(
            'notificationsagent_triggers',
            [
                'ruleid' => $ruleid,
                'userid' => $userid,
                'courseid' => $courseid,
            ],
            'id, startdate'
        );

        $objdb = new \stdClass();
        $objdb->userid = $userid;
        $objdb->courseid = $courseid;
        $objdb->ruleid = $ruleid;
        $objdb->conditionid = $conditionid;
        $objdb->ruleoff = $ruleoff;
        // ...TODO Add retroactive rules as setting.
        $objdb->startdate = $timer > time() ? $timer : time() + 120;
        if (!$exists) {
            $DB->insert_record('notificationsagent_triggers', $objdb);
        } else {
            $objdb->id = $exists->id;
            $DB->update_record('notificationsagent_triggers', $objdb);
        }
    }

    /**
     * Delete all cache records by rule ID
     *
     * @param int $id rule ID
     *
     * @return void
     */
    public static function delete_cache_by_ruleid($id) {
        global $DB;

        $conditions = $DB->get_records('notificationsagent_condition', ['ruleid' => $id], 'id');
        if (!empty($conditions)) {
            $conditionsid = array_keys($conditions);
            list($insql, $inparams) = $DB->get_in_or_equal($conditionsid);
            $DB->delete_records_select('notificationsagent_cache', "conditionid $insql", $inparams);
        }
    }

    /**
     * Delete all trigger records by rule ID
     *
     * @param int $id rule ID
     *
     * @return void
     */
    public static function delete_triggers_by_ruleid($id) {
        global $DB;

        $DB->delete_records('notificationsagent_triggers', ['ruleid' => $id]);
    }

    /**
     * Get course module dates by condition.
     *
     * @param int $cmid The course module ID
     *
     * @return stdClass The dates of the course module
     */
    public static function notificationsagent_condition_get_cm_dates($cmid) {
        // Table :course modules.
        global $DB;

        $dates = new stdClass();
        $dates->timestart = null;
        $dates->timeend = null;

        $line = '';
        $starttimequery = "
                    SELECT mcm.id, instance, module, mm.name, mcm.course
                      FROM {course_modules} mcm
                      JOIN {modules} mm ON mm.id = mcm.module
                     WHERE mcm.id = :cmid";

        if($modtype = $DB->get_record_sql($starttimequery, ['cmid' => $cmid])){
            $config = get_config('local_notificationsagent', 'startdate');
            $array = explode("\n", $config);
    
            foreach (preg_grep('/\b' . $modtype->name . '\b/i', $array) as $key => $value) {
                $line = $value;
            }
    
            if (!empty($line)) {
                $datatables = explode("|", $line);
                list($pluginname, $table, $timestart, $timeend) = $datatables;
                $dates = "SELECT " . $timestart . " AS timestart, " . $timeend . "  as timeend
                    FROM {" . $table . "}
                    WHERE id = :instance";
    
                $dates = $DB->get_record_sql(
                    $dates,
                    [
                        'instance' => $modtype->instance,
                    ]
                );
            }

            if (empty($dates->timestart)) {
                $dates->timestart = get_course($modtype->course)->startdate;
            }
            
            if (empty($dates->timeend)) {
                $dates->timeend = null;
            }

        }

        return $dates;
    }

    /**
     * Check if the rule has been launched the maximum number of times given a user and course.
     *
     * @param integer $ruleid         Rule id
     * @param integer $ruletimesfired Number of times a rule can be launched
     * @param integer $courseid       Course id
     * @param integer $userid         User id
     *
     * @return bool $waslaunched Has the rule been launched the max. number of times by user?
     */
    public static function was_launched_indicated_times($ruleid, $ruletimesfired, $courseid, $userid) {
        global $DB;

        $waslaunched = false;

        $timesfired = $DB->get_field('notificationsagent_launched', 'timesfired', [
            'ruleid' => $ruleid, 'courseid' => $courseid,
            'userid' => $userid,
        ]);

        if (isset($timesfired) && $timesfired >= $ruletimesfired) {
            $waslaunched = true;
        }

        return $waslaunched;
    }

    /**
     *  Is the rule off?
     *
     * @param int $ruleid Rule id
     * @param int $userid User id
     *
     * @return bool
     */
    public static function is_ruleoff($ruleid, $userid) {
        global $DB;
        $ruleoff = $DB->get_field(
            'notificationsagent_triggers', 'MAX(ruleoff)',
            [
                'ruleid' => $ruleid,
                'userid' => $userid,
            ]
        );

        return is_numeric($ruleoff);
    }
}
