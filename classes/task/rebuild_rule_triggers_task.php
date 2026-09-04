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

/**
 * Adhoc task to rebuild triggers for a non-generic rule in a course.
 *
 * @package    local_notificationsagent
 * @copyright  2023 Proyecto UNIMOODLE
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_notificationsagent\task;

use core\task\adhoc_task;
use local_notificationsagent\notificationsagent;
use local_notificationsagent\rule;

/**
 * Rebuild rule triggers in the background after saving a non-generic rule.
 */
class rebuild_rule_triggers_task extends adhoc_task {
    /**
     * Human-readable name for the task.
     *
     * @return string
     */
    public function get_name(): string {
        return get_string('taskrebuildtriggers', 'local_notificationsagent');
    }

    /**
     * Execute trigger rebuild for the rule and course in custom data.
     */
    public function execute(): void {
        $data = $this->get_custom_data();
        if (empty($data->ruleid) || empty($data->courseid) || !isset($data->token)) {
            return;
        }

        $ruleid = (int) $data->ruleid;
        $courseid = (int) $data->courseid;
        $token = (int) $data->token;

        if (notificationsagent::get_rebuild_token($ruleid, $courseid) !== $token) {
            return;
        }

        if ($courseid == SITEID || !notificationsagent::is_course_visible_for_rules($courseid)) {
            return;
        }

        try {
            notificationsagent::set_rebuild_in_progress($ruleid, $courseid, true);

            $rule = rule::create_instance($ruleid);
            if ($rule === null) {
                return;
            }

            $rule->rebuild_triggers_for_course($courseid, $token);
        } finally {
            notificationsagent::set_rebuild_in_progress($ruleid, $courseid, false);
        }
    }
}
