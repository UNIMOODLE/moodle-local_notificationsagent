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
require_once('notificationplugin.php');

use notificationplugin;

class Rule {

    private $ruleid;
    private $name;
    private $description;
    private $conditions; //= array();
    private $exceptions;
    private $actions;
    private $record;
    private $assigned;
    private $template;
    private $status;
    /********************************
     * Life-cycle functions
     ********************************/
    /** Construct an empty Rule.
     *
     * @param $rule
     */
    public function __construct($rule) {

            $this->ruleid = $rule->ruleid;
            $this->name = $rule->name;
            $this->description = $rule->description;
            //$this->assigned = $rule->assigned;
            //$this->template = $rule->template;
            //$this->status = $rule->status;

    }

    /**
     * Factory for loading a Rule from database .
     */
    public static function create_instance($id) {
        global $DB;
        $rule = $DB->get_record('notifications_rule', ['ruleid' => $id]);
        if ($rule) {
            $rule = new Rule($rule);
            // TODO.
            $rule->conditions = $rule->get_conditions($id);
            $rule->exceptions = $rule->get_exceptions($id);
            $rule->actions =  $rule->get_actions($id);
        }
        return $rule;
    }

    public function save() {

    }

    public static function get_rules(){
        global $DB;
        $instances = array();
        $rules = $DB->get_records('notifications_rule');
        foreach ($rules as $rule) {
            $instances[] = Rule::create_instance($rule->ruleid);
        }
        return $instances;
    }

    /**
     * @return mixed
     */
    public function get_id() {
        return $this->ruleid;
    }

    /**
     * @return mixed
     */
    public function get_name() {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function set_name($name): void {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function get_description() {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function set_description($description): void {
        $this->description = $description;
    }

    public function get_conditions($id){
        global $DB;
        $this->conditions = notificationplugin::create_subplugins($DB->get_records('notifications_rule_plugins',
            ['ruleid' => $id, 'type'=>'condition', 'complementary' => 0]));
        return $this->conditions;
    }

    public function get_exceptions($id){
        global $DB;
        $this->exceptions = notificationplugin::create_subplugins($DB->get_records('notifications_rule_plugins',
            ['ruleid' => $id, 'type'=>'condition','complementary' => 1]));
        return $this->exceptions;
    }

    public function get_actions($id){
        global $DB;
        $this->actions = notificationplugin::create_subplugins($DB->get_records('notifications_rule_plugins',
            ['ruleid' => $id, 'type'=>'action']));
        return $this->actions;
    }

    /**
     * @param mixed $conditions
     */
    public function set_conditions($conditions): void {
        $this->conditions = $conditions;
    }

    /**
     * @param mixed $exceptions
     */
    public function set_exceptions($exceptions): void {
        $this->exceptions = $exceptions;
    }

    /**
     * @param mixed $actions
     */
    public function set_actions($actions): void {
        $this->actions = $actions;
    }

    /**
     * @return mixed
     */
    public function get_assigned() {
        return $this->assigned;
    }

    /**
     * @param mixed $assigned
     */
    public function set_assigned($assigned): void {
        $this->assigned = $assigned;
    }

    /**
     * @return mixed
     */
    public function get_template() {
        return $this->template;
    }

    /**
     * @param mixed $template
     */
    public function set_template($template): void {
        $this->template = $template;
    }

    /**
     * @return mixed
     */
    public function get_status() {
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function set_status($status): void {
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function get_ruleid() {
        return $this->ruleid;
    }

    /**
     * @param mixed $ruleid
     */
    public function set_ruleid($ruleid): void {
        $this->ruleid = $ruleid;
    }


}
