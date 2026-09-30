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
 * Rule assignment helper.
 *
 * @package    local_notificationsagent
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_notificationsagent\helper;

use local_notificationsagent\notificationsagent;
use local_notificationsagent\rule;

/**
 * Manages the categories and courses a rule or template is assigned to.
 */
class rule_assignment {
    /**
     * Get the categories and courses assigned to a rule.
     *
     * @param int $ruleid Rule id
     * @return array Array with keys 'category' and 'course'
     */
    public static function get_assigned_contexts(int $ruleid): array {
        $rule = rule::create_instance($ruleid);
        return $rule->get_assignedcontext();
    }

    /**
     * Replace the categories and courses assigned to a rule.
     *
     * @param int $ruleid Rule id
     * @param array $categories Category ids
     * @param array $courses Course ids
     */
    public static function set_assigned_contexts(int $ruleid, array $categories = [], array $courses = []): void {
        global $DB;

        $instance = rule::create_instance($ruleid);
        $DB->delete_records('notificationsagent_context', ['ruleid' => $ruleid]);
        $instance->set_default_context(SITEID);

        if (!empty($categories)) {
            $paramscat = [];
            foreach ($categories as $category) {
                $paramscat[] = [
                    'ruleid' => $ruleid,
                    'contextid' => CONTEXT_COURSECAT,
                    'objectid' => $category,
                ];
            }
            $DB->insert_records('notificationsagent_context', $paramscat);
        }

        if (!empty($courses)) {
            $paramscourse = [];
            foreach ($courses as $course) {
                $paramscourse[] = [
                    'ruleid' => $ruleid,
                    'contextid' => CONTEXT_COURSE,
                    'objectid' => $course,
                ];
            }
            $DB->insert_records('notificationsagent_context', $paramscourse);
        }

        notificationsagent::invalidate_conditions_cache();
    }

    /**
     * Set the forced field of a rule if the user can force rules.
     *
     * @param int $ruleid Rule id
     * @param int $forced Value sent by the assignment modal
     */
    public static function set_forced(int $ruleid, int $forced): void {
        global $DB;

        $instance = rule::create_instance($ruleid);
        $context = \context_course::instance(SITEID);
        if (has_capability('local/notificationsagent:forcerule', $context)) {
            $request = new \stdClass();
            $request->id = $instance->get_id();
            $request->forced = !$forced ? rule::FORCED_RULE : rule::NONFORCED_RULE;

            $DB->update_record('notificationsagent_rule', $request);
        }
    }
}
