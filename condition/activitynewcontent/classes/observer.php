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
 * @package    notificationscondition_activitynewcontent
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_notificationsagent\evaluationcontext;
use local_notificationsagent\notificationsagent;
use notificationscondition_activitynewcontent\activitynewcontent;

/**
 * Activitynewcontent observer
 */
class notificationscondition_activitynewcontent_observer {
    /**
     * Module created listener
     *
     * @param \core\event\course_module_created $event
     *
     * @return void
     */
    public static function course_module_created(\core\event\course_module_created $event) {
        // Bypass the event handler if the plugin is disabled.
        if (!local_notificationsagent\plugininfo\notificationscondition::is_plugin_enabled(activitynewcontent::NAME)) {
            return;
        }
        $other = $event->other;
        $namemodule = $other["modulename"];
        if (in_array($namemodule, activitynewcontent::UNUSED_TYPES)) {
            return;
        }
        $courseid = $event->courseid;

        $pluginname = activitynewcontent::NAME;

        $conditions = notificationsagent::get_conditions_by_course($pluginname, $courseid);

        foreach ($conditions as $condition) {
            $decode = $condition->parameters;
            $param = json_decode($decode, true);
            $modname = $param[activitynewcontent::MODNAME];

            if ($namemodule == $modname) {
                $subplugin = new activitynewcontent($condition->ruleid, $condition->id);
                $context = new evaluationcontext();
                $context->set_params($subplugin->get_parameters());
                $context->set_complementary($subplugin->get_iscomplementary());
                $context->set_timeaccess($event->timecreated);
                $context->set_observer(true);
                $context->set_courseid($courseid);

                notificationsagent::generate_cache_triggers($subplugin, $context);
            }
        }
    }
}
