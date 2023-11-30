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


defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../activityend.php');
require_once(__DIR__ . '/../../../notificationsagent.php');
require_once(__DIR__ . '/../../../classes/engine/notificationsagent_engine.php');

use notificationsagent\notificationsagent;

class notificationscondition_activityend_observer {

    public static function course_module_updated(\core\event\course_module_updated $event) {

        $courseid = $event->courseid;
        $cmid = $event->objectid;

        $timeend = notificationsagent::notificationsagent_condition_get_cm_dates($cmid)->timeend;

        $pluginname = get_string('subtype', 'notificationscondition_activityend');

        $conditions = notificationsagent::get_conditions_by_cm($pluginname, $courseid, $cmid);
        $context = context_course::instance($courseid);
        $enrolledusers = notificationsagent::get_usersbycourse($context);

        foreach ($conditions as $condition) {
            $decode = $condition->parameters;
            $pluginname = $condition->pluginname;
            $condtionid = $condition->id;
            $param = json_decode($decode, true);
            $cache = $timeend;
            foreach ($enrolledusers as $enrolleduser) {
                if (!notificationsagent::was_launched_indicated_times(
                    $condition->ruleid, $condition->ruletimesfired, $courseid, $enrolleduser->id)) {
                    // Update every time a module is updated.
                    notificationsagent::set_timer_cache($enrolleduser->id, $courseid, $cache, $pluginname, $condtionid, true);
                    notificationsagent::set_time_trigger($condition->ruleid, $enrolleduser->id, $courseid, $cache);
                }
            }
        }
    }
}
