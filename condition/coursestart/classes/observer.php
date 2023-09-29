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

    public static function course_updated(core\event\course_updated $event) {
        global $DB;

        //Cuando se reciba un evento de tipo course_updated se buscará que condiciones tienen a ese evento como desencadenante. 
        //Sabiendo las condiciones se podrá encontrar las reglas que tengas esa condición y están asociadas al curso que desencadena el evento.
        //Se evaluará la condición para el curso y alumno correspondiente calculando la fecha de cumplimiento de la condición.
        //Si por ejemplo TTTT fuera 10 días y el evento ocurre el 12/05/2023 el método de evaluación buscará en la tabla mdl_course.startdate
        //la fecha de inicio del curso. Hará el cálculo y devolverá 22/05/2023. Este valor se guardaría en la tabla de caché.

        if (!isloggedin() || $event->courseid == 1) {
            return;
        }

        $userid = $event->userid;
        $courseid = $event->courseid;
        $timeaccess = $event->timecreated;
        $other = $event->other;

        $startdate = $DB->get_field('course','startdate', ['id'=> $courseid],);

        if(isset($other["updatedfields"]["startdate"])){
            $startdate = $other["updatedfields"]["startdate"];
        }

        $rule = new \stdClass();
        $rule->ruleid = null;
        $session = new notificationsagent_condition_coursestart($rule);
        $pluginname = $session->get_subtype();
        $conditions = notificationsagent::get_conditions_by_course($pluginname, $courseid);
        $ruleids = [];
        foreach ($conditions as $condition){
            $decode = $condition->parameters;
            $pluginname = $condition->pluginname;
            $condtionid = $condition->id;
            $ruleids[] = $condition->ruleid;
            $param = json_decode($decode, true);
            $cache = $startdate + $param['time'];
            notificationsagent::set_timer_cache($userid, $courseid, $cache, $pluginname, $condtionid, true);
        }
        
        // Call engine with userid, courseid, timecreated
        Notificationsagent_engine::notificationsagent_engine_evaluate_rule($ruleids, $timeaccess, $userid);

    }

}
