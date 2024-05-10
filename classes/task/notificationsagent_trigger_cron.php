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

namespace local_notificationsagent\task;

use core\task\scheduled_task;
use local_notificationsagent\engine\notificationsagent_engine;
use local_notificationsagent\notificationsagent;

/**
 * Class to define the task to trigger notifications agent.
 */
class notificationsagent_trigger_cron extends scheduled_task {
    /**
     * Get the name using the get_string function from the local_notificationsagent plugin.
     *
     * @return string the retrieved name
     */
    public function get_name() {
        return get_string('tasktriggers', 'local_notificationsagent');
    }

    /**
     * Execute the cron.
     */
    public function execute() {
        $timestarted = $this->get_timestarted();
        \local_notificationsagent\helper\helper::custom_mtrace("Task started-> " . $timestarted);
        // Get cron task lastrun.
        $tasklastrunttime = get_config('local_notificationsagent', 'cronlastrun');
        // Rules in the interval  $timestarted and $tasklastrunttime.
        $triggers = notificationsagent::get_triggersbytimeinterval($timestarted, $tasklastrunttime);
        // Set cron task lastrun.
        set_config('cronlastrun', $timestarted, 'local_notificationsagent');
        // Evalutate rules.
        foreach ($triggers as $trigger) {
            notificationsagent_engine::notificationsagent_engine_evaluate_rule(
                [$trigger->ruleid], $timestarted, $trigger->userid, $trigger->courseid, $trigger->conditionid, $trigger->startdate
            );
        }
        \local_notificationsagent\helper\helper::custom_mtrace("Task finished-> " . time());
        \local_notificationsagent\helper\helper::custom_mtrace("RUNTIME: " . $tasklastrunttime);
    }
}
