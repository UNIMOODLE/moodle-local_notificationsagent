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
require_once(__DIR__ . '/../../../../../calendar/lib.php');
use notificationsagent\notificationsagent;
class notificationscondition_calendarstart_observer {

    public static function calendar_updated(\core\event\calendar_event_updated $event) {
        global $DB;

        $courseid = $event->courseid;
        $ruleids = [];

        $pluginname = get_string('subtype', 'notificationscondition_calendarstart');
        $conditions = notificationsagent::get_conditions_by_course($pluginname, $courseid);

        foreach ($conditions as $condition) {
            $decode = $condition->parameters;
            $pluginname = $condition->pluginname;
            $condtionid = $condition->id;
            $ruleids[] = $condition->ruleid;
            $param = json_decode($decode);
            $radio = $param->radio;

            $calendarevent = calendar_get_events_by_id([$param->calendar]);

            if ($radio == 1) {
                $cache = $calendarevent[$param->calendar]->timestart + $param->time;
            } else {
                $cache = $calendarevent[$param->calendar]->timestart +
                $calendarevent[$param->calendar]->timeduration + $param->time;
            }

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
