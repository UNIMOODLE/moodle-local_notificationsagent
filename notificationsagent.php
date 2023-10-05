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
        $conditios_sql = "SELECT mnr.ruleid, mnrc.id,mnrc.parameters, mnrc.pluginname
                            FROM mdl_notificationsagent_condition mnrc
                            JOIN mdl_notificationsagent_rule mnr ON mnr.ruleid=mnrc.ruleid
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
        $conditios_sql = "SELECT mnr.ruleid, mnrc.id, mnrc.parameters, mnrc.pluginname,
                                 JSON_VALUE(parameters, '$.activity') AS cmid
                            FROM mdl_notificationsagent_condition mnrc
                            JOIN mdl_notificationsagent_rule mnr ON mnr.ruleid=mnrc.ruleid
                           WHERE pluginname = :pluginname
                             AND courseid = :courseid
                             AND JSON_VALUE(parameters, '$.activity') = :cmid";

        $conditions = $DB->get_records_sql(
            $conditios_sql,
            [
                'pluginname' => $pluginname,
                'courseid' => $courseid,
                'cmid'=> $cmid
            ]
        );

        return $conditions;
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

        // Insert.
        if (!$exists) {
            $objdb = new \stdClass();
            $objdb->userid = $userid;
            $objdb->courseid = $courseid;
            $objdb->timestart = $timer;
            $objdb->pluginname = $pluginname;
            $objdb->conditionid = $conditionid;
            $DB->insert_record('notificationsagent_cache', $objdb);
        }
        // Update.
        if ($exists && $updatecacheifexist) {
            $objdb = new \stdClass();
            $objdb->id = $exists;
            $objdb->userid = $userid;
            $objdb->courseid = $courseid;
            $objdb->timestart = $timer;
            $objdb->pluginname = $pluginname;
            $objdb->conditionid = $conditionid;
            $DB->update_record('notificationsagent_cache', $objdb);
        }
    }

    // TODO WIP.
    public static function set_time_trigger($ruleid, $userid, $courseid, $timer) {

        global $DB;
        $objdb = new \stdClass();
        $objdb->userid = $userid; // TODO is case is NULL. DB forces to not null.
        $objdb->courseid = $courseid;
        $objdb->ruleid = $ruleid;
        $objdb->day = $timer;
        $DB->insert_record('notificationsagent_triggers', $objdb);

    }
}

