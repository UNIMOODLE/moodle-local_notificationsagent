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
defined('MOODLE_INTERNAL') || die();
require_once(__DIR__ .'/../coursestart.php');
require_once(__DIR__ .'/../../../notificationsagent.php');
require_once(__DIR__ .'/../../../classes/engine/notificationsagent_engine.php');
use notificationsagent\notificationsagent;
class notificationscondition_coursestart_observer {

    public static function course_updated(\core\event\course_updated $event) {
        global $DB;

        /*Cuando se reciba un evento de tipo course_updated se buscará que condiciones tienen a ese evento como desencadenante.
        Sabiendo las condiciones se podrá encontrar las reglas que tengas esa condición y
        están asociadas al curso que desencadena el evento.
        Se evaluará la condición para el curso y alumno correspondiente calculando la fecha de cumplimiento
        de la condición.
        Si por ejemplo TTTT fuera 10 días y el evento ocurre el 12/05/2023 el método de evaluación buscará en la
        tabla mdl_course.startdate
        la fecha de inicio del curso. Hará el cálculo y devolverá 22/05/2023. Este valor se guardaría en la tabla de caché.*/

        if (!isloggedin() || $event->courseid == 1) {
            return;
        }

        $courseid = $event->courseid;
        $other = $event->other;
        // If stardate is not set in other array then the startdate setting has not been modified.
        if (isset($other["updatedfields"]["startdate"])) {
            $startdate = $other["updatedfields"]["startdate"];
        } else {
            return;
        }

        $pluginname = 'coursestart';
        $conditions = notificationsagent::get_conditions_by_course($pluginname, $courseid);

        foreach ($conditions as $condition) {
            $decode = $condition->parameters;
            $pluginname = $condition->pluginname;
            $condtionid = $condition->id;
            $param = json_decode($decode, true);
            $cache = $startdate + $param['time'];
            if (!notificationsagent::was_launched_indicated_times(
                $condition->ruleid, $condition->ruletimesfired, $courseid, notificationsagent::GENERIC_USERID)) {
                notificationsagent::set_timer_cache(
                    notificationsagent::GENERIC_USERID, $courseid, $cache, $pluginname, $condtionid, true
                );
                notificationsagent::set_time_trigger($condition->ruleid, notificationsagent::GENERIC_USERID, $courseid, $cache);
            }
        }
    }
}
