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

$string['pluginname'] = 'Axente de notificacións';
// Configuración.
$string['settings'] = 'Configuración';
$string['disable_user_use'] = 'Desactivar para o usuario';
$string['disable_user_use_desc'] = 'Desactivar o uso do axente de notificacións para o usuario';
$string['tracelog'] = 'Rexistro de rastrexo';
$string['tracelog_desc'] = 'Rexistro de rastrexo. Desactivar nos sitios de produción';
$string['startdate'] = 'Configuración da data da actividade';
$string['startdate_desc'] = 'Utiliza unha liña para cada actividade co patrón: pluginname|tablename|startdate|startend';

// Configuración dos subplugins.
$string['notificationaction_action'] = 'Acción do tipo de subcomplementos ';

$string['managenotificationsactionplugins'] = 'Xestionar complementos de acción';
$string['managenotificationsconditionplugins'] = 'Xestionar complementos de condicións';

$string['manageactionplugins'] = 'Xestionar complementos de acción';
$string['manageconditionplugins'] = 'Xestionar complementos de condicións';

$string['actionplugins'] = 'Complementos de acción';
$string['conditionplugins'] = 'Condición de complementos';

$string['notificationsactionpluginname'] = 'Acción do complemento';
$string['notificationsconditionpluginname'] = 'Condición do complemento';

$string['hideshow'] = 'Ocultar/Mostrar';

// Tarefa.
$string['tatasktriggerssk'] = 'Tarefa de activación de notificacións';
$string['menu'] = 'O meu asistente';
$string['heading'] = 'Axente de notificación';

// Modelo de estado.
$string['status_active'] = 'Activo';
$string['status_paused'] = 'En pausa';
$string['status_required'] = 'Requirido';

// Importar modelo.
$string['import'] = 'Importar';
$string['no_file_selected'] = 'Non se seleccionou ningún ficheiro';
$string['import_success'] = 'Regra importada correctamente';
$string['import_error'] = 'Non se puido importar a regra, comprobe o ficheiro JSON';
$string['no_json_file'] = 'O ficheiro non é JSON';

// Exportar modelo.
$string['export'] = 'Exportar';
$string['ruledownload'] = 'Exportar regra como';

// Asignar modelo.
$string['assign'] = 'Asignar';
$string['type_template'] = 'Modelo';
$string['type_rule'] = 'Regra';

// Condicionar complementos.
$string['condition_days'] = 'Días';
$string['condition_hours'] = 'Horas ';
$string['condition_minutes'] = 'Minutos';
$string['condition_seconds'] = 'Segundos';

// Editar regra.
$string['editrule_newrule'] = 'Nova regra';
$string['editrule_activaterule'] = 'Activar';
$string['editrule_pauserule'] = 'Pausa';
$string['ediditrule_editrule'] = 'Editar';
$string['ediditrule_deleterule'] = 'Eliminar';
$string['editrule_newtemplate'] = 'Novo modelo';
$string['editrule_title'] = 'Título';
$string['editrule_type'] = 'Tipo de regra';
$string['editrule_usetemplate'] = 'Seleccionar';
$string['editrule_sharerule'] = 'Compartir';
$string['editrule_unsharerule'] = 'Non compartir';
$string['editrule_shareallrule'] = 'Compartir todo';
$string['editrule_sharedallrule'] = 'Compartido';
$string['editrule_timesfired'] = 'Número de veces disparado';
$string['editrule_runtime'] = 'Periocidade';

// Condition.
$string['editrule_generalconditions'] = 'Condicións xerais';
$string['editrule_newcondition'] = 'Nova condición:';
$string['editrule_condition_title_tocloseactivity'] = 'Queda menos de [TTTT] para que se peche a actividade [AAAA]';
$string['editrule_condition_title_usercompleteactivity'] = 'O usuario completou a actividade [AAAA]';
$string['editrule_condition_title_activeactivity'] = 'A actividade [AAAA] está dispoñible';
$string['editrule_condition_title_betweendates'] = 'Estamos entre a data [FFFF-1] e [FFFF-2]';
$string['editrule_condition_title_accessforumactivitiescomplete'] = 'Completa todas as actividades para acceder a este foro [FFFF]';
$string['editrule_condition_title_forumnotanswer'] = 'Unha conversa aberta polo usuario no foro [FFFF] sen responder durante máis de [TTTT] tempo';

