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

/**
 * Abstract class representing a notification condition plugin.
 *
 * This class should be extended to implement the necessary logic
 * for evaluating conditions specific to notification plugins.
 *
 */
abstract class notificationconditionplugin extends notificationplugin {

    /**
     * Get the type of the function.
     *
     * @return string
     */
    public function get_type() {
        return parent::TYPE_CONDITION;
    }

    /**
     * Get the title of the condition.
     *
     * @return string The title of the condition.
     */
    abstract public function get_title();

    /**
     * Get the elements of the condition.
     *
     * @return array The elements of the condition.
     */
    abstract public function get_elements();

    /**
     * Get the subtype of the condition.
     *
     * @return string The subtype of the condition.
     */
    abstract public function get_subtype();

    /**
     * Return the module identifier specified in the condition
     *
     * @return int|null $cmid Course module id or null
     */
    public function get_cmid() {
        $cmid = null;
        $parameters = $this->get_parameters();
        if (!empty($parameters)) {
            $cmid = json_decode($this->get_parameters(), true)[self::UI_ACTIVITY] ?? null;
        }
        return $cmid;
    }

    /**
     * Checks whether the user has the capability to use the condition within a given context.
     *
     * @param \context $context The context to check the capability in.
     *
     * @return bool True if the user has the capability, false otherwise.
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

    /**
     * Estimate the next time the condition will be true.
     *
     * @param evaluationcontext $context Context for the condition evaluation.
     *
     * @return mixed Estimated time as a Unix timestamp or null if cannot be estimated.
     */
    abstract public function estimate_next_time(evaluationcontext $context);

    /**
     * Create subplugins from the given records.
     *
     * @param array $records The array of records to create subplugins from.
     *
     * @return array The array of created subplugins.
     */
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

    /**
     * Create a subplugin based on the given ID.
     *
     * @param int $id description
     *
     * @return array
     */
    public static function create_subplugin($id) {
        global $DB;
        // Find type of subplugin.
        $record = $DB->get_record('notificationsagent_condition', ['id' => $id]);
        $subplugins = self::create_subplugins([$record]);
        return $subplugins[$id];
    }

    /**
     * Save data and set notifications for students.
     *
     * @param string    $action
     * @param int       $idname
     * @param \stdClass $data
     * @param int       $complementary
     * @param array     $arraytimer
     * @param array     $students
     *
     * @return void
     */
    public function save($action, $idname, $data, $complementary, &$arraytimer, $students = []) {
        $courseid = $data->courseid;

        $dataplugin = new \stdClass();
        $dataplugin->ruleid = $this->rule->get_id();
        $dataplugin->pluginname = get_called_class()::NAME;
        $dataplugin->type = $this->get_type();
        $dataplugin->complementary = $complementary;
        $dataplugin->parameters = $this->convert_parameters($idname, $data);
        $dataplugin->cmid = $this->get_cmid();

        if (parent::insert_update_delete($action, $idname, $dataplugin)) {
            $contextevaluation = new evaluationcontext();
            $contextevaluation->set_courseid($courseid);
            $contextevaluation->set_params($this->get_parameters());
            $contextevaluation->set_timeaccess(time());
            $contextevaluation->set_complementary($complementary);

            if (!$this->is_generic()) {
                foreach ($students as $student) {
                    $contextevaluation->set_userid($student->id);
                    $cache = $this->estimate_next_time($contextevaluation);
                    notificationsagent::set_timer_cache(
                        $student->id, $data->courseid, $cache, $dataplugin->pluginname, $dataplugin->id
                    );
                    if (isset($arraytimer[$student->id])) {
                        if ($arraytimer[$student->id]['timer'] < $cache) {
                            $arraytimer[$student->id]['timer'] = $cache;
                            $arraytimer[$student->id]['conditionid'] = $dataplugin->id;
                        }
                        continue;
                    }
                    $arraytimer[$student->id]['timer'] = $cache;
                    $arraytimer[$student->id]['conditionid'] = $dataplugin->id;
                }
            } else {
                $cache = $this->estimate_next_time($contextevaluation);
                notificationsagent::set_timer_cache(
                    notificationsagent::GENERIC_USERID, $data->courseid, $cache, $dataplugin->pluginname, $dataplugin->id
                );
                if (isset($arraytimer[notificationsagent::GENERIC_USERID])) {
                    if ($arraytimer[notificationsagent::GENERIC_USERID]['timer'] < $cache) {
                        $arraytimer[notificationsagent::GENERIC_USERID]['timer'] = $cache;
                        $arraytimer[notificationsagent::GENERIC_USERID]['conditionid'] = $dataplugin->id;
                    }
                } else {
                    $arraytimer[notificationsagent::GENERIC_USERID]['timer'] = $cache;
                    $arraytimer[notificationsagent::GENERIC_USERID]['conditionid'] = $dataplugin->id;
                }
            }
        }
    }

    /**
     * Update any necessary ids and json parameters in the database.
     * It is called near the completion of course restoration.
     *
     * @param string       $restoreid Restore identifier
     * @param integer      $courseid  Course identifier
     * @param \base_logger $logger    Logger if any warnings
     *
     * @return bool|void False if restore is not required
     */
    public function update_after_restore($restoreid, $courseid, \base_logger $logger) {
        global $DB;

        $oldcmid = json_decode($this->get_parameters())->{self::UI_ACTIVITY};
        $rec = \restore_dbops::get_backup_ids_record($restoreid, 'course_module', $oldcmid);

        if (!$rec || !$rec->newitemid) {
            // If we are on the same course (e.g. duplicate) then we can just
            // use the existing one.
            if ($DB->record_exists('course_modules', ['id' => $oldcmid, 'course' => $courseid])) {
                return false;
            }
            // Otherwise it's a warning.
            $logger->process(
                'Subplugin (' . $this->get_pluginname() . ')
                has an item on condition that was not restored',
                \backup::LOG_WARNING
            );
        } else {
            $newparameters = json_decode($this->get_parameters());
            $newparameters->{self::UI_ACTIVITY} = $rec->newitemid;
            $newparameters = json_encode($newparameters);

            $record = new \stdClass();
            $record->id = $this->get_id();
            if (!is_null($this->get_cmid())) {
                $record->cmid = $rec->newitemid;
            }
            $record->parameters = $newparameters;

            $DB->update_record('notificationsagent_condition', $record);
        }
    }
}
