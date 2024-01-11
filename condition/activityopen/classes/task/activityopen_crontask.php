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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
namespace notificationscondition_activityopen\task;
defined('MOODLE_INTERNAL') || die();
require_once(__DIR__ . '/../../../../notificationsagent.php');
require_once(__DIR__ . '/../../../../classes/engine/notificationsagent_engine.php');
require_once(__DIR__ . '/../../../../lib.php');

use core\task\scheduled_task;
use core_reportbuilder\local\helpers\custom_fields;
use notificationsagent\notificationsagent;

class activityopen_crontask extends scheduled_task {

    /**
     * Get a descriptive name for the task (shown to admins)
     *
     * @return string
     */
    public function get_name() {
        return get_string('activityopen_crontask', 'notificationscondition_activityopen');
    }

    /**
     * Throw exceptions on errors (the job will be retried).
     */
    public function execute() {
        custom_mtrace("Activity open start");
        /*
        * Debido a que las condiciones temporales disponen de un método para calcular la próxima fecha en que se cumplirán,
        * solo se volverán a evaluar cuando un evento específico requiera esa información.
        * De lo contrario, se utilizará la fecha almacenada en caché para evitar cálculos innecesarios y
        * reducir la carga en el sistema.
        * */

        $pluginname = 'activityopen';
        $conditions = notificationsagent::get_conditions_by_plugin($pluginname);

        foreach ($conditions as $condition) {
            $courseid = $condition->courseid;
            $condtionid = $condition->id;
            $decode = $condition->parameters;
            $param = json_decode($decode, true);
            $cmid = $param['activity'];
            $timestart = notificationsagent::notificationsagent_condition_get_cm_dates($cmid)->timestart;
            $cache = $timestart + $param['time'];
            if (!notificationsagent::was_launched_indicated_times(
                $condition->ruleid, $condition->ruletimesfired, $courseid, notificationsagent::GENERIC_USERID)) {
                notificationsagent::set_timer_cache(
                    notificationsagent::GENERIC_USERID, $courseid, $cache, $pluginname, $condtionid, false
                );
            }
        }

        custom_mtrace("Activity open end ");

    }
}
