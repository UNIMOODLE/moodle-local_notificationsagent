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

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once("$CFG->dirroot/local/notificationsagent/lib.php");

use context;
use moodle_url;
use context_course;
use local_notificationsagent\notificationsagent;
use local_notificationsagent\form\editrule_form;
use local_notificationsagent\helper\helper;
use notificationscondition_ac\ac;
use stdClass;

/**
 * Class rule manages the rules for notifications in the notifications agent system.
 */
class rule {
    /** @var int $id Unique identifier for the rule */
    private $id;

    /** @var string $name Name of the rule */
    private $name;

    /** @var string $description Description of the rule */
    private $description;

    /** @var int $status Status of the rule, default is 0 */
    private $status = 0;

    /** @var int $createdby User ID of the creator */
    private $createdby;

    /** @var int $createdat Timestamp when the rule was created */
    private $createdat;

    /** @var int $shared Flag indicating if the rule is shared, default is 1 */
    private $shared = 1;

    /** @var int $defaultrule Flag indicating if it is a default rule, default is 1 */
    private $defaultrule = 1;

    /** @var int $template Flag indicating if the rule is a template, default is 1 */
    private $template = 1;

    /** @var int $forced Flag indicating if the rule is forced, default is 1 */
    private $forced = 1;

    /** @var int $assigned Flag indicating if the rule is assigned, default is 1 */
    private $assigned = 1;

    /** @var int $timesfired Number of times the rule has to be fired, default is 1 */
    private $timesfired = 1;

    /** @var int $runtime Execution runtime in seconds, default is 86400 */
    private $runtime = 86400;

    /** @var int $deleted Soft delete flag, default is 0 */
    private $deleted = 0;

    /** @var mixed $ac Access control instance or null */
    private $ac = null;

    /** @var array $conditions List of conditions for the rule */
    private $conditions = [];

    /** @var array $exceptions List of exceptions for the rule */
    private $exceptions = [];

    /** @var array $actions List of actions for the rule */
    private $actions;

    /** @var bool $isgeneric Flag indicating if the rule is generic */
    private $isgeneric;

    /** @var mixed $dataform Data form associated with the rule */
    private $dataform;

    /** @var int $action Flag indicating the action add, edit, clone */
    private $ruleaction = self::RULE_ADD;

    /** @var string Separator for placeholders */
    public const SEPARATOR = '______________________';

    /** @var string[] List of allowed placeholders in rule templates */
    private const PLACEHOLDERS
        = [
            'User_FirstName', 'User_LastName', 'User_Email', 'User_Username', 'User_Address',
        ];

    /** @var string[] List of allowed placeholders in rule templates */
    private const PLACEHOLDERS_GEN
        = [
            'Course_FullName', 'Course_Url', 'Course_Category_Name', 'Teacher_FirstName', 'Teacher_LastName',
            'Teacher_Email',
            'Teacher_Username',
            'Teacher_Address', 'Current_time',
            self::SEPARATOR, 'Follow_Link',
        ];

    /** @var array Types of a rule */
    private const RULE_TYPES
        = [
            0 => 'template',
            1 => 'rule',
        ];
    /** @var int Template type identifier */
    public const TEMPLATE_TYPE = 0;
    /** @var int Rule type identifier */
    public const RULE_TYPE = 1;

    /** @var string Rule type identifier */
    public const RULE_ADD = 'add';
    /** @var string Rule type identifier */
    public const RULE_EDIT = 'edit';
    /** @var string Rule type identifier */
    public const RULE_CLONE = 'clone';
    /** @var string Rule type identifier */
    public const RULE_ONLY = 'only';

    /** @var int Status of a rule that is enabled */
    public const RESUME_RULE = 0;
    /** @var int The status of a rule that is disabled */
    public const PAUSE_RULE = 1;
    /** @var int Status of a rule that is shared with the administrator */
    public const SHARED_RULE = 0;
    /** @var int The status of a rule that is not shared with the administrador */
    public const UNSHARED_RULE = 1;
    /** @var int Status of a rule that is forced */
    public const FORCED_RULE = 0;
    /** @var int The status of a rule that is not forced */
    public const NONFORCED_RULE = 1;
    /** @var int Minimum number of rule executions */
    public const MINIMUM_EXECUTION = 1;
    /** @var int Maximum number of rule executions */
    public const MAXIMUM_EXECUTION = 99999999999;
    /** @var int Minimum days of rule execution */
    public const MINIMUM_RUNTIME = 1;

    /**
     * Constructs a Rule object and initializes its properties from a record in the database.
     *
     * If an ID is provided, the constructor will attempt to retrieve the corresponding
     * record from the `notificationsagent_rule` table and populate the object's properties.
     * It also loads additional related information such as access controls, conditions,
     * exceptions, and actions associated with the rule.
     *
     * @param int|null $id Optional ID of the rule to load from the database.
     * @param int $type
     * @param string $ruleaction
     *
     */
    public function __construct($id = null, $type = self::RULE_TYPE, $ruleaction = self::RULE_ADD) {
        global $DB;

        if (is_null($id)) {
            $this->set_template($type);
            return;
        }

        $this->ruleaction = $ruleaction;

        // Retrieve the rule record from the database based on the provided ID.
        $rule = $DB->get_record('notificationsagent_rule', ['id' => $id]);
        $this->set_id($rule->id);

        // Set all properties if ruleaction is not RULE_CLONE.
        if (in_array($this->ruleaction, [self::RULE_ADD, self::RULE_EDIT, self::RULE_ONLY, self::RULE_CLONE])) {
            // Set the properties of the rule object.
            $this->set_name($rule->name);
            $this->set_description($rule->description);
            $this->set_status($rule->status);
            $this->set_createdby($rule->createdby);
            $this->set_createdat($rule->createdat);
            $this->set_shared($rule->shared);
            $this->set_defaultrule($rule->defaultrule);
            $this->set_template($rule->template);
            $this->set_forced($rule->forced);
            $this->set_timesfired($rule->timesfired);
            $this->set_runtime($rule->runtime);
        }

        // Set load additional rule details if ruleaction is not RULE_ONLY.
        if ($this->ruleaction != self::RULE_ONLY) {
            $this->load_ac(); // Load access control settings.
            $this->load_conditions(); // Load rule conditions.
            $this->load_exceptions(); // Load rule exceptions.
            $this->load_actions(); // Load rule actions.
            $this->is_generic(); // Check if the rule is generic.
            $this->load_dataform(); // Load the data form associated with the rule.
        }
    }

    /**
     * A function to convert the object properties into a record.
     *
     * @return object
     */
    public function to_record() {
        $record = [
            'id' => $this->get_id(),
            'name' => $this->get_name(),
            'description' => $this->get_description(),
            'status' => $this->get_status(),
            'createdby' => $this->get_createdby(),
            'createdat' => $this->get_createdat(),
            'shared' => $this->get_shared(),
            'defaultrule' => $this->get_defaultrule(),
            'template' => $this->get_template(),
            'forced' => $this->get_forced(),
            'timesfired' => $this->get_timesfired(),
            'runtime' => $this->get_runtime(),
        ];

        return (object) $record;
    }

    /**
     * Creates a new rule instance.
     *
     * If an ID is provided, it will initialize the rule object with data from the database.
     * If no ID is provided, it will create a new, empty rule object.
     *
     * @param int|null $id Optional ID of the rule to load from the database.
     *
     * @return rule The newly created rule object.
     */
    public static function create_instance($id = null) {
        global $DB;
        if (empty($id)) {
            return null;
        }
        if ($DB->record_exists('notificationsagent_rule', ['id' => $id])) {
            return new rule($id);
        }

        return null;
    }

