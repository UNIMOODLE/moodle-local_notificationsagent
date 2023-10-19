<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace notificationsagent;

class notificationsagent {

    public static function get_conditions_by_course($pluginname, $courseid) {
        global $DB;
        $conditios_sql = "SELECT mnr.id, mnrc.ruleid,mnrc.parameters, mnrc.pluginname
                            FROM {notificationsagent_condition} mnrc
                            JOIN {notificationsagent_rule} mnr 
                              ON mnr.id = mnrc.ruleid
                             AND mnr.status = 0
                           WHERE pluginname = :pluginname
                             AND courseid = :courseid";

        $conditions = $DB->get_records_sql(
            $conditios_sql,
            [
                'pluginname' => $pluginname,
                'courseid' => $courseid
            ]
        );

        return $conditions;
    }

    public static function get_conditions_by_cm($pluginname, $courseid, $cmid) {
        global $DB;
        $conditios_sql = "SELECT mnr.id, mnrc.ruleid, mnrc.parameters, mnrc.pluginname,
                                 JSON_VALUE(parameters, '$.activity') AS cmid
                            FROM {notificationsagent_condition} mnrc
                            JOIN {notificationsagent_rule} mnr ON mnr.id = mnrc.ruleid
                             AND mnr.status = 0
                           WHERE pluginname = :pluginname
                             AND courseid = :courseid
                             AND JSON_VALUE(parameters, '$.activity') = :cmid";

        $conditions = $DB->get_records_sql(
            $conditios_sql,
            [
                'pluginname' => $pluginname,
                'courseid' => $courseid,
                'cmid' => $cmid
            ]
        );

        return $conditions;
    }


    public static function get_conditions_by_plugin($pluginname) {
        global $DB;
        $conditions_sql = "SELECT mnrc.id, mnr.id, mnrc.ruleid,mnrc.parameters, mnrc.pluginname, mnr.courseid
                            FROM {notificationsagent_condition} mnrc
                            JOIN {notificationsagent_rule} mnr 
                              ON mnr.id = mnrc.ruleid
                             AND mnr.status = 0
                           WHERE pluginname = :pluginname";

        $conditions = $DB->get_records_sql(
            $conditions_sql,
            [
                'pluginname' => $pluginname,
            ]
        );

        return $conditions;
    }


    /**
     * @param $context
     *
     * @return array
     */
    public static function get_usersbycourse($context): array {
        $enrolledusers = get_enrolled_users($context);
        $users = [];
        foreach ($enrolledusers as $user) {
            $users[] = $user->id;
        }
        return $users;
    }


    // Engine functions.
    public static function set_timer_cache($userid, $courseid, $timer, $pluginname, $conditionid, $updatecacheifexist) {
        // Sessionstart no actualiza si hay registro.
        // Sessionend actualiza siempre.
        // activityopen actualiza siempre.
        // coursestart actualiza siempre.
        global $DB;
        $exists = $DB->get_field(
            'notificationsagent_cache', 'id',
            array(
                'userid' => $userid,
                'courseid' => $courseid,
                'pluginname' => $pluginname,
                'conditionid' => $conditionid
            )
        );
        $objdb = new \stdClass();
        $objdb->userid = $userid;
        $objdb->courseid = $courseid;
        $objdb->timestart = $timer;
        $objdb->pluginname = $pluginname;
        $objdb->conditionid = $conditionid;
        // Insert.
        if (!$exists) {
            $DB->insert_record('notificationsagent_cache', $objdb);
        }
        // Update.
        if ($exists && $updatecacheifexist) {
            $objdb->id = $exists;
            $DB->update_record('notificationsagent_cache', $objdb);
        }
    }

    // TODO WIP.
    public static function set_time_trigger($ruleid, $userid, $courseid, $timer) {
        global $DB;
        $exists = $DB->get_field(
            'notificationsagent_triggers', 'id',
            array(
                'ruleid' => $ruleid,
                'userid' => $userid,
                'courseid' => $courseid,
            )
        );

        $objdb = new \stdClass();
        $objdb->userid = $userid;
        $objdb->courseid = $courseid;
        $objdb->ruleid = $ruleid;
        $objdb->startdate = $timer;

        if (!$exists) {
            $DB->insert_record('notificationsagent_triggers', $objdb);
        } else {
            $objdb->id = $exists;
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
    public static function delete_cache_by_ruleid($id)
    {
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
}


