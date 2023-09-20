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
        $conditions = $DB->get_records_sql('
                select mnrc.ruleid, mnrc.parameters, mnrc.pluginname as pluginname
                from mdl_notificationsagent_condition mnrc
                inner join mdl_notificationsagent_rule mnr ON mnr.ruleid=mnrc.ruleid
                where pluginname=?
                and courseid=?',
            [$pluginname, $courseid]
        );

        return $conditions;
    }

    public static function set_timer_cache($userid, $courseid, $timer, $pluginname, $updaterecord) {
        // Sessionstart no actualiza si hay registro.
        // Sessionend actualiza siempre.
        global $DB;
        $exists = $DB->record_exists(
            'notificationsagent_cache',
            array(
                'userid' => $userid,
                'courseid' => $courseid,
                'pluginname' => $pluginname
            )
        );

        if (!$exists) {
            $objdb = new \stdClass();
            $objdb->userid = $userid;
            $objdb->courseid = $courseid;
            $objdb->timestart = $timer;
            $objdb->pluginname = $pluginname;
            $DB->insert_record('notificationsagent_cache', $objdb);
        }

    }


}

