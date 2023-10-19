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
use notificationsagent\notificationsagent;

class Rule {

    private $id;
    private $name;
    private $description;
    private $conditions;
    private $exceptions;
    private $actions;
    private $record;
    private $assigned;
    private $template;
    private $status;
    private $courseid;
    private $createdby;

    private const PLACEHOLDERS
    = array(
        'User_FirstName', 'User_LastName', 'User_Email', 'User_Username', 'User_Address', 'Course_FullName', 'Course_Url',
        'Teacher_FirstName', 'Teacher_LastName', 'Teacher_Email', 'Teacher_Username', 'Teacher_Address', 'Current_time'
    );

    /** @var int Status of a rule that is enabled */
    public const RESUME_RULE = 0;
    /** @var int The status of a rule that is disabled */
    public const PAUSE_RULE = 1;

    /** Construct an empty Rule.
     *
     * @param $rule
     */
    public function __construct($rule) {
            $this->id = $rule->id;
            $this->name = $rule->name;
            $this->description = $rule->description;
            $this->courseid = $rule->courseid;
            $this->createdby = $rule->createdby;
            $this->status = $rule->status;
    }

    /**
     * Factory for loading a Rule from database .
     */
    public static function create_instance($id) {
        global $DB;
        $rule = $DB->get_record('notificationsagent_rule', ['id' => $id]);
        if ($rule) {
            $rule = new Rule($rule);
            // TODO.
            $rule->conditions = $rule->get_conditions();
            $rule->exceptions = $rule->get_exceptions();
            $rule->actions = $rule->get_actions();
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
            $instances[] = self::create_instance($rule->id);
        }
        return $instances;
    }

