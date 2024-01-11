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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * activitystudentend observer.php .
 *
 * @package    activitystudentend
 * @copyright  2023 fernando
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../lib.php');
require_once(__DIR__ . '/../activitystudentend.php');
require_once(__DIR__ . '/../../../notificationsagent.php');
require_once(__DIR__ . '/../../../classes/engine/notificationsagent_engine.php');

use notificationsagent\notificationsagent;

class notificationscondition_activitystudentend_observer {

    /**
     * @throws dml_exception
     */
    public static function course_module_viewed($event) {
        if ($event->courseid == 1 || !isloggedin()) {
            return;
        }

        $courseid = $event->courseid;

        $userid = $event->userid;
        $idactivity = $event->contextinstanceid;
        $timecreated = $event->timecreated;

        $pluginname = 'activitystudentend';

        set_activity_access($userid, $courseid, $idactivity, $timecreated);

        $conditions = notificationsagent::get_conditions_by_course($pluginname, $courseid);

        foreach ($conditions as $condition) {
            $decode = $condition->parameters;
            $pluginname = $condition->pluginname;
            $condtionid = $condition->id;
            $param = json_decode($decode, true);
            $cache = $timecreated + $param['time'];
            if (!notificationsagent::was_launched_indicated_times(
                $condition->ruleid, $condition->ruletimesfired, $courseid, $userid)) {
                notificationsagent::set_timer_cache($userid, $courseid, $cache, $pluginname, $condtionid, true);
            }
        }
    }
}
