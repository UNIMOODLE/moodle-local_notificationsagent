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
namespace notificationscondition_activitysinceend\task;
defined('MOODLE_INTERNAL') || die();
require_once(__DIR__ . '/../../../../notificationsagent.php');
require_once(__DIR__ . '/../../../../classes/engine/notificationsagent_engine.php');
require_once(__DIR__ . '/../../../../lib.php');
require_once(__DIR__ . '/../../lib.php');

use core\task\scheduled_task;
use notificationsagent\notificationsagent;

class activitysinceend_crontask extends scheduled_task {

    /**
     * Get a descriptive name for the task (shown to admins)
     *
     * @return string
     */
    public function get_name() {
        return get_string('activitysinceend_crontask', 'notificationscondition_activitysinceend');
    }

    /**
     * Throw exceptions on errors (the job will be retried).
     */
    public function execute() {
        custom_mtrace("Activity open start");

        $pluginname = 'activitysinceend';
        $conditions = notificationsagent::get_conditions_by_plugin($pluginname);

        foreach ($conditions as $condition) {
            $courseid = $condition->courseid;
            $context = \context_course::instance($courseid);
            $enrolledusers = notificationsagent::get_usersbycourse($context);
            $condtionid = $condition->id;
            $decode = $condition->parameters;
            $param = json_decode($decode, true);
            $cmid = $param['activity'];
            $completions = notificationsagent_condition_activitysinceend_get_cm_endtime($cmid);
            foreach ($completions as $completion) {
                if (!notificationsagent::was_launched_indicated_times(
                $condition->ruleid, $condition->ruletimesfired, $courseid, $completion->userid)) {
                    $cache = $completion->timemodified + $param['time'];
                    notificationsagent::set_timer_cache($completion->userid, $courseid, $cache, $pluginname, $condtionid, true);
                    notificationsagent::set_time_trigger($condition->ruleid, $completion->userid, $courseid, $cache);
                }
            }

        }

        custom_mtrace("Activity open end ");

    }
}

