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

// Project implemented by the "Recovery, Transformation and Resilience Plan.
// Funded by the European Union - Next GenerationEU\".
//
// Produced by the UNIMOODLE University Group: Universities of
// Valladolid, Complutense de Madrid, UPV/EHU, León, Salamanca,
// Illes Balears, Valencia, Rey Juan Carlos, La Laguna, Zaragoza, Málaga,
// Córdoba, Extremadura, Vigo, Las Palmas de Gran Canaria y Burgos.

/**
 * Version details
 *
 * @package    local_notificationsagent
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');
global $CFG, $DB;

$courseid = required_param('courseid', PARAM_INT);
$context = context_course::instance($courseid);
$capability = 'local/notificationsagent:importrule';
$pluginname = '';
$timer = 0;

if (!$courseid) {
    require_login();
    throw new \moodle_exception('needcourseid', 'local_notificationsagent');;
}
if (!has_capability($capability, $context)) {
    require_capability($capability, $context);
}

if (!isset($_FILES['userfile']) || $_FILES['userfile']['error'] == UPLOAD_ERR_NO_FILE) {
    $message = get_string('no_file_selected', 'local_notificationsagent');
    echo \core\notification::error($message);
} else {
    // Read json file.
    $data = file_get_contents($_FILES['userfile']['tmp_name']);
    $data = json_decode($data, true);
    // Check if file uploaded is a JSON.
    if ( json_last_error() !== JSON_ERROR_NONE || mime_content_type(($_FILES['userfile']['tmp_name']) ) !== 'application/json') {
        $message = get_string('no_json_file', 'local_notificationsagent');
        echo \core\notification::error($message. ": " . json_last_error_msg());
    } else {
        $transaction = $DB->start_delegated_transaction();
        $sqlrule = new \stdClass();
        $sqlrule->name = s($data['rule']['name']);
        $sqlrule->description = s($data['rule']['description']);
        $sqlrule->createdby = $data['rule']['createdby'];
        $sqlrule->createdat = $data['rule']['createdat'];
        $sqlrule->template = $data['rule']['template'];
        $sqlrule->timesfired = $data['rule']['timesfired'];
        $sqlrule->runtime = $data['rule']['runtime'];

        if ($idrule = $DB->insert_record('notificationsagent_rule', $sqlrule)) {
            $sqlcontext = new \stdClass();
            $sqlcontext->ruleid = $idrule;
            $sqlcontext->contextid = CONTEXT_COURSE;
            $sqlcontext->objectid = $courseid;

            $idcontext = $DB->insert_record('notificationsagent_context', $sqlcontext);
        }

        if ($data['actions']) {
            $sqlactions = [];
            foreach ($data['actions'] as $key => $value) {
                $value['ruleid'] = $idrule;
                $sqlactions[$key] = $value;
            }
            $DB->insert_records('notificationsagent_action', $sqlactions);

        }

        if ($data['conditions']) {
            $sqlconditions = [];
            foreach ($data['conditions'] as $key => $value) {
                    $value['ruleid'] = $idrule;
                    $sqlconditions[$key] = $value;
            }
                $DB->insert_records('notificationsagent_condition', $sqlconditions);
        }
        $transaction->allow_commit();
    }
}

redirect('index.php?courseid=' . $courseid);
