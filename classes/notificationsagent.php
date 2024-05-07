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
    /** @var int Default CMID for news type forum */
    public const FORUM_NEWS_CMID = -1;
    /** @var int Default USERID for grading course item */
    public const USERID_COURSEITEM = 0;

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
     *
     * @return int|null The ID of the inserted or updated record, or null if no action was taken.
     */
    public static function set_timer_cache($deletedata, $insertdata) {
        global $DB;
        if (!empty($deletedata)) {
            $todelete = implode(' OR ', $deletedata);
            $DB->delete_records_select('notificationsagent_cache', $todelete);
        }
        if (!empty($insertdata)) {
            $DB->insert_records('notificationsagent_cache', $insertdata);
        }
    }

    /**
     * Set a time trigger for a specific rule, condition, user, and course.
     *
     */
    public static function set_time_trigger($deletedata, $insertdata) {
        global $DB;

        if (!empty($deletedata)) {
            $todelete = implode(' OR ', $deletedata);
            $DB->delete_records_select('notificationsagent_triggers', $todelete);
        }
        if (!empty($insertdata)) {
            $DB->insert_records('notificationsagent_triggers', $insertdata);
        }
    }

    public static function generate_cache_triggers($subplugin, $context) {
        $insertdata = [];
        $deletedata = [];
        $courseid = $context->get_courseid();
        $coursecontext = \context_course::instance($courseid);
        $contextuser = $context->get_userid();  // 0 or userid>0
        $student = $subplugin->rule->createdby;
        // Avoid to set triggers for event or cron triggered by users who don't own the rule.
        if ($contextuser > 0 && $contextuser != $student
            && !has_capability(
                'local/notificationsagent:managecourserule',
                $coursecontext, $student
            )
        ) {
            return;
        }
        // Student or user is from event or cron per user
        if ($contextuser > 0 && !has_capability('local/notificationsagent:managecourserule', $coursecontext, $student)) {
            $userid = $student;
            $userslimit = rule::get_limit_reached_by_users(
                $courseid,
                $subplugin->rule->id,
                $subplugin->rule->timesfired,
                [$userid]
            );
            if (!$userslimit[$userid]
                && !self::is_ruleoff($subplugin->rule->id, $userid)
            ) {
                $context->set_userid($userid);
                $cache = $subplugin->estimate_next_time($context);

                $deletedata[]
                    = "(userid =  $userid  AND courseid= $courseid AND conditionid= {$subplugin->get_id()})";

                if (!empty($cache)) {
                    $insertdata[] = [
                        'userid' => $userid,
                        'courseid' => $courseid,
                        'startdate' => $cache,
                        'pluginname' => $subplugin->get_subtype(),
                        'conditionid' => $subplugin->get_id(),
                        'ruleid' => $subplugin->rule->id,
                    ];
                }
            }
            self::set_timer_cache($deletedata, $insertdata);
            self::set_time_trigger($deletedata, $insertdata);
            return;
        }
        if (!$subplugin->is_generic()) {
            // If $USER has student role, only generate triggers for its
            if (has_capability(
                'local/notificationsagent:managecourserule',
                $coursecontext, $student
            )) {
                $users =  $contextuser ? [(object) ['id' => $contextuser]] : self::get_usersbycourse($coursecontext);
            } else {
                $users = [(object) ['id' => $student]];
            }
            $userslimit = rule::get_limit_reached_by_users(
                $courseid,
                $subplugin->rule->id,
                $subplugin->rule->timesfired,
                array_column($users, 'id')
            );

            foreach ($users as $user) {
                if (
                    !$userslimit[$user->id]
                    && !self::is_ruleoff($subplugin->rule->id, $user->id)
                ) {
                    $context->set_userid($user->id);
                    $cache = $subplugin->estimate_next_time($context);

                    $deletedata[]
                        = "(userid = $user->id AND courseid= $courseid AND conditionid= {$subplugin->get_id()})";
                    if (empty($cache)) {
                        continue;
                    }
                    $insertdata[] = [
                        'userid' => $user->id,
                        'courseid' => $courseid,
                        'startdate' => $cache,
                        'pluginname' => $subplugin->get_subtype(),
                        'conditionid' => $subplugin->get_id(),
                        'ruleid' => $subplugin->rule->id,
                    ];
                }
            }
        }

        if ($subplugin->is_generic()) {
            // If $USER has student role, only generate triggers for its
            if (has_capability(
                'local/notificationsagent:managecourserule',
                $coursecontext, $student
            )) {
                $userid = notificationsagent::GENERIC_USERID;
            } else {
                $userid = $student;
            }

            $userslimit = rule::get_limit_reached_by_users(
                $courseid,
                $subplugin->rule->id,
                $subplugin->rule->timesfired,
                [$userid]
            );

            if (!$userslimit[$userid]
                && !self::is_ruleoff($subplugin->rule->id, $userid)
            ) {
                $context->set_userid($userid);
                $cache = $subplugin->estimate_next_time($context);

                $deletedata[]
                    = "(userid =  $userid  AND courseid= $courseid AND conditionid= {$subplugin->get_id()})";

                if (!empty($cache)) {
                    $insertdata[] = [
                        'userid' => $userid,
                        'courseid' => $courseid,
                        'startdate' => $cache,
                        'pluginname' => $subplugin->get_subtype(),
                        'conditionid' => $subplugin->get_id(),
                        'ruleid' => $subplugin->rule->id,
                    ];
                }
            }
        }
        self::set_timer_cache($deletedata, $insertdata);
        self::set_time_trigger($deletedata, $insertdata);
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

        if ($modtype = $DB->get_record_sql($starttimequery, ['cmid' => $cmid])) {
            $config = get_config('local_notificationsagent', 'startdate');
            $array = explode("\n", $config);

            foreach (preg_grep('/\b' . $modtype->name . '\b/i', $array) as $key => $value) {
                $line = $value;
            }

            if (!empty($line)) {
                $datatables = explode("|", $line);
                $joinarray = [];
                $table = $datatables[1];
                !empty($datatables[2]) ? $joinarray[] = $datatables[2] . " AS timestart " : '';
                !empty($datatables[3]) ? $joinarray[] = $datatables[3] . " AS timeend " : '';

                $joinarray = implode(',', $joinarray);
                $dates = "SELECT " . $joinarray . "
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
                $dates->timeend = get_course($modtype->course)->enddate;
            }

        }

        return $dates;
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

    /**
     * Get triggers to evaluate.
     *
     * @param int $timestarted
     * @param int $tasklastrunttime
     *
     * @return array
     */
    public static function get_triggersbytimeinterval($timestarted, $tasklastrunttime) {
        global $DB;
        $rulesidquery = "
                    SELECT nt.id, nt.ruleid, nt.conditionid, nt.courseid, nt.userid, nt.startdate
                      FROM {notificationsagent_triggers} nt
                      JOIN {notificationsagent_rule} nr ON nr.id = nt.ruleid AND nr.status = 0
                     WHERE startdate
                   BETWEEN :tasklastrunttime AND :timestarted
                       AND nt.courseid != :courseid
                     ";

        return $DB->get_records_sql(
            $rulesidquery,
            [
                'tasklastrunttime' => $tasklastrunttime,
                'timestarted' => $timestarted,
                'courseid' => SITEID,
            ]
        );
    }

    /**
     * Get supported course modules .
     *
     * @param int $cmid     The course module ID
     * @param int $courseid The course ID
     *
     * @return bool Whether the module is supported
     */
    public static function supported_cm($cmid, $courseid) {
        $supported = false;

        $fastmodinfo = get_fast_modinfo($courseid);
        $checkcm = $fastmodinfo->cms[$cmid] ?? null;

        if ($checkcm) {
            $modname = $checkcm->modname;
            $config = get_config('local_notificationsagent', 'startdate');
            $array = explode("\n", $config);

            foreach (preg_grep('/\b' . $modname . '\b/i', $array) as $value) {
                $supported = true;
            }
        }

        return $supported;
    }

    /**
     * Deletes cache records for a given user and a list of condition IDs.
     *
     * @param array $conditionids An array of condition IDs to delete.
     * @param int   $userid       The user ID whose cache records to delete.
     *
     * @return void
     */
    public static function cache_bulk_delete_conditions_by_userid($conditionids, $userid) {
        global $DB;

        list($conditionsql, $params) = $DB->get_in_or_equal($conditionids, SQL_PARAMS_NAMED);
        $params = ['userid' => $userid] + $params;

        $DB->delete_records_select(
            'notificationsagent_cache',
            "userid = :userid AND conditionid {$conditionsql}", $params
        );
    }

    /**
     * Evaluate the result of an expression based on the given operator.
     *
     * @param string $operator The operator to be used in the expression.
     * @param float  $a        The first operand in the expression.
     * @param float  $b        The second operand in the expression.
     *
     * @return  bool           The result of the evaluation.
     */
    public static function evaluate_expression($operator, $a, $b) {
        switch ($operator) {
            case '=':
                return $a == $b;
            case '!=':
                return $a != $b;
            case '>':
                return $a > $b;
            case '<':
                return $a < $b;
            case '>=':
                return $a >= $b;
            case '<=':
                return $a <= $b;
            default:
                return false;
        }
    }
}
