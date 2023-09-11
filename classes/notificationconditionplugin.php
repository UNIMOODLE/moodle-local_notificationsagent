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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
require_once('notificationplugin.php');
abstract class notificationconditionplugin extends notificationplugin {

    public function get_type() {
        return parent::CAT_CONDITION;
    }
    abstract public function get_title();

    abstract public function get_elements();

    abstract public function get_subtype();

    /** Evaluates this condition using the context variables or the system's state and the complementary flag.
     *
     * @param \EvaluationContext $context |null collection of variables to evaluate the condition.
     *                                    If null the system's state is used.
     *
     * @return bool true if the condition is true, false otherwise.
     */
    public abstract function evaluate(\EvaluationContext $context): bool;

     /** Estimate next time when this condition will be true. */
    public abstract function estimate_next_time();
}
