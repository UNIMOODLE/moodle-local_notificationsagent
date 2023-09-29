<?php

namespace notificationscondition_activityopen\task;

use core\task\scheduled_task;

class activityopen_crontask extends scheduled_task {

    /**
     * Get a descriptive name for the task (shown to admins)
     *
     * @return string
     */
    public function get_name() {
        return get_string('activityopen_crontask', 'notificationscondition_activityopen');
    }

    /**
     * Throw exceptions on errors (the job will be retried).
     */
    public function execute() {

        mtrace("Activity open start");

        // Pick up conditions.

        mtrace("Activity open end");

    }
}
