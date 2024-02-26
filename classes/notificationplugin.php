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

defined('MOODLE_INTERNAL') || die();

use local_notificationsagent\rule;

abstract class notificationplugin {

    public const TYPE_CONDITION = 'condition';
    public const TYPE_EXCEPTION = 'exception';
    public const TYPE_ACTION = 'action';

    // AC for CONDITION/EXCEPTION => Save same JSON and row value in table mdl_notificationsagent_condition
    public const COMPLEMENTARY_CONDITION = 0;
    public const COMPLEMENTARY_EXCEPTION = 1;

    public const UI_ACTIVITY = 'cmid';
    public const UI_TIME = 'time';
    public const UI_DAYS = 'days';
    public const UI_HOURS = 'hours';
    public const UI_MINUTES = 'minutes';
    public const UI_SECONDS = 'seconds';
    public const UI_USER = 'user';

    public const UI_DAYS_DEFAULT_VALUE = 0;
    public const UI_HOURS_DEFAULT_VALUE = 0;
    public const UI_MINUTES_DEFAULT_VALUE = 0;
    public const UI_SECONDS_DEFAULT_VALUE = 0;

    const CONFIG_DISABLED = 'disabled';
    const CONFIG_ENABLED = 'enabled';

    /**
     * @var $id int the id of the subplugin instance
     */
    public $id;
    private $ruleid;
    /**
     * @var Rule $rule the assistrule object for this instance
     */
    public $rule;
    private $pluginname;
    private $parameters;
    private $type;
    /**
     * Returns the main plugin type qualifier.
     *
     * @return string "condition" or "action".
     */
    /** Complementary condition. This condition is used in exceptions phase */
    private $iscomplementary = 0;

    public function __construct($rule) {
        $this->rule = $rule;
    }

    protected function get_name_ui($id, $name) {
        return $id . '_' . get_called_class()::NAME . '_' . $name;
    }

    abstract public function get_type();

    abstract public function get_title();

    abstract public function get_elements();

    /** Returns subtype string for building classnames, filenames, modulenames, etc.
     *
     * @return string subplugin type. "messageagent"
     */
    abstract public function get_subtype();

    public function get_description() {
        return [
            'title' => $this->get_title(),
            'name' => $this->get_subtype(),
        ];
    }

    protected function get_ui_title($mform, $id, $type) {
        $title = \html_writer::start_tag('h5');
        $title .= $this->get_title();
        $class = 'remove-' . $type . '-span';
        $title .= \html_writer::span('', 'btn icon fa fa-trash align-top ' . $class, ['id' => $id]);
        $title .= \html_writer::end_tag('h5');
        $titleelement = $mform->createElement('html', $title);
        $mform->insertElementBefore($titleelement, 'new' . $type . '_group');
    }

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

    protected function set_default_select_date($id) {
        $params[$this->get_name_ui($id, self::UI_DAYS)] = self::UI_DAYS_DEFAULT_VALUE;
        $params[$this->get_name_ui($id, self::UI_HOURS)] = self::UI_HOURS_DEFAULT_VALUE;
        $params[$this->get_name_ui($id, self::UI_MINUTES)] = self::UI_MINUTES_DEFAULT_VALUE;
        $params[$this->get_name_ui($id, self::UI_SECONDS)] = self::UI_SECONDS_DEFAULT_VALUE;
        return $params;
    }

    public function set_ui(&$array) {
        $array[$this->get_id()] = ["pluginname" => $this->get_subtype()];
    }

    /**
     * Returns a human-readable string from database records
     *
     * @param mixed $content
     * @param mixed $params
     * @param mixed $courseid
     * @param mixed $options
     *
     * @return string
     */
    abstract public function process_markups(&$content, $courseid, $options = null);

    /**
     * Factory for loading subplugins from database records
     *
     * @param array $records
     *
     * @return array of subplugins
     */
    abstract public static function create_subplugins($records);

    abstract public static function create_subplugin($id);

    abstract public function is_generic();

    /**
     * @param $ruleid
     */
    public function set_ruleid($ruleid) {
        $this->ruleid = $ruleid;
    }

    /**
     * @return mixed
     */
    public function get_ruleid() {
        return $this->ruleid;
    }

    /**
     * @return boolean
     */
    protected function is_new() {
        return is_null($this->id);
    }

    /**
     * @return mixed
     */
    public function get_id() {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function set_id(int $id): void {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function get_iscomplementary(): int {
        return $this->iscomplementary;
    }

    /**
     * @param int $iscomplementary
     */
    public function set_iscomplementary(int $iscomplementary): void {
        $this->iscomplementary = $iscomplementary;
    }

    /**
     * @return mixed
     */
    public function get_pluginname() {
        return $this->pluginname;
    }

    /**
     * @param mixed $pluginname
     */
    public function set_pluginname($pluginname): void {
        $this->pluginname = $pluginname;
    }

    public function get_parameters() {
        return $this->parameters;
    }

    /**
     * @param array $params
     *
     * @return mixed
     */

    abstract protected function convert_parameters($id, $params);

    /**
     * @param mixed $parameters
     */
    public function set_parameters($parameters): void {
        $this->parameters = $parameters;
    }

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
        $array = json_decode($this->get_parameters(), true);
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
        return $return;
    }

    /**
     * Override in each subplugin for setting default values
     */
    public function set_default($form, $id) {
    }

}
