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

// Project implemented by the "Recovery, Transformation and Resilience Plan.
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

$string['actiondetail'] = 'Detalle da acción';
$string['actionerror'] = 'Erro ao realizar a acción ';
$string['actionid'] = 'ID de acción';
$string['actionplugins'] = 'Complementos de acción';
$string['actions'] = 'Accións';
$string['admin_breadcrumb'] = 'Axente de notificacións Admin';
$string['assign'] = 'Seleccionar';
$string['assignassign'] = 'Asignar: ';
$string['assigncancel'] = 'Cancelar';
$string['assignforced'] = 'Asignar a regra á forza';
$string['assignsave'] = 'Gardar os cambios';
$string['assignselectcourses'] = 'Marcar cursos';
$string['assignselectedinfo'] = '{$a->courses} cursos seleccionados e categorías {$a->categorías}';
$string['assignunselectcourses'] = 'Desmarcar cursos';
$string['brokenrulebody'] = 'A regra: {$a->rule} do curso :{$a->course} foi detida.';
$string['cachedef_launched'] = 'Lista de usuarios cuxa recorrencia comezou.';
$string['card_day'] = 'día';
$string['card_day_plural'] = 'días';
$string['card_hour'] = 'hora';
$string['card_hour_plural'] = 'horas';
$string['card_minute'] = 'minuto';
$string['card_minute_plural'] = 'minutos';
$string['card_second'] = 'segundo';
$string['card_second_plural'] = 'segundos';
$string['cardif'] = 'Se todo:';
$string['cardsharedby'] = 'Compartido por {$a->username} no curso {$a->coursename}';
$string['cardthen'] = 'Entón:';
$string['cardunless'] = 'A non ser que:';
$string['condition_days'] = 'Días';
$string['condition_grade'] = 'Cualificación';
$string['condition_hours'] = 'Horas ';
$string['condition_minutes'] = 'Minutos';
$string['condition_seconds'] = 'Segundos';
$string['conditionplugins'] = 'Condición de complementos';
$string['conditions'] = 'Condicións';
$string['course_breadcrumb'] = 'Axente de notificacións';
$string['courseid'] = 'ID do curso';
$string['deleteaccept'] = 'Regra eliminada';
$string['deletecontent_hascontext']
    = 'O {$a->type} {$a->title} que quere eliminar está asociado con outros contextos, quere continuar?';
$string['deletecontent_nocontext'] = '{$a->type} {$a->title} estase a eliminar, queres continuar?';
$string['deletetitle'] = 'Eliminar o {$a->type} {$a->title}';
$string['disable_user_use'] = 'Desactivar para o usuario';
$string['disable_user_use_desc'] = 'Desactivar o uso do axente de notificacións para o usuario';
$string['ediditrule_deleterule'] = 'Eliminar';
$string['ediditrule_editrule'] = 'Editar';
$string['editrule_action_error'] = 'Debes engadir polo menos unha acción';
$string['editrule_activaterule'] = 'Activar';
$string['editrule_clonerule'] = 'Engadir regra';
$string['editrule_condition_error'] = 'Debes engadir polo menos unha condición';
$string['editrule_execution_error'] = '{$a->timesfired} entre {$a->minimum} e {$a->maximum}';
$string['editrule_generalconditions'] = 'Condicións xerais';
$string['editrule_newaction'] = 'Nova acción:';
$string['editrule_newcondition'] = 'Nova condición:';
$string['editrule_newrule'] = 'Nova regra';
$string['editrule_newtemplate'] = 'Novo modelo';
$string['editrule_orderby'] = 'Ordenar por';
$string['editrule_pauserule'] = 'Pausa';
$string['editrule_required_error'] = 'Campo obrigatorio';
$string['editrule_runtime'] = 'Periocidade';
$string['editrule_runtime_error'] = 'Si {$a->timesfired} é > 0, debe indicar un intervalo';
$string['editrule_shareallrule'] = 'Compartir';
$string['editrule_sharedallrule'] = 'Compartido';
$string['editrule_sharerule'] = 'Compartir';
$string['editrule_timesfired'] = 'Número de veces disparado';
$string['editrule_title'] = 'Título';
$string['editrule_type'] = 'Tipo de regra';
$string['editrule_unshareallrule'] = 'Non compartir';
$string['editrule_unsharerule'] = 'Non compartir';
$string['editrule_usetemplate'] = 'Seleccionar';
$string['evaluaterule'] = 'Avaliar a regra';
$string['evaluaterule_help'] =
    'A regra avalíase do seguinte xeito:
 ([Condición 1] **AND** [Condición 2] **AND** ... ) **AND NOT** ([Excepción 1] **OU** [Excepción 2] ...) -> [Acción 1]->[Acción 2 ]';
