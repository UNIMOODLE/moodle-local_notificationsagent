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

use local_notificationsagent\form\editrule_form;
use local_notificationsagent\rule;
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
     * User interface constant for seconds.
     */
    public const UI_SECONDS = 'seconds';

    /**
     * User interface constant for user.
     */
    public const UI_USER = 'user';

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
     * Default value for seconds in UI.
     */
    public const UI_SECONDS_DEFAULT_VALUE = 0;

    /**
     * Configuration status disabled.
     */
    const CONFIG_DISABLED = 'disabled';

    /**
     * Configuration status enabled.
     */
    const CONFIG_ENABLED = 'enabled';

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
     * @var Rule $rule the assistrule object for this instance
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
     * Indicates if the plugin is complementary.
     *
     * @var int
     */
    private $iscomplementary = 0;

    /**
     * Constructor for the class.
     *
     * @param rule $rule Description of the rule parameter
     */
    public function __construct($rule) {
        $this->rule = $rule;
    }

    /**
     * Get name for user interface.
     *
     * @param int    $id   description
     * @param string $name description
     *
     * @return string
     */
    protected function get_name_ui($id, $name) {
        return $id . '_' . get_called_class()::NAME . '_' . $name;
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
    abstract public function get_subtype();

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
     * @param int|string       $id    The identifier used for the title element.
     * @param string           $type  The type of the notification plugin, used to build the class attribute.
     */
    protected function get_ui_title($mform, $id, $type) {
        $title = \html_writer::start_tag('h5');
        $title .= $this->get_title();
        $class = 'remove-' . $type . '-span';
        $title .= \html_writer::span('', 'btn icon fa fa-trash align-top ' . $class, ['id' => $id]);
        $title .= \html_writer::end_tag('h5');
        $titleelement = $mform->createElement('html', $title);
        $mform->insertElementBefore($titleelement, 'new' . $type . '_group');
    }

    /**
     * Builds select elements for date selection in the form.
     *
     * @param \MoodleQuickForm $mform The form to which the date elements will be added.
     * @param int|string       $id    The identifier used for date elements.
     * @param string           $type  The type of the notification plugin, used to determine condition or action.
     */
    protected function get_ui_select_date($mform, $id, $type) {
        $conditionoraction = ($type == self::TYPE_ACTION ? self::TYPE_ACTION : self::TYPE_CONDITION);

        $timegroup = [];

        // Days.
        $timegroup[] = $mform->createElement(
            'float',
            $this->get_name_ui($id, self::UI_DAYS),
            '',
            [
                'class' => 'mr-2', 'size' => '7', 'maxlength' => '3',
                'placeholder' => get_string('condition_days', 'local_notificationsagent'),
                'oninput' => 'this.value = this.value.replace(/[^0-9]/g, "").replace(/(\..*)\./g, "$1")',
            ]
        );

        // Hours.
        $timegroup[] = $mform->createElement(
            'float',
            $this->get_name_ui($id, self::UI_HOURS),
            '',
            [
                'class' => 'mr-2', 'size' => '7', 'maxlength' => '3',
                'placeholder' => get_string('condition_hours', 'local_notificationsagent'),
                'oninput' => 'this.value = this.value.replace(/[^0-9]/g, "").replace(/(\..*)\./g, "$1")',
            ]
        );

        // Minutes.
        $timegroup[] = $mform->createElement(
            'float',
            $this->get_name_ui($id, self::UI_MINUTES),
            '',
            [
                'class' => 'mr-2', 'size' => '7', 'maxlength' => '2',
                'placeholder' => get_string('condition_minutes', 'local_notificationsagent'),
                'oninput' => 'this.value = this.value.replace(/[^0-9]/g, "").replace(/(\..*)\./g, "$1")',
            ]
        );

        // Seconds.
        $timegroup[] = $mform->createElement(
            'float',
            $this->get_name_ui($id, self::UI_SECONDS),
            '',
            [
                'class' => 'mr-2', 'size' => '7', 'maxlength' => '2',
                'placeholder' => get_string('condition_seconds', 'local_notificationsagent'),
                'oninput' => 'this.value = this.value.replace(/[^0-9]/g, "").replace(/(\..*)\./g, "$1")',
            ]
        );

        // GroupTime.
        $group = $mform->createElement(
            'group', $this->get_name_ui($id, $this->get_subtype()),
            get_string(
                'editrule_condition_element_time', 'notifications' . $conditionoraction . '_' . get_called_class()::NAME,
                ['typeelement' => '[TTTT]']
            ),
            $timegroup, null, false
        );

        $mform->insertElementBefore($group, 'new' . $type . '_group');

        $mform->addGroupRule($this->get_name_ui($id, $this->get_subtype()), '- You must supply a value here.', 'required');
    }

    /**
     * Set the default select date values for the given ID.
     *
     * @param int $id description
     *
     * @return array
     */
    protected function set_default_select_date($id) {
        $params[$this->get_name_ui($id, self::UI_DAYS)] = self::UI_DAYS_DEFAULT_VALUE;
        $params[$this->get_name_ui($id, self::UI_HOURS)] = self::UI_HOURS_DEFAULT_VALUE;
        $params[$this->get_name_ui($id, self::UI_MINUTES)] = self::UI_MINUTES_DEFAULT_VALUE;
        $params[$this->get_name_ui($id, self::UI_SECONDS)] = self::UI_SECONDS_DEFAULT_VALUE;
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
     *  Validation editrule_form
     *
     * @param editrule_form $mform
     * @param int           $id       The name from form field
     * @param int           $courseid course id
     * @param array         $array    The array to be modified by reference
     *
     * @return void
     */
    public function validation_form($mform, $id, $courseid, &$array) {
    }

    /**
     * Save data (insert/update/delete)
     *
     * @param string $action
     * @param integer $idname
     * @param \stdClass $dataplugin
     *
     * @return bool
     * @throws moodle_exception
     */
    protected function insert_update_delete($action, $idname, &$dataplugin) {
        global $DB;

        $table = $this->get_type() == self::TYPE_ACTION ? 'notificationsagent_action' : 'notificationsagent_condition';
        // Insert plugin.
        if ($action == editrule_form::FORM_JSON_ACTION_INSERT) {
            if (!$dataplugin->id = $DB->insert_record($table, $dataplugin)) {
                throw new moodle_exception('errorinserting', 'notificationplugin');
            }
            return true;

        // Update plugin.
        } elseif ($action == editrule_form::FORM_JSON_ACTION_UPDATE) {
            $dataplugin->id = $idname;
            if (!$DB->update_record($table, $dataplugin)) {
                throw new moodle_exception('errorupdating', 'notificationplugin');
            }
            return true;

        // Delete plugin.
        } elseif ($action == editrule_form::FORM_JSON_ACTION_DELETE) {
            if (!$DB->delete_records($table, ['id' => $idname])) {
                throw new moodle_exception('errordeleting', 'notificationplugin');
            }
            if ($this->get_type() != self::TYPE_ACTION){
                if (!$DB->delete_records('notificationsagent_cache', ['conditionid' => $idname])) {
                    throw new moodle_exception('errordeletingcache', 'notificationplugin');
                }
                if (!$DB->delete_records('notificationsagent_triggers', ['conditionid' => $idname])) {
                    throw new moodle_exception('errordeletingtriggers', 'notificationplugin');
                }
            }
            return false;

        } else{
            throw new moodle_exception('errorinsertupdatedelete', 'notificationplugin');
        }

        return false;
    }

    /**
     * Process and replace markups in the supplied content.
     *
     * This function should handle any markup logic specific to a notification plugin,
     * such as replacing placeholders with dynamic data, formatting content, etc.
     *
     * @param string $content  The content to be processed, passed by reference.
     * @param int    $courseid The ID of the course related to the content.
     * @param mixed  $options  Additional options if any, null by default.
     *
     * @return void Processed content with markups handled.
     */
    abstract public function process_markups(&$content, $courseid, $options = null);

    /**
     * Creates an array of subplugin instances from the provided database records.
     *
     * @param array $records Database records to create subplugin instances from.
     *
     * @return array An array of instantiated subplugins.
     */
    abstract public static function create_subplugins($records);

    /**
     * Creates a single subplugin instance based on the provided identifier.
     *
     * @param mixed $id The identifier used to create a subplugin instance.
     *
     * @return mixed An instance of a subplugin.
     */
    abstract public static function create_subplugin($id);

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
     * @param int $id The ID to set
     *
     * @return void
     */
    public function set_id(int $id): void {
        $this->id = $id;
    }

    /**
     * Get the value of iscomplementary
     *
     * @return int
     */
    public function get_iscomplementary(): int {
        return $this->iscomplementary;
    }

    /**
     * Set the value of iscomplementary.
     *
     * @param int $iscomplementary The new value for iscomplementary
     */
    public function set_iscomplementary(int $iscomplementary): void {
        $this->iscomplementary = $iscomplementary;
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
     * @param int   $id     The identifier for the notification.
     * @param array $params The parameters associated with the notification.
     *
     * @return mixed The converted parameters.
     */
    abstract protected function convert_parameters($id, $params);

    /**
     * Set the parameters for the PHP function.
     *
     * @param array $parameters description
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
        if(!empty($parameters)){
            $array = json_decode($parameters, true);
            if (!empty($array)) {
                foreach ($array as $key => $value) {
                    if ($key == self::UI_TIME) {
                        $format = to_human_format($value);
                        $return[$this->get_name_ui($this->get_id(), self::UI_DAYS)] = $format["days"];
                        $return[$this->get_name_ui($this->get_id(), self::UI_HOURS)] = $format["hours"];
                        $return[$this->get_name_ui($this->get_id(), self::UI_MINUTES)] = $format["minutes"];
                        $return[$this->get_name_ui($this->get_id(), self::UI_SECONDS)] = $format["seconds"];
                        continue;
                    }
                    $return[$this->get_name_ui($this->get_id(), $key)] = $value;
                }
            }
        }
        return $return;
    }

    /**
     * Set the defalut values
     *
     * @param editrule_form $form
     * @param int           $id
     *
     * @return void
     */
    public function set_default($form, $id) {
    }

}
