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
// Córdoba, Extremadura, Vigo, Las Palmas de Gran Canaria y Burgos

/**
 * Version details
 *
 * @package    local_notificationsagent
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class local_notificationsagent_observer {

    public static function user_graded(\core\event\user_graded $event) {
        // 20. El usuario ha obtenido una calificación [condición numérica] en el gradeitem [GGGG/cualquiera].
        // TRIGGER:EVERY TIME A USER GETS A GRADE
        // WARNING: TWO EVENTS ARE SENT, USER_ID=-1
        // The user with id userid updated the grade with id objectid for the user with
        // id relateduserid for the grade item with id other['itemid']
        // A null finalgrade means a grade from an activity. Only sending grades with value, including 0.
        $userid = $event->relateduserid;
        $courseid = $event->courseid;
        $objectid = $event->objectid; // Check id on table grade_grades.

    }

    public static function course_module_completion_updated(\core\event\course_module_completion_updated $event) {
        // 19. El usuario tiene completada la actividad [AAAA].
        $userid = $event->relateduserid;
        $courseid = $event->courseid;
    }

    public static function course_module_updated(\core\event\course_module_updated $event) {
        // 16. Se ha modificado el contenido de la actividad [AAAA] (por ejemplo, ficheros en una carpeta, nueva versión de un PDF).
        $userid = $event->relateduserid;
        $courseid = $event->courseid;
    }

    public static function group_member_added(\core\event\group_member_added $event) {
        // 17. El usuario se ha añadido a un grupo [Lista de Grupos/Agrupamiento].
        $userid = $event->relateduserid;
        $courseid = $event->courseid;

    }

    public static function course_module_created(\core\event\course_module_created $event) {
        // 15. Hay nuevo contenido en el curso de tipo [AAAA/cualquiera] (excepto etiquetas).
        $userid = $event->relateduserid;
        $courseid = $event->courseid;

    }
}
