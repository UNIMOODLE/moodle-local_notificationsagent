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
namespace local_notificationsagent\task;


class cron_task_local_notificationsagent extends \core\task\scheduled_task {
    public function get_name() {
        return get_string('task', 'local_notificationsagent');
    }

    public function execute() {
        global $CFG;
        require_once($CFG->dirroot . '/local/notificationsagent/lib.php');
        custom_mtrace("Task started " . time());
        sleep(5);
        custom_mtrace("Task finished " . time());
    }
}
