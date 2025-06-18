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
 * Notificationsagent test data generator class.
 *
 * @package    local_notificationsagent
 * @copyright  2025 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     PLANIFICACIÓN DE ENTORNOS TECNOLÓGICOS, S.L. <admon@pentec.es>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_notificationsagent_generator extends testing_data_generator {
    /** @var int Number of created rules */
    protected $numofrules = 0;


    /**
     * Create rule.
     *
     * @param stdClass $rule Rule object to will be created.
     * @return int The new rule id.
     */
    public function create_rule(stdClass $rule): int {
        $newrule = new local_notificationsagent\rule();

        if (!isset($rule->title)) {
            $rule->title = "Rule $this->numofrules";
        }

        if (!isset($rule->type)) {
            $rule->type = local_notificationsagent\rule::RULE_TYPE;
        }

        if (!isset($rule->courseid)) {
            // Create new course.
            $course = $this->create_course(['startdate' => time() - WEEKSECS, 'enddate' => time() + YEARSECS]);
            $rule->courseid = $course->id;
        }

        if (!isset($rule->timesfired)) {
            $rule->timesfired = 1;
        }

        if (!isset($rule->runtime_group)) {
            $rule->runtime_group = ['runtime_days' => 5, 'runtime_hours' => 0, 'runtime_minutes' => 0];
        }

        if (!isset($rules->createdby)) {
            // Create new user.
            $user = $this->create_user();
            $rule->createdby = $user->id;
        }

        return $newrule->create($rule);
    }

    /**
     * Create condition.
     *
     * @param  stdClass $condition The condition that will be created.
     * @return int The new condition id.
     */
    public function create_condition(stdClass $condition): int {
        global $DB;

        $newcondition = new stdClass();

        if (!isset($condition->ruleid)) {
            $newrule = new stdClass();
            $newrule->timesfired = 2;
            $condition->ruleid = $this->create_rule($newrule);
        }

        if (!isset($condition->type)) {
            $condition->type = local_notificationsagent\notificationplugin::TYPE_CONDITION;
        }

        if (!isset($condition->complementary)) {
            $condition->complementary = local_notificationsagent\notificationplugin::COMPLEMENTARY_CONDITION;
        }

        if (!isset($condition->parameters)) {
            $condition->parameters = '{"time": 300}';
        }

        if (!isset($condition->pluginname)) {
            $condition->pluginname = 'coursestart';
        }

        return $DB->insert_record('notificationsagent_condition', (array)$condition);
    }
}
