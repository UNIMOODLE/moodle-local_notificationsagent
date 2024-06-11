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
    public const USERID_COURSEITEM = -1;

    /**
     * Get the current conditions by plugin and course id
     *
     * @param string $pluginname Plugin name
     * @param int $courseid Course id
     *
     * @return array $data Plugin and course conditions
     */
    public static function get_conditions_by_course($pluginname, $courseid) {
        global $DB;

        $coursesbycategory = [];
        $data = [];

        $conditionssql = 'SELECT nc.id, nr.id AS ruleid, nc.parameters, nc.pluginname, nctx.contextid, nctx.objectid
                            FROM {notificationsagent_condition} nc
                            JOIN {notificationsagent_rule} nr ON nc.ruleid = nr.id
                             AND nr.status = 0 AND nr.template = 1 AND nr.deleted = 0
                            JOIN {notificationsagent_context} nctx ON nctx.ruleid = nr.id
                             AND ((nctx.contextid = :categorycontextid)
                              OR (nctx.contextid = :coursecontextid AND nctx.objectid = :courseid))
                            JOIN {course} c ON nctx.objectid = c.id
                           WHERE nc.pluginname = :pluginname
        ';
        $conditions = $DB->get_recordset_sql(
            $conditionssql,
            [
                        'pluginname' => $pluginname,
                        'categorycontextid' => CONTEXT_COURSECAT,
                        'coursecontextid' => CONTEXT_COURSE,
                        'courseid' => $courseid,
                ]
        );

        if ($conditions->valid()) {
            foreach ($conditions as $condition) {
                $conditionid = $condition->id;

                if (isset($data[$conditionid])) {
                    continue;
                }

                $contextid = $condition->contextid;
                $objectid = $condition->objectid;
                if ($contextid == CONTEXT_COURSE) {
                    $data[$conditionid] = $condition;
                    continue;
                }

                // If is category, search the courses inside it.
                if (!isset($coursesbycategory[$objectid])) {
                    $coursecat = \core_course_category::get($objectid);
                    $coursecategories = $coursecat->get_courses(['recursive' => 1]);
                    $coursesbycategory[$objectid] = array_column($coursecategories, 'id');
                }

                if (in_array($courseid, $coursesbycategory[$objectid])) {
                    $data[$conditionid] = $condition;
                }
            }
        }

        $conditions->close();

        return $data;
    }

    /**
     * Get the current conditions by plugin, course and cmid
     *
     * @param string $pluginname Plugin name
     * @param int $courseid Course id
     * @param int $cmid Course module id
     *
     * @return array $data Plugin, course and cmid conditions
     */
    public static function get_conditions_by_cm($pluginname, $courseid, $cmid) {
        global $DB;

        $coursesbycategory = [];
        $data = [];

        $conditionssql = 'SELECT nc.id, nr.id AS ruleid, nc.parameters, nc.pluginname, nctx.contextid, nctx.objectid
                            FROM {notificationsagent_condition} nc
                            JOIN {notificationsagent_rule} nr ON nc.ruleid = nr.id
                             AND nr.status = 0 AND nr.template = 1 AND nr.deleted = 0
                            JOIN {notificationsagent_context} nctx ON nctx.ruleid = nr.id
                             AND ((nctx.contextid = :categorycontextid)
                              OR (nctx.contextid = :coursecontextid AND nctx.objectid = :courseid))
                            JOIN {course} c ON nctx.objectid = c.id
                           WHERE nc.pluginname = :pluginname
                             AND nc.cmid = :cmid
        ';
        $conditions = $DB->get_recordset_sql(
            $conditionssql,
            [
                        'pluginname' => $pluginname,
                        'categorycontextid' => CONTEXT_COURSECAT,
                        'coursecontextid' => CONTEXT_COURSE,
                        'courseid' => $courseid,
                        'cmid' => $cmid,
                ]
        );

        if ($conditions->valid()) {
            foreach ($conditions as $condition) {
                $conditionid = $condition->id;

                if (isset($data[$conditionid])) {
                    continue;
                }

                $contextid = $condition->contextid;
                $objectid = $condition->objectid;

                if ($contextid == CONTEXT_COURSE) {
                    $data[$conditionid] = $condition;
                    continue;
                }

                // If is category, search the courses inside it.
                if (!isset($coursesbycategory[$objectid])) {
                    $coursecat = \core_course_category::get($objectid);
                    $coursecategories = $coursecat->get_courses(['recursive' => 1]);
                    $coursesbycategory[$objectid] = array_column($coursecategories, 'id');
                }

                if (in_array($courseid, $coursesbycategory[$objectid])) {
                    $data[$conditionid] = $condition;
                }
            }
        }

        $conditions->close();

        return $data;
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

        $conditionssql = 'SELECT nc.id, nr.id AS ruleid, nc.parameters
                            FROM {notificationsagent_rule} nr
                            JOIN {notificationsagent_condition} nc ON nr.id = nc.ruleid
                             AND nr.status = 0 AND nr.template = 1  AND nr.deleted = 0
                           WHERE nc.pluginname = :pluginname
        ';
        $conditions = $DB->get_recordset_sql(
            $conditionssql,
            [
                        'pluginname' => $pluginname,
                ]
        );

        $coursesbyrule = [];

        if ($conditions->valid()) {
            foreach ($conditions as $condition) {
                $conditionid = $condition->id;
                $ruleid = $condition->ruleid;
                if (!isset($coursesbyrule[$ruleid])) {
                    $coursesbyrule[$ruleid] = self::get_all_courses_by_ruleid($ruleid);
                }
                $condition->courses = $coursesbyrule[$ruleid];
                $data[$conditionid] = $condition;
            }
        }

        $conditions->close();

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

        $conditionssql = 'SELECT nc.id, nr.id AS ruleid, nc.parameters
                                     FROM {notificationsagent_rule} nr
                                     JOIN {notificationsagent_condition} nc ON nr.id = nc.ruleid
                                      AND nr.status = 0 AND nr.template = 1  AND nr.deleted = 0
                                    WHERE nc.pluginname = :pluginname
                                    AND NOT EXISTS (
                                        SELECT ncaux.id, nraux.id AS ruleid
                                        FROM {notificationsagent_rule} nraux
                                        JOIN {notificationsagent_condition} ncaux ON nraux.id = ncaux.ruleid
                                        WHERE nraux.id = nr.id AND ncaux.pluginname != :pluginnameaux
                                    )
        ';
        $conditions = $DB->get_recordset_sql(
            $conditionssql,
            [
                        'pluginname' => self::CONDITION_AVAILABILITY,
                        'pluginnameaux' => self::CONDITION_AVAILABILITY,
                ]
        );

        $coursesbyrule = [];

        if ($conditions->valid()) {
            foreach ($conditions as $condition) {
                $conditionid = $condition->id;
                $ruleid = $condition->ruleid;
                if (!isset($coursesbyrule[$ruleid])) {
                    $coursesbyrule[$ruleid] = self::get_all_courses_by_ruleid($ruleid);
                }
                $condition->courses = $coursesbyrule[$ruleid];
                $data[$conditionid] = $condition;
            }
        }

        $conditions->close();

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
                              AND nctx.contextid = :categorycontextid
                            WHERE nr.id = :ruleid
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
     * Get the courses associated with the category context given a ruleid
     *
     * @param int $id Rule id
     *
     * @return array $data Courses where the rule is applied
     */
    public static function get_all_courses_by_ruleid($id) {
        global $DB;

        $data = [];

        $sqlcategoryctx = 'SELECT nctx.contextid, nctx.objectid
                             FROM {notificationsagent_rule} nr
                             JOIN {notificationsagent_context} nctx ON nr.id = nctx.ruleid
                          AND NOT (nctx.contextid = :coursecontextid AND nctx.objectid = :siteid)
                            WHERE nr.id = :ruleid
                              AND nr.status = 0 AND nr.template = 1  AND nr.deleted = 0
        ';
        $contexts = $DB->get_records_sql($sqlcategoryctx, [
                'coursecontextid' => CONTEXT_COURSE,
                'siteid' => SITEID,
                'ruleid' => $id,
        ]);

        if (!empty($contexts)) {
            foreach ($contexts as $context) {
                $contextid = $context->contextid;
                $objectid = $context->objectid;
                if ($contextid == CONTEXT_COURSE) {
                    $data[$objectid] = $objectid;
                    continue;
                }

                if ($contextid == CONTEXT_COURSECAT) {
                    $coursecat = \core_course_category::get($objectid);
                    $coursecategories = $coursecat->get_courses(['recursive' => 1]);
                    $coursesid = array_column($coursecategories, 'id');
                    if (!empty($coursesid)) {
                        foreach ($coursesid as $courseid) {
                            $data[$courseid] = $courseid;
                        }
                    }
                }
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
            5,
            $context,
            false,
            'u.*',
            '',
            true,
            '',
            '',
            '',
            'u.suspended = 0',
            ''
        );
    }

    /**
     *
     * Set timer cache in the notifications agent cache table.
     *
     * @param array $deletedata
     * @param array $insertdata
     *
     * @return void
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
     * @param array $deletedata
     * @param array $insertdata
     *
     * @return void
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

    /**
     *  Generate cache and triggers function.
     *
     * @param object $subplugin
     * @param evaluationcontext $context
     * @return void
     */
    public static function generate_cache_triggers($subplugin, $context) {
        global $DB;
        $transaction = $DB->start_delegated_transaction();
        $insertdata = [];
        $deletedata = [];
        $courseid = $context->get_courseid();
        $coursecontext = \context_course::instance($courseid);
        $contextuser = $context->get_userid();  // 0 or userid>0
        $student = $subplugin->rule->createdby;
        // Avoid to set triggers for event or cron triggered by users who don't own the rule.
        if (
            $contextuser > 0 && $contextuser != $student
                && !has_capability(
                    'local/notificationsagent:managecourserule',
                    $coursecontext,
                    $student
                )
        ) {
            return;
        }
        // Student or user is from event or cron per user.
        if ($contextuser > 0 && !has_capability('local/notificationsagent:managecourserule', $coursecontext, $student)) {
            $userid = $student;
            $userslimit = rule::get_limit_reached_by_users(
                $courseid,
                $subplugin->rule->id,
                $subplugin->rule->timesfired,
                [$userid]
            );
            if (
                !$userslimit[$userid]
                    && !self::is_ruleoff($subplugin->rule->id, $userid, $courseid)
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
            // If $USER has student role, only generate triggers for its.
            if (
                has_capability(
                    'local/notificationsagent:managecourserule',
                    $coursecontext,
                    $student
                )
            ) {
                $users = $contextuser ? [(object) ['id' => $contextuser]] : self::get_usersbycourse($coursecontext);
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
                        && !self::is_ruleoff($subplugin->rule->id, $user->id, $courseid)
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
            // If $USER has student role, only generate triggers for the user.
            if (
                has_capability(
                    'local/notificationsagent:managecourserule',
                    $coursecontext,
                    $student
                )
            ) {
                $userid = self::GENERIC_USERID;
            } else {
                $userid = $student;
            }

            $userslimit = rule::get_limit_reached_by_users(
                $courseid,
                $subplugin->rule->id,
                $subplugin->rule->timesfired,
                [$userid]
            );

            if (
                !$userslimit[$userid]
                    && !self::is_ruleoff($subplugin->rule->id, $userid, $courseid)
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

        $transaction->allow_commit();
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
     * @param int $courseid Course id
     *
     * @return bool
     */
    public static function is_ruleoff($ruleid, $userid, $courseid) {
        global $DB;
        $ruleoff = $DB->get_field(
            'notificationsagent_triggers',
            'MAX(ruleoff)',
            [
                        'ruleid' => $ruleid,
                        'userid' => $userid,
                        'courseid' => $courseid,
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

        // Get max_rules_cron.
        $maxrulescron = get_config('local_notificationsagent', 'max_rules_cron');
        \local_notificationsagent\helper\helper::custom_mtrace("Task max_rules_cron-> " . $maxrulescron);

        $rulesidquery = '
                    SELECT nt.id, nt.ruleid, nt.conditionid, nt.courseid, nt.userid, nt.startdate
                      FROM {notificationsagent_triggers} nt
                      JOIN {notificationsagent_rule} nr ON nr.id = nt.ruleid AND nr.status = 0
                     WHERE startdate
                    BETWEEN :tasklastrunttime AND :timestarted
                       AND nt.courseid != :courseid
                    ORDER BY nt.startdate ASC
        ';

        return $DB->get_records_sql(
            $rulesidquery,
            [
                        'tasklastrunttime' => $tasklastrunttime,
                        'timestarted' => $timestarted,
                        'courseid' => SITEID,
                ],
            0,
            $maxrulescron
        );
    }

    /**
     * Get supported course modules .
     *
     * @param int $cmid The course module ID
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
     * Deletes cache and triggers records for a given user and a list of condition IDs.
     *
     * @param array $conditionids An array of condition IDs to delete.
     * @param int $userid The user ID whose cache records to delete.
     *
     * @return void
     */
    public static function bulk_delete_conditions_by_userid($conditionids, $userid) {
        global $DB;

        if (!empty($conditionids)) {
            [$conditionsql, $params] = $DB->get_in_or_equal($conditionids, SQL_PARAMS_NAMED);
            $params = ['userid' => $userid] + $params;

            $DB->delete_records_select(
                'notificationsagent_cache',
                "userid = :userid AND conditionid {$conditionsql}",
                $params
            );
            $DB->delete_records_select(
                'notificationsagent_triggers',
                "userid = :userid AND conditionid {$conditionsql}",
                $params
            );
        }
    }

    /**
     * Evaluate the result of an expression based on the given operator.
     *
     * @param string $operator The operator to be used in the expression.
     * @param float $a The first operand in the expression.
     * @param float $b The second operand in the expression.
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

    /**
     * Get rules by course id
     *
     * @param int $courseid Course id
     *
     * @return array $rules
     */
    public static function get_rules_by_course($courseid) {
        global $DB;

        $sql = 'SELECT nr.id as ruleid, nctx.id as contextid
                    FROM {notificationsagent_rule} nr
                    JOIN {notificationsagent_context} nctx ON nctx.ruleid = nr.id
                WHERE nr.template = 1
                    AND (nctx.contextid = :coursecontextid AND nctx.objectid = :courseid)
        ';
        $rules = $DB->get_records_sql(
            $sql,
            [
                        'coursecontextid' => CONTEXT_COURSE,
                        'courseid' => $courseid,
                ]
        );

        return $rules;
    }

    /**
     * Delete all tables when course is deleted
     *
     * @param int $courseid
     *
     * @return  bool The result of the evaluation.
     */
    public static function delete_all_by_course($courseid) {
        global $DB;

        $DB->delete_records('notificationsagent_bootstrap', ['courseid' => $courseid]);
        $DB->delete_records('notificationsagent_cache', ['courseid' => $courseid]);
        $DB->delete_records('notificationsagent_cmview', ['courseid' => $courseid]);
        $DB->delete_records('notificationsagent_crseview', ['courseid' => $courseid]);
        $DB->delete_records('notificationsagent_launched', ['courseid' => $courseid]);
        $DB->delete_records('notificationsagent_report', ['courseid' => $courseid]);
        $DB->delete_records('notificationsagent_triggers', ['courseid' => $courseid]);

        // Get all rules.
        if ($rules = self::get_rules_by_course($courseid)) {
            [$insql, $inparams] = $DB->get_in_or_equal(array_column($rules, 'ruleid'), SQL_PARAMS_NAMED);
            $DB->delete_records_select(
                'notificationsagent_context',
                "contextid = :contextid AND objectid = :objectid AND ruleid $insql",
                ["contextid" => CONTEXT_COURSE, "objectid" => $courseid, ...$inparams]
            );

            // Check if rule has more contexts.
            $rulesid = [];
            foreach ($rules as $rule) {
                $ruleid = $rule->ruleid;
                $instance = new rule($ruleid, rule::RULE_TYPE, rule::RULE_ONLY);
                if (!$instance->has_context()) {
                    $rulesid[] = $ruleid;
                }
            }
            if (!empty($rulesid)) {
                [$insql, $inparams] = $DB->get_in_or_equal($rulesid, SQL_PARAMS_NAMED);
                $DB->delete_records_select('notificationsagent_action', "ruleid $insql", $inparams);
                $DB->delete_records_select('notificationsagent_condition', "ruleid $insql", $inparams);
                $DB->delete_records_select('notificationsagent_rule', "id $insql", $inparams);
            }
        }
    }
}
