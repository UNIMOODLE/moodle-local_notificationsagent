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
 * @package    notificationscondition_coursestart
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace notificationscondition_coursestart\task;

use core\task\scheduled_task;
use local_notificationsagent\notificationsagent;
use notificationscondition_coursestart\coursestart;
use local_notificationsagent\evaluationcontext;

/**
 * Class coursestart_crontask
 */
class coursestart_crontask extends scheduled_task {

    /**
     * Get a descriptive name for the task (shown to admins)
     *
     * @return string
     */
    public function get_name() {
        return get_string('coursestart_crontask', 'notificationscondition_coursestart');
    }

    /**
     * Throw exceptions on errors (the job will be retried).
     */
    public function execute() {
        \local_notificationsagent\helper\helper::custom_mtrace("Coursestart start");

        $pluginname = coursestart::NAME;
        $conditions = notificationsagent::get_conditions_by_plugin($pluginname);
        foreach ($conditions as $condition) {
            $courses = $condition->courses;
            $subplugin = new coursestart($condition->ruleid, $condition->id);
            $context = new evaluationcontext();
            $context->set_params($subplugin->get_parameters());
            $context->set_complementary($subplugin->get_iscomplementary());
            $context->set_timeaccess($this->get_timestarted());
            if (!empty($courses)) {
                foreach ($courses as $courseid) {
                    $context->set_courseid($courseid);
                    notificationsagent::generate_cache_triggers($subplugin, $context);
                }
            }
        }

        \local_notificationsagent\helper\helper::custom_mtrace("Coursestart end");
    }
}
