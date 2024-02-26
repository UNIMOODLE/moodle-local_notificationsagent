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

namespace local_notificationsagent;

use moodle_exception;
use local_notificationsagent\notificationplugin;
use local_notificationsagent\notificationsagent;
use local_notificationsagent\plugininfo\notificationsbaseinfo;
use local_notificationsagent\evaluationcontext;

abstract class notificationconditionplugin extends notificationplugin {

    public function get_type() {
        return parent::TYPE_CONDITION;
    }

    abstract public function get_title();

    abstract public function get_elements();

    abstract public function get_subtype();

    /**
     * Return the module identifier specified in the condition
     *
     * @return int|null $cmid Course module id or null
     */
    public function get_cmid() {
        return json_decode($this->get_parameters(), true)[self::UI_ACTIVITY] ?? null;
    }

    /*
     * Check whether a user has capabilty to use a condition.
     */
    abstract public function check_capability($context);

    /** Evaluates this condition using the context variables or the system's state and the complementary flag.
     *
     * @param evaluationcontext $context  |null collection of variables to evaluate the condition.
     *                                    If null the system's state is used.
     *
     * @return bool true if the condition is true, false otherwise.
     */
    abstract public function evaluate(evaluationcontext $context): bool;

    /** Estimate next time when this condition will be true. */
    abstract public function estimate_next_time(evaluationcontext $context);

    public static function create_subplugins($records) {
        $subplugins = [];
        global $DB;
        foreach ($records as $record) {
            $rule = $DB->get_record('notificationsagent_rule', ['id' => $record->ruleid]);
            $subplugin = notificationsbaseinfo::instance($rule, $record->type, $record->pluginname);
            if (!empty($subplugin)) {
                $subplugin->set_iscomplementary($record->complementary);
                $subplugin->set_pluginname($record->pluginname);
                $subplugin->set_id($record->id);
                $subplugin->set_parameters($record->parameters);
                $subplugin->set_type($record->type);
                $subplugin->set_ruleid($record->ruleid);

                $subplugins[$record->id] = $subplugin;
            }
        }
        return $subplugins;
    }

    public static function create_subplugin($id) {
        global $DB;
        // Find type of subplugin.
        $record = $DB->get_record('notificationsagent_condition', ['id' => $id]);
        $subplugins = self::create_subplugins([$record]);
        return $subplugins[$id];
    }

    public function save($idname, $data, $complementary, $students = [], &$timer = 0) {
        global $DB;

        $dataplugin = new \stdClass();
        $dataplugin->ruleid = $this->rule->get_id();
        $dataplugin->pluginname = get_called_class()::NAME;
        $dataplugin->type = $this->get_type();
        $dataplugin->complementary = $complementary;
        $dataplugin->parameters = $this->convert_parameters($idname, $data);
        $dataplugin->cmid = $this->get_cmid();
        // Insert plugin.
        if (!$dataplugin->id = $DB->insert_record('notificationsagent_condition', $dataplugin)) {
            throw new moodle_exception('errorinserting_notificationsagent_condition');
        }

        $contextevaluation = new evaluationcontext();
        $contextevaluation->set_courseid($data->courseid);
        $contextevaluation->set_params($this->get_parameters());
        $cache = $this->estimate_next_time($contextevaluation);

        if (!$this->is_generic()) {
            foreach ($students as $student) {
                notificationsagent::set_timer_cache(
                    $student->id, $data->courseid, $cache, $dataplugin->pluginname, $dataplugin->id, true
                );
                if ($timer <= $cache) {
                    $timer = $cache;
                    notificationsagent::set_time_trigger(
                        $dataplugin->ruleid, $dataplugin->id, $student->id, $data->courseid, $timer
                    );
                }
            }
        } else {
            notificationsagent::set_timer_cache(
                notificationsagent::GENERIC_USERID, $data->courseid, $cache, $dataplugin->pluginname, $dataplugin->id, true
            );
            if ($timer <= $cache) {
                $timer = $cache;
                notificationsagent::set_time_trigger(
                    $dataplugin->ruleid, $dataplugin->id, notificationsagent::GENERIC_USERID, $data->courseid, $timer
                );
            }
        }
    }

}
