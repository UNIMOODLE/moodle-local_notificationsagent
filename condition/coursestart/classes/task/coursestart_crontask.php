<?php

namespace notificationscondition_coursestart\task;

use core\task\scheduled_task;

class coursestart_crontask extends scheduled_task {

    /**
     * Get a descriptive name for the task (shown to admins)
     *
     * @return string
     */
    public function get_name() {
        return get_string('coursestart_crontask', 'notificationscondition_coursestart');
    }

    /**
     * Do the job.
     * Throw exceptions on errors (the job will be retried).
     */
    public function execute() {

        mtrace("Coursestart start");



        mtrace("coursestart end");

    }
}
