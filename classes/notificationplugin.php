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

use local_notificationsagent\form\editrule_form;
use moodle_exception;

/**
 * Abstract class for defining notification plugins.
 *
 * This class provides the necessary structure and methods that all notification plugin
 * subclasses should implement to work within the notification agent system.
 */
abstract class notificationplugin {
    /**
     * Type of the notification plugin condition.
     */
    public const TYPE_CONDITION = 'condition';

    /**
     * Type of the notification plugin exception.
     */
    public const TYPE_EXCEPTION = 'exception';

    /**
     * Type of the notification plugin action.
     */
    public const TYPE_ACTION = 'action';

    /**
     * Complementary type representing a condition.
     */
    public const COMPLEMENTARY_CONDITION = 0;

    /**
     * Complementary type representing an exception.
     */
    public const COMPLEMENTARY_EXCEPTION = 1;

    /**
     * User interface constant for activity ID.
     */
    public const UI_ACTIVITY = 'cmid';

    /**
     * User interface constants for jsons.
     */
    public const UI_TITLE = 'title';
    /**
     * User interface constants for jsons.
     */
    public const UI_MESSAGE = 'message';
    /**
     * User interface constants for jsons.
     */
    public const UI_MESSAGE_FORMAT = 'format';
    /**
     * User interface constants for jsons.
     */
    public const UI_MESSAGE_ITEMID = 'itemid';

    /**
     * User interface constant for time.
     */
    public const UI_TIME = 'time';

    /**
     * User interface constant for days.
     */
    public const UI_DAYS = 'days';

    /**
     * User interface constant for hours.
     */
    public const UI_HOURS = 'hours';

    /**
     * User interface constant for minutes.
     */
    public const UI_MINUTES = 'minutes';

    /**
     * User interface constant for user.
     */
    public const UI_USER = 'user';

    /**
     * User interface constant for grade.
     */
    public const UI_GRADE = 'grade';

    /**
     * User interface constant for operator.
     */
    public const UI_OP = 'op';

    /**
     * Default value for days in UI.
     */
    public const UI_DAYS_DEFAULT_VALUE = 0;

    /**
     * Default value for hours in UI.
     */
    public const UI_HOURS_DEFAULT_VALUE = 0;

    /**
     * Default value for minutes in UI.
     */
    public const UI_MINUTES_DEFAULT_VALUE = 0;

    /**
     * Configuration status disabled.
     */
    const CONFIG_DISABLED = 'disabled';

    /**
     * Configuration status enabled.
     */
    const CONFIG_ENABLED = 'enabled';

    /**
     * Conditional operators.
     */
    const OPERATORS
            = [
                    0 => '>',
                    1 => '>=',
                    2 => '=',
                    3 => '<',
                    4 => '<=',
            ];

    /**
     * @var $id int the id of the subplugin instance
     */
    public $id;
    /**
     * The rule ID associated with this plugin instance.
     *
     * @var int
     */
    private $ruleid;
    /**
     * @var stdClass $rule object
     */
    public $rule;
    /**
     * The name of the plugin.
     *
     * @var string
     */
    private $pluginname;

    /**
     * The parameters for the plugin.
     *
     * @var array
     */
    private $parameters;

    /**
     * The type of the plugin.
     *
     * @var string
     */
    private $type;

    /**
     * Constructor for the class.
     *
     * @param int|\stdClass $ruleorid object from DB table 'notificationsagent_rule' or just a rule id
     * @param mixed $id If is numeric => value is already in DB
     *
     */
    public function __construct($ruleorid, $id = null) {
        global $DB;

        if (is_object($ruleorid)) {
            $rule = $ruleorid;
        } else {
            $rule = $DB->get_record('notificationsagent_rule', ['id' => $ruleorid]);
        }
        $this->rule = $rule;
        $this->set_id($id);
    }

    /**
     * Get name for user interface.
     *
     * @param string $name UI name
     *
     * @return string
     */
    protected function get_name_ui($name) {
        return $this->get_id() . '_' . get_called_class()::NAME . '_' . $name;
    }

    /**
     * Gets the type of the notification plugin.
     *
     * @return string The type of the plugin.
     */
    abstract public function get_type();

    /**
     * Gets the title of the notification plugin.
     *
     * @return string The title of the plugin.
     */
    abstract public function get_title();

    /**
     * Gets the elements of the notification plugin configuration.
     *
     * @return array An array of elements for the plugin configuration.
     */
    abstract public function get_elements();

    /** Returns subtype string for building classnames.
     *
     * @return string subplugin type. "messageagent"
     */
    public function get_subtype() {
        return get_called_class()::NAME;
    }

