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
 * External function to get the contexts a rule is assigned to.
 *
 * @package    local_notificationsagent
 * @category   external
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_notificationsagent\external;

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;
use core_external\external_warnings;
use local_notificationsagent\helper\rule_assignment;
use local_notificationsagent\rule;

/**
 * Rule external API for getting the categories and courses a rule is assigned to.
 */
class get_rule_assigned_contexts extends external_api {
    /**
     * Describes the parameters for the external function.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'ruleid' => new external_value(PARAM_INT, 'The rule ID', VALUE_REQUIRED),
        ]);
    }

    /**
     * Get the categories and courses a rule is assigned to.
     *
     * @param int $ruleid The rule ID
     * @return array
     */
    public static function execute(int $ruleid): array {
        ['ruleid' => $ruleid] = self::validate_parameters(self::execute_parameters(), [
            'ruleid' => $ruleid,
        ]);

        $result = ['category' => [], 'course' => [], 'warnings' => []];

        $instance = rule::create_instance($ruleid);
        if (empty($instance)) {
            $result['warnings'][] = [
                'item' => 'local_notificationsagent',
                'warningcode' => 'nosuchinstance',
                'message' => get_string('nosuchinstance', 'local_notificationsagent'),
            ];
            return $result;
        }

        $context = \context_course::instance($instance->get_default_context(), IGNORE_MISSING);
        if ($context) {
            self::validate_context($context);
        }

        if (!$context || !has_capability('local/notificationsagent:assignrule', $context)) {
            $result['warnings'][] = [
                'item' => 'local_notificationsagent',
                'itemid' => $instance->get_id(),
                'warningcode' => 'nopermissions',
                'message' => get_string(
                    'nopermissions',
                    'error',
                    get_capability_string('local/notificationsagent:assignrule')
                ),
            ];
            return $result;
        }

        $assigned = rule_assignment::get_assigned_contexts($instance->get_id());
        $result['category'] = array_map('intval', $assigned['category']);
        $result['course'] = array_map('intval', $assigned['course']);

        return $result;
    }

    /**
     * Describes the data returned from the external function.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'category' => new external_multiple_structure(
                new external_value(PARAM_INT, 'Assigned category ID')
            ),
            'course' => new external_multiple_structure(
                new external_value(PARAM_INT, 'Assigned course ID')
            ),
            'warnings' => new external_warnings(),
        ]);
    }
}
