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
namespace local_notificationsagent;
/**
 * This class implements the model of rule whith conditions, exceptions, actions and triggers.
 */
define('NEVER', PHP_INT_MIN);
// UNKNOWN time is max time.
define('UNKNOWN', PHP_INT_MAX);
// ALWAYS time is null.
define('ALWAYS', null);

class Rule {

    private $id;
    private $description;
    private $conditions;
    private $exceptions;
    private $actions;
    // Next expected runtime.
    private $next;
    // Last time the rule was executed.
    private $last;
    // Report of the last evaluation.
    private $record;

    /********************************
     * Life-cycle functions
     ********************************/
    /** Construct an empty Rule.
     * @param int $id
     * @param string $description
     */
    public function __construct($id, $description) {
        $this->id = $id;
        $this->description = $description;
    }

    /**
     * Factory for loading a Rule from database.
     * TODO: Adjust for actual database schema.
     */
    public static function create_instance($id) {
        global $DB;
        $rule = $DB->get_record('assistant_rule', ['id' => $id]);
        if ($rule) {
            $rule = new Rule($rule->id, $rule->description);
            // TODO.
            // $rule->conditions = notificationplugin::create_subplugins($DB->get_records('/*TODO table*/', ['ruleid' => $id]));
            // $rule->exceptions = notificationplugin::create_subplugins($DB->get_records('/*TODO table*/', ['ruleid' => $id]));
            // $rule->actions = notificationplugin::create_subplugins($DB->get_records('/*TODO table*/', ['ruleid' => $id]));
        }
        return $rule;
    }

    public function save() {

    }

}
