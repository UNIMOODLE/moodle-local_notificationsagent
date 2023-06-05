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

/**
 * Plugin strings are defined here.
 *
 * @package     local_notificationsagent
 * @category    string
 * @copyright   2023 ISYC
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Agente de notificaciones';
// Settings.
$string['disable_user_use'] = 'Deshabilitar para el usuario';
$string['disable_user_use_desc'] = 'Deshabilitar el uso del Agente de notificaciones para el usuario';
$string['tracelog'] = 'Trace log';
$string['tracelog_desc'] = 'Trace log. Deshabilitar en sitios en producción';

// Task.
$string['task'] = 'Test Task';
$string['menu'] = 'Agente de notificaciones';

// EditRule.
    $string['editrule_newrule'] = 'Nueva regla';
    $string['editrule_title'] = 'Título';
    // Condition.
    $string['editrule_newcondition'] = 'Nueva condición:';
    $string['editrule_condition_title_tocloseactivity'] = 'Queda menos de [TTTT] para el cierre de la actividad [AAAA]';
    $string['editrule_condition_title_usercompleteactivity'] = 'El usuario tiene completada la actividad [AAAA]';
    $string['editrule_condition_title_activeactivity'] = 'La actividad [AAAA] está disponible';
    $string['editrule_condition_title_betweendates'] = 'Estamos entre la fecha [FFFF-1] y [FFFF-2]';
    $string['editrule_condition_title_accessforumactivitiescomplete'] = 'Completa todas las actividades para acceder a este foro [FFFF]';
    $string['editrule_condition_title_forumnotanswer'] = 'Un hilo abierto por el usuario en el foro [FFFF] sin responder más de [TTTT] tiempo';

        $string['editrule_condition_element_time'] = 'Tiempo {$a->typeelement}:';
        $string['editrule_condition_element_activity'] = 'Actividad {$a->typeelement}:';
        $string['editrule_condition_element_date_from'] = get_string('from').' {$a->typeelement}:';
        $string['editrule_condition_element_date_to'] = get_string('to').' {$a->typeelement}:';
    // Actions.
    $string['editrule_newaction'] = 'Nueva acción:';
    $string['editrule_action_title_individualnotification'] = 'Enviar notificación individual con título [TTTT] y mensaje [BBBB]';
    $string['editrule_action_title_notificationtouser'] = 'Enviar notificación a un usuario concreto [UUUU] con título [TTTT] y mensaje [BBBB]';
    $string['editrule_action_title_postgeneralforum'] = 'Publicar un post general en el foro [FFFF] con título [TTTT] y mensaje [BBBB]';
    $string['editrule_action_title_postprivateforum'] = 'Publicar un post privado en el foro [FFFF] con título [TTTT] y mensaje [BBBB]';
    $string['editrule_action_title_addusertogroup'] = 'Añadir un usuario a grupo [GGGG]';
    $string['editrule_action_title_removeuserfromgroup'] = 'Eliminar un usuario de un grupo [GGGG]';
    $string['editrule_action_title_bootstrapnotification'] = 'Notificación bootstrap';

        $string['editrule_action_element_title'] = 'Título {$a->typeelement}:';
        $string['editrule_action_element_message'] = 'Mensaje {$a->typeelement}:';
        $string['editrule_action_element_user'] = 'Usuario {$a->typeelement}:';
        $string['editrule_action_element_forum'] = 'Foro {$a->typeelement}:';
        $string['editrule_action_element_group'] = 'Grupo {$a->typeelement}';
