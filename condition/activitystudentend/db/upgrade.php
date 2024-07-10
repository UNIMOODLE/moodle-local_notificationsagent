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
// Funded by the European Union - Next GenerationEU".
//
// Produced by the UNIMOODLE University Group: Universities of
// Valladolid, Complutense de Madrid, UPV/EHU, León, Salamanca,
// Illes Balears, Valencia, Rey Juan Carlos, La Laguna, Zaragoza, Málaga,
// Córdoba, Extremadura, Vigo, Las Palmas de Gran Canaria y Burgos.

/**
 * Version details
 *
 * @package    notificationscondition_activitystudentend
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Stub for upgrade code
 *
 * @param int $oldversion
 *
 * @return bool
 */
function xmldb_notificationscondition_activitystudentend_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2023101804) {
        // Define key fk_userid (foreign) to be added to notificationsagent_cmview.
        $table = new xmldb_table('notificationsagent_cmview');
        $key = new xmldb_key('fk_userid', XMLDB_KEY_FOREIGN, ['userid'], 'user', ['id']);

        // Launch add key fk_userid.
        $dbman->add_key($table, $key);

        // Define key fk_courseid (foreign) to be added to notificationsagent_cmview.
        $table = new xmldb_table('notificationsagent_cmview');
        $key = new xmldb_key('fk_courseid', XMLDB_KEY_FOREIGN, ['courseid'], 'course', ['id']);

        // Launch add key fk_courseid.
        $dbman->add_key($table, $key);

        // Define key fk_cmid (foreign) to be added to notificationsagent_cmview.
        $table = new xmldb_table('notificationsagent_cmview');
        $key = new xmldb_key('fk_cmid', XMLDB_KEY_FOREIGN, ['idactivity'], 'course_modules', ['id']);

        // Launch add key fk_cmid.
        $dbman->add_key($table, $key);

        // Activitystudentend savepoint reached.
        upgrade_plugin_savepoint(true, 2023101804, 'notificationscondition', 'activitystudentend');
    }

    if ($oldversion < 2023101811) {
        // Define field timecreated to be added to notificationsagent_cmview.
        $table = new xmldb_table('notificationsagent_cmview');
        $field = new xmldb_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'firstaccess');

        // Conditionally launch add field timecreated.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'timecreated');

        // Conditionally launch add field timemodified.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'timemodified');

        // Conditionally launch add field usermodified.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Activitystudentend savepoint reached.
        upgrade_plugin_savepoint(true, 2023101811, 'notificationscondition', 'activitystudentend');
    }

    if ($oldversion < 2024071002) {
        // Set values.
        $recordset = $DB->get_recordset('notificationsagent_cmview');
        foreach ($recordset as $record) {

            if ($record->usermodified == null) {
                $record->usermodified = $record->userid;
            }
            if ($record->timecreated == null) {
                $record->timecreated = $record->firstaccess;
            }
            if ($record->timemodified == null) {
                $record->timemodified = $record->firstaccess;
            }

            $DB->update_record('notificationsagent_cmview', $record);

        }
        $recordset->close();

        // Changing nullability of field usermodified on table notificationsagent_cmview to not null.
        $table = new xmldb_table('notificationsagent_cmview');
        $field = new xmldb_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'firstaccess');

        // Launch change of nullability for field usermodified.
        $dbman->change_field_notnull($table, $field);

        // Launch change of default for field usermodified.
        $dbman->change_field_default($table, $field);

        $field = new xmldb_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'usermodified');

        // Launch change of nullability for field timecreated.
        $dbman->change_field_notnull($table, $field);

        // Launch change of default for field timecreated.
        $dbman->change_field_default($table, $field);

        $field = new xmldb_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'timecreated');

        // Launch change of nullability for field timemodified.
        $dbman->change_field_notnull($table, $field);

        // Launch change of default for field timemodified.
        $dbman->change_field_default($table, $field);

        // Activitystudentend savepoint reached.
        upgrade_plugin_savepoint(true, 2024071002, 'notificationscondition', 'activitystudentend');
    }

    return true;
}
