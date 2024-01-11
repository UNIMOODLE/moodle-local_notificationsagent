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
 * activityopen observer.php .
 *
 * @package    activityopen
 * @copyright  2023 fernando
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../activityopen.php');
require_once(__DIR__ . '/../../../notificationsagent.php');
require_once(__DIR__ . '/../../../classes/engine/notificationsagent_engine.php');

use notificationsagent\notificationsagent;

class notificationscondition_activityopen_observer {

    public static function course_module_updated(\core\event\course_module_updated $event) {

        $courseid = $event->courseid;
        $cmid = $event->objectid;

        $timestart = notificationsagent::notificationsagent_condition_get_cm_dates($cmid)->timestart;

        $pluginname = 'activityopen';

        $conditions = notificationsagent::get_conditions_by_cm($pluginname, $courseid, $cmid);
        foreach ($conditions as $condition) {
            $decode = $condition->parameters;
            $pluginname = $condition->pluginname;
            $condtionid = $condition->id;
            $param = json_decode($decode, true);
            $cache = $timestart + $param['time'];
            if (!notificationsagent::was_launched_indicated_times(
                $condition->ruleid, $condition->ruletimesfired, $courseid, notificationsagent::GENERIC_USERID)) {
                notificationsagent::set_timer_cache(
                    notificationsagent::GENERIC_USERID, $courseid, $cache, $pluginname, $condtionid, true
                );
                notificationsagent::set_time_trigger($condition->ruleid, notificationsagent::GENERIC_USERID, $courseid, $cache);
            }
        }
    }
}