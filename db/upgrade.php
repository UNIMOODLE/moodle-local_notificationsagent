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
 * Execute local_notificationsagent upgrade from the given old version.
 *
 * @param int $oldversion
 *
 * @return bool
 */
function xmldb_local_notificationsagent_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2023112100) {
        // Define table notificationsagent_context to be created.
        $table = new xmldb_table('notificationsagent_context');

        // Adding fields to table notificationsagent_context.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('ruleid', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, null);
        $table->add_field('contextid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('objectid', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table notificationsagent_context.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table for notificationsagent_context.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Notificationsagent savepoint reached.
        upgrade_plugin_savepoint(true, 2023112100, 'local', 'notificationsagent');
    }

    if ($oldversion < 2023112100) {
        // Define field cmid to be added to notificationsagent_condition.
        $table = new xmldb_table('notificationsagent_condition');
        $field = new xmldb_field('cmid', XMLDB_TYPE_INTEGER, '11', null, null, null, null, 'parameters');

        // Conditionally launch add field cmid.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Notificationsagent savepoint reached.
        upgrade_plugin_savepoint(true, 2023112100, 'local', 'notificationsagent');
    }

    if ($oldversion < 2023112900) {
        // Define table notificationsagent_rule to be created.
        $table = new xmldb_table('notificationsagent_rule');

        // Adding fields to table notificationsagent_rule.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('name', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('description', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('status', XMLDB_TYPE_INTEGER, '1', null, null, null, '0');
        $table->add_field('createdby', XMLDB_TYPE_INTEGER, '11', null, null, null, null);
        $table->add_field('createdat', XMLDB_TYPE_INTEGER, '11', null, null, null, null);
        $table->add_field('shared', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1');
        $table->add_field('defaultrule', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1');
        $table->add_field('template', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('forced', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1');
        $table->add_field('timesfired', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, '1');
        $table->add_field('runtime', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, '86400');

        // Adding keys to table notificationsagent_rule.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table for notificationsagent_rule.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Notificationsagent savepoint reached.
        upgrade_plugin_savepoint(true, 2023112900, 'local', 'notificationsagent');
    }

    if ($oldversion < 2024020600) {
        // Define field conditionid to be added to notificationsagent_triggers.
        $table = new xmldb_table('notificationsagent_triggers');
        $field = new xmldb_field('conditionid', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, null, 'ruleid');

        // Conditionally launch add field conditionid.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define key fk_conditionid (foreign) to be added to notificationsagent_triggers.
        $table = new xmldb_table('notificationsagent_triggers');
        $key = new xmldb_key('fk_conditionid', XMLDB_KEY_FOREIGN, ['conditionid'], 'notificationsagent_condition', ['id']);

        // Launch add key fk_conditionid.
        $dbman->add_key($table, $key);

        // Notificationsagent savepoint reached.
        upgrade_plugin_savepoint(true, 2024020600, 'local', 'notificationsagent');
    }

    if ($oldversion < 2024020602) {
        // Define field ruleoff to be added to notificationsagent_triggers.
        $table = new xmldb_table('notificationsagent_triggers');
        $field = new xmldb_field('ruleoff', XMLDB_TYPE_INTEGER, '11', null, null, null, null, 'startdate');

        // Conditionally launch add field ruleoff.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Notificationsagent savepoint reached.
        upgrade_plugin_savepoint(true, 2024020602, 'local', 'notificationsagent');
    }

    if ($oldversion < 2024020604) {
        // Define key fk_ruleid (foreign) to be added to notificationsagent_triggers.
        $table = new xmldb_table('notificationsagent_triggers');
        $key = new xmldb_key('fk_ruleid', XMLDB_KEY_FOREIGN, ['ruleid'], 'notificationsagent_rule', ['id']);

        // Launch add key fk_ruleid.
        $dbman->add_key($table, $key);

        // Define key fk_conditionid (foreign) to be added to notificationsagent_cache.
        $table = new xmldb_table('notificationsagent_cache');
        $key = new xmldb_key('fk_conditionid', XMLDB_KEY_FOREIGN, ['conditionid'], 'notificationsagent_condition', ['id']);

        // Launch add key fk_conditionid.
        $dbman->add_key($table, $key);

        // Define key fk_ruleid (foreign) to be added to notificationsagent_report.
        $table = new xmldb_table('notificationsagent_report');
        $key = new xmldb_key('fk_ruleid', XMLDB_KEY_FOREIGN, ['ruleid'], 'notificationsagent_rule', ['id']);

        // Launch add key fk_ruleid.
        $dbman->add_key($table, $key);

        // Define key fk_actionid (foreign) to be added to notificationsagent_report.
        $table = new xmldb_table('notificationsagent_report');
        $key = new xmldb_key('fk_actionid', XMLDB_KEY_FOREIGN, ['actionid'], 'notificationsagent_action', ['id']);

        // Launch add key fk_actionid.
        $dbman->add_key($table, $key);

        // Define key fk_ruleid (foreign) to be added to notificationsagent_context.
        $table = new xmldb_table('notificationsagent_context');
        $key = new xmldb_key('fk_ruleid', XMLDB_KEY_FOREIGN, ['ruleid'], 'notificationsagent_rule', ['id']);

        // Launch add key fk_ruleid.
        $dbman->add_key($table, $key);

        // Define key fk_ruleid (foreign) to be added to notificationsagent_launched.
        $table = new xmldb_table('notificationsagent_launched');
        $key = new xmldb_key('fk_ruleid', XMLDB_KEY_FOREIGN, ['ruleid'], 'notificationsagent_rule', ['id']);

        // Launch add key fk_ruleid.
        $dbman->add_key($table, $key);

        // Notificationsagent savepoint reached.
        upgrade_plugin_savepoint(true, 2024020604, 'local', 'notificationsagent');
    }

    return true;
}
