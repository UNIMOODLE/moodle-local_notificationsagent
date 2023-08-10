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

/**
 * sessionstart observer.php .
 *
 * @package    sessionstart
 * @copyright  2023 fernando
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();
require_once(__DIR__ . '/../lib.php');
require_once(__DIR__ .'/../sessionstart.php');
class notificationscondition_sessionstart_observer {



    /**
     * @throws \dml_exception
     */
    public static function course_viewed(\core\event\course_viewed $event) {
        // We use this event to avoid querying the log_standard_log for a course firstaccess.
        if (!isloggedin() || $event->courseid == 1) {
            return;
        }
        global $DB;
        $userid = $event->userid;
        $courseid = $event->courseid;
        $timeaccess = $event->timecreated;
        // TODO
        //Cuando se reciba un evento de tipo course_viewed se buscará que condiciones tienen
        // a ese evento como desencadenante.
        // Sabiendo las condiciones se podrá encontrar las reglas que tengas esa condición y
        // están asociadas al curso que desencadena el evento.
        //Se evaluará la condición para el curso y alumno correspondiente calculando la fecha de cumplimiento de la condición.
        //Si por ejemplo TTTT fuera 10 días y el evento (primer inicio del curso)
        // ocurre el 12/05/2023 el método de evaluación de tiempo devolverá 22/05/2023.
        // Este valor se guardaría en la tabla de cache.
        //En los siguientes eventos course_viewed para ese curso y ese alumno se verificará
        // si hay algún valor guardado en la cache, de ser asi no se tocaría ya que el primer inicio de sesión no puede cambiar.
        // parámetros necesarios
        //condición para saber TTTT
        // Timeaccess que nos lo da el evento
        // Insertar en timer cache TTTT + timeacces
        $rule = new \stdClass();
        $rule->ruleid = null;
        $session = new notificationsagent_condition_sessionstart($rule);
        $pluginname =$session->get_subtype();

        $results = $DB->get_records_sql('
                select mnrc.parameters, mnrc.pluginname as pluginname
                from mdl_notifications_rule_plugins mnrc
                inner join mdl_notifications_rule mnr ON mnr.ruleid=mnrc.ruleid
                where pluginname=?
                and courseid=?',
                [$pluginname,$courseid]
            );

        foreach ($results as $result){
            $decode = $result->parameters;
            $pluginname = $result->pluginname;
            $param = json_decode($decode, true);
            $cache = $timeaccess + $param['time'];
            sessionstart_set_timer_cache($userid, $courseid, $cache, $pluginname);
        }
    }
}
