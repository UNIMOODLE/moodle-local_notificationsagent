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
 * @category   string
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Agente de notificaciones';
// Settings.
$string['settings'] = 'Ajustes';
$string['disable_user_use'] = 'Deshabilitar para el usuario';
$string['disable_user_use_desc'] = 'Deshabilitar el uso del Agente de notificaciones para el usuario';
$string['tracelog'] = 'Trace log';
$string['tracelog_desc'] = 'Trace log. Deshabilitar en sitios en producción';
$string['startdate'] = 'Configuración de fechas de actividad';
$string['startdate_desc'] = 'Usar una línea por cada actividad con el patrón: pluginname|tablename|startdate|startend';

// Subplugins settings.
$string['notificationaction_action'] = 'Subplugins tipo action ';

$string['managenotificationsactionplugins'] = 'Gestionar plugins tipo action';
$string['managenotificationsconditionplugins'] = 'Gestionar plugins tipo condition';

$string['manageactionplugins'] = 'Gestionar plugins action';
$string['manageconditionplugins'] = 'Gestionar plugins condition';

$string['actionplugins'] = 'Plugins tipo action';
$string['conditionplugins'] = 'Plugins tipo condition';

$string['notificationsactionpluginname'] = 'Plugin action';
$string['notificationsconditionpluginname'] = 'Plugin condition';

$string['hideshow'] = 'Ocultar/Mostrar';

// Task.
$string['tatasktriggerssk'] = 'Tarea de desencadenadores de notificaciones';
$string['menu'] = 'Mi asistente';
$string['heading'] = 'Agente de Notificaciones';

// Status Template.
$string['status_active'] = 'Activa';
$string['status_paused'] = 'Pausada';
$string['status_required'] = 'Obligatoria';

// Import Template.
$string['import'] = 'Import';
$string['no_file_selected'] = 'Ningún archivo seleccionado';
$string['import_success'] = 'Regla importada correctamente';
$string['import_error'] = 'No se ha podido importar la regla, revisa el archivo JSON';
$string['no_json_file'] = 'El archivo no es JSON';

// Export Template.
$string['export'] = 'Exportar';
$string['ruledownload'] = 'Exportar regla como';

// Assign Template.
$string['assign'] = 'Asignar';
$string['type_template'] = 'Plantilla';
$string['type_rule'] = 'Regla';

// Condition plugins.
$string['condition_days'] = 'Días';
$string['condition_hours'] = 'Horas';
$string['condition_minutes'] = 'Minutos';
$string['condition_seconds'] = 'Segundos';

// EditRule.
$string['editrule_newrule'] = 'Nueva regla';
$string['editrule_reports'] = 'Informes';
$string['editrule_activaterule'] = 'Activar';
$string['editrule_pauserule'] = 'Pausar';
$string['editrule_editrule'] = 'Editar';
$string['editrule_reportrule'] = 'Informe';
$string['editrule_deleterule'] = 'Borrar';
$string['editrule_newtemplate'] = 'Nueva plantilla';
$string['editrule_title'] = 'Título';
$string['editrule_type'] = 'Tipo de regla';
$string['editrule_usetemplate'] = 'Seleccionar';
$string['editrule_sharerule'] = 'Compartir';
$string['editrule_unsharerule'] = 'Descompartir';
$string['editrule_shareallrule'] = 'Compartir todos';
$string['editrule_sharedallrule'] = 'Compartido';
$string['editrule_timesfired'] = 'Nº de veces ejecución';
$string['editrule_runtime'] = 'Periocidad';

// Condition.
$string['editrule_generalconditions'] = 'Condiciones generales';
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

$string['editrule_condition_error'] = 'Debe añadir al menos una condición';

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

$string['subplugintype_notificationsagentaction'] = 'Subplugins action';

$string['editrule_action_error'] = 'Debe añadir al menos una acción';

// Rule.
$string['rulecancelled'] = 'Regla cancelada';
$string['rulesaved'] = 'Regla guardada';

// Card content.
$string['cardif'] = 'Si:';
$string['cardunless'] = 'Excepto si:';
$string['cardthen'] = 'Entonces:';

// Card Condition time.
$string['card_day'] = 'día';
$string['card_day_plural'] = 'días';
$string['card_hour'] = 'hora';
$string['card_hour_plural'] = 'horas';
$string['card_minute'] = 'minuto';
$string['card_minute_plural'] = 'minutos';
$string['card_second'] = 'segundo';
$string['card_second_plural'] = 'segundos';

// Status modal.
$string['status_pausetitle'] = 'Pausar regla {$a->title}';
$string['status_activatetitle'] = 'Activar regla {$a->title}';
$string['status_pausecontent'] = 'Se va a pausar la regla {$a->title}, ¿desea continuar?';
$string['status_activatecontent'] = 'Se va a activar la regla {$a->title}, ¿desea continuar?';
$string['status_acceptactivated'] = 'Regla activada';
$string['status_acceptpaused'] = 'Regla pausada';
$string['statuspause'] = 'Pausar';
$string['statusactivate'] = 'Activar';

// Delete modal.
$string['deletetitle'] = 'Borrar la {$a->type} {$a->title}';
$string['deletecontent_nocontext'] = 'Se va a borrar {$a->type} {$a->title}, ¿desea continuar?';
$string['deletecontent_hascontext'] = 'La {$a->type} {$a->title} que desea borrar, se encuentra asociada a otros contextos, ¿desea continuar?';
$string['deleteaccept'] = 'Regla borrada';

// Assign modal.
$string['assignassign'] = 'Assignar: ';
$string['assigncancel'] = 'Cancelar';
$string['assignsave'] = 'Guardar cambios';
$string['assignforced'] = 'Asignar la regla forzosamente';