$string['exceptions'] = 'Excepcións';
$string['export'] = 'Exportar';
$string['fullaction'] = 'Acción';
$string['fullcourse'] = 'Curso';
$string['fullrule'] = 'Regra';
$string['fulltemplate'] = 'Modelo';
$string['fulluser'] = 'Usuario';
$string['heading'] = 'Axente de notificación';
$string['hideshow'] = 'Ocultar/Mostrar';
$string['id'] = 'id';
$string['import'] = 'Importar';
$string['import_error'] = 'Non se puido importar a regra, comprobe o ficheiro JSON';
$string['import_success'] = 'Regra importada correctamente';
$string['isnotrule'] = 'Este identificador de regra non pertence a unha regra.';
$string['manageactionplugins'] = 'Xestionar complementos de acción';
$string['manageconditionplugins'] = 'Xestionar complementos de condicións';
$string['managenotificationsactionplugins'] = 'Xestionar complementos de acción';
$string['managenotificationsconditionplugins'] = 'Xestionar complementos de condicións';
$string['max_rules_cron'] = 'Número máximo de regras por ciclo cron';
$string['max_rules_cron_desc'] = 'Número máximo de regras por ciclo cron';
$string['menu'] = 'O meu asistente';
$string['messageprovider:notificationsagent_message'] = 'Notificacións de regras non válidas';
$string['no_file_selected'] = 'Non se seleccionou ningún ficheiro';
$string['no_json_file'] = 'O ficheiro non é JSON';
$string['nosuchinstance'] = 'Non se atopou esta instancia.';
$string['notificationaction_action'] = 'Acción do tipo de subcomplementos ';
$string['notificationsactionpluginname'] = 'Acción do complemento';
$string['notificationsagent:activityavailable'] = 'Capacidade necesaria para utilizar a condición de actividade dispoñible';
$string['notificationsagent:activitycompleted'] = 'Capacidade de usar a condición de actividade completada';
$string['notificationsagent:activityend'] = 'Capacidade necesaria para utilizar a condición de finalización da actividade';
$string['notificationsagent:activitylastsend'] = 'Capacidade necesaria para utilizar a condición de último envío da actividade';
$string['notificationsagent:activitymodified'] = 'Capacidade necesaria para utilizar a condición de modificación da actividade';
$string['notificationsagent:activitynewcontent'] = 'Capacidade necesaria para utilizar a condición de contido novo da actividade';
$string['notificationsagent:activityopen'] = 'Capacidade de usar a condición activityopen';
$string['notificationsagent:activitysinceend'] = 'Capacidade necesaria para utilizar a actividade desde a condición final';
$string['notificationsagent:activitystudentend']
    = 'Capacidade necesaria para utilizar a condición de finalización do alumno da actividade';
$string['notificationsagent:addusergroup'] = 'Capacidade de usar a acción addusergroup';
$string['notificationsagent:assignrule'] = 'Asignar unha regra';
$string['notificationsagent:bootstrapnotifications'] = 'Capacidade de usar a acción bootstrapnotifications';
$string['notificationsagent:calendareventto'] = 'Capacidade necesaria para usar o evento do calendario para condicionar';
$string['notificationsagent:calendarstart'] = 'Capacidade de usar a condición de calendarstart';
$string['notificationsagent:checkrulecontext'] = 'Comprobar o contexto dunha regra';
$string['notificationsagent:courseend'] = 'Capacidade necesaria para utilizar a condición de fin de curso';
$string['notificationsagent:coursestart'] = 'Capacidade de usar a condición de inicio do curso';
$string['notificationsagent:createrule'] = 'Crear unha regra';
$string['notificationsagent:deleterule'] = 'Eliminar unha regra';
$string['notificationsagent:etrule'] = 'Actualizar unha regra';
$string['notificationsagent:exportrule'] = 'Exportar unha regra';
$string['notificationsagent:forcerule'] = 'Forzar unha regra';
$string['notificationsagent:forummessage'] = 'Capacidade de usar a acción forummessage';
$string['notificationsagent:forumnoreply'] = 'Necesítase capacidade para utilizar a condición de sen resposta do foro';
$string['notificationsagent:importrule'] = 'Importar unha regra';
$string['notificationsagent:itemgraded'] = 'Capacidade necesaria para utilizar a condición de elemento cualificativo';
$string['notificationsagent:manageallrule'] = 'Capacidade de xestionar todas as regras';
$string['notificationsagent:managecourserule'] = 'Xestionar regras a nivel de curso';
$string['notificationsagent:manageownrule'] = 'Xestiona as túas propias regras no curso';
$string['notificationsagent:managesessions'] = 'Capacidade de gardar a orde das regras';
$string['notificationsagent:managesiterule'] = 'Xestionar regras de nivel de sitio';
$string['notificationsagent:messageagent'] = 'Capacidade de usar a acción messageagent';
$string['notificationsagent:numberoftimes'] = 'Capacidade necesaria para utilizar a condición de número de veces';
$string['notificationsagent:ondates'] = 'Capacidade necesaria para usar a condición entre datas';
$string['notificationsagent:privateforummessage'] = 'Capacidade de usar a acción privateforummessage';
$string['notificationsagent:removeusergroup'] = 'Capacidade de usar a acción removeusergroup';
$string['notificationsagent:sessionend'] = 'Capacidade necesaria para utilizar a condición de finalización da sesión';
$string['notificationsagent:sessionstart'] = 'Capacidade de usar a condición de inicio de sesión';
$string['notificationsagent:shareruleall'] = 'Aprobar a compartición dunha regra';
$string['notificationsagent:unshareruleall'] = 'Rexeita compartir unha regra';
$string['notificationsagent:updateruleshare'] = 'Actualizar o estado de uso compartido dunha regra';
$string['notificationsagent:updaterulestatus'] = 'Actualizar o estado dunha regra';
$string['notificationsagent:usergroupadd']
    = 'Capacidade necesaria para usar a condición de engadir usuario a unha condición de grupo';
