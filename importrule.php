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
// Project implemented by the \"Recovery, Transformation and Resilience Plan.
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

require_once("../../config.php");
global $DB;

$courseid = required_param('courseid', PARAM_INT);

if (!$courseid) {
    require_login();
    throw new \moodle_exception('needcourseid');
}

if (!isset($_FILES['userfile']) || $_FILES['userfile']['error'] == UPLOAD_ERR_NO_FILE) {
    $message = get_string('no_file_selected', 'local_notificationsagent');
    echo \core\notification::error($message);
} else {
    // Read json file.
    $data = file_get_contents($_FILES['userfile']['tmp_name']);
    $data = json_decode($data, true);

    // Check if file uploaded is a JSON.
    if (!$data) {
        $message = get_string('no_json_file', 'local_notificationsagent');
        echo \core\notification::error($message);
    } else {
        $sqlrule = new \stdClass();
        $sqlrule->name = $data['rule']['name'];
        $sqlrule->description = $data['rule']['description'];
        $sqlrule->createdby = $data['rule']['createdby'];
        $sqlrule->createdat = $data['rule']['createdat'];
        $sqlrule->template = $data['rule']['template'];

        if ($idrule = $DB->insert_record('notificationsagent_rule', $sqlrule)) {
            $sqlcontext = new \stdClass();
            $sqlcontext->ruleid = $idrule;
            $sqlcontext->contextid = CONTEXT_COURSE;
            $sqlcontext->objectid = $courseid;

            $idcontext = $DB->insert_record('notificationsagent_context', $sqlcontext);
        }

        if ($data['actions']) {
            $sqlactions = new \stdClass();
            $countactions = count($data['actions']);

            for ($i = 0; $i < $countactions; $i++) {
                foreach ($data['actions'][array_key_first($data['actions']) + $i] as $key => $value) {
                    if ($key == 'ruleid') {
                        $sqlactions->ruleid = $idrule;
                    } else {
                        $sqlactions->$key = $value;
                    }
                }

                $idactions = $DB->insert_record('notificationsagent_action', $sqlactions);
            }
        }

        if ($data['conditions']) {
            $sqlconditions = new \stdClass();
            $countconditions = count($data['conditions']);

            for ($i = 0; $i < $countconditions; $i++) {
                foreach ($data['conditions'][array_key_first($data['conditions']) + $i] as $key => $value) {
                    if ($key == 'ruleid') {
                        $sqlconditions->ruleid = $idrule;
                    } else {
                        $sqlconditions->$key = $value;
                    }
                }

                $idconditions = $DB->insert_record('notificationsagent_condition', $sqlconditions);
            }
        }

        if ($idcontext && $idrule && (!is_null($idactions) && !is_null($idconditions))) {
            $message = get_string('import_success', 'local_notificationsagent');
            echo \core\notification::success($message);
        } else {
            $message = get_string('import_error', 'local_notificationsagent');
            echo \core\notification::error($message);
        }
    }
}

header('Location: index.php?courseid=' . $courseid);