// Share modal.
$string['sharetitle'] = 'Compartir la regla {$a->title}';
$string['sharecontent'] = 'Se va a compartir la regla {$a->title} con el administrador, ¿desea continuar?';
$string['unsharetitle'] = 'Descompartir la regla {$a->title}';
$string['unsharecontent'] = 'Se va a descompartir la regla {$a->title} con el administrador, ¿desea continuar?';
$string['shareaccept'] = 'Regla compartida';
$string['unshareaccept'] = 'Regla descompartida';

// Share all modal.
$string['sharealltitle'] = 'Aprobar la compartición de la regla {$a->title}';
$string['shareallcontent'] = 'Se va a aprobar la compartición de la regla {$a->title}, ¿desea continuar?';

// Capabilities.
$string['notificationsagent:createrule'] = 'Crear una regla';
$string['notificationsagent:editrule'] = 'Actualizar una regla';
$string['notificationsagent:checkrulecontext'] = 'Comprobar el contexto de una regla';
$string['notificationsagent:deleterule'] = 'Borrar una regla';
$string['notificationsagent:updaterulestatus'] = 'Actualizar el estado de una regla';
$string['notificationsagent:exportrule'] = 'Exportar una regla';
$string['notificationsagent:importrule'] = 'Importar una regla';
$string['notificationsagent:assignrule'] = 'Asignar una regla';
$string['notificationsagent:forcerule'] = 'Forzar una regla';
$string['notificationsagent:updateruleshare'] = 'Actualizar el estado de compartición de una regla';
$string['notificationsagent:shareruleall'] = 'Aprobar la compartición de una regla';
$string['notificationsagent:managesiterule'] = 'Gestionar las reglas a nivel de sitio';
$string['notificationsagent:managecourserule'] = 'Gestionar las reglas a nivel de curso';
$string['notificationsagent:manageownrule'] = 'Gestionar tus propias reglas en el curso';
$string['notificationsagent:viewassistantreport'] = 'Ver informe de reglas';

$string['notificationsagent:activitycompleted'] = 'Capacidad para usar la condición activitycompleted';
$string['notificationsagent:activityopen'] = 'Capacidad para usar la condición activityopen';
$string['notificationsagent:coursestart'] = 'Capacidad para usar la condición coursestart';
$string['notificationsagent:calendarstart'] = 'Capacidad para usar la condición calendarstart';
$string['notificationsagent:sessionstart'] = 'Capacidad para usar la condición sessionstart';
$string['notificationsagent:activityavailable'] = 'Capacidad necesaria para utilizar la condición de actividad disponible';
$string['notificationsagent:activityend'] = 'Capacidad necesaria para utilizar la condición de fin de actividad';
$string['notificationsagent:activitylastsend'] = 'Capacidad necesaria para utilizar la condición de último envío de la actividad';
$string['notificationsagent:activitymodified'] = 'Capacidad necesaria para utilizar la condición de actividad modificada';
$string['notificationsagent:activitynewcontent'] = 'Capacidad necesaria para utilizar la condición de contenido nuevo de la actividad';
$string['notificationsagent:activitysinceend'] = 'Capacidad necesaria para utilizar la actividad desde la condición final';
$string['notificationsagent:activitystudentend'] = 'Capacidad necesaria para utilizar la actividad condición final del estudiante';
$string['notificationsagent:calendareventto'] = 'Capacidad necesaria para utilizar el evento del calendario para condicionar';
$string['notificationsagent:courseend'] = 'Capacidad necesaria para utilizar la condición de fin del curso';
$string['notificationsagent:forumnoreply'] = 'Capacidad necesaria para utilizar el foro sin condición de respuesta';
$string['notificationsagent:numberoftimes'] = 'Capacidad necesaria para utilizar la condición de número de veces';
$string['notificationsagent:sessionend'] = 'Capacidad necesaria para utilizar la condición de fin de sesión';
$string['notificationsagent:weekend'] = 'Capacidad necesaria para utilizar la condición de fin de semana';

$string['notificationsagent:addusergroup'] = 'Capacidad para usar la acción addusergroup';
$string['notificationsagent:bootstrapnotifications'] = 'Capacidad para usar la acción bootstrapnotifications';
$string['notificationsagent:forummessage'] = 'Capacidad para usar la acción forummessage';
$string['notificationsagent:messageagent'] = 'Capacidad para usar la acción messageagent';
$string['notificationsagent:removeusergroup'] = 'Capacidad para usar la acción removeusergroup';
$string['notificationsagent:usermessageagent'] = 'Capacidad para usar la acción usermessageagent';
$string['notificationsagent:privateforummessage'] = 'Capacidad para usar la acción privateforummessage';

// Webservices.
$string['nosuchinstance'] = 'Dicha instancia no ha sido encontrada.';
$string['isnotrule'] = 'Dicho identificador de regla no pertenece a una regla.';

// Report.
$string['rulename'] = 'Nombre de la regla';
$string['report'] = 'Informe del agente de notificaciones';
$string['id'] = 'id';
$string['ruleid'] = 'Id de la regla';
$string['fullrule'] = 'Regla';
$string['userid'] = 'Id de usuario';
$string['fulluser'] = 'Usuario';
$string['fullcourse'] = 'Curso';
$string['courseid'] = 'Id del curso';
$string['actionid'] = 'Ide de la acción';
$string['fullaction'] = 'Acción';
$string['actiondetail'] = 'Detalle de la acción';
$string['timestamp'] = 'Fecha';

// Nav.
$string['conditions'] = 'Condiciones';
$string['exceptions'] = 'Excepciones';
$string['actions'] = 'Acciones';