    /**
     * Retrieve the description of the entity.
     *
     * @return array
     */
    public function get_description() {
        return [
                'title' => $this->get_title(),
                'name' => $this->get_subtype(),
        ];
    }

    /**
     * Generates the UI title element for the form and inserts it before a specified group.
     *
     * @param \MoodleQuickForm $mform The form to which the title element will be added.
     * @param string $type The type of the notification plugin, used to build the class attribute.
     */
    protected function get_ui_title($mform, $type) {
        $title = \html_writer::start_tag('h5');
        $title .= $this->get_title();
        $class = 'remove-' . $type . '-span';
        $title .= \html_writer::span('', 'btn icon fa fa-trash align-top ' . $class, ['id' => $this->get_id()]);
        $title .= \html_writer::end_tag('h5');
        $titleelement = $mform->createElement('html', $title);
        $mform->insertElementBefore($titleelement, 'new' . $type . '_group');
    }

    /**
     * Builds select elements for date selection in the form.
     *
     * @param \MoodleQuickForm $mform The form to which the date elements will be added.
     * @param string $type The type of the notification plugin, used to determine condition or action.
     */
    protected function get_ui_select_date($mform, $type) {
        $conditionoraction = ($type == self::TYPE_ACTION ? self::TYPE_ACTION : self::TYPE_CONDITION);

        // Days.
        $timegroup[] = $mform->createElement(
            'static',
            'labeldays',
            '',
            get_string('condition_days', 'local_notificationsagent')
        );
        $timegroup[] = $mform->createElement(
            'float',
            $this->get_name_ui(self::UI_DAYS),
            get_string('condition_days', 'local_notificationsagent'),
            [
                        'class' => 'mr-2', 'size' => '7', 'maxlength' => '3',
                        'placeholder' => get_string('condition_days', 'local_notificationsagent'),
                        'oninput' => 'this.value = this.value.replace(/[^0-9]/g, "").replace(/(\..*)\./g, "$1")',
                ]
        );

        // Hours.
        $timegroup[] = $mform->createElement(
            'static',
            'labelhours',
            '',
            get_string('condition_hours', 'local_notificationsagent'),
        );
        $timegroup[] = $mform->createElement(
            'float',
            $this->get_name_ui(self::UI_HOURS),
            '',
            [
                        'class' => 'mr-2', 'size' => '7', 'maxlength' => '3',
                        'placeholder' => get_string('condition_hours', 'local_notificationsagent'),
                        'oninput' => 'this.value = this.value.replace(/[^0-9]/g, "").replace(/(\..*)\./g, "$1")',
                ]
        );

        // Minutes.
        $timegroup[] = $mform->createElement(
            'static',
            'labelminutes',
            '',
            get_string('condition_minutes', 'local_notificationsagent'),
        );
        $timegroup[] = $mform->createElement(
            'float',
            $this->get_name_ui(self::UI_MINUTES),
            '',
            [
                        'class' => 'mr-2', 'size' => '7', 'maxlength' => '2',
                        'placeholder' => get_string('condition_minutes', 'local_notificationsagent'),
                        'oninput' => 'this.value = this.value.replace(/[^0-9]/g, "").replace(/(\..*)\./g, "$1")',
                ]
        );

        // GroupTime.
        $group = $mform->createElement(
            'group',
            $this->get_name_ui($this->get_subtype()),
            get_string(
                'editrule_condition_element_time',
                'notifications' . $conditionoraction . '_' . get_called_class()::NAME,
                ['typeelement' => '[TTTT]']
            ),
            $timegroup,
            null,
            false
        );

        $mform->insertElementBefore($group, 'new' . $type . '_group');
    }

    /**
     * Convert select date value to unix
     *
     * @param array $params The parameters of the form
     *
     * @return array
     */
    protected function select_date_to_unix($params) {
        $timevalues = [
                'days' => !empty($params[$this->get_name_ui(self::UI_DAYS)]) ? $params[$this->get_name_ui(self::UI_DAYS)] : 0,
                'hours' => !empty($params[$this->get_name_ui(self::UI_HOURS)]) ? $params[$this->get_name_ui(self::UI_HOURS)] : 0,
                'minutes' => !empty($params[$this->get_name_ui(self::UI_MINUTES)]) ? $params[$this->get_name_ui(self::UI_MINUTES)] :
                        0,
        ];
        return ($timevalues['days'] * 24 * 60 * 60) + ($timevalues['hours'] * 60 * 60)
                + ($timevalues['minutes'] * 60);
    }