    /**
     * Get the rules from index view
     *
     * @param context $context context object
     * @param int $courseid course id
     * @param int $orderid if of order
     *
     * @return array $instances Rule object
     */
    public static function get_rules_index($context, $courseid, $orderid = null) {
        $rules = [];
        $instances = [];

        if ($courseid == SITEID && has_capability('local/notificationsagent:managesiterule', $context)) {
            $rules = [...$rules, ...self::get_shared_rules()];
        }

        if (has_capability('local/notificationsagent:manageownrule', $context)) {
            $rules = [...$rules, ...self::get_owner_rules_by_course($courseid)];
        }
        if (has_capability('moodle/category:viewhiddencategories', $context)) {
            $forcedrules = self::get_course_rules_forced($courseid);
            if (
                has_capability('local/notificationsagent:viewcourserule', $context)
                || has_capability(
                    'local/notificationsagent:managecourserule',
                    $context
                )
            ) {
                $rules = [...$rules, ...self::get_course_rules($courseid, true, null, false),
                    ...$forcedrules,
                ];
            }
            if (has_capability('local/notificationsagent:manageallrule', $context)) {
                $rules = [...$rules, ...self::get_course_rules($courseid, false, null, false),
                    ...$forcedrules,
                ];
            }
        }
        $rules = array_unique($rules, SORT_REGULAR);

        if (!empty($rules)) {
            foreach ($rules as $rule) {
                $instances[] = self::create_instance($rule->id);
            }
        }
        $order = 0;
        if ($orderid) {
            switch ($orderid) {
                case 1:
                    $orderstring = 'status';
                    break;
                case 2:
                    $orderstring = 'status';
                    $order = 1;
                    break;
                case 3:
                    $orderstring = 'forced';
                    break;
                case 4:
                    $orderstring = 'shared';
                    break;
                case 5:
                    $orderstring = 'broken';
                    break;
                case 6:
                    $orderstring = 'template';
                    $order = 1;
                    break;
                case 7:
                    $orderstring = 'template';
                    break;
            }
            $instances = self::order_rules_by_field($instances, $orderstring, $order, $courseid);
        }
        return $instances;
    }

    /**
     * Get the rules from assign view
     *
     * @param context $context context object
     * @param integer $courseid course id
     *
     * @return array $instances Rule object
     */
    public static function get_rules_assign($context, $courseid) {
        $rules = [];
        $instances = [];

        if ($courseid != SITEID && has_capability('local/notificationsagent:managecourserule', $context)) {
            $rules = array_unique([...self::get_owner_rules(), ...self::get_course_rules($courseid, true)], SORT_REGULAR);
        }

        if (!empty($rules)) {
            foreach ($rules as $rule) {
                $instances[] = self::create_instance($rule->id);
            }
        }

        return $instances;
    }

    /**
     * Retrieves the ID of the current rule.
     *
     * @return mixed The ID of the rule.
     */
    public function get_id() {
        return $this->id;
    }

    /**
     * Check if the object is new by checking if the id is null.
     *
     * @return bool
     */
    private function is_new() {
        return is_null($this->id) || $this->ruleaction == self::RULE_CLONE;
    }

    /**
     * Retrieves the name of the rule.
     *
     * @return mixed The name of the rule.
     */
    public function get_name() {
        return $this->name;
    }

    /**
     * Sets the name of the rule.
     *
     * @param mixed $name The new name for the rule.
     */
    public function set_name($name): void {
        $this->name = $name;
    }

    /**
     * Retrieves the description of the rule.
     *
     * @return mixed The description of the rule.
     */
    public function get_description() {
        return $this->description;
    }

    /**
     * load array data for form
     */
    public function load_dataform(): void {
        $ruleaction = $this->ruleaction == self::RULE_CLONE ? editrule_form::FORM_JSON_ACTION_INSERT
            : editrule_form::FORM_JSON_ACTION_UPDATE;

        $data = [];
        $data["title"] = $this->get_name();
        $data["timesfired"] = $this->get_timesfired();
        $runtime = $this->get_runtime_format();
        $data["runtime_group[runtime_days]"] = $runtime["days"];
        $data["runtime_group[runtime_hours]"] = $runtime["hours"];
        $data["runtime_group[runtime_minutes]"] = $runtime["minutes"];
        $this->dataform = $data;

        if ($this->get_ac()) {
            $dataformac = $this->get_ac()->load_dataform();
            $this->dataform = array_merge($this->dataform, $dataformac);
        }

        $jsoncondition = [];
        foreach ($this->get_conditions() as $condition) {
            $dataformconditions = $condition->load_dataform();
            $this->dataform = array_merge($this->dataform, $dataformconditions);
            $jsoncondition[$condition->get_id()] = ["pluginname" => $condition->get_subtype(), "action" => $ruleaction];
        }
        $this->dataform = array_merge($this->dataform, [editrule_form::FORM_JSON_CONDITION => json_encode($jsoncondition)]);

        $jsonexception = [];
        foreach ($this->get_exceptions() as $exception) {
            $dataformexceptions = $exception->load_dataform();
            $this->dataform = array_merge($this->dataform, $dataformexceptions);
            $jsonexception[$exception->get_id()] = ["pluginname" => $exception->get_subtype(), "action" => $ruleaction];
        }
        $this->dataform = array_merge($this->dataform, [editrule_form::FORM_JSON_EXCEPTION => json_encode($jsonexception)]);

        $jsonactions = [];
        foreach ($this->get_actions() as $action) {
            $dataformactions = $action->load_dataform();
            $this->dataform = array_merge($this->dataform, $dataformactions);
            $jsonactions[$action->get_id()] = ["pluginname" => $action->get_subtype(), "action" => $ruleaction];
        }
        $this->dataform = array_merge($this->dataform, [editrule_form::FORM_JSON_ACTION => json_encode($jsonactions)]);
    }

    /**
     * load array data for export
     */
    public function load_dataexport(): array {
        $data = [];
        $data["title"] = $this->get_name();
        $data["description"] = $this->get_description();
        $data["type"] = $this->get_template();
        $data["timesfired"] = $this->get_timesfired();
        $runtime = $this->get_runtime_format();
        $data["runtime_group"]["runtime_days"] = $runtime["days"];
        $data["runtime_group"]["runtime_hours"] = $runtime["hours"];
        $data["runtime_group"]["runtime_minutes"] = $runtime["minutes"];

        if ($this->get_ac()) {
            $data = [...$data, ...$this->get_ac()->load_dataform()];
        }

        $jsoncondition = [];
        foreach ($this->get_conditions() as $condition) {
            $data = [...$data, ...$condition->load_dataform()];
            $jsoncondition[$condition->get_id()] = [
                "pluginname" => $condition->get_subtype(),
                "action" => editrule_form::FORM_JSON_ACTION_INSERT,
            ];
        }
        $data[editrule_form::FORM_JSON_CONDITION] = json_encode($jsoncondition);

        $jsonexception = [];
        foreach ($this->get_exceptions() as $exception) {
            $data = array_merge($data, $exception->load_dataform());
            $jsonexception[$exception->get_id()] = [
                "pluginname" => $exception->get_subtype(),
                "action" => editrule_form::FORM_JSON_ACTION_INSERT,
            ];
        }
        $data[editrule_form::FORM_JSON_EXCEPTION] = json_encode($jsonexception);

        $jsonactions = [];
        foreach ($this->get_actions() as $action) {
            $data = array_merge($data, $action->load_dataform());
            $jsonactions[$action->get_id()] = [
                "pluginname" => $action->get_subtype(),
                "action" => editrule_form::FORM_JSON_ACTION_INSERT,
            ];
        }
        $data[editrule_form::FORM_JSON_ACTION] = json_encode($jsonactions);

        return $data;
    }

