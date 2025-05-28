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

$string['pluginname'] = 'Axente de notificacións';

$string['admin_breadcrumb'] = 'Axente de notificacións Admin';
$string['course_breadcrumb'] = 'Axente de notificacións';

// Configuración.
$string['settings'] = 'Configuración';
$string['disable_user_use'] = 'Desactivar para o usuario';
$string['disable_user_use_desc'] = 'Desactivar o uso do axente de notificacións para o usuario';
$string['max_rules_cron'] = 'Número máximo de regras por ciclo cron';
$string['max_rules_cron_desc'] = 'Número máximo de regras por ciclo cron';
$string['tracelog'] = 'Rexistro de rastrexo';
$string['tracelog_desc'] = 'Rexistro de rastrexo. Desactivar nos sitios de produción';
$string['startdate'] = 'Configuración da data da actividade';
$string['startdate_desc'] = 'Utiliza unha liña para cada actividade co patrón: pluginname|tablename|startdate|enddate';
$string['pause_after_restore'] = 'Pausa as regras despois da restauración';
$string['pause_after_restore_desc'] = 'Pausa as regras despois de restaurar un curso';

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
$string['status_broken'] = 'Roto';

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
$string['assign'] = 'Seleccionar';
$string['type_template'] = 'Modelo';
$string['type_rule'] = 'Regra';
$string['type_sharedrule'] = 'Regra compartida';
$string['fulltemplate'] = 'Modelo';

// Condicionar complementos.
$string['condition_days'] = 'Días';
$string['condition_hours'] = 'Horas ';
$string['condition_minutes'] = 'Minutos';
$string['condition_seconds'] = 'Segundos';
$string['condition_grade'] = 'Cualificación';

// Editar regra.
$string['editrule_clonerule'] = 'Engadir regra';
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
$string['editrule_shareallrule'] = 'Compartir';
$string['editrule_unshareallrule'] = 'Non compartir';
$string['editrule_sharedallrule'] = 'Compartido';
$string['editrule_timesfired'] = 'Número de veces disparado';
$string['editrule_runtime'] = 'Periocidade';
$string['editrule_orderby'] = 'Ordenar por';

// Condition.
$string['editrule_generalconditions'] = 'Condicións xerais';
$string['editrule_newcondition'] = 'Nova condición:';
$string['editrule_condition_error'] = 'Debes engadir polo menos unha condición';

// Accións.
$string['editrule_newaction'] = 'Nova acción:';
$string['subplugintype_notificationsagentaction'] = 'Acción de subplugins';
$string['editrule_action_error'] = 'Debes engadir polo menos unha acción';

// Rule.
$string['rulecancelled'] = 'Regra cancelada';
$string['rulesaved'] = 'Gardouse a regra';

// Rule errors.
$string['editrule_required_error'] = 'Campo obrigatorio';
$string['editrule_runtime_error'] = 'Si {$a->timesfired} é > 0, debe indicar un intervalo';
$string['editrule_execution_error'] = '{$a->timesfired} entre {$a->minimum} e {$a->maximum}';

// Contido da tarxeta.
$string['cardif'] = 'Se todo:';
$string['cardunless'] = 'A non ser que:';
$string['cardthen'] = 'Entón:';
$string['cardsharedby'] = 'Compartido por {$a->username} no curso {$a->coursename}';

// Tempo de condición da tarxeta.
$string['card_day'] = 'día';
$string['card_day_plural'] = 'días';
$string['card_hour'] = 'hora';
$string['card_hour_plural'] = 'horas';
$string['card_minute'] = 'minuto';
$string['card_minute_plural'] = 'minutos';
$string['card_second'] = 'segundo';
$string['card_second_plural'] = 'segundos';

// Estado modal.
$string['status_pausetitle'] = 'Pausar regra {$a->title}';
$string['status_activatetitle'] = 'Activar regra {$a->title}';
$string['status_pausecontent'] = 'Vaise a pausar a regra {$a->title}, desexa continuar?';
$string['status_activatecontent'] = 'Vai activar a regra {$a->title}, desexa continuar?';
$string['status_acceptactivated'] = 'Regra activada';
$string['status_acceptpaused'] = 'Regra pausada';
$string['statuspause'] = 'Pausar';
$string['statusactivate'] = 'Activar';

