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
namespace notificationscondition_activitymodified\task;
defined('MOODLE_INTERNAL') || die();
require_once(__DIR__ . '/../../../../notificationsagent.php');
require_once(__DIR__ . '/../../../../classes/engine/notificationsagent_engine.php');
require_once(__DIR__ . '/../../../../lib.php');
require_once(__DIR__ . '/../../lib.php');

use core\task\scheduled_task;
use notificationsagent\notificationsagent;
use Notificationsagent_engine;

class activitymodified_crontask extends scheduled_task {

    /**
     * Get a descriptive name for the task (shown to admins)
     *
     * @return string
     */
    public function get_name() {
        return get_string('activitymodified_crontask', 'notificationscondition_activitymodified');
    }

    /**
     * Throw exceptions on errors (the job will be retried).
     */
    public function execute() {
        custom_mtrace("Activity open start");

        $pluginname = 'activitymodified';
        $conditions = notificationsagent::get_conditions_by_plugin($pluginname);
        $ruleids = [];
        foreach ($conditions as $condition) {
            $courseid = $condition->courseid;
            $ruleids[] = $condition->ruleid;
            $condtionid = $condition->id;
            $decode = $condition->parameters;
            $param = json_decode($decode, true);
            $cmid = $param['activity'];

            $results = notificationsagent_condition_activitymodified_get_cm_endtime($cmid);
            foreach ($results as $result) {
                if (!notificationsagent::was_launched_indicated_times(
                    $condition->ruleid, $condition->ruletimesfired, $courseid, $result->userid)) {
                        $cache = $result->timemodified;
                        notificationsagent::set_timer_cache($result->userid, $courseid, $cache, $pluginname, $condtionid, true);
                }
            }

        }

        custom_mtrace("Activity open end ");

    }
}