    /**
     * Get validation from each subplugin
     *
     * @param int $courseid
     */
    public function validation($courseid): bool {
        foreach ($this->get_conditions_to_evaluate() as $condition) {
            if (!$condition->validation($courseid)) {
                return false;
            }
        }

        foreach ($this->get_exceptions() as $exception) {
            if (!$exception->validation($courseid)) {
                return false;
            }
        }

        foreach ($this->get_actions() as $action) {
            if (!$action->validation($courseid)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Retrieves the data form array.
     *
     * This method returns the array representing the data form which
     * may contain various settings and values associated with the rule.
     *
     * @return array The data form array.
     */
    public function get_dataform() {
        return $this->dataform;
    }

    /**
     * Set the description of the object.
     *
     * @param string $description The new description
     *
     * @return void
     */
    public function set_description($description): void {
        $this->description = $description;
    }

    /**
     * Get the 'activity completions'.
     *
     * @return notificationconditionplugin|null The 'ac' subplugin instance if loaded, null otherwise.
     */
    public function get_ac() {
        return $this->ac;
    }

    /**
     * Load the 'ac' type condition from the database and initialize the 'ac' property.
     *
     * @return void
     */
    private function load_ac() {
        global $DB;

        if (
            $ac = $DB->get_record(
                'notificationsagent_condition',
                ['ruleid' => $this->id, 'type' => notificationplugin::TYPE_CONDITION, 'pluginname' => 'ac']
            )
        ) {
            $this->ac = notificationplugin::create_instance($ac->id, $ac->type, $ac->pluginname, $this->to_record());
        }
    }

    /**
     * Get conditions based on the plugin name.
     *
     * @param mixed $pluginname (optional) The plugin name to filter conditions.
     *
     * @return array The filtered conditions.
     */
    public function get_conditions($pluginname = null) {
        $conditions = [];
        if ($pluginname) {
            foreach ($this->conditions as $condition) {
                if ($condition->get_pluginname() == $pluginname) {
                    $conditions[$condition->get_id()] = $condition;
                }
            }
        } else {
            $conditions = $this->conditions;
        }
        return $conditions;
    }

    /**
     * Get a condition by plugin name.
     *
     * @param string $pluginname The name of the plugin
     *
     * @return mixed The condition object or null if not found
     */
    public function get_condition($pluginname) {
        foreach ($this->conditions as $condition) {
            if ($condition->get_pluginname() == $pluginname) {
                return $condition;
            }
        }
        return null;
    }

    /**
     * Get the conditions to evaluate.
     *
     * @return array
     */
    public function get_conditions_to_evaluate() {
        if ($this->get_ac() && $this->get_conditions()) {
            return array_merge($this->get_conditions(), [$this->get_ac()]);
        }

        return !empty($this->get_conditions()) ? $this->get_conditions() : [$this->get_ac()];
    }

    /**
     * Load conditions from the database and assign them to the current instance.
     */
    private function load_conditions() {
        global $DB;

        $selectconditions = $DB->get_records_select(
            'notificationsagent_condition',
            'ruleid = ? AND type = ? AND complementary = ? AND pluginname <> ?',
            [$this->id, notificationplugin::TYPE_CONDITION, notificationplugin::COMPLEMENTARY_CONDITION, ac::NAME]
        );
        $this->conditions = notificationplugin::create_subplugins($selectconditions, $this->to_record());
    }

    /**
     * Retrieve the exceptions associated with the current object.
     *
     * @return array The exceptions associated with the current object.
     */
    public function get_exceptions() {
        return $this->exceptions;
    }

    /**
     * Loads exceptions from the database and initializes the exceptions property.
     */
    private function load_exceptions() {
        global $DB;

        $selectexceptions = $DB->get_records_select(
            'notificationsagent_condition',
            'ruleid = ? AND type = ? AND complementary = ? AND pluginname <> ?',
            [$this->id, notificationplugin::TYPE_CONDITION, notificationplugin::COMPLEMENTARY_EXCEPTION, ac::NAME]
        );
        $this->exceptions = notificationplugin::create_subplugins($selectexceptions, $this->to_record());
    }

    /**
     * Get the actions.
     *
     * @return mixed
     */
    public function get_actions() {
        return $this->actions;
    }

    /**
     * Load actions from the database and create subplugins.
     */
    private function load_actions() {
        global $DB;

        $selectactions = $DB->get_records(
            'notificationsagent_action',
            ['ruleid' => $this->id, 'type' => notificationplugin::TYPE_ACTION]
        );

        $this->actions = notificationplugin::create_subplugins($selectactions, $this->to_record());
    }

    /**
     * Check if the object is generic.
     */
    private function is_generic() {
        $isgeneric = false;
        if ($this->is_subplugin_generic($this->get_conditions()) && $this->is_subplugin_generic($this->get_exceptions())) {
            $isgeneric = true;
        }

        $this->set_isgeneric($isgeneric);
    }

    /**
     * Check if the rule has any generic conditions or exceptions
     *
     * @param array $subplugins Conditions plugin
     *
     * @return bool $isgeneric Is there any condition or exception as a generic?
     */
    private function is_subplugin_generic($subplugins) {
        foreach ($subplugins as $subplugin) {
            if (!$subplugin->is_generic()) {
                return false;
            }
        }
        return true;
    }

    /**
     * Delete conditions function from the notificationsagent_condition table based on the ruleid.
     *
     * @return array The deleted conditions
     */
    private function delete_conditions() {
        global $DB;
        $this->conditions = $DB->delete_records('notificationsagent_condition', ['ruleid' => $this->get_id()]);
        return $this->conditions;
    }

    /**
     * Delete actions from the notificationsagent_action table based on the ruleid.
     *
     * @return array The deleted actions
     */
    public function delete_actions() {
        global $DB;
        $this->actions = $DB->delete_records('notificationsagent_action', ['ruleid' => $this->get_id()]);
        return $this->actions;
    }

    /**
     * Delete all context records of the rule
     *
     * @return void
     */
    private function delete_context() {
        global $DB;

        $DB->delete_records('notificationsagent_context', ['ruleid' => $this->get_id()]);
    }

    /**
     * Delete all launched records of the rule
     *
     * @return void
     */
    private function delete_launched() {
        global $DB;

        $DB->delete_records('notificationsagent_launched', ['ruleid' => $this->get_id()]);
        (\cache::make('local_notificationsagent', 'launched'))->delete($this->get_id());
    }

    /**
     * Delete all triggers records of the rule
     *
     * @param int $courseid
     *
     * @return void
     */
    private function delete_triggers($courseid = null) {
        global $DB;

        $where['ruleid'] = $this->get_id();
        if ($courseid) {
            $where['courseid'] = $courseid;
        }
        $DB->delete_records('notificationsagent_triggers', $where);
    }

    /**
     * Delete all cache records of the rule/conditions
     *
     * @return void
     */
    private function delete_cache() {
        global $DB;

        $conditions = $this->get_conditions();
        if (!empty($conditions)) {
            $conditionsid = array_keys($conditions);
            [$insql, $inparams] = $DB->get_in_or_equal($conditionsid);
            $DB->delete_records_select('notificationsagent_cache', "conditionid $insql", $inparams);
        }
    }

    /**
     * Set the conditions for the rule.
     *
     * This method assigns the provided conditions to the rule object. Conditions are
     * typically an array or a collection of criteria that must be met for the rule
     * to be applicable.
     *
     * @param mixed $conditions The conditions to set for the rule.
     *
     * @return void
     */
    public function set_conditions($conditions): void {
        $this->conditions = $conditions;
    }

    /**
     * Set the exceptions.
     *
     * @param mixed $exceptions
     */
    public function set_exceptions($exceptions): void {
        $this->exceptions = $exceptions;
    }

    /**
     * Set the actions.
     *
     * @param mixed $actions
     */
    public function set_actions($actions): void {
        $this->actions = $actions;
    }

    /**
     * Get the assigned property.
     *
     * @return mixed The assigned value
     */
    public function get_assigned() {
        return $this->assigned;
    }

    /**
     * Set the assigned property.
     *
     * @param mixed $assigned The new value for assigned
     *
     * @return void
     */
    public function set_assigned($assigned): void {
        $this->assigned = $assigned;
    }

    /**
     * Get the template.
     *
     * @return mixed
     */
    public function get_template() {
        return $this->template;
    }

    /**
     * Set the template.
     *
     * @param mixed $template
     */
    public function set_template($template): void {
        $this->template = $template;
    }

    /**
     * Get the status.
     *
     * @return mixed
     */
    public function get_status() {
        return $this->status;
    }

    /**
     * Set the status.
     *
     * @param mixed $status
     */
    public function set_status($status): void {
        $this->status = $status;
    }

    /**
     * Set the id.
     *
     * @param mixed $id
     */
    public function set_id($id): void {
        $this->id = $id;
    }

    /**
     * Get the createdby.
     *
     * @return mixed
     */
    public function get_createdby() {
        return $this->createdby;
    }

    /**
     * Set the createdby.
     *
     * @param mixed $createdby
     */
    public function set_createdby($createdby): void {
        $this->createdby = $createdby;
    }

    /**
     * Get the createdat.
     *
     * @return mixed
     */
    public function get_createdat() {
        return $this->createdat;
    }

    /**
     * Set the createdat.
     *
     * @param mixed $createdat
     */
    public function set_createdat($createdat): void {
        $this->createdat = $createdat;
    }

    /**
     * Get the value of forced.
     *
     * @return mixed
     */
    public function get_forced() {
        return $this->forced;
    }

    /**
     * Set the value of forced.
     *
     * @param mixed $forced
     */
    public function set_forced($forced): void {
        $this->forced = $forced;
    }

    /**
     * Get the value of shared.
     *
     * @return mixed
     */
    public function get_shared() {
        return $this->shared;
    }

    /**
     * Set the value of shared.
     *
     * @param mixed $shared The new value for shared.
     */
    public function set_shared($shared): void {
        $this->shared = $shared;
    }

    /**
     * Get the default rule.
     *
     * @return mixed
     */
    public function get_defaultrule() {
        return $this->defaultrule;
    }

    /**
     * Set the default rule.
     *
     * @param mixed $defaultrule
     */
    public function set_defaultrule($defaultrule): void {
        $this->defaultrule = $defaultrule;
    }

    /**
     * Get the number of times the rule has been fired.
     *
     * @return int The times fired.
     */
    public function get_timesfired() {
        return $this->timesfired;
    }

    /**
     * Set the number of times the rule has been fired.
     *
     * @param int $timesfired The new number of times fired.
     */
    public function set_timesfired($timesfired): void {
        $this->timesfired = $timesfired;
    }

    /**
     * Get the runtime configuration.
     *
     * @return mixed The runtime configuration.
     */
    public function get_runtime() {
        return $this->runtime;
    }

    /**
     * Set the runtime configuration.
     *
     * @param mixed $runtime The new runtime configuration.
     */
    public function set_runtime($runtime): void {
        $this->runtime = $runtime;
    }

    /**
     * Check if the rule is generic.
     *
     * @return bool true if the rule is generic, false otherwise.
     */
    public function get_isgeneric() {
        return $this->isgeneric;
    }

    /**
     * Set the rule as generic or not.
     *
     * @param bool $isgeneric true to set the rule as generic, false otherwise.
     */
    public function set_isgeneric($isgeneric): void {
        $this->isgeneric = $isgeneric;
    }

    /**
     * Evaluates the rule based on the provided context by checking conditions and exceptions.
     *
     * This method iterates over all conditions of the rule and evaluates them.
     * If any condition evaluates to false, it sets a time trigger for re-evaluation and returns false.
     * If all conditions pass, it then checks for exceptions in a similar manner.
     * If no exceptions are triggered, it sets a time trigger for the rule to be executed and returns true.
     *
     * @param evaluationcontext $context The context in which the rule is being evaluated.
     *
     * @return bool True if all conditions are met and no exceptions are triggered, false otherwise.
     */
    public function evaluate(evaluationcontext $context): bool {
        // Evaluate conditions.
        $conditions = $this->get_conditions_to_evaluate();
        $insertdata = [];
        $deletedata = [];

        foreach ($conditions as $condition) {
            $context->set_params($condition->get_parameters());
            $context->set_complementary(false);
            $result = $condition->evaluate($context);
            if ($result === false) {
                $timetrigger = $condition->estimate_next_time($context);

                $deletedata[] = "
                    (userid = {$context->get_userid()}
                    AND courseid = {$context->get_courseid()}
                    AND conditionid = {$condition->get_id()})";

                // Keep record in trigger.
                // Event driven conditions return a null timetrigger.
                if (!empty($timetrigger)) {
                    $insertdata[] = [
                        'userid' => $context->get_userid(),
                        'courseid' => $context->get_courseid(),
                        'startdate' => $timetrigger,
                        'pluginname' => $condition->get_subtype(),
                        'conditionid' => $condition->get_id(),
                        'ruleid' => $this->get_id(),
                    ];
                }

                notificationsagent::set_time_trigger(
                    $deletedata,
                    $insertdata
                );

                return false;
            }
        }
        // Evaluate exceptions.
        foreach ($this->exceptions as $exception) {
            $context->set_params($exception->get_parameters());
            $context->set_complementary(true);
            $result = $exception->evaluate($context);
            if ($result === true) {
                $timetrigger = $exception->estimate_next_time($context);

                $deletedata[] = "
                    (userid = {$context->get_userid()}
                    AND courseid = {$context->get_courseid()}
                    AND conditionid = {$exception->get_id()})";
                // Keep record in trigger.
                // Event driven exceptions return a null timetrigger.
                if (!empty($timetrigger)) {
                    $insertdata[] = [
                        'userid' => $context->get_userid(),
                        'courseid' => $context->get_courseid(),
                        'startdate' => $timetrigger,
                        'pluginname' => $exception->get_subtype(),
                        'conditionid' => $exception->get_id(),
                        'ruleid' => $this->get_id(),
                    ];
                }

                notificationsagent::set_time_trigger(
                    $deletedata,
                    $insertdata
                );

                return false;
            }
        }

        // Set a time trigger for the rule to be executed.
        $deletedata[] = "
            (userid = {$context->get_userid()}
            AND courseid = {$context->get_courseid()}
            AND conditionid = {$context->get_triggercondition()})";

        $insertdata[] = [
            'userid' => $context->get_userid(),
            'courseid' => $context->get_courseid(),
            'startdate' => time() + $this->get_runtime(),
            'ruleoff' => time(),
            'conditionid' => $context->get_triggercondition(),
            'ruleid' => $this->get_id(),
        ];

        notificationsagent::set_time_trigger(
            $deletedata,
            $insertdata
        );

        // All conditions are met, and no exceptions are triggered.
        return true;
    }

    /**
     * Return list of placeholders.
     *
     * @param bool $generic
     *
     * @return array
     */
    public static function get_placeholders($generic): array {
        if ($generic) {
            return array_merge(self::PLACEHOLDERS, self::PLACEHOLDERS_GEN);
        }

        return self::PLACEHOLDERS_GEN;
    }

    /**
     * Replace placeholders with actual values in the given parameters.
     *
     * @param evaluationcontext $context The context object.
     * @param string $parameters JSON string containing the parameters.
     *
     * @return string The parameters with placeholders replaced by actual values.
     */
    public function replace_placeholders($context, $parameters) {
        $paramstoreplace = [];
        $placeholderstoreplace = [];
        $placeholders = self::get_placeholders(true);
        $idcreatedby = $context->get_rule()->get_createdby();
        $courseid = $context->get_courseid();
        $userid = $context->get_userid();

        if ($userid != notificationsagent::GENERIC_USERID) {
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
                            $paramstoreplace[] = isset($user->firstname) ? $user->firstname : '';
                            $placeholderstoreplace[] = '{' . $placeholder . '}';
                            break;

                        case 'User_LastName':
                            $paramstoreplace[] = isset($user->lastname) ? $user->lastname : '';
                            $placeholderstoreplace[] = '{' . $placeholder . '}';
                            break;

                        case 'User_Email':
                            $paramstoreplace[] = isset($user->email) ? $user->email : '';
                            $placeholderstoreplace[] = '{' . $placeholder . '}';
                            break;

                        case 'User_Username':
                            $paramstoreplace[] = isset($user->username) ? $user->username : '';
                            $placeholderstoreplace[] = '{' . $placeholder . '}';
                            break;

                        case 'User_Address':
                            $paramstoreplace[] = isset($user->address) ? $user->address : '';
                            $placeholderstoreplace[] = '{' . $placeholder . '}';
                            break;

                        case 'Course_Category_Name':
                            $paramstoreplace[] = \core_course_category::get($course->category)->name;
                            $placeholderstoreplace[] = '{' . $placeholder . '}';
                            break;

                        case 'Course_FullName':
                            $paramstoreplace[] = $course->fullname;
                            $placeholderstoreplace[] = '{' . $placeholder . '}';
                            break;

                        case 'Course_Url':
                            $paramstoreplace[] = new moodle_url('/course/view.php', [
                                'id' => $courseid,
                            ]);
                            $placeholderstoreplace[] = '{' . $placeholder . '}';
                            break;

                        case 'Teacher_FirstName':
                            $paramstoreplace[] = $createdbyuser->firstname;
                            $placeholderstoreplace[] = '{' . $placeholder . '}';
                            break;

                        case 'Teacher_LastName':
                            $paramstoreplace[] = $createdbyuser->lastname;
                            $placeholderstoreplace[] = '{' . $placeholder . '}';
                            break;

                        case 'Teacher_Email':
                            $paramstoreplace[] = $createdbyuser->email;
                            $placeholderstoreplace[] = '{' . $placeholder . '}';
                            break;

                        case 'Teacher_Username':
                            $paramstoreplace[] = $createdbyuser->username;
                            $placeholderstoreplace[] = '{' . $placeholder . '}';
                            break;

                        case 'Teacher_Address':
                            $paramstoreplace[] = $createdbyuser->address;
                            $placeholderstoreplace[] = '{' . $placeholder . '}';
                            break;

                        case 'Current_time':
                            $paramstoreplace[] = date('d-m-Y h:i:s', $context->get_startdate() ?? time());
                            $placeholderstoreplace[] = '{' . $placeholder . '}';
                            break;

                        case 'Follow_Link':
                            $paramstoreplace[] = \local_notificationsagent\helper\helper::get_follow_link($context);
                            $placeholderstoreplace[] = '{' . $placeholder . '}';
                            break;
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
    public function before_delete() {
        $this->delete_launched();
        $this->delete_cache();
        $this->delete_triggers();
        $this->delete_conditions();
        $this->delete_context();
    }

    /**
     * Delete rule entry from the database
     *
     * @return bool
     */
    public function delete() {
        global $DB;

        $this->before_delete();
        $this->set_deleted(1);
        return $DB->update_record('notificationsagent_rule', ['id' => $this->get_id(), 'deleted' => $this->get_deleted()]);
    }

    /**
     * Records a report entry in the database.
     *
     * This method inserts a new record into the 'notificationsagent_report' table with details
     * about a specific rule execution.
     *
     * @param int $ruleid The ID of the rule being reported.
     * @param int $userid The ID of the user for whom the rule is reported.
     * @param int $courseid The ID of the course related to the rule execution.
     * @param int $actionid The ID of the action triggered by the rule.
     * @param string $parameters JSON encoded string containing additional details about the action.
     * @param int $timeaccess The timestamp when the rule was executed.
     *
     * @throws \dml_exception If there is a problem executing the database operation.
     */
    public function record_report($ruleid, $userid, $courseid, $actionid, $parameters, $timeaccess) {
        global $DB;
        $params = [
            'ruleid' => $ruleid,
            'userid' => $userid,
            'courseid' => $courseid,
            'actionid' => $actionid,
            'actiondetail' => $parameters,
            'timestamp' => $timeaccess,
        ];
        $DB->insert_record('notificationsagent_report', $params);
    }

    /**
     * Get assigned contexts for the rule.
     *
     * This function retrieves all related contexts from the database
     * and organizes them into categories and courses based on their context type.
     *
     * @return array An associative array with keys 'category' and 'course',
     *               each containing an array of respective object IDs.
     */
    public function get_assignedcontext() {
        global $DB;

        $data = ['category' => [], 'course' => []];

        $results = $DB->get_records('notificationsagent_context', ['ruleid' => $this->id]);
        foreach ($results as $result) {
            if ($result->contextid == CONTEXT_COURSE) {
                $data['course'][] = $result->objectid;
            }
            if ($result->contextid == CONTEXT_COURSECAT) {
                $data['category'][] = $result->objectid;
            }
        }
        return $data;
    }

    /**
     * Save the form data by creating or updating the rule, and processing conditions, actions, and exceptions.
     *
     * @param stdClass $data Form data to be processed.
     */
    public function save_form($data) {
        global $DB;
        $transaction = $DB->start_delegated_transaction();

        if ($this->is_new()) {
            $this->create($data);
        } else {
            $this->update($data);
        }

        $this->save_form_conditions_exceptions($data);
        $this->save_form_actions($data);

        if ($data->courseid == SITEID) {
            $this->delete_triggers();
            $this->delete_cache();
        }
        $transaction->allow_commit();
    }

    /**
     * Save form data related to conditions or exceptions based on provided JSON configuration.
     *
     * @param stdClass $data Form data containing the conditions or exceptions.
     */
    private function save_form_conditions_exceptions($data) {
        global $USER;

        $courseid = $data->courseid;
        $context = context_course::instance($courseid);

        // If $USER has student role, only generate triggers for the user.
        if (
            has_capability(
                'local/notificationsagent:managecourserule',
                $context,
                $USER->id
            )
        ) {
            $students = notificationsagent::get_usersbycourse($context);
        } else {
            $students = [$USER];
        }

        $arraytimer = [];

        $this->save_form_ac($data, $arraytimer, $students);

        $conditions = $data->{editrule_form::FORM_JSON_CONDITION};
        $exceptions = $data->{editrule_form::FORM_JSON_EXCEPTION};

        $array = json_decode($conditions, true);
        if (!empty($array)) {
            foreach ($array as $idname => $value) {
                $pluginname = $value["pluginname"];
                $action = $value["action"];
                if (
                    $subplugin = notificationplugin::create_instance(
                        $idname,
                        notificationplugin::TYPE_CONDITION,
                        $pluginname,
                        $this->to_record()
                    )
                ) {
                    $subplugin->save($action, $data, notificationplugin::COMPLEMENTARY_CONDITION, $arraytimer, $students);
                }
            }
        }

        $array = json_decode($exceptions, true);
        if (!empty($array)) {
            foreach ($array as $idname => $value) {
                $pluginname = $value["pluginname"];
                $action = $value["action"];
                if (
                    $subplugin = notificationplugin::create_instance(
                        $idname,
                        notificationplugin::TYPE_CONDITION,
                        $pluginname,
                        $this->to_record()
                    )
                ) {
                    $subplugin->save($action, $data, notificationplugin::COMPLEMENTARY_EXCEPTION, $arraytimer, $students);
                }
            }
        }

        // Delete triggers from concrete courseid, only if not SITEID.
        if ($courseid != SITEID) {
            $this->delete_triggers($courseid);
        }

        $this->save_form_triggers($data, $arraytimer, $context);
    }

    /**
     * Save form data related to the 'ac' subplugin condition.
     *
     * @param stdClass $data Form data containing the conditions.
     * @param array $arraytimer For triggers.
     * @param array $students For triggers.
     */
    private function save_form_ac($data, &$arraytimer, $students) {
        $id = $this->get_ac() ? $this->get_ac()->id : null;
        if (
            $subpluginac = notificationplugin::create_instance(
                $id,
                notificationplugin::TYPE_CONDITION,
                ac::NAME,
                $this->to_record()
            )
        ) {
            $action = $this->get_ac() ? editrule_form::FORM_JSON_ACTION_UPDATE : editrule_form::FORM_JSON_ACTION_INSERT;
            $subpluginac->save($action, $data, notificationplugin::COMPLEMENTARY_CONDITION, $arraytimer, $students);
        }
    }

    /**
     * Save form data for triggers
     *
     * @param stdClass $data Form data containing the conditions.
     * @param array $arraytimer Form data containing the actions.
     * @param context_course $context Course context.
     */
    private function save_form_triggers($data, $arraytimer, $context) {
        global $USER;

        $courseid = $data->courseid;
        if ($courseid == SITEID) {
            return;
        }

        if (!empty($arraytimer)) {
            $generictimer = $arraytimer[notificationsagent::GENERIC_USERID]["timer"] ?? null;
            $genericconditionid = $arraytimer[notificationsagent::GENERIC_USERID]["conditionid"] ?? null;
            unset($arraytimer[notificationsagent::GENERIC_USERID]);
            if (count($arraytimer) > 0) {
                foreach ($arraytimer as $studentid => $value) {
                    $timer = $generictimer > $value["timer"] ? $generictimer : $value["timer"];
                    $conditionid = $generictimer > $value["timer"] ? $genericconditionid : $value["conditionid"];

                    $insertdata[] = [
                        'userid' => $studentid,
                        'courseid' => $courseid,
                        'startdate' => $timer,
                        'conditionid' => $conditionid,
                        'ruleid' => $this->get_id(),
                    ];
                }
            } else {
                // If $USER has student role, only generate triggers for the user.
                if (
                    has_capability(
                        'local/notificationsagent:managecourserule',
                        $context,
                        $USER->id
                    )
                ) {
                    $studentid = notificationsagent::GENERIC_USERID;
                } else {
                    $studentid = $USER->id;
                }
                $insertdata[] = [
                    'userid' => $studentid,
                    'courseid' => $courseid,
                    'startdate' => $generictimer,
                    'conditionid' => $genericconditionid,
                    'ruleid' => $this->get_id(),
                ];
            }

            notificationsagent::set_time_trigger([], $insertdata);
        }
    }

    /**
     * Save form data related to actions based on the provided JSON configuration.
     *
     * @param stdClass $data Form data containing the actions.
     */
    private function save_form_actions($data) {
        $actions = $data->{editrule_form::FORM_JSON_ACTION};
        $array = json_decode($actions, true);
        if (!empty($array)) {
            foreach ($array as $idname => $value) {
                $pluginname = $value["pluginname"];
                $action = $value["action"];
                if (
                    $subplugin = notificationplugin::create_instance(
                        $idname,
                        notificationplugin::TYPE_ACTION,
                        $pluginname,
                        $this->to_record()
                    )
                ) {
                    $subplugin->save($action, $data);
                }
            }
        }
    }

    /**
     * Create the entity rule
     *
     * @param object $data Form data
     *
     * @return integer $id Rule id
     */
    public function create($data) {
        global $USER;

        $record = $this->to_record();
        $record->name = $data->title;
        $record->createdat = time();
        $record->createdby = $USER->id;
        $record->template = $data->type;

        if (isset($data->timesfired) && !empty($data->timesfired)) {
            $record->timesfired = $data->timesfired;
        }
        if (isset($data->runtime_group)) {
            $record->runtime = self::get_runtime_database_format($data->runtime_group);
        }

        $this->save($record);
        $this->set_default_context($data->courseid);

        return $this->get_id();
    }

    /**
     * Create rule entry in the database
     *
     * @param object $record Rule object
     *
     * @return void
     */
    private function save($record) {
        global $DB;

        unset($record->id);
        $id = $DB->insert_record('notificationsagent_rule', $record);
        $this->set_id($id);
    }

    /**
     * Set the default context of a rule
     *
     * @param integer $courseid course id
     *
     * @return void
     */
    public function set_default_context($courseid) {
        global $DB;

        $record = new stdClass();
        $record->ruleid = $this->get_id();
        $record->contextid = CONTEXT_COURSE;
        $record->objectid = $courseid;

        $DB->insert_record('notificationsagent_context', $record);
    }

    /**
     * Get the main context of a rule
     *
     * @return integer $objectid Course ID
     */
    public function get_default_context() {
        global $DB;

        $data = $DB->get_records(
            'notificationsagent_context',
            ['ruleid' => $this->get_id(), 'contextid' => CONTEXT_COURSE],
            '',
            'objectid',
            0,
            1
        );

        return reset($data)->objectid ?? 0;
    }

    /**
     * Update rule entry in the database
     *
     * @param object $data Form data
     *
     * @return void
     */
    public function update($data) {
        global $DB, $USER;

        $this->set_name($data->title);

        if (!empty($data->timesfired)) {
            $this->set_timesfired($data->timesfired);
        } else {
            $this->set_timesfired(self::MINIMUM_EXECUTION);
        }
        $this->set_runtime(self::get_runtime_database_format($data->runtime_group));

        $record = new stdClass();
        $record->id = $this->get_id();
        $record->name = $this->get_name();
        $record->timesfired = $this->get_timesfired();
        $record->runtime = $this->get_runtime();

        if (!has_capability('local/notificationsagent:manageallrule', context_course::instance($data->courseid), $USER->id)) {
            $record->createdby = $USER->id;
        }

        $DB->update_record('notificationsagent_rule', $record);
    }

    /**
     * Cloning one rule from another and converting it into a template
     *
     * @param integer $id Rule from which to clone
     *
     * @return void
     */
    public function clone($id) {
        global $DB;

        $fromrule = self::create_instance($id);
        $request = new stdClass();
        $request->id = $fromrule->get_id();
        $request->defaultrule = self::TEMPLATE_TYPE;
        $DB->update_record('notificationsagent_rule', $request);

        $record = new stdClass();
        $record->title = $fromrule->get_name();
        $record->type = self::TEMPLATE_TYPE;
        $record->courseid = SITEID;

        $torule = new rule();
        $torule->create($record);

        $torule->clone_conditions($fromrule->get_id());
        $torule->clone_actions($fromrule->get_id());
    }

    /**
     * Cloning the conditions of a rule to another rule
     *
     * @param integer $id Rule from which to clone
     *
     * @return void
     */
    private function clone_conditions($id) {
        global $DB;

        $conditions = $DB->get_records(
            'notificationsagent_condition',
            ['ruleid' => $id],
            '',
            'pluginname, type, parameters, complementary'
        );
        foreach ($conditions as $condition) {
            $data = new stdClass();
            $data->ruleid = $this->get_id();
            $data->pluginname = $condition->pluginname;
            $data->type = $condition->type;
            $data->parameters = $condition->parameters;
            $data->complementary = $condition->complementary;
            $DB->insert_record('notificationsagent_condition', $data);
        }
    }

    /**
     * Cloning the actions of a rule to another rule
     *
     * @param integer $id Rule from which to clone
     *
     * @return void
     */
    private function clone_actions($id) {
        global $DB;

        $actions = $DB->get_records(
            'notificationsagent_action',
            ['ruleid' => $id],
            '',
            'pluginname, type, parameters'
        );
        foreach ($actions as $action) {
            $data = new stdClass();
            $data->ruleid = $this->get_id();
            $data->pluginname = $action->pluginname;
            $data->type = $action->type;
            $data->parameters = $action->parameters;
            $DB->insert_record('notificationsagent_action', $data);
        }
    }

    /**
     * Get the rules created in a site context
     *
     * @return array $data rules
     */
    private static function get_site_rules() {
        global $DB;

        $data = [];

        $sql = 'SELECT DISTINCT nr.id
                  FROM {notificationsagent_rule} nr
                  JOIN {notificationsagent_context} nctx ON nr.id = nctx.ruleid AND nr.deleted = 0
                   AND nctx.contextid = :coursecontextid AND nctx.objectid = :siteid
        ';
        $data = $DB->get_records_sql($sql, [
            'coursecontextid' => CONTEXT_COURSE,
            'siteid' => SITEID,
        ]);

        return $data;
    }

    /**
     * Get all shared rules
     *
     * @return array $data rules
     */
    private static function get_shared_rules() {
        global $DB;

        $data = [];

        $sql = 'SELECT DISTINCT nr.id
                  FROM {notificationsagent_rule} nr
                 WHERE nr.shared = 0 AND nr.deleted = 0
        ';
        $data = $DB->get_records_sql($sql);

        return $data;
    }

    /**
     * Get all rules related to a given course
     *
     * @param integer $courseid Course id
     * @param bool $notstudent Filter
     * @param int $ruleid Concrete ruleid
     * @param bool $notcatcontext Filter
     *
     * @return array $data rules
     */
    private static function get_course_rules($courseid, $notstudent = false, $ruleid = null, $notcatcontext = true) {
        global $DB;

        $data = [];

        $parents = helper::get_parents_categories_course($courseid);
        [$sqlparents, $params] = $DB->get_in_or_equal($parents, SQL_PARAMS_NAMED);

        $notstudentjoin = '';
        if ($notstudent) {
            $notstudentjoin = "JOIN {user} u ON nr.createdby = u.id AND u.suspended = 0 AND u.deleted = 0
                                JOIN {user_enrolments} ue ON ue.userid = u.id AND ue.status = 0
                                JOIN {enrol} e ON e.id = ue.enrolid AND e.courseid = c.id AND e.status = 0
                                JOIN {role_assignments} ra ON ra.userid = u.id
                                JOIN {context} ct ON ct.id = ra.contextid AND ct.contextlevel = 50 AND ct.instanceid = c.id
                                JOIN {role} r ON r.id = ra.roleid AND r.id != 5";
        }

        $ruleidwheresql1 = '';
        $ruleidwheresql2 = '';
        if (!is_null($ruleid)) {
            $ruleidwheresql1 = " AND nr.id = :ruleid1";
            $ruleidwheresql2 = " AND nr.id = :ruleid2";
            $params = [
                    'ruleid1' => $ruleid,
                    'ruleid2' => $ruleid,
                ] + $params;
        }

        $unioncatcontext = '';
        if ($notcatcontext) {
            $unioncatcontext =
                "UNION
            SELECT nr.id
              FROM {notificationsagent_rule} nr
              JOIN {notificationsagent_context} nctx ON nr.id = nctx.ruleid
               AND nctx.contextid = :categorycontextid AND nr.deleted = 0
              JOIN {course_categories} cc ON nctx.objectid = cc.id
              JOIN {course} c ON cc.id = c.category
              $notstudentjoin
             WHERE cc.id $sqlparents
             $ruleidwheresql2";

            $params = [
                    'categorycontextid' => CONTEXT_COURSECAT,
                ] + $params;
        }

        $sql = "SELECT nr.id
                  FROM {notificationsagent_rule} nr
                  JOIN {notificationsagent_context} nctx ON nr.id = nctx.ruleid
                   AND nctx.contextid = :coursecontextid  AND nr.deleted = 0
                  JOIN {course} c ON nctx.objectid = c.id
                  $notstudentjoin
                 WHERE c.id = :coursecontext
                 $ruleidwheresql1
                 $unioncatcontext
        ";

        $params = [
                'coursecontextid' => CONTEXT_COURSE,
                'coursecontext' => $courseid,
            ] + $params;

        $data = $DB->get_records_sql($sql, $params);

        return $data;
    }

    /**
     * Get the rules forced related to a given course.
     *
     * @param int $courseid The course ID.
     * @param int $forced (optional) The forced value. Default is 0.
     *
     * @return array The data containing the rules.
     */
    private static function get_course_rules_forced($courseid, $forced = 0) {
        global $DB;

        $data = [];

        $parents = helper::get_parents_categories_course($courseid);
        [$sqlparents, $params] = $DB->get_in_or_equal($parents, SQL_PARAMS_NAMED);

        $sql = "SELECT nr.id
                  FROM {notificationsagent_rule} nr
                  JOIN {notificationsagent_context} nctx ON nr.id = nctx.ruleid
                   AND nctx.contextid = :coursecontextid AND nr.deleted = 0
                 WHERE nctx.objectid = :coursecontext
                   AND nr.forced = :forcedcourse
                 UNION
                SELECT nr.id
                  FROM {notificationsagent_rule} nr
                  JOIN {notificationsagent_context} nctx ON nr.id = nctx.ruleid
                   AND nctx.contextid = :categorycontextid AND nr.deleted = 0
                  JOIN {course_categories} cc ON nctx.objectid = cc.id
                 WHERE cc.id $sqlparents
                   AND nr.forced = :forcedcat
        ";

        $params = [
                'coursecontextid' => CONTEXT_COURSE,
                'coursecontext' => $courseid,
                'categorycontextid' => CONTEXT_COURSECAT,
                'forcedcourse' => $forced,
                'forcedcat' => $forced,
            ] + $params;

        $data = $DB->get_records_sql($sql, $params);

        return $data;
    }

    /**
     * Get the owner rules in a course
     *
     * @param integer $courseid Course id
     *
     * @return array $data rules
     */
    private static function get_owner_rules_by_course($courseid) {
        global $DB, $USER;

        $data = [];

        $sql = 'SELECT nr.id
                  FROM {notificationsagent_rule} nr
                  JOIN {notificationsagent_context} nctx ON nr.id = nctx.ruleid
                   AND nctx.contextid = :coursecontextid AND nctx.objectid = :objectid
                  AND nr.deleted = 0
                 WHERE nr.createdby = :createdby
              ORDER BY nr.status, nr.createdat ASC
        ';
        $data = $DB->get_records_sql($sql, [
            'coursecontextid' => CONTEXT_COURSE,
            'objectid' => $courseid,
            'createdby' => $USER->id,
        ]);

        return $data;
    }

    /**
     * Get all owner rules.
     *
     * @return array An array containing the owner rules.
     */
    private static function get_owner_rules() {
        global $DB, $USER;

        $data = [];

        $sql = 'SELECT nr.id
                  FROM {notificationsagent_rule} nr
                 WHERE nr.createdby = :createdby AND nr.deleted = 0
        ';
        $data = $DB->get_records_sql($sql, [
            'createdby' => $USER->id,
        ]);

        return $data;
    }

    /**
     * Get all rules.
     *
     * @return array An array containing all rules.
     */
    private static function get_all_rules() {
        global $DB;

        $data = [];

        $sql = 'SELECT nr.id
                  FROM {notificationsagent_rule} nr
                 WHERE nr.deleted = 0
        ';
        $data = $DB->get_records_sql($sql);

        return $data;
    }

    /**
     * Get if the record is of rule or template type
     *
     * @return string $data Rule or template
     */
    public function get_type() {
        return self::RULE_TYPES[$this->get_template()];
    }

    /**
     * Check if a rule is created from a template based on the course and context
     *
     * @param int $courseid The course ID to check the rule against
     * @param context $context The context in which to check the capability
     *
     * @return bool True if the rule uses a template, False otherwise
     */
    public function is_use_template($courseid, $context) {
        return
            $courseid != SITEID
            && has_capability('local/notificationsagent:managecourserule', $context)
            && !$this->get_template();
    }

    /**
     * Check if a rule can be shared
     *
     * @return bool $data Is it the owner?
     */
    public function can_share() {
        global $USER;

        return $this->get_createdby() == $USER->id;
    }

    /**
     * Check if the rule has a context other than the default one
     *
     * @return bool $hascontext Is there any other context?
     */
    public function has_context() {
        global $DB;

        $hascontext = false;

        $sql = 'SELECT nctx.id as ctxid, nr.id
                  FROM {notificationsagent_rule} nr
                  JOIN {notificationsagent_context} nctx ON nr.id = nctx.ruleid
                   AND (nctx.contextid != :contextid OR nctx.objectid != :objectid)
                   AND nr.id = :id
        ';
        $data = $DB->get_records_sql($sql, [
            'contextid' => CONTEXT_COURSE,
            'objectid' => $this->get_default_context(),
            'id' => $this->get_id(),
        ]);

        if ($data) {
            $hascontext = true;
        }

        return $hascontext;
    }

    /**
     * Check if a rule can be deleted
     *
     * @return bool $hasdelete Is it the owner, or the administrator?
     */
    public function can_delete() {
        global $USER;
        $hasdelete = false;

        $context = \context_course::instance($this->get_default_context(), IGNORE_MISSING);
        if (
            $this->get_createdby() == $USER->id
            || ($context
                && has_capability(
                    'local/notificationsagent:managesiterule',
                    $context
                ))
        ) {
            $hasdelete = true;
        }

        return $hasdelete;
    }

    /**
     * Returns the human format of a rule's runtime
     *
     * @return integer $data Time in days, hours and minutes
     */
    public function get_runtime_format() {
        return \local_notificationsagent\helper\helper::to_human_format($this->get_runtime());
    }

    /**
     * Returns the seconds of a rule's runtime
     *
     * @param array $runtime Time in days, hours and minutes
     *
     * @return integer $data Seconds
     */
    private static function get_runtime_database_format($runtime) {
        $days = trim($runtime['runtime_days']);
        $hours = trim($runtime['runtime_hours']);
        $minutes = trim($runtime['runtime_minutes']);
        if (!empty($days) || !empty($hours) || !empty($minutes)) {
            $data = \local_notificationsagent\helper\helper::to_seconds_format([
                'days' => $days,
                'hours' => $hours,
                'minutes' => $minutes,
            ]);
        } else {
            $data = \local_notificationsagent\helper\helper::to_seconds_format(['days' => self::MINIMUM_RUNTIME]);
        }

        return $data;
    }

    /**
     * Returns the number of times the rule has been executed in a given context
     *
     * @param evaluationcontext $context The context in which the rule is being evaluated.
     *
     * @return object|null $launched Launched object, or null if not found.
     */
    public function get_launched($context) {
        global $DB;

        $cache = \cache::make('local_notificationsagent', 'launched');
        $rulecache = $cache->get($this->get_id()) ? $cache->get($this->get_id()) : [];

        if (!isset($rulecache[$context->get_courseid()][$context->get_userid()])) {
            $record = $DB->get_record('notificationsagent_launched', [
                'ruleid' => $this->get_id(),
                'courseid' => $context->get_courseid(),
                'userid' => $context->get_userid(),
            ], '*');

            if ($record) {
                $rulecache[$context->get_courseid()][$context->get_userid()] = $record;
                $cache->set($this->get_id(), $rulecache);
            }
        }

        return $rulecache[$context->get_courseid()][$context->get_userid()] ?? null;
    }

    /**
     * Store the number of times a rule has been executed in a specific context
     *
     * @param evaluationcontext $context The context in which the rule is being evaluated.
     *
     * @return void
     *
     */
    public function set_launched($context) {
        $launched = $this->get_launched($context);

        if (is_null($launched)) {
            $launched = $this->create_launched_record($context);
        } else {
            $launched = $this->update_launched_record($launched, $context);
        }

        $cache = \cache::make('local_notificationsagent', 'launched');
        $rulecache = $cache->get($this->get_id()) ? $cache->get($this->get_id()) : [];
        $rulecache[$context->get_courseid()][$context->get_userid()] = $launched;
        $cache->set($this->get_id(), $rulecache);
    }

    /**
     * Create a record in the 'notificationsagent_launched' table for the given context.
     *
     * @param evaluationcontext $context The context in which the rule is being evaluated.
     *
     * @return object            $record  The object of the 'notificationsagent_launched' table.
     */
    private function create_launched_record($context) {
        global $DB;

        $launched = new stdClass();
        $launched->ruleid = $this->get_id();
        $launched->courseid = $context->get_courseid();
        $launched->userid = $context->get_userid();
        $launched->timesfired = self::MINIMUM_EXECUTION;
        $launched->timecreated = time();
        $launched->timemodified = time();
        $launched->id = $DB->insert_record('notificationsagent_launched', $launched);

        return $launched;
    }

    /**
     * /**
     *  Update a record in the 'notificationsagent_launched' table for the given context.
     *
     * @param object $launched
     * @param evaluationcontext $context The context in which the rule is being evaluated.
     * @return object $record  The object of the 'notificationsagent_launched' table.
     *
     */
    private function update_launched_record($launched, $context) {
        global $DB;

        $launched->timesfired = $context->get_usertimesfired();
        $launched->timemodified = time();
        $DB->update_record('notificationsagent_launched', $launched);

        return $launched;
    }

    /**
     * Check if the rule has been launched the maximum number of times given a course and userids.
     *
     * @param integer $courseid Course identifier
     * @param int $ruleid Id of rule
     * @param int $timesfired Fired times
     * @param array $userids Users identifiers
     *
     * @return array  $users    Has the rule been launched the max. number of times by each user?
     */
    public static function get_limit_reached_by_users($courseid, $ruleid, $timesfired, $userids = []) {
        global $DB;

        $cache = \cache::make('local_notificationsagent', 'launched');
        $rulecache = $cache->get($ruleid) ? $cache->get($ruleid) : [];
        $coursecache = $rulecache[$courseid] ?? [];
        $users = array_fill_keys($userids, false);

        // There is no cache, we have to get it from the database and set the cache.
        if (empty($coursecache)) {
            [$launchedsql, $params] = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED);
            $params = ['ruleid' => $ruleid, 'courseid' => $courseid] + $params;
            $userslaunched = $DB->get_records_select(
                'notificationsagent_launched',
                "ruleid = :ruleid AND courseid = :courseid AND userid {$launchedsql}",
                $params
            );

            foreach ($userslaunched as $userlaunched) {
                $users[$userlaunched->userid] = $userlaunched->timesfired >= $timesfired;
                $coursecache[$userlaunched->userid] = $userlaunched;
            }

            // Set the cache for fetched data.
            $rulecache[$courseid] = $coursecache;
            $cache->set($ruleid, $rulecache);
        } else {
            foreach ($userids as $userid) {
                // There is no cache for this user, we have to get it from the database and set the cache.
                if (empty($coursecache[$userid])) {
                    $userlaunched = $DB->get_record('notificationsagent_launched', [
                        'ruleid' => $ruleid,
                        'courseid' => $courseid,
                        'userid' => $userid,
                    ]);

                    if ($userlaunched) {
                        $users[$userid] = $userlaunched->timesfired >= $timesfired;

                        // Set the cache for fetched data.
                        $rulecache[$courseid][$userid] = $userlaunched;
                        $cache->set($ruleid, $rulecache);
                    }

                    continue;
                }

                // There is cache for this user. Check if the max. number of times has been reached.
                $users[$userid] = $coursecache[$userid]->timesfired >= $timesfired;
            }
        }

        return $users;
    }

    /**
     * Rejects the request to share a rule to all
     *
     * @param int $id Rule ID
     *
     * @return void
     */
    public function reject_share_rule($id) {
        global $DB;

        $request = new \stdClass();

        $request->id = $id;
        $request->shared = self::UNSHARED_RULE;
        $request->defaultrule = self::RULE_TYPE;
        $DB->update_record('notificationsagent_rule', $request);
    }

    /**
     * Get if rule is deleted
     *
     * @return int
     */
    public function get_deleted(): int {
        return $this->deleted;
    }

    /**
     * Set rule as deleted
     *
     * @param int $deleted
     */
    public function set_deleted(int $deleted): void {
        $this->deleted = $deleted;
    }

    /**
     * Order rules by field
     *
     * @param array $rules
     * @param string $field Field order by.
     * @param int $desc Desc or Asc.
     * @param int $courseid
     *
     * @return array
     */
    public static function order_rules_by_field($rules, $field, $desc, $courseid) {
        if ($field == "broken") {
            usort($rules, function ($a, $b) use ($courseid) {
                if ($a->validation($courseid) && !$b->validation($courseid)) {
                    return 1;
                } else if (!$a->validation($courseid) && $b->validation($courseid)) {
                    return -1;
                } else {
                    return 0;
                }
            });
        } else {
            if ($desc == 1) {
                usort($rules, function ($a, $b) use ($field, $courseid) {
                    if ($b->$field - $a->$field == 0) {
                        if ($a->validation($courseid) && !$b->validation($courseid)) {
                            return -1;
                        } else if (!$a->validation($courseid) && $b->validation($courseid)) {
                            return 1;
                        } else {
                            return 0;
                        }
                    }
                    return $b->$field - $a->$field;
                });
            } else {
                usort($rules, function ($a, $b) use ($field, $courseid) {
                    if ($b->$field - $a->$field == 0) {
                        if ($a->validation($courseid) && !$b->validation($courseid)) {
                            return -1;
                        } else if (!$a->validation($courseid) && $b->validation($courseid)) {
                            return 1;
                        } else {
                            return 0;
                        }
                    }
                    return $a->$field - $b->$field;
                });
            }
        }

        return $rules;
    }

    /**
     * Return username and coursename by rule.
     *
     * @return array
     */
    public function get_coursename_and_username_by_rule() {
        $username = $this->get_owner();
        $data = [];
        if ($coursename = get_course($this->get_default_context())) {
            $data = [
                'username' => $username,
                'coursename' => $coursename->shortname,
            ];
            return get_string('cardsharedby', 'local_notificationsagent', $data);
        }
        return '';
    }

    /**
     * Get the owner of the rule
     *
     * @return string $owner owner's fullname
     */
    public function get_owner() {
        return fullname(\core_user::get_user($this->get_createdby(), '*', MUST_EXIST));
    }

    /**
     * Get the capabilities of the rule.
     *
     * @param int $courseid The course id.
     *
     * @return array $capabilities The capabilities of the rule.
     */
    public function get_card_options($courseid) {
        global $USER;

        $context = \context_course::instance($courseid);
        $isownrule = $this->get_createdby() == $USER->id;
        $hasmanagecourserule = has_capability('local/notificationsagent:managecourserule', $context);

        $capabilities = $isownrule || $hasmanagecourserule
            ? \local_notificationsagent\helper\helper::get_capabilities($context)
            : \local_notificationsagent\helper\helper::get_default_capabilities($context);

        return $capabilities;
    }

    /**
     * Check permission to access to rule
     *
     * @param \context_system|context_course $context
     * @param int $courseid
     */
    public function check_permission($context, $courseid) {
        global $USER;

        $isownrule = $this->get_createdby() == $USER->id;
        $hasmanageallrule = has_capability('local/notificationsagent:manageallrule', $context);
        $hasviewcourserules = has_capability('local/notificationsagent:viewcourserule', $context);
        $hasmanagecourserule = has_capability('local/notificationsagent:managecourserule', $context);

        if ($this->ruleaction == self::RULE_ADD || $isownrule || $hasmanageallrule) {
            return true;
        } else if ($hasviewcourserules || $hasmanagecourserule) {
            if (self::get_course_rules($courseid, true, $this->get_id())) {
                return true;
            }
        }

        throw new \moodle_exception(
            'nopermissions',
            '',
            '',
            get_capability_string('local/notificationsagent:managecourserule')
        );
    }
}