$string['editrule_condition_element_time'] = 'Hora {$a->typeelement}:';
$string['editrule_condition_element_activity'] = 'Actividade {$a->typeelement}:';
$string['editrule_condition_element_date_from'] = get_string('de').' {$a->typeelement}:';
$string['editrule_condition_element_date_to'] = get_string('to').' {$a->typeelement}:';

// Accións.
$string['editrule_newaction'] = 'Nova acción:';
$string['editrule_action_title_individualnotification'] = 'Enviar notificación individual con título [TTTT] e mensaxe [BBBB]';
$string['editrule_action_title_notificationtouser'] = 'Enviar notificación a un usuario específico [UUUU] con título [TTTT] e mensaxe [BBBB]';
$string['editrule_action_title_postgeneralforum'] = 'Publicar unha publicación xeral no foro [FFFF] con título [TTTT] e mensaxe [BBBB]';
$string['editrule_action_title_postprivateforum'] = 'Publicar unha publicación privada no foro [FFFF] con título [TTTT] e mensaxe [BBBB]';
$string['editrule_action_title_addusertogroup'] = 'Engadir usuario ao grupo [GGGG]';
$string['editrule_action_title_removeuserfromgroup'] = 'Eliminar un usuario dun grupo [GGGG]';
$string['editrule_action_title_bootstrapnotification'] = 'Notificación de arranque';

$string['editrule_action_element_title'] = 'Título {$a->typeelement}:';
$string['editrule_action_element_message'] = 'Mensaxe {$a->typeelement}:';
$string['editrule_action_element_user'] = 'Usuario {$a->typeelement}:';
$string['editrule_action_element_forum'] = 'Foro {$a->typeelement}:';
$string['editrule_action_element_group'] = 'Grupo {$a->typeelement}';

$string['subplugintype_notificationsagentaction'] = 'Acción de subplugins';

// Rule.
$string['rulecancelled'] = 'Regra cancelada';
$string['rulesaved'] = 'Gardouse a regra';

// Contido da tarxeta.
$string['cardif'] = 'Si:';
$string['cardunless'] = 'Excepto se:';
$string['cardthen'] = 'Entón:';

// Tempo de condición da tarxeta.
$string['card_day'] = 'día';
$string['card_hour'] = 'hora';
$string['card_minute'] = 'minuto';
$string['card_second'] = 'segundo';

// Estado modal.
$string['statustitle'] = '{$a->textstatus} regra {$a->title}';
$string['statuscontent'] = 'Vas a regra {$a->textstatus} {$a->title}, queres continuar?';
$string['statuscancel'] = 'Cancelar';
$string['statusaccept'] = 'Aceptar';
$string['statusacceptactivated'] = 'Regra activada';
$string['statusacceptpaused'] = 'Regra en pausa';

// Eliminar modal.
$string['deletetitle'] = 'Eliminar o {$a->type} {$a->title}';
$string['deletecontent_nocontext'] = '{$a->type} {$a->title} estase a eliminar, queres continuar?';
$string['deletecontent_hascontext'] = 'O {$a->type} {$a->title} que quere eliminar está asociado con outros contextos, quere continuar?';
$string['deleteaccept'] = 'Regra eliminada';

// Asignar modal.
$string['assignassign'] = 'Asignar: ';
$string['assigncancel'] = 'Cancelar';
$string['assignsave'] = 'Gardar os cambios';
$string['assignforced'] = 'Asignar a regra á forza';

// Compartir modal.
$string['sharetitle'] = 'Compartir regra {$a->title}';
$string['sharecontent'] = 'A regra {$a->title} estase compartindo co administrador, queres continuar?';
$string['unsharetitle'] = 'Descompartir a regra {$a->title}';
$string['unsharecontent'] = 'A regra {$a->title} está a piques de deixarse de compartir co administrador, queres continuar?';
$string['shareaccept'] = 'Regra compartida';
$string['unshareaccept'] = 'Regra non compartida';

// Compartir todos os modais.
$string['sharealltitle'] = 'Aprobar a regra de uso compartido {$a->title}';
$string['shareallcontent'] = 'A regra de uso compartido {$a->title} está a piques de ser aprobada, queres continuar?';

