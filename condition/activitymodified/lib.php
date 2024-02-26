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

function notificationsagent_condition_activitymodified_get_cm_endtime($cmid) {
    // Table :course modules.
    global $DB;
    $endtimequery = "
                    SELECT id
                      FROM {context}
                     WHERE instanceid = :cmid
                     AND contextlevel = :contextlevel";

    $context = $DB->get_record_sql(
        $endtimequery,
        [
            'cmid' => $cmid,
            'contextlevel' => CONTEXT_MODULE,
        ]
    );

    if ($context) {

        $filesquery = "
        WITH RankedFiles AS (
            SELECT
              f.*,
              ROW_NUMBER() OVER (PARTITION BY f.userid ORDER BY f.timemodified DESC) AS RowNum
            FROM {files} f
            WHERE f.contextid = 96
              AND f.filesize <> 0
              AND (f.timemodified - f.timecreated) > 5
              AND f.source IS NOT NULL
              AND f.userid IS NOT NULL
          )
          SELECT *
          FROM RankedFiles
          WHERE RowNum = 1";

        $files = $DB->get_records_sql($filesquery, ['contextid' => $context->id]);

    }

    return $files;

}
