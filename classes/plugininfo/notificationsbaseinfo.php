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

namespace local_notificationsagent\plugininfo;

use core\plugininfo\base;
use core_plugin_manager;
use local_notificationsagent\notificationplugin;
use local_notificationsagent\rule;

/**
 * Plugin information class for the notifications base plugin
 */
class notificationsbaseinfo extends base {
    /**
     * @var array $plugins Cache of initialized plugins indexed by notification rule id and type
     */
    private static $plugins = [];

    /**
     * Create a new instance of a specific plugin based on the given rule, subtype, and plugin name.
     *
     * @param rule   $rule       description of the rule parameter
     * @param string $subtype    description of the subtype parameter
     * @param string $pluginname description of the pluginname parameter
     *
     * @return mixed|void
     */
    public static function instance($rule, $subtype, $pluginname) {
        $type = ($subtype == notificationplugin::TYPE_ACTION ? notificationplugin::TYPE_ACTION
            : notificationplugin::TYPE_CONDITION);
        $pluginclass = '\notifications' . $type . '_' . $pluginname . '\\' . $pluginname;
        if (class_exists($pluginclass)) {
            $plugin = new $pluginclass($rule);
            return $plugin;
        }
    }

    /**
     * Finds all system-wide enabled plugins, the result may include missing plugins.
     * First conditions, then actions.
     *
     * @param \stdClass $rule record of the instance for innitiallizing plugins
     *
     * @return array
     */
    public static function get_system_enabled_plugins_all_types($rule = null) {
        $conditions = self::get_system_enabled_plugins($rule, notificationplugin::TYPE_CONDITION);
        $actions = self::get_system_enabled_plugins($rule, notificationplugin::TYPE_ACTION);
        return array_merge($conditions, $actions);
    }

    /** Finds all system-wide enabled plugins, the result may include missing plugins.
     *
     * @param \stdClass $rule    record of the instance for initiallizing plugins
     * @param string    $subtype 'condition' or 'action'
     *
     * @return array of enabled plugins $pluginname=>$plugin
     */
    public static function get_system_enabled_plugins($rule = null, $subtype = null) {
        global $DB;
        if (!isset(self::$plugins[$subtype])) {
            if ($subtype == null) {
                return self::get_system_enabled_plugins_all_types($rule);
            } else {
                $plugins = core_plugin_manager::instance()->get_installed_plugins('notifications' . $subtype);
            }
            if (!$plugins) {
                return [];
            }
            $installed = [];
            foreach ($plugins as $pluginname => $version) {
                $installed[] = 'notifications' . $subtype . '_' . $pluginname;
            }

            list($installed, $params) = $DB->get_in_or_equal($installed, SQL_PARAMS_NAMED);
            $disabled = $DB->get_records_select('config_plugins', "plugin $installed AND name = 'disabled'", $params, 'plugin ASC');
            foreach ($disabled as $conf) {
                if (empty($conf->value)) {
                    continue;
                }
                list($type, $name) = explode('_', $conf->plugin, 2);
                unset($plugins[$name]);
            }
            self::$plugins[$subtype] = $plugins;
        } else {
            $plugins = self::$plugins[$subtype];
        }
        $enabled = [];
        foreach ($plugins as $pluginname => $version) {
            $enabled[$pluginname] = self::instance($rule, $subtype, $pluginname);
        }
        return $enabled;
    }

    /**
     * Get description.
     *
     * @param int    $courseid courseid
     * @param string $subtype  plugin subtype
     *
     * @return array
     */
    public static function get_description($courseid, $subtype) {
        $listactions = [];
        $rule = new \stdClass();
        $rule->ruleid = null;
        foreach (array_keys(self::get_system_enabled_plugins(null, $subtype)) as $pluginname) {
            $type = ($subtype == notificationplugin::TYPE_ACTION ? notificationplugin::TYPE_ACTION
                : notificationplugin::TYPE_CONDITION);
            $pluginclass = '\notifications' . $type . '_' . $pluginname . '\\' . $pluginname;
            if (class_exists($pluginclass)) {
                $pluginobj = new $pluginclass($rule);
                $context = \context_course::instance($courseid);
                // Check subplugin capability for current user in course.
                if ($pluginobj->check_capability($context)) {
                    $listactions[] = $pluginobj->get_description();
                }
            }
        }

        return $listactions;
    }

}