    /**
     * @return mixed
     */
    public function get_id() {
        return $this->id;
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

    public function get_conditions() {
        global $DB;
        $this->conditions = notificationconditionplugin::create_subplugins($DB->get_records('notificationsagent_condition',
            ['ruleid' => $this->id, 'type' => 'condition', 'complementary' => 0]));
        return $this->conditions;
    }

    public function get_exceptions() {
        global $DB;
        $this->exceptions = notificationconditionplugin::create_subplugins($DB->get_records('notificationsagent_condition',
            ['ruleid' => $this->id, 'type' => 'condition', 'complementary' => 1]));
        return $this->exceptions;
    }

    public function get_actions() {
        global $DB;
        $this->actions = notificationactionplugin::create_subplugins($DB->get_records('notificationsagent_action',
            ['ruleid' => $this->id, 'type' => 'action']));
        return $this->actions;
    }

    public function delete_conditions($id) {
        global $DB;
        $this->conditions = $DB->delete_records('notificationsagent_condition', ['ruleid' => $id]);
            return $this->conditions;
    }

    public function delete_actions($id) {
        global $DB;
        $this->actions = $DB->delete_records('notificationsagent_action', ['ruleid' => $id]);
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
     * @param mixed $id
     */
    public function set_id($id): void {
        $this->id = $id;
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

    /**
     * @return mixed
     */
    public function get_createdby() {
        return $this->createdby;
    }

    /**
     * @param mixed $createdby
     */
    public function set_createdby($createdby): void {
        $this->createdby = $createdby;
    }

    public function evaluate(EvaluationContext $context): bool {
        // Evaluate conditions.
        foreach ($this->conditions as $condition) {
            $context->set_params($condition->get_parameters());
            $result = $condition->evaluate($context);
            if ($result === false) {
                /* Las condiciones temporales poseen un método adicional que permite calcular y devolver
                la próxima fecha en la que se cumplirán estas condiciones. Esta fecha se almacena en la
                tabla de triggers para que pueda ser consultada en cualquier momento sin tener
                que recalcularla repetidamente, lo que mejora la eficiencia y rendimiento del
                sistema de notificaciones.
                */
                $timetrigger = $condition->estimate_next_time($context);
                // Keep record in trigger.
                // Event driven conditions return a null timetrigger.
                if (!empty($timetrigger)) {
                    notificationsagent::set_time_trigger($this->get_id(),
                        $context->get_userid(),
                        $context->get_courseid(),
                        $timetrigger);
                }
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
    public function replace_placeholders($parameters, $courseid = null, $userid = null, $rule = null) {
        $paramstoreplace = [];
        $placeholderstoreplace = [];
        $placeholders = self::get_placeholders();
        $idcreatedby = $rule->get_createdby();

        if (!empty($userid)) {
            $user = \core_user::get_user($userid, '*', MUST_EXIST);
        }

        if (!empty($courseid)) {
            $course = get_course($courseid);
        }

        if (!empty($idcreatedby)) {
            $createdbyuser = \core_user::get_user($idcreatedby, '*', MUST_EXIST);
        }

        $jsonparams = json_decode($parameters);

        foreach ($jsonparams as $item) {
            foreach ($placeholders as $placeholder) {
                if (strpos($item, $placeholder) !== false) {
                    switch ($placeholder) {
                        case 'User_FirstName':
                            $paramstoreplace[] = $user->firstname;
                            $placeholderstoreplace[] = '{' . $placeholder . '}';

                        case 'User_LastName':
                            $paramstoreplace[] = $user->lastname;
                            $placeholderstoreplace[] = '{' . $placeholder . '}';

                        case 'User_Email':
                            $paramstoreplace[] = $user->email;
                            $placeholderstoreplace[] = '{' . $placeholder . '}';

                        case 'User_Username':
                            $paramstoreplace[] = $user->username;
                            $placeholderstoreplace[] = '{' . $placeholder . '}';

                        case 'User_Address':
                            $paramstoreplace[] = $user->address;
                            $placeholderstoreplace[] = '{' . $placeholder . '}';

                        case 'Course_FullName':
                            $paramstoreplace[] = $course->fullname;
                            $placeholderstoreplace[] = '{' . $placeholder . '}';

                        case 'Course_Url':
                            $paramstoreplace[] = new moodle_url('/course/view.php', [
                                'id' => $courseid,
                            ]);
                            $placeholderstoreplace[] = '{' . $placeholder . '}';

                        case 'Teacher_FirstName':
                            $paramstoreplace[] = $createdbyuser->firstname;
                            $placeholderstoreplace[] = '{' . $placeholder . '}';

                        case 'Teacher_LastName':
                            $paramstoreplace[] = $createdbyuser->lastname;
                            $placeholderstoreplace[] = '{' . $placeholder . '}';

                        case 'Teacher_Email':
                            $paramstoreplace[] = $createdbyuser->email;
                            $placeholderstoreplace[] = '{' . $placeholder . '}';

                        case 'Teacher_Username':
                            $paramstoreplace[] = $createdbyuser->username;
                            $placeholderstoreplace[] = '{' . $placeholder . '}';

                        case 'Teacher_Address':
                            $paramstoreplace[] = $createdbyuser->address;
                            $placeholderstoreplace[] = '{' . $placeholder . '}';

                        case 'Current_time':
                            $paramstoreplace[] = date('d-m-Y h:i:s', time());
                            $placeholderstoreplace[] = '{' . $placeholder . '}';
                    }
                }
            }
        }

        $humanvalue = str_replace($placeholderstoreplace, $paramstoreplace, $parameters);

        return $humanvalue;
    }

    /**
     * Hook to execute before deleting a rule
     *
     * @return void
     */
    protected function before_delete() {
        $this->delete_conditions($this->get_id());
        $this->delete_actions($this->get_id());
    }

    /**
     * Delete rule entry from the database
     *
     * @return void
     */
    public function delete() {
        global $DB;

        self::before_delete($this->get_id());

        $DB->delete_records('notificationsagent_rule', ['id' => $this->get_id()]);
    }
}