    /**
     * Set the default select date values for the given ID.
     *
     * @return array
     */
    protected function set_default_select_date() {
        $params[$this->get_name_ui(self::UI_DAYS)] = self::UI_DAYS_DEFAULT_VALUE;
        $params[$this->get_name_ui(self::UI_HOURS)] = self::UI_HOURS_DEFAULT_VALUE;
        $params[$this->get_name_ui(self::UI_MINUTES)] = self::UI_MINUTES_DEFAULT_VALUE;
        return $params;
    }

    /**
     * Set the UI for the given array by adding the plugin name and subtype.
     *
     * @param array $array The array to be modified by reference
     */
    public function set_ui(&$array) {
        $array[$this->get_id()] = ["pluginname" => $this->get_subtype(), "action" => editrule_form::FORM_JSON_ACTION_UPDATE];
    }

    /**
     * Validation subplugin
     * If this method overrides, call to parent::validation
     *
     * @param int $courseid Course id
     * @param array $array The array to be modified by reference. If is null, validation is not being called from the form
     *                                  and return directly
     * @param bool $onlyverifysiteid Default false. If true, only SITEID is verified
     *
     * @return bool
     */
    public function validation($courseid, &$array = null, $onlyverifysiteid = false) {
        $validation = true;
        if ($courseid == SITEID) {
            return 'break';
        }

        if ($onlyverifysiteid) {
            return $validation;
        }

        // All parameters.
        $data = json_decode($this->get_parameters() ?? '', true);

        // Check cmid exists.
        if ($cmid = $this->get_activity_cmid($data, $courseid) ?? null) {
            $fastmodinfo = get_fast_modinfo($courseid);
            if (!$validation = isset($fastmodinfo->cms[$cmid]) ? true : false) {
                if (is_null($array)) {
                    return $validation;
                }
                $array[$this->get_name_ui(self::UI_ACTIVITY)] = get_string(
                    'validation_editrule_form_supported_cm',
                    'notificationscondition_activityend'
                );
            }
        }

        return $validation;
    }

    /**
     * Get the activity id.
     *
     * @param array $data Data parameters.
     * @param int $courseid The course identifier.
     *
     * @return int $cmid The course module identifier.
     */
    public function get_activity_cmid($data, $courseid) {
        return ((object)$data)->{self::UI_ACTIVITY} ?? null;
    }

    /**
     * Save data (insert/update/delete)
     *
     * @param string $action
     * @param \stdClass $dataplugin
     *
     * @return bool
     * @throws moodle_exception
     */
    protected function insert_update_delete($action, &$dataplugin) {
        global $DB;

        $id = $this->get_id();
        $table = $this->get_type() == self::TYPE_ACTION ? 'notificationsagent_action' : 'notificationsagent_condition';
        // Insert plugin.
        if ($action == editrule_form::FORM_JSON_ACTION_INSERT) {
            if (!$dataplugin->id = $DB->insert_record($table, $dataplugin)) {
                throw new moodle_exception('errorinserting', 'notificationplugin');
            }
            $this->set_id($dataplugin->id);
            return true;

            // Update plugin.
        } else if ($action == editrule_form::FORM_JSON_ACTION_UPDATE) {
            $dataplugin->id = $id;
            if (!$DB->update_record($table, $dataplugin)) {
                throw new moodle_exception('errorupdating', 'notificationplugin');
            }
            return true;

            // Delete plugin.
        } else if ($action == editrule_form::FORM_JSON_ACTION_DELETE) {
            if (!$DB->delete_records($table, ['id' => $id])) {
                throw new moodle_exception('errordeleting', 'notificationplugin');
            }

            if ($this->get_type() != self::TYPE_ACTION) {
                if (!$DB->delete_records('notificationsagent_cache', ['conditionid' => $id])) {
                    throw new moodle_exception('errordeletingcache', 'notificationplugin');
                }
            }

            return false; // If delete, do not call to estimate_next_time method.
        } else {
            throw new moodle_exception('errorinsertupdatedelete', 'notificationplugin');
        }
    }

    /**
     * Process and replace markups in the supplied content.
     *
     * This function should handle any markup logic specific to a notification plugin,
     * such as replacing placeholders with dynamic data, formatting content, etc.
     *
     * @param string $content The content to be processed, passed by reference.
     * @param int $courseid The ID of the course related to the content.
     * @param mixed $options Additional options if any, null by default.
     *
     * @return void Processed content with markups handled.
     */
    abstract public function process_markups(&$content, $courseid, $options = null);

