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

/**
 * Plugin information class for the notifications base plugin
 */
class notificationsbaseinfo extends base {
    /**
     * @var array $plugins Cache of initialized plugins indexed by notification rule id and type
     */
    private static $pluginscondition = [];
    /**
     * @var array
     */
    private static $pluginsaction = [];

    /**
     * Finds all system-wide enabled plugins, the result may include missing plugins.
     * First conditions, then actions.
     *
     *
     * @return void
     */
    public static function get_all_enabled_plugins() {
        $cachecondition = \cache::make('local_notificationsagent', notificationplugin::TYPE_CONDITION);
        self::$pluginscondition = $cachecondition->get(notificationplugin::TYPE_CONDITION) ? $cachecondition->get(
                notificationplugin::TYPE_CONDITION
        ) : [];
        if (empty(self::$pluginscondition)) {
            $notificationscondition = core_plugin_manager::instance()->get_enabled_plugins('notificationscondition');
            foreach ($notificationscondition as $pluginname) {
                if ($pluginobj = notificationplugin::create_instance(null, notificationplugin::TYPE_CONDITION, $pluginname)) {
                    self::$pluginscondition[] = $pluginobj;
                }
            }
            $cachecondition->set(notificationplugin::TYPE_CONDITION, self::$pluginscondition);
        }

        $cacheaction = \cache::make('local_notificationsagent', notificationplugin::TYPE_ACTION);
        self::$pluginsaction = $cacheaction->get(notificationplugin::TYPE_ACTION) ? $cacheaction->get(
                notificationplugin::TYPE_ACTION
        ) : [];
        if (empty(self::$pluginsaction)) {
            $notificationsaction = core_plugin_manager::instance()->get_enabled_plugins('notificationsaction');
            foreach ($notificationsaction as $pluginname) {
                if ($pluginobj = notificationplugin::create_instance(null, notificationplugin::TYPE_ACTION, $pluginname)) {
                    self::$pluginsaction[] = $pluginobj;
                }
            }
            $cacheaction->set(notificationplugin::TYPE_ACTION, self::$pluginsaction);
        }

    }

    /**
     * Get description.
     *
     * @param int $courseid courseid
     * @param string $subtype plugin subtype
     *
     * @return array
     */
    public static function get_description($courseid, $subtype) {
        self::get_all_enabled_plugins();
        $context = \context_course::instance($courseid);

        if ($subtype == notificationplugin::TYPE_CONDITION) {
            $list = [];
            foreach (self::$pluginscondition as $pluginobj) {
                // Check subplugin capability for current user in course.
                if ($pluginobj->check_capability($context)) {
                    $list[] = $pluginobj->get_description();
                }
            }
            return $list;
        }

        if ($subtype == notificationplugin::TYPE_ACTION) {
            $list = [];
            foreach (self::$pluginsaction as $pluginobj) {
                // Check subplugin capability for current user in course.
                if ($pluginobj->check_capability($context)) {
                    $list[] = $pluginobj->get_description();
                }
            }
            return $list;
        }
    }

}