// Eliminar modal.
$string['deletetitle'] = 'Eliminar o {$a->type} {$a->title}';
$string['deletecontent_nocontext'] = '{$a->type} {$a->title} estase a eliminar, queres continuar?';
$string['deletecontent_hascontext']
    = 'O {$a->type} {$a->title} que quere eliminar está asociado con outros contextos, quere continuar?';
$string['deleteaccept'] = 'Regra eliminada';

// Asignar modal.
$string['assignassign'] = 'Asignar: ';
$string['assigncancel'] = 'Cancelar';
$string['assignsave'] = 'Gardar os cambios';
$string['assignforced'] = 'Asignar a regra á forza';
$string['assignselectcourses'] = 'Marcar cursos';
$string['assignunselectcourses'] = 'Desmarcar cursos';
$string['assignselectedinfo'] = '{$a->courses} cursos seleccionados e categorías {$a->categorías}';

// Compartir modal.
$string['sharetitle'] = 'Compartir regra {$a->title}';
$string['sharecontent'] = 'A regra {$a->title} estase compartindo co administrador, queres continuar?';
$string['unsharetitle'] = 'Descompartir a regra {$a->title}';
$string['unsharecontent'] = 'A regra {$a->title} está a piques de deixarse de compartir co administrador, queres continuar?';
$string['shareaccept'] = 'Regra compartida';
$string['unshareaccept'] = 'Regra non compartida';
$string['sharereject'] = 'Regra rexeitada';

// Compartir todos os modais.
$string['sharealltitle'] = 'Aprobar a regra de uso compartido {$a->title}';
$string['shareallcontent'] = 'A regra de uso compartido {$a->title} está a piques de ser aprobada, queres continuar?';

// Unshare all modal.
$string['unsharealltitle'] = 'Denegar a regra de uso compartido {$a->title}';
$string['unshareallcontent'] = 'A regra de uso compartido {$a->title} está a ser rexeitada, queres continuar?';

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
$string['notificationsagent:unshareruleall'] = 'Rexeita compartir unha regra';
$string['notificationsagent:managesiterule'] = 'Xestionar regras de nivel de sitio';
$string['notificationsagent:managecourserule'] = 'Xestionar regras a nivel de curso';
$string['notificationsagent:manageownrule'] = 'Xestiona as túas propias regras no curso';
$string['notificationsagent:viewassistantreport'] = 'Ver informe regra';
$string['notificationsagent:viewcourserule'] = 'Capacidade para ver as regras do curso';
$string['notificationsagent:manageallrule'] = 'Capacidade de xestionar todas as regras';
$string['notificationsagent:managesessions'] = 'Capacidade de gardar a orde das regras';

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

// Caché.
$string['cachedef_launched'] = 'Lista de usuarios cuxa recorrencia comezou.';

// Privacy.
$string['privacy:metadata:userid'] = 'O ID de usuario ligado a esta táboa.';
$string['privacy:metadata:courseid'] = 'Un identificador para un curso';
$string['privacy:metadata:actionid'] = 'Un identificador para unha acción';
$string['privacy:metadata:ruleid'] = 'Un identificador para unha regra';
$string['privacy:metadata:actiondetail'] = 'Detalle da acción enviada ao usuario.';
$string['privacy:metadata:notificationsagentreport']
    = 'Rexistra as mensaxes enviadas aos usuarios que poden conter algúns datos do usuario.';
$string['privacy:metadata:timestamp'] = 'Marca de tempo da acción enviada.';
$string['privacy:metadata:localnotificationsagentreport'] = 'Informe do axente de notificacións.';

// Message provider.
$string['messageprovider:notificationsagent_message'] = 'Notificacións de regras non válidas';
$string['brokenrulebody'] = 'A regra: {$a->rule} do curso :{$a->course} foi detida.';

// Engine.
$string['actionerror'] = 'Erro ao realizar a acción ';

$string['subplugintype_notificationscondition'] = 'Complemento de condición';
$string['subplugintype_notificationsaction'] = 'Complemento de acción';
$string['subplugintype_notificationscondition_plural'] = 'Condición de complementos';
$string['subplugintype_notificationsaction_plural'] = 'Complementos de acción';

// Help.
$string['evaluaterule'] = 'Avaliar a regra';
$string['evaluaterule_help'] =
    'A regra avalíase do seguinte xeito:
 ([Condición 1] **AND** [Condición 2] **AND** ... ) **AND NOT** ([Excepción 1] **OU** [Excepción 2] ...) -> [Acción 1]->[Acción 2 ]';
