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
defined('MOODLE_INTERNAL') || die();
require_once('plugininfo/notificationsbaseinfo.php');
require_once('rule.php');

use local_notificationsagent\plugininfo\notificationsbaseinfo;
use local_notificationsagent\Rule;

abstract class notificationplugin {

    const CONFIG_DISABLED = 'disabled';
    const CONFIG_ENABLED = 'enabled';
    const CAT_ACTION = 'action';
    const CAT_CONDITION = 'condition';
    const CAT_CONDITION_CHILDREN = 0;
    const CAT_EXCEPTION_CHILDREN = 1;

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
     * @return string "condition" or "action".
     */
    /** Complementary condition. This condition is used in exceptions phase */
    private $iscomplementary = 0;

    public function __construct($rule) {
        $this->rule = $rule;
    }


    abstract public function get_type();
    abstract public function get_title();
    abstract public function get_elements();

    /** Returns subtype string for building classnames, filenames, modulenames, etc.
     * @return string subplugin type. "messageagent"
     */
    abstract public function get_subtype();

    /** Returns the name of the plugin
     * @return string
     */
    abstract public function get_name();
    abstract public function get_ui($mform, $id, $courseid, $exception);

    abstract public function get_description();

    /**
     * Returns a human-readable string from database records
     *
     * @param  mixed $content
     * @param  mixed $params
     * @param  mixed $courseid
     * @param  mixed $complementary
     * @return string
     */
    abstract public function process_markups(&$content, $params, $courseid, $complementary=null);

    /**
     * Factory for loading subplugins from database records
     * @param array $records
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
     * @return int
     */
    public function get_id(): int {
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

    abstract public function  convert_parameters($params);

    /**
     * @param mixed $parameters
     */
    public function set_parameters($parameters): void {
        $this->parameters = $parameters;
    }

    public function set_type($type) {
        $this->type = $type;
    }



}