$string['notificationsagent:usermessageagent'] = 'Capacidade de usar a acción usermessageagent';
$string['notificationsagent:viewassistantreport'] = 'Ver informe regra';
$string['notificationsagent:viewcourserule'] = 'Capacidade para ver as regras do curso';
$string['notificationsagent:weekdays'] = 'Capacidade necesaria para usar a condición dos días da semana';
$string['notificationsagent:weekend'] = 'Capacidade necesaria para usar a condición de fin de semana';
$string['notificationsconditionpluginname'] = 'Condición do complemento';
$string['pause_after_restore'] = 'Pausa as regras despois da restauración';
$string['pause_after_restore_desc'] = 'Pausa as regras despois de restaurar un curso';
$string['pluginname'] = 'Axente de notificacións';
$string['privacy:metadata:actiondetail'] = 'Detalle da acción enviada ao usuario.';
$string['privacy:metadata:actionid'] = 'Un identificador para unha acción';
$string['privacy:metadata:courseid'] = 'Un identificador para un curso';
$string['privacy:metadata:localnotificationsagentreport'] = 'Informe do axente de notificacións.';
$string['privacy:metadata:notificationsagentreport']
    = 'Rexistra as mensaxes enviadas aos usuarios que poden conter algúns datos do usuario.';
$string['privacy:metadata:ruleid'] = 'Un identificador para unha regra';
$string['privacy:metadata:timestamp'] = 'Marca de tempo da acción enviada.';
$string['privacy:metadata:userid'] = 'O ID de usuario ligado a esta táboa.';
$string['report'] = 'Informe do axente de notificación';
$string['rulecancelled'] = 'Regra cancelada';
$string['ruledownload'] = 'Exportar regra como';
$string['ruleid'] = 'ID da regra';
$string['rulename'] = 'Nome da regra';
$string['rulesaved'] = 'Gardouse a regra';
$string['settings'] = 'Configuración';
$string['shareaccept'] = 'Regra compartida';
$string['shareallcontent'] = 'A regra de uso compartido {$a->title} está a piques de ser aprobada, queres continuar?';
$string['sharealltitle'] = 'Aprobar a regra de uso compartido {$a->title}';
$string['sharecontent'] = 'A regra {$a->title} estase compartindo co administrador, queres continuar?';
$string['sharereject'] = 'Regra rexeitada';
$string['sharetitle'] = 'Compartir regra {$a->title}';
$string['startdate'] = 'Configuración da data da actividade';
$string['startdate_desc'] = 'Utiliza unha liña para cada actividade co patrón: pluginname|tablename|startdate|startend';
$string['status_acceptactivated'] = 'Regra activada';
$string['status_acceptpaused'] = 'Regra pausada';
$string['status_activatecontent'] = 'Vai activar a regra {$a->title}, desexa continuar?';
$string['status_activatetitle'] = 'Activar regra {$a->title}';
$string['status_active'] = 'Activo';
$string['status_broken'] = 'Roto';
$string['status_pausecontent'] = 'Vaise a pausar a regra {$a->title}, desexa continuar?';
$string['status_paused'] = 'En pausa';
$string['status_pausetitle'] = 'Pausar regra {$a->title}';
$string['status_required'] = 'Requirido';
$string['statusactivate'] = 'Activar';
$string['statuspause'] = 'Pausar';
$string['subplugintype_notificationsaction'] = 'Complemento de acción';
$string['subplugintype_notificationsaction_plural'] = 'Complementos de acción';
$string['subplugintype_notificationsagentaction'] = 'Acción de subplugins';
$string['subplugintype_notificationscondition'] = 'Complemento de condición';
$string['subplugintype_notificationscondition_plural'] = 'Condición de complementos';
$string['tatasktriggerssk'] = 'Tarefa de activación de notificacións';
$string['timestamp'] = 'Data';
$string['tracelog'] = 'Rexistro de rastrexo';
$string['tracelog_desc'] = 'Rexistro de rastrexo. Desactivar nos sitios de produción';
$string['type_rule'] = 'Regra';
$string['type_sharedrule'] = 'Regra compartida';
$string['type_template'] = 'Modelo';
$string['unshareaccept'] = 'Regra non compartida';
$string['unshareallcontent'] = 'A regra de uso compartido {$a->title} está a ser rexeitada, queres continuar?';
$string['unsharealltitle'] = 'Denegar a regra de uso compartido {$a->title}';
$string['unsharecontent'] = 'A regra {$a->title} está a piques de deixarse de compartir co administrador, queres continuar?';
$string['unsharetitle'] = 'Descompartir a regra {$a->title}';
$string['userid'] = 'ID de usuario';
