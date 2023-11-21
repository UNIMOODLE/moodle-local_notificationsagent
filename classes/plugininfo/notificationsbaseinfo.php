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
defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->dirroot . '/local/notificationsagent/classes/rule.php');

use core\plugininfo\base;
use core_plugin_manager, core_component;

class notificationsbaseinfo extends base {

    private static $plugins = [];

    /**
     * @param \stdClass $rule record of the instance for initializing plugins
     * @param string $subtype 'condition' or 'action'
     * @param string $pluginname
     * @return \notificationplugin */
    public static function instance($rule, $subtype, $pluginname) {
        $path = \core_component::get_plugin_directory('notifications' . $subtype, $pluginname);
        $classfile = $pluginname;
        if (file_exists($path .'/' . $classfile.'.php')) {
            require_once($path . '/' . $classfile.'.php');
            $pluginclass = 'notificationsagent_' . $subtype . '_' . $pluginname;
            $plugin = new $pluginclass($rule);
            return $plugin;
        }
    }

    /** Finds all system-wide enabled plugins, the result may include missing plugins.
     * First conditions, then actions.
     * @param \stdClass $rule record of the instance for innitiallizing plugins
     * @return \notificationplugin[] of enabled plugins $pluginname=>$plugin,
     */
    public static function get_system_enabled_plugins_all_types($rule = null) {
        $conditions = self::get_system_enabled_plugins($rule, 'condition');
        $actions = self::get_system_enabled_plugins($rule, 'action');
        return array_merge($conditions, $actions);
    }


    /** Finds all system-wide enabled plugins, the result may include missing plugins.
     *
     * @param \stdClass $rule record of the instance for initiallizing plugins
     * @param string $subtype 'condition' or 'action'
     * @return \notificationplugin[] of enabled plugins $pluginname=>$plugin */
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

    public static function get_description($subtype) {
        global $CFG;
        $courseid = required_param('courseid', PARAM_INT);
        $listactions = [];
        // TODO enabled.
        $rule = new \stdClass();
        $rule->ruleid = null;
        foreach (array_keys(self::get_system_enabled_plugins(null, $subtype)) as $pluginname) {
            require_once($CFG->dirroot . '/local/notificationsagent/' . $subtype . '/' . $pluginname . '/' . $pluginname . '.php');
            $pluginclass = 'notificationsagent_' . $subtype . '_' . $pluginname;
            $pluginobj = new $pluginclass($rule);
            $context = \context_course::instance($courseid);
            // Check subplugin capability for current user in course.
            if ($pluginobj->check_capability($context)) {
                $listactions[] = $pluginobj->get_description();
            }
        }

        return $listactions;
    }

}
