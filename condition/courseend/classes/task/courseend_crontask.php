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
namespace notificationscondition_courseend\task;
defined('MOODLE_INTERNAL') || die();
require_once(__DIR__ . '/../../../../notificationsagent.php');
require_once(__DIR__ . '/../../../../classes/engine/notificationsagent_engine.php');
require_once(__DIR__ . '/../../../../lib.php');

use core\task\scheduled_task;
use notificationsagent\notificationsagent;

class courseend_crontask extends scheduled_task {

    /**
     * Get a descriptive name for the task (shown to admins)
     *
     * @return string
     */
    public function get_name() {
        return get_string('courseend_crontask', 'notificationscondition_courseend');
    }

    /**
     * Throw exceptions on errors (the job will be retried).
     */
    public function execute() {
        global $DB;
        custom_mtrace("Courseend start");

        $pluginname = get_string('subtype', 'notificationscondition_courseend');
        $conditions = notificationsagent::get_conditions_by_plugin($pluginname);

        foreach ($conditions as $condition) {
            $courseid = $condition->courseid;
            $enddate = $DB->get_field('course', 'enddate', ['id' => $courseid]);
            $condtionid = $condition->id;
            $decode = $condition->parameters;
            $param = json_decode($decode, true);
            $cache = $enddate - $param['time'];

            if (!notificationsagent::was_launched_indicated_times(
                $condition->ruleid, $condition->ruletimesfired, $courseid, notificationsagent::GENERIC_USERID)) {
                notificationsagent::set_timer_cache(
                    notificationsagent::GENERIC_USERID, $courseid, $cache, $pluginname, $condtionid, false
                );
            }
        }

        custom_mtrace("Courseend end ");
    }
}