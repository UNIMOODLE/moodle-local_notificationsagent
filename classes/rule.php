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
require_once('notificationactionplugin.php');
require_once('notificationconditionplugin.php');

use notificationactionplugin;
use notificationconditionplugin;
use notificationplugin;
use local_notificationsagent\EvaluationContext;
use moodle_url;

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
    private $courseid;

    private const PLACEHOLDERS
    = array(
        '{User_FirstName}', '{User_LastName_s}', '{User_Email}', '{User_Username}', '{User_Address}', '{Course_FullName}', '{Course_Url}'
    );
    
    /********************************
     * Life-cycle functions
     ********************************/
    /** Construct an empty Rule.
     *
     * @param $rule
     */
    public function __construct($rule) {

            $this->ruleid = $rule->ruleid;
            $this->name = format_text($rule->name);
            $this->description = format_text($rule->description);
            $this->courseid = $rule->courseid;
            //$this->assigned = $rule->assigned;
            //$this->template = $rule->template;
            //$this->status = $rule->status;

    }

    /**
     * Factory for loading a Rule from database .
     */
    public static function create_instance($id) {
        global $DB;
        $rule = $DB->get_record('notificationsagent_rule', ['ruleid' => $id]);
        if ($rule) {
            $rule = new Rule($rule);
            // TODO.
            $rule->conditions = $rule->get_conditions($id);
            $rule->exceptions = $rule->get_exceptions($id);
            $rule->actions =  $rule->get_actions($id);
        }
        return $rule;
    }

    public function save($rule) {

    }

    public static function get_rules() {
        global $DB;
        $instances = array();
        $rules = $DB->get_records('notificationsagent_rule');
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

    public function get_conditions($id) {
        global $DB;
        $this->conditions = notificationconditionplugin::create_subplugins($DB->get_records('notificationsagent_condition',
            ['ruleid' => $id, 'type'=>'condition', 'complementary' => 0]));
        return $this->conditions;
    }

    public function get_exceptions($id) {
        global $DB;
        $this->exceptions = notificationconditionplugin::create_subplugins($DB->get_records('notificationsagent_condition',
            ['ruleid' => $id, 'type'=>'condition','complementary' => 1]));
        return $this->exceptions;
    }

    public function get_actions($id) {
        global $DB;
        $this->actions = notificationactionplugin::create_subplugins($DB->get_records('notificationsagent_action',
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

    /**
     * @return mixed
     */
    public function get_courseid() {
        return $this->courseid;
    }

    /**
     * @param mixed $courseid
     */
    public function set_courseid($courseid): void {
        $this->courseid = $courseid;
    }

    public function evaluate(EvaluationContext $context): bool {
        // Evaluate conditions.
        foreach ($this->conditions as $condition) {
            $context->set_params($condition->get_parameters());
            $result = $condition->evaluate($context);
            if ($result === false) {
                return false;
            }
        }
        // Evaluate exceptions.
        foreach ($this->exceptions as $exception) {
            $context->set_params($exception->get_parameters());
            $result = $exception->evaluate($context);
            if ($result === true) {
                return false;
            }
        }
        return true;
    }

    /**
     * Return list of placeholders.
     *
     * @return array
     */
    public static function get_placeholders(): array {
        return self::PLACEHOLDERS;
    }

    /**
     * Replace place holders in the template with respective content.
     *
     * @param string $template Message template.
     * @param subscription $subscription subscription instance
     * @param \stdclass $eventobj Event data
     * @param \context $context context object
     *
     * @return mixed final template string.
     */
    public function replace_placeholders($parameters, $courseid = null, $userid = null) {
        $paramstoreplace = [];
        $placeholderstoreplace = [];
        $placeholders = self::get_placeholders();

        if (!empty($userid)) {
            $user = \core_user::get_user($userid, '*', MUST_EXIST);
        }

        if (!empty($courseid)) {
            $course = get_course($courseid);
        }

        $jsonparams = json_decode($parameters);

        foreach ($jsonparams as $item) {
            foreach ($placeholders as $placeholder) {
                if (strpos($item, $placeholder) !== false) {
                    switch ($placeholder) {
                        case '{User_FirstName}':
                            $paramstoreplace[] = $user->firstname;
                            $placeholderstoreplace[] = $placeholder;

                        case '{User_LastName_s}':
                            $paramstoreplace[] = $user->lastname;
                            $placeholderstoreplace[] = $placeholder;

                        case '{User_Email}':
                            $paramstoreplace[] = $user->email;
                            $placeholderstoreplace[] = $placeholder;

                        case '{User_Username}':
                            $paramstoreplace[] = $user->username;
                            $placeholderstoreplace[] = $placeholder;

                        case '{User_Address}':
                            $paramstoreplace[] = $user->address;
                            $placeholderstoreplace[] = $placeholder;

                        case '{Course_FullName}':
                            $paramstoreplace[] = $course->fullname;
                            $placeholderstoreplace[] = $placeholder;

                        case '{Course_Url}':
                            $paramstoreplace[] = new moodle_url('/course/view.php', [
                                'id' => $courseid,
                            ]);
                            $placeholderstoreplace[] = $placeholder;
                    }
                }
            }
        }

        $humanvalue = str_replace($placeholderstoreplace, $paramstoreplace, $parameters);

        return $humanvalue;
    }
}