    /**
     * Create subplugins from the given records.
     *
     * @param array $records The array of records to create subplugins from.
     * @param int|stdClass $ruleorid Object from DB table 'notificationsagent_rule' or just a rule id
     *
     * @return array The array of created subplugins.
     */
    public static function create_subplugins($records, $ruleorid) {
        global $DB;

        $subplugins = [];

        if (is_object($ruleorid)) {
            $rule = $ruleorid;
        } else {
            $rule = $DB->get_record('notificationsagent_rule', ['id' => $ruleorid]);
        }

        foreach ($records as $record) {
            if ($subplugin = self::create_instance($record->id, $record->type, $record->pluginname, $rule)) {
                $subplugins[$record->id] = $subplugin;
            }
        }

        return $subplugins;
    }

    /**
     * Create a subplugin
     *
     * @param int|\stdClass $id If is numeric => value is already in DB
     * @param string $type The type of the subplugin
     * @param string $pluginname The name of the subplugin
     * @param int|\stdClass $ruleorid Object from DB table 'notificationsagent_rule' or just a rule id
     *
     * @return \stdClass
     */
    public static function create_instance($id, $type, $pluginname, $ruleorid = 0) {
        global $DB;

        if (is_object($ruleorid)) {
            $rule = $ruleorid;
        } else {
            $rule = $DB->get_record('notificationsagent_rule', ['id' => $ruleorid]);
        }

        $pluginclass = '\notifications' . $type . '_' . $pluginname . '\\' . $pluginname;
        if (class_exists($pluginclass)) {
            $plugin = new $pluginclass($rule, $id);
            return $plugin;
        }
    }

    /**
     * Determines if the plugin is generic or specific to a certain type.
     *
     * @return bool True if the plugin is generic, false otherwise.
     */
    abstract public function is_generic();

    /**
     * Set the rule ID.
     *
     * @param int $ruleid The rule ID to be set
     */
    public function set_ruleid($ruleid) {
        $this->ruleid = $ruleid;
    }

    /**
     * Get the rule ID.
     *
     * @return int
     */
    public function get_ruleid() {
        return $this->ruleid;
    }

    /**
     * Check if the object is new.
     *
     * @return boolean
     */
    protected function is_new() {
        return is_null($this->id);
    }

    /**
     * Get the id of the object.
     *
     * @return mixed
     */
    public function get_id() {
        return $this->id;
    }

    /**
     * Set the ID of the object.
     *
     * @param mixed|null $id The ID to set
     *
     * @return void
     */
    public function set_id($id): void {
        $this->id = $id;
    }

    /**
     * Get the plugin name.
     *
     * @return mixed
     */
    public function get_pluginname() {
        return $this->pluginname;
    }

    /**
     * Set the plugin name.
     *
     * @param string $pluginname The new plugin name
     */
    public function set_pluginname($pluginname): void {
        $this->pluginname = $pluginname;
    }

    /**
     * Get the parameters of the subplugin.
     *
     * @return mixed
     */
    public function get_parameters() {
        return $this->parameters;
    }

    /**
     * Convert parameters for the notification plugin.
     *
     * This method should take an identifier and parameters for a notification
     * and convert them into a format suitable for use by the plugin.
     *
     * @param array $params The parameters associated with the notification.
     *
     * @return mixed The converted parameters.
     */
    abstract public function convert_parameters($params);

    /**
     * Set the parameters for the PHP function.
     *
     * @param array $parameters
     */
    public function set_parameters($parameters): void {
        $this->parameters = $parameters;
    }

    /**
     * Set the type of the object.
     *
     * @param string $type The type to set
     */
    public function set_type($type) {
        $this->type = $type;
    }

    /**
     * Data for $form->set_data()
     *
     * @return mixed
     */
    public function load_dataform() {
        $return = [];
        $parameters = $this->get_parameters();
        if (!empty($parameters)) {
            $array = json_decode($parameters, true);
            if (!empty($array)) {
                foreach ($array as $key => $value) {
                    if ($key == self::UI_TIME) {
                        $format = \local_notificationsagent\helper\helper::to_human_format($value);
                        $return[$this->get_name_ui(self::UI_DAYS)] = $format["days"];
                        $return[$this->get_name_ui(self::UI_HOURS)] = $format["hours"];
                        $return[$this->get_name_ui(self::UI_MINUTES)] = $format["minutes"];
                        continue;
                    }
                    $return[$this->get_name_ui($key)] = $value;
                }
            }
        }
        return $return;
    }

    /**
     * Set the defalut values
     *
     * @param editrule_form $form
     *
     * @return void
     */
    public function set_default($form) {
    }
}
