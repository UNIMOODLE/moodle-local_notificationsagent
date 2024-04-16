<?php

namespace local_notificationsagent\helper\test;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/backup/util/interfaces/checksumable.class.php');
require_once($CFG->dirroot . '/backup/backup.class.php');
require_once($CFG->dirroot . '/backup/util/loggers/base_logger.class.php');

use base_logger;

class mock_base_logger extends base_logger {

    protected function action($message, $level, $options = null) {
        return $message + $level; // Simply return that, for testing
    }
}