// Capabilities.
$string['notificationsagent:createrule'] = 'Crear unha regra';
$string['notificationsagent:etrule'] = 'Actualizar unha regra';
$string['notificationsagent:checkrulecontext'] = 'Comprobar o contexto dunha regra';
$string['notificationsagent:deleterule'] = 'Eliminar unha regra';
$string['notificationsagent:updaterulestatus'] = 'Actualizar o estado dunha regra';
$string['notificationsagent:exportrule'] = 'Exportar unha regra';
$string['notificationsagent:importrule'] = 'Importar unha regra';
$string['notificationsagent:assignrule'] = 'Asignar unha regra';
$string['notificationsagent:forcerule'] = 'Forzar unha regra';
$string['notificationsagent:updateruleshare'] = 'Actualizar o estado de uso compartido dunha regra';
$string['notificationsagent:shareruleall'] = 'Aprobar a compartición dunha regra';
$string['notificationsagent:managesiterule'] = 'Xestionar regras de nivel de sitio';
$string['notificationsagent:managecourserule'] = 'Xestionar regras a nivel de curso';
$string['notificationsagent:manageownrule'] = 'Xestiona as túas propias regras no curso';
$string['notificationsagent:viewassistantreport'] = 'Ver informe regra';

$string['notificationsagent:activitycompleted'] = 'Capacidade de usar a condición de actividade completada';
$string['notificationsagent:activityopen'] = 'Capacidade de usar a condición activityopen';
$string['notificationsagent:coursestart'] = 'Capacidade de usar a condición de inicio do curso';
$string['notificationsagent:calendarstart'] = 'Capacidade de usar a condición de calendarstart';
$string['notificationsagent:sessionstart'] = 'Capacidade de usar a condición de inicio de sesión';
$string['notificationsagent:activityavailable'] = 'Capacidade necesaria para utilizar a condición de actividade dispoñible';
$string['notificationsagent:activityend'] = 'Capacidade necesaria para utilizar a condición de finalización da actividade';
$string['notificationsagent:activitylastsend'] = 'Capacidade necesaria para utilizar a condición de último envío da actividade';
$string['notificationsagent:activitymodified'] = 'Capacidade necesaria para utilizar a condición de modificación da actividade';
$string['notificationsagent:activitynewcontent'] = 'Capacidade necesaria para utilizar a condición de contido novo da actividade';
$string['notificationsagent:activitysinceend'] = 'Capacidade necesaria para utilizar a actividade desde a condición final';
$string['notificationsagent:activitystudentend'] = 'Capacidade necesaria para utilizar a condición de finalización do alumno da actividade';
$string['notificationsagent:calendareventto'] = 'Capacidade necesaria para usar o evento do calendario para condicionar';
$string['notificationsagent:courseend'] = 'Capacidade necesaria para utilizar a condición de fin de curso';
$string['notificationsagent:forumnoreply'] = 'Necesítase capacidade para utilizar a condición de sen resposta do foro';
$string['notificationsagent:numberoftimes'] = 'Capacidade necesaria para utilizar a condición de número de veces';
$string['notificationsagent:sessionend'] = 'Capacidade necesaria para utilizar a condición de finalización da sesión';
$string['notificationsagent:weekend'] = 'Capacidade necesaria para usar a condición de fin de semana';

$string['notificationsagent:addusergroup'] = 'Capacidade de usar a acción addusergroup';
$string['notificationsagent:bootstrapnotifications'] = 'Capacidade de usar a acción bootstrapnotifications';
$string['notificationsagent:forummessage'] = 'Capacidade de usar a acción forummessage';
$string['notificationsagent:messageagent'] = 'Capacidade de usar a acción messageagent';
$string['notificationsagent:removeusergroup'] = 'Capacidade de usar a acción removeusergroup';
$string['notificationsagent:usermessageagent'] = 'Capacidade de usar a acción usermessageagent';
$string['notificationsagent:privateforummessage'] = 'Capacidade de usar a acción privateforummessage';

// Servizos web.
$string['nosuchinstance'] = 'Non se atopou esta instancia.';
$string['isnotrule'] = 'Este identificador de regra non pertence a unha regra.';

// Report.
$string['rulename'] = 'Nome da regra';
$string['report'] = 'Informe do axente de notificación';
$string['id'] = 'id';
$string['ruleid'] = 'ID da regra';
$string['fullrule'] = 'Regra';
$string['userid'] = 'ID de usuario';
$string['fulluser'] = 'Usuario';
$string['fullcourse'] = 'Curso';
$string['courseid'] = 'ID do curso';
$string['actionid'] = 'ID de acción';
$string['fullaction'] = 'Acción';
$string['actiondetail'] = 'Detalle da acción';
$string['timestamp'] = 'Data';

// Nav.
$string['conditions'] = 'Condicións';
$string['exceptions'] = 'Excepcións';
$string['actions'] = 'Accións';