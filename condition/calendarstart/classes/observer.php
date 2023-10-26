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
defined('MOODLE_INTERNAL') || die();
require_once(__DIR__ .'/../calendarstart.php');
require_once(__DIR__ .'/../../../notificationsagent.php');
require_once(__DIR__ .'/../../../classes/engine/notificationsagent_engine.php');
use notificationsagent\notificationsagent;
class notificationscondition_calendarstart_observer {

    public static function calendar_updated(core\event\calendar_event_updated $event) {
        global $DB;
        if (!isloggedin() || $event->courseid == 1) {
            return;
        }

        $other = $event->other;
        $courseid = $event->courseid;
        $ruleids = [];

        // If stardate is not set in other array then the startdate setting has not been modified.
        if (isset($other["timestart"])) {
            $startdate = $other["timestart"];
        } else {
            return;
        }

        $pluginname = get_string('subtype', 'notificationscondition_calendarstart');
        $conditions = notificationsagent::get_conditions_by_course($pluginname, $courseid);
        $context = \context_course::instance($courseid);
        $enrolledusers = notificationsagent::get_usersbycourse($context);
     

        foreach ($conditions as $condition) {
            $decode = $condition->parameters;
            $pluginname = $condition->pluginname;
            $condtionid = $condition->id;
            $ruleids[] = $condition->ruleid;
            $param = json_decode($decode, true);
            $cache = $startdate + $param['time'];

            foreach ($enrolledusers as $enrolleduser) {
                notificationsagent::set_timer_cache($enrolleduser->id, $courseid, $cache, $pluginname, $condtionid, true);
                notificationsagent::set_time_trigger($condition->ruleid, $enrolleduser->id, $courseid,$cache);
            }
        }
    }
}
