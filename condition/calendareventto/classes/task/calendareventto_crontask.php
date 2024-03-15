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
 * @package    notificationscondition_calendareventto
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace notificationscondition_calendareventto\task;

use core\task\scheduled_task;
use local_notificationsagent\notificationsagent;
use local_notificationsagent\notificationplugin;

/**
 * Scheduled tak for condition
 */
class calendareventto_crontask extends scheduled_task {

    /**
     * Get a descriptive name for the task (shown to admins)
     *
     * @return string
     */
    public function get_name() {
        return get_string('calendareventto_crontask', 'notificationscondition_calendareventto');
    }

    /**
     * Throw exceptions on errors (the job will be retried).
     */
    public function execute() {
        custom_mtrace("calendareventto start");

        $pluginname = get_string('subtype', 'notificationscondition_calendareventto');
        $conditions = notificationsagent::get_conditions_by_plugin($pluginname);

        foreach ($conditions as $condition) {
            $courseid = $condition->courseid;
            $condtionid = $condition->id;
            $decode = $condition->parameters;
            $param = json_decode($decode);
            $calendarevent = calendar_get_events_by_id([$param->{notificationplugin::UI_ACTIVITY}]);
            $cache = $calendarevent[$param->{notificationplugin::UI_ACTIVITY}]->timestart - $param->{notificationplugin::UI_TIME};

            if (!notificationsagent::was_launched_indicated_times(
                    $condition->ruleid, $condition->ruletimesfired, $courseid, notificationsagent::GENERIC_USERID
                )
                && !notificationsagent::is_ruleoff($condition->ruleid, notificationsagent::GENERIC_USERID)
            ) {
                notificationsagent::set_timer_cache(
                    notificationsagent::GENERIC_USERID, $courseid, $cache, $pluginname, $condtionid, false
                );

                notificationsagent::set_time_trigger(
                    $condition->ruleid, $condtionid, notificationsagent::GENERIC_USERID, $courseid, $cache
                );
            }
        }

        custom_mtrace("calendareventto end ");
    }
}
