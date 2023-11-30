<?php
// This file is part of Moodle - http://moodle.org/
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

defined('MOODLE_INTERNAL') || die();
require_once('notificationplugin.php');
require_once('plugininfo/notificationsbaseinfo.php');

use local_notificationsagent\plugininfo\notificationsbaseinfo;
use local_notificationsagent\EvaluationContext;
abstract class notificationconditionplugin extends notificationplugin {

    public function get_type() {
        return parent::CAT_CONDITION;
    }
    abstract public function get_title();

    abstract public function get_elements();

    abstract public function get_subtype();

    /**
     * Return the module identifier specified in the condition
     * @param object $parameters Plugin parameters
     *
     * @return int|null $cmid Course module id or null
     */
    abstract protected function get_cmid($parameters);

    /*
     * Check whether a user has capabilty to use a condition.
     */
    abstract public function check_capability($context);

    /** Evaluates this condition using the context variables or the system's state and the complementary flag.
     *
     * @param EvaluationContext $context  |null collection of variables to evaluate the condition.
     *                                    If null the system's state is used.
     *
     * @return bool true if the condition is true, false otherwise.
     */
    abstract public function evaluate(EvaluationContext $context): bool;

     /** Estimate next time when this condition will be true. */
    abstract public function estimate_next_time(EvaluationContext $context);

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

                $subplugins[] = $subplugin;
            }
        }
        return $subplugins;
    }

    public static function create_subplugin($id) {
        global $DB;
        // Find type of subplugin.
        $record = $DB->get_record('notificationsagent_condition', ['id' => $id]);
        $subplugins = self::create_subplugins([$record]);
        return $subplugins[0];
    }

    /**
     * Returns date from seconds value
     *
     * @param  mixed $inputseconds
     * @return void
     */
    public function get_human_time($inputseconds) {
        $secondsinaminute = 60;
        $secondsinhour = 60 * $secondsinaminute;
        $secondsinday = 24 * $secondsinhour;

        // Extract days.
        $days = floor($inputseconds / $secondsinday);

        // Extract hours.
        $hourseconds = $inputseconds % $secondsinday;
        $hours = floor($hourseconds / $secondsinhour);

        // Extract minutes.
        $minuteseconds = $hourseconds % $secondsinhour;
        $minutes = floor($minuteseconds / $secondsinaminute);

        // Extract the remaining seconds.
        $remainingseconds = $minuteseconds % $secondsinaminute;
        $seconds = ceil($remainingseconds);

        // Format and return.
        $timeparts = [];
        $sections = [
            get_string('card_day', 'local_notificationsagent')  => (int)$days,
            get_string('card_hour', 'local_notificationsagent') => (int)$hours,
            get_string('card_minute', 'local_notificationsagent') => (int)$minutes,
            get_string('card_second', 'local_notificationsagent') => (int)$seconds,
        ];

        foreach ($sections as $name => $value) {
            if ($value > 0) {
                $timeparts[] = $value . ' ' . $name . ($value == 1 ? '' : 's');
            }
        }

        if (empty($timeparts)) {
            $timeparts[] = 0 . ' ' . get_string('card_second', 'local_notificationsagent') . 's';
        }

        return implode(', ', $timeparts);
    }

}
