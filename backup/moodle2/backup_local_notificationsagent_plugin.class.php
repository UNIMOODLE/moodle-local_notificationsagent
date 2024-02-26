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
 * @category   string
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class backup_local_notificationsagent_plugin extends backup_local_plugin {

    protected function define_course_plugin_structure() {
        $plugin = $this->get_plugin_element(null);

        $pluginwrapper = new backup_nested_element($this->get_recommended_name());

        $rules = new backup_nested_element('rules');
        $plugin->add_child($pluginwrapper);
        $pluginwrapper->add_child($rules);

        $rule = new backup_nested_element('rule', ['id'], [
            'name', 'description', 'status', 'createdby', 'createdat', 'shared',
            'defaultrule', 'template', 'forced', 'timesfired', 'runtime',
        ]);
        $rules->add_child($rule);

        $contexts = new backup_nested_element('contexts');
        $rule->add_child($contexts);
        $context = new backup_nested_element('context', ['id'], [
            'ruleid', 'contextid', 'objectid',
        ]);
        $contexts->add_child($context);

        $conditions = new backup_nested_element('conditions');
        $rule->add_child($conditions);
        $condition = new backup_nested_element('condition', ['id'], [
            'ruleid', 'pluginname', 'type', 'parameters', 'cmid', 'complementary',
        ]);
        $conditions->add_child($condition);

        $actions = new backup_nested_element('actions');
        $rule->add_child($actions);
        $action = new backup_nested_element('action', ['id'], [
            'ruleid', 'pluginname', 'type', 'parameters',
        ]);
        $actions->add_child($action);

        $launcheds = new backup_nested_element('launcheds');
        $rule->add_child($launcheds);
        $launched = new backup_nested_element('launched', ['id'], [
            'ruleid', 'courseid', 'userid', 'timesfired', 'timecreated', 'timemodified',
        ]);
        $launcheds->add_child($launched);

        if (backup::VAR_COURSEID != SITEID) {
            // Rule source.
            $rule->set_source_sql('
                SELECT nr.*
                  FROM {notificationsagent_rule} nr
                  JOIN {notificationsagent_context} nctx ON nr.id = nctx.ruleid
                   AND nctx.contextid = ?
                 WHERE nctx.objectid = ?
            ', [
                backup_helper::is_sqlparam(CONTEXT_COURSE), backup::VAR_COURSEID,
            ]);

            // If it's the site context, we need to back up rules with course and category context.
            if ($this->task->get_courseid() == SITEID) {
                // Context source.
                $context->set_source_sql('
                    SELECT nctx.*
                      FROM {notificationsagent_rule} nr
                      JOIN {notificationsagent_context} nctx ON nr.id = nctx.ruleid
                     WHERE nr.id = ?
                ', [
                    backup::VAR_PARENTID,
                ]);
            } else {
                // Context source.
                $context->set_source_sql('
                    SELECT nctx.*
                      FROM {notificationsagent_rule} nr
                      JOIN {notificationsagent_context} nctx ON nr.id = nctx.ruleid
                       AND nctx.contextid = ?
                     WHERE nctx.objectid = ?
                       AND nr.id = ?
                ', [
                    backup_helper::is_sqlparam(CONTEXT_COURSE), backup::VAR_COURSEID, backup::VAR_PARENTID,
                ]);
            }

            // Condition source.
            $condition->set_source_sql('
                SELECT nc.*
                  FROM {notificationsagent_rule} nr
                  JOIN {notificationsagent_context} nctx ON nr.id = nctx.ruleid
                   AND nctx.contextid = ?
                  JOIN {notificationsagent_condition} nc ON nr.id = nc.ruleid
                 WHERE nctx.objectid = ?
                   AND nr.id = ?
            ', [
                backup_helper::is_sqlparam(CONTEXT_COURSE), backup::VAR_COURSEID, backup::VAR_PARENTID,
            ]);

            // Action source.
            $action->set_source_sql('
                SELECT na.*
                  FROM {notificationsagent_rule} nr
                  JOIN {notificationsagent_context} nctx ON nr.id = nctx.ruleid
                   AND nctx.contextid = ?
                  JOIN {notificationsagent_action} na ON nr.id = na.ruleid
                 WHERE nctx.objectid = ?
                   AND nr.id = ?
            ', [
                backup_helper::is_sqlparam(CONTEXT_COURSE), backup::VAR_COURSEID, backup::VAR_PARENTID,
            ]);

            // Launched source.
            $launched->set_source_sql('
                SELECT nl.*
                  FROM {notificationsagent_rule} nr
                  JOIN {notificationsagent_context} nctx ON nr.id = nctx.ruleid
                   AND nctx.contextid = ?
                  JOIN {notificationsagent_launched} nl ON nr.id = nl.ruleid
                   AND nl.courseid = ?
                 WHERE nctx.objectid = ?
                   AND nr.id = ?
            ', [
                backup_helper::is_sqlparam(CONTEXT_COURSE), backup::VAR_COURSEID,
                backup::VAR_COURSEID, backup::VAR_PARENTID,
            ]);
        }

        return $plugin;
    }
}
