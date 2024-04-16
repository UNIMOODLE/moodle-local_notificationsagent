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
 * @package    notificationscondition_calendarstart
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace notificationscondition_calendarstart\task;

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->dirroot . '/local/notificationsagent/lib.php');
require_once(__DIR__ . '/../../../../../../calendar/lib.php');

use core\task\scheduled_task;
use local_notificationsagent\notificationplugin;
use local_notificationsagent\notificationsagent;
use notificationscondition_calendarstart\calendarstart;
use local_notificationsagent\evaluationcontext;

class calendarstart_crontask extends scheduled_task {

    /**
     * Get a descriptive name for the task (shown to admins)
     *
     * @return string
     */
    public function get_name() {
        return get_string('calendarstart_crontask', 'notificationscondition_calendarstart');
    }

    /**
     * Throw exceptions on errors (the job will be retried).
     */
    public function execute() {
        custom_mtrace("calendarstart start");

        $pluginname = get_string('subtype', 'notificationscondition_calendarstart');
        $conditions = notificationsagent::get_conditions_by_plugin($pluginname);
        foreach ($conditions as $condition) {
            $courseid = $condition->courseid;

            $conditionid = $condition->id;

            $subplugin = new calendarstart(null, $conditionid);
            $context = new evaluationcontext();
            $context->set_params($subplugin->get_parameters());
            $context->set_complementary($subplugin->get_iscomplementary());
            $context->set_timeaccess($this->get_timestarted());
            $context->set_courseid($courseid);

            $cache = $subplugin->estimate_next_time($context);

            if (empty($cache)) {
                continue;
            }

            // $calendarevent = calendar_get_events_by_id([$param->{notificationplugin::UI_ACTIVITY}]);
            // if ($param->{calendarstart::UI_RADIO} == 1) {
            //     $cache = $calendarevent[$param->{notificationplugin::UI_ACTIVITY}]->timestart
            //         + $param->{notificationplugin::UI_TIME};
            // } else {
            //     $cache = $calendarevent[$param->{notificationplugin::UI_ACTIVITY}]->timestart +
            //         $calendarevent[$param->{notificationplugin::UI_ACTIVITY}]->timeduration + $param->{notificationplugin::UI_TIME};
            // }

            if (!notificationsagent::was_launched_indicated_times(
                    $condition->ruleid, $condition->ruletimesfired, $courseid, notificationsagent::GENERIC_USERID
                )
                && !notificationsagent::is_ruleoff($condition->ruleid, notificationsagent::GENERIC_USERID)
            ) {
                notificationsagent::set_timer_cache(
                    notificationsagent::GENERIC_USERID, $courseid, $cache, $pluginname, $conditionid
                );
                notificationsagent::set_time_trigger(
                    $condition->ruleid, $conditionid, notificationsagent::GENERIC_USERID, $courseid, $cache
                );
            }

        }
        custom_mtrace("calendarstart end ");
    }
}
