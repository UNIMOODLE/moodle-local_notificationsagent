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

/**
 * Event observer for activitycompleted component.
 *
 * @package    activitycompleted
 * @copyright  2023 fernando
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../activitycompleted.php');
require_once(__DIR__ . '/../../../notificationsagent.php');
require_once(__DIR__ . '/../../../classes/engine/notificationsagent_engine.php');
use notificationsagent\notificationsagent;

class notificationscondition_activitycompleted_observer {
    /**
     * Triggered when 'course_module_completion_updated' event is triggered.
     *
     * @param \core\event\course_module_completion_updated $event
     */
    public static function course_module_completion_updated(\core\event\course_module_completion_updated $event) {
        global $DB;

        $courseid = $event->courseid;
        $userid = $event->relateduserid;
        $cmid = $event->contextinstanceid;
        $cmstate = $event->other['completionstate'];
        $time = $event->timecreated;

        $pluginname = 'activitycompleted';
        $conditions = notificationsagent::get_conditions_by_cm($pluginname, $courseid, $cmid);

        $ruleids = [];
        foreach ($conditions as $condition) {
            $ruleids[] = $condition->ruleid;
            if ($cmstate == 0) {
                $recordexists = $DB->record_exists('notificationsagent_cache', [
                    'pluginname' => $pluginname, 'conditionid' => $condition->id,
                    'courseid' => $courseid, 'userid' => $userid,
                ]);
                if ($recordexists) {
                    $DB->delete_records('notificationsagent_cache', [
                        'pluginname' => $pluginname, 'conditionid' => $condition->id,
                        'courseid' => $courseid, 'userid' => $userid,
                    ]);
                }
            } else {
                notificationsagent::set_timer_cache($userid, $courseid, $time, $pluginname, $condition->id, true);
            }
        }

        Notificationsagent_engine::notificationsagent_engine_evaluate_rule($ruleids, null, $userid);
    }
}
