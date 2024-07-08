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
 * @package    notificationscondition_sessionstart
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
function xmldb_notificationscondition_sessionstart_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2023101804) {
        // Define key fk_userid (foreign) to be added to notificationsagent_crseview.
        $table = new xmldb_table('notificationsagent_crseview');
        $key = new xmldb_key('fk_userid', XMLDB_KEY_FOREIGN, ['userid'], 'user', ['id']);

        // Launch add key fk_userid.
        $dbman->add_key($table, $key);

        // Define key fk_courseid (foreign) to be added to notificationsagent_crseview.
        $table = new xmldb_table('notificationsagent_crseview');
        $key = new xmldb_key('fk_courseid', XMLDB_KEY_FOREIGN, ['courseid'], 'course', ['id']);

        // Launch add key fk_courseid.
        $dbman->add_key($table, $key);

        // Sessionstart savepoint reached.
        upgrade_plugin_savepoint(true, 2023101804, 'notificationscondition', 'sessionstart');
    }

    return true;
}
