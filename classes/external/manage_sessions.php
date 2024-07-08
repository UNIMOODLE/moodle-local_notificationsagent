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

use local_notificationsagent\rule;

/**
 * Rule external API for save sessions.
 *
 */
class manage_sessions extends \external_api {
    /**
     * Define parameters for external function.
     *
     * @return \external_function_parameters
     */
    public static function execute_parameters(): \external_function_parameters {
        return new \external_function_parameters([
                'sessionname' => new \external_value(PARAM_TEXT, 'The session name', VALUE_REQUIRED),
                'orderid' => new \external_value(PARAM_INT, 'Option order', VALUE_REQUIRED),
                'courseid' => new \external_value(PARAM_INT, 'Course id', VALUE_REQUIRED),
        ]);
    }

    /**
     * Return a list of the required fields
     *
     * @param string $sessionname The session name
     * @param int $orderid The rule order id
     * @param int $courseid The course id
     *
     * @return array
     */
    public static function execute($sessionname, $orderid, $courseid) {
        global $USER;
        $params = self::validate_parameters(
            self::execute_parameters(),
            [
                        "sessionname" => $sessionname,
                        "orderid" => $orderid,
                        "courseid" => $courseid,
                ]
        );
        if ($courseid != null) {
            $context = \context_course::instance($courseid);
        } else {
            $context = \context_system::instance();
        }

        if (has_capability('local/notificationsagent:managecourserule', $context)) {
            if ($orderid != -1) {
                set_user_preference($sessionname, $orderid, $USER);
            } else {
                isset($USER->preference['orderid']) ? $orderid = $USER->preference['orderid'] : '';
            }
        }

        $result['orderid'] = $orderid;
        return $result;
    }

    /**
     * Describes the data returned from the external function.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): \external_single_structure {
        return new \external_single_structure(
            [
                        'orderid' => new \external_value(PARAM_INT, 'Order id value', VALUE_REQUIRED),
                ]
        );
    }
}
