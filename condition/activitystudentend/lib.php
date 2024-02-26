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

/**
 * @throws dml_exception
 */
function set_activity_access($userid, $courseid, $idactivity, $timecreated) {
    global $DB;
    $exists = $DB->record_exists(
        'notificationsagent_cmview',
        ['userid' => $userid, 'courseid' => $courseid, 'idactivity' => $idactivity]
    );

    $objdb = new stdClass();
    $objdb->userid = $userid;
    $objdb->courseid = $courseid;
    $objdb->idactivity = $idactivity;
    $objdb->firstaccess = $timecreated;

    if (!$exists) {
        // Si el registro no existe, inserta uno nuevo.
        $DB->insert_record('notificationsagent_cmview', $objdb);
    } else {
        // Si el registro existe, obtén la ID y actualiza el registro.
        $existingrecord = $DB->get_record(
            'notificationsagent_cmview',
            ['userid' => $userid, 'courseid' => $courseid, 'idactivity' => $idactivity]
        );
        $objdb->id = $existingrecord->id;
        $DB->update_record('notificationsagent_cmview', $objdb);
    }
}
function get_cmlastaccess($userid, $courseid, $cmid) {
    global $DB;
    $lastaccess = $DB->get_field(
        'notificationsagent_cmview',
        'firstaccess', ['courseid' => $courseid, 'userid' => $userid, 'idactivity' => $cmid],
    );

    if (empty($lastaccess)) {
        $query = "SELECT timecreated
                FROM {logstore_standard_log} mlsl
                JOIN {course_modules} mcm ON mcm.id = mlsl.contextinstanceid
                 AND mlsl.courseid = :courseid
                 AND mlsl.contextinstanceid = :cmid
                 AND mlsl.userid = :userid
                JOIN {modules} mm ON mcm.module = mm.id
               WHERE eventname = CONCAT('\\mod_',mm.name,'\\event\\course_module_viewed')
            ORDER BY timecreated
               LIMIT 1";

        $result = $DB->get_record_sql(
            $query, [
                'courseid' => $courseid,
                'userid' => $userid,
                'cmid' => $cmid,
            ]
        );

        if (!$result) {
            $lastaccess = null;
        } else {
            $lastaccess = $result->timecreated;
        }
    }

    return $lastaccess;
}


