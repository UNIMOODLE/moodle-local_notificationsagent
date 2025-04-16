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
 * @category   external
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_notificationsagent\external;

use external_api;
use external_function_parameters;
use external_value;
use external_warnings;
use external_single_structure;
use local_notificationsagent\rule;

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->dirroot . '/local/notificationsagent/externalcompatibility.php');

/**
 * Rule external API for rejecting the rule's sharing.
 *
 */
class unshare_rule_all extends external_api {
    /**
     * Define parameters for external function.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
                'ruleid' => new external_value(PARAM_INT, 'The rule ID', VALUE_REQUIRED),
        ]);
    }

    /**
     * Return a list of the required fields
     *
     * @param int $ruleid The rule ID
     *
     * @return array
     */
    public static function execute(int $ruleid) {
        [
                'ruleid' => $ruleid,
        ]
                = self::validate_parameters(self::execute_parameters(), [
                'ruleid' => $ruleid,
        ]);

        $result = ['warnings' => []];

        $instance = rule::create_instance($ruleid);
        if (empty($instance)) {
            try {
                throw new \moodle_exception(
                    'nosuchinstance',
                    'local_notificationsagent',
                );
            } catch (\moodle_exception $e) {
                $result['warnings'][] = [
                        'item' => 'local_notificationsagent',
                        'warningcode' => $e->errorcode,
                        'message' => $e->getMessage(),
                ];
                return $result;
            }
        }

        $context = \context_course::instance($instance->get_default_context(), IGNORE_MISSING);
        self::validate_context($context);
        try {
            if ($context && has_capability('local/notificationsagent:unshareruleall', $context)) {
                if ($instance->get_template()) {
                    $instance->reject_share_rule($instance->get_id());
                } else {
                    throw new \moodle_exception(
                        'isnotrule',
                        '',
                        '',
                        '',
                        get_string('isnotrule', 'local_notificationsagent')
                    );
                }
            } else {
                throw new \moodle_exception(
                    'nopermissions',
                    '',
                    '',
                    get_capability_string('local/notificationsagent:unshareruleall')
                );
            }
        } catch (\moodle_exception $e) {
            $result['warnings'][] = [
                    'item' => 'local_notificationsagent',
                    'itemid' => $instance->get_id(),
                    'warningcode' => $e->errorcode,
                    'message' => $e->getMessage(),
            ];
        }

        return $result;
    }

    /**
     * Describes the data returned from the external function.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure(
            [
                        'warnings' => new external_warnings(),
                ]
        );
    }
}
