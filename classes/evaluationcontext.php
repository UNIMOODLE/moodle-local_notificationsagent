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

/**
 * Context of a notification evaluation.
 */
class evaluationcontext {

    /**
     * @var int The user ID.
     */
    private $userid;

    /**
     * @var int The course ID.
     */
    private $courseid;

    /**
     * @var int The time access.
     */
    private $timeaccess;

    /**
     * @var array Additional parameters.
     */
    private $params;

    /**
     * @var bool Indicates if it is complementary.
     */
    private $iscomplementary;

    /**
     * @var int The number of times fired.
     */
    private $usertimesfired;

    /**
     * @var string The trigger condition.
     */
    private $triggercondition;

    /**
     * @var object The rule.
     */
    private $rule;

    /**
     * @var array List of conditions.
     */
    private $conditions = [];

    /**
     * @var array List of exceptions.
     */
    private $exceptions = [];
    /**
     * @var int Trigger setted timestamp.
     */
    private $startdate;

    /**
     * Get the user ID.
     *
     * @return int
     */
    public function get_userid() {
        return $this->userid;
    }

    /**
     * Set the user ID.
     *
     * @param int $userid description
     */
    public function set_userid($userid): void {
        $this->userid = $userid;
    }

    /**
     * Retrieve the course ID.
     *
     * @return int
     */
    public function get_courseid() {
        return $this->courseid;
    }

    /**
     * Set the course ID.
     *
     * @param int $courseid The course ID to be set
     */
    public function set_courseid($courseid): void {
        $this->courseid = $courseid;
    }

    /**
     * Get the value of timeaccess
     *
     * @return mixed
     */
    public function get_timeaccess() {
        return $this->timeaccess;
    }

    /**
     * Set the timeaccess property.
     *
     * @param int $timeaccess The new value for the timeaccess property
     */
    public function set_timeaccess($timeaccess): void {
        $this->timeaccess = $timeaccess;
    }

    /**
     * Get the parameters of the PHP function.
     *
     * @return mixed
     */
    public function get_params() {
        return $this->params;
    }

    /**
     * Set the parameters for the PHP function.
     *
     * @param array $params description
     */
    public function set_params($params): void {
        $this->params = $params;
    }

    /**
     * Get if it is complementary.
     *
     * @return boolean
     */
    public function is_complementary() {
        return $this->iscomplementary;
    }

    /**
     * Set the complementary flag.
     *
     * @param bool $iscomplementary The value to set the complementary flag to
     */
    public function set_complementary(bool $iscomplementary): void {
        $this->iscomplementary = $iscomplementary;
    }

    /**
     * Get the conditions.
     *
     * @return array
     */
    public function get_conditions(): array {
        return $this->conditions;
    }

    /**
     * Set the conditions.
     *
     * @param array $conditions
     */
    public function set_conditions(array $conditions): void {
        $this->conditions = $conditions;
    }

    /**
     * Get the exceptions.
     *
     * @return array
     */
    public function get_exceptions(): array {
        return $this->exceptions;
    }

    /**
     * Set the exceptions.
     *
     * @param array $exceptions The array of exceptions to set
     */
    public function set_exceptions(array $exceptions): void {
        $this->exceptions = $exceptions;
    }

    /**
     * Get the number of times the user has fired.
     *
     * @return int
     */
    public function get_usertimesfired(): int {
        return $this->usertimesfired;
    }

    /**
     * Set the usertimesfired property to the specified value.
     *
     * @param int $usertimesfired The new value for the usertimesfired property
     */
    public function set_usertimesfired(int $usertimesfired): void {
        $this->usertimesfired = $usertimesfired;
    }

    /**
     * Get the trigger condition.
     *
     * @return int
     */
    public function get_triggercondition(): int {
        return $this->triggercondition;
    }

    /**
     * Set the trigger condition.
     *
     * @param int $triggercondition The trigger condition to set
     */
    public function set_triggercondition(int $triggercondition): void {
        $this->triggercondition = $triggercondition;
    }

    /**
     * Get the rule object.
     *
     * @return object
     */
    public function get_rule(): object {
        return $this->rule;
    }

    /**
     * Set a rule for the PHP function.
     *
     * @param object $rule
     */
    public function set_rule(object $rule): void {
        $this->rule = $rule;
    }

    /**
     * Check if the context can be evaluated
     *
     * @param object $rule Rule object
     *
     * @return bool $isevaluate Is the context evaluable?
     */
    public function is_evaluate($rule) {
        $isevaluate = false;

        $record = $rule->get_launched($this);
        if (empty($record) || ($record->timesfired < $rule->get_timesfired())) {
            $isevaluate = true;
        }

        return $isevaluate;
    }

    /**
     * Get trigger setted time
     *
     * @return int
     */
    public function get_startdate(): int {
        return $this->startdate;
    }

    /**
     * Set trigger setted time
     *
     * @param int $startdate
     */
    public function set_startdate(int $startdate): void {
        $this->startdate = $startdate;
    }

}
