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

$string['actiondetail'] = 'Detalle de la acció';
$string['actionerror'] = 'Erro ao realizar a acción ';
$string['actionid'] = 'Ide de la acció';
$string['actionplugins'] = 'Plugins tipus action';
$string['actions'] = 'Accions';
$string['admin_breadcrumb'] = 'Agent de notificacions Admin';
$string['assign'] = 'Seleccionar';
$string['assignassign'] = 'Assignar: ';
$string['assigncancel'] = 'Cancel·la';
$string['assignforced'] = 'Assignar la regla forçosament';
$string['assignsave'] = 'Desa els canvis';
$string['assignselectcourses'] = 'Marqueu curs';
$string['assignselectedinfo'] = '{$a->courses} cursos i {$a->categories} categories seleccionats';
$string['assignunselectcourses'] = 'Desmarqueu curs';
$string['brokenrulebody'] = 'La regla: {$a->rule} del curs :{$a->course} s\'ha pausat.';
$string['cachedef_launched'] = 'Llista d\'usuaris la periodicitat dels quals ha començat.';
$string['card_day'] = 'dia';
$string['card_day_plural'] = 'dies';
$string['card_hour'] = 'hora';
$string['card_hour_plural'] = 'hores';
$string['card_minute'] = 'minut';
$string['card_minute_plural'] = 'minuts';
$string['card_second'] = 'segon';
$string['card_second_plural'] = 'segons';
$string['cardif'] = 'Si tot:';
$string['cardsharedby'] = 'Compartida per {$a->username} al curs {$a->coursename}';
$string['cardthen'] = 'Aleshores:';
$string['cardunless'] = 'Llevat que cap de:';
$string['condition_days'] = 'Dies';
$string['condition_grade'] = 'Qualificació';
$string['condition_hours'] = 'Hores';
$string['condition_minutes'] = 'Minuts';
$string['condition_seconds'] = 'Segons';
$string['conditionplugins'] = 'Plugins tipus condition';
$string['conditions'] = 'Condicions';
$string['course_breadcrumb'] = 'Agent de notificacions';
$string['courseid'] = 'Id del curs';
$string['deleteaccept'] = 'Regla esborrada';
$string['deletecontent_hascontext'] = 'La {$a->type} {$a->title} que voleu suprimir, està associada a altres contextos, voleu continuar?';
$string['deletecontent_nocontext'] = 'S\'esborrarà {$a->type} {$a->title}, voleu continuar?';
$string['deletetitle'] = 'Esborra la {$a->type} {$a->title}';
$string['disable_user_use'] = 'Deshabiliteu per a l\'usuari';
$string['disable_user_use_desc'] = 'Deshabiliteu l\'ús de l\'agent de notificacions per a l\'usuari';
$string['editrule_action_error'] = 'Heu d\'afegir almenys una acció';
$string['editrule_activaterule'] = 'Activa';
$string['editrule_clonerule'] = 'Afegir regla';
$string['editrule_condition_error'] = 'Heu d\'afegir almenys una condició';
$string['editrule_deleterule'] = 'Esborra';
$string['editrule_editrule'] = 'Edita';
$string['editrule_execution_error'] = '{$a->timesfired} entre {$a->minimum} i {$a->maximum}';
$string['editrule_generalconditions'] = 'Condicions generals';
$string['editrule_newaction'] = 'Nova acció:';
$string['editrule_newcondition'] = 'Nova condició:';
$string['editrule_newrule'] = 'Nova regla';
$string['editrule_newtemplate'] = 'Nova plantilla';
$string['editrule_orderby'] = 'Ordenar per';
$string['editrule_pauserule'] = 'Pausa';
$string['editrule_reportrule'] = 'Informe';
$string['editrule_reports'] = 'Informes';
$string['editrule_required_error'] = 'Camp requerit';
$string['editrule_runtime'] = 'Periocitat';
$string['editrule_runtime_error'] = 'Si {$a->timesfired} és > 0, ha d\'indicar un interval';
$string['editrule_shareallrule'] = 'Compartir';
$string['editrule_sharedallrule'] = 'Compartit';
$string['editrule_sharerule'] = 'Compartir';
$string['editrule_timesfired'] = 'Nombre de vegades execució';
$string['editrule_title'] = 'Títol';
$string['editrule_type'] = 'Tipus de regla';
$string['editrule_unshareallrule'] = 'Descompartir';
$string['editrule_unsharerule'] = 'Descompartir';
$string['editrule_usetemplate'] = 'Selecciona';
$string['evaluaterule'] = 'Avalua la regla';
$string['evaluaterule_help'] = 'La regla s\'avalua de la següent manera: ([Condició 1] **AND** [Condició 2] **AND** ... ) **I NO** ([Excepció 1] **OR** [Excepció 2] ...) -> [Acció 1]->[Acció 2 ]';
$string['exceptions'] = 'Excepcions';
$string['export'] = 'Exportar';
$string['fullaction'] = 'Acció';
$string['fullcourse'] = 'Curs';
$string['fullrule'] = 'Regla';
$string['fulltemplate'] = 'Plantilla';
$string['fulluser'] = 'Usuario';
$string['heading'] = 'Agent de Notificacions';
$string['hideshow'] = 'Amagar/Mostrar';
$string['id'] = 'id';
$string['import'] = 'Import';
$string['import_error'] = 'No s\'ha pogut importar la regla, revisa el fitxer JSON';
$string['import_success'] = 'Regla importada correctament';
$string['isnotrule'] = 'El identificador de regla no pertany a una regla.';
$string['manageactionplugins'] = 'Gestionar plugins action';
$string['manageconditionplugins'] = 'Gestionar plugins condition';
$string['managenotificationsactionplugins'] = 'Gestionar plugins tipus action';
$string['managenotificationsconditionplugins'] = 'Gestionar plugins tipus condition';
$string['max_rules_cron'] = 'Màxim nombre de regles per cicle de cron';
$string['max_rules_cron_desc'] = 'Màxim nombre de regles per cicle de cron';
$string['menu'] = 'El meu assistent';
$string['messageprovider:notificationsagent_message'] = 'Notificacions de regles no vàlides';
$string['no_file_selected'] = 'Cap fitxer seleccionat';
$string['no_json_file'] = 'El fitxer no és JSON';
$string['nosuchinstance'] = 'No s\'ha trobat aquesta instància.';
$string['notificationaction_action'] = 'Subplugins tipus action ';
$string['notificationsactionpluginname'] = 'Plugin action';
$string['notificationsagent:activityavailable'] = 'Capacitat necessària per utilitzar la condició d\'activitat disponible';
$string['notificationsagent:activitycompleted'] = 'Capacitat per utilitzar la condició activitycompleted';
$string['notificationsagent:activityend'] = 'Capacitat necessària per utilitzar la condició de finalització de l\'activitat';
$string['notificationsagent:activitylastsend'] = 'Capacitat necessària per utilitzar la condició d\'últim enviament de l\'activitat';
$string['notificationsagent:activitymodified'] = 'Capacitat necessària per utilitzar la condició modificada per l\'activitat';
$string['notificationsagent:activitynewcontent'] = 'Capacitat necessària per utilitzar la condició de contingut nou de l\'activitat';
$string['notificationsagent:activityopen'] = 'Capacitat per utilitzar la condició activityopen';
$string['notificationsagent:activitysinceend'] = 'Capacitat necessària per utilitzar l\'activitat des de la condició final';
$string['notificationsagent:activitystudentend'] = 'Capacitat necessària per utilitzar la condició final de l\'activitat de l\'estudiant';
$string['notificationsagent:addusergroup'] = 'Capacitat per utilitzar l\'acció addusergroup';
$string['notificationsagent:assignrule'] = 'Assignar una regla';
$string['notificationsagent:bootstrapnotifications'] = 'Capacitat per utilitzar l\'acció bootstrapnotifications';
$string['notificationsagent:calendareventto'] = 'Capacitat necessària per utilitzar l\'esdeveniment del calendari per condicionar';
$string['notificationsagent:calendarstart'] = 'Capacitat per utilitzar la condició calendarstart';
$string['notificationsagent:checkrulecontext'] = 'Comprovar el context d\'una regla';
$string['notificationsagent:courseend'] = 'Capacitat necessària per utilitzar la condició de finalització del curs';
$string['notificationsagent:coursestart'] = 'Capacitat per utilitzar la condició coursestart';
$string['notificationsagent:createrule'] = 'Crear una regla';
$string['notificationsagent:deleterule'] = 'Esborrar una regla';
$string['notificationsagent:editrule'] = 'Actualitzar una regla';
$string['notificationsagent:exportrule'] = 'Exporta una regla';
$string['notificationsagent:forcerule'] = 'Forçar una regla';
$string['notificationsagent:forummessage'] = 'Capacitat per utilitzar l\'acció forummessage';
$string['notificationsagent:forumnoreply'] = 'Capacitat necessària per utilitzar la condició sense resposta del fòrum';
$string['notificationsagent:importrule'] = 'Importa una regla';
$string['notificationsagent:itemgraded'] = 'Capacitat necessària per utilitzar la condició de element de qualificació';
$string['notificationsagent:manageallrule'] = 'Capacitat de gestionar totes les regles';
$string['notificationsagent:managecourserule'] = 'Gestiona les regles a nivell de curs';
$string['notificationsagent:manageownrule'] = 'Gestionar les regles pròpies en el curs';
$string['notificationsagent:managesessions'] = 'Capacitat per desar l\'ordre de les regles';
$string['notificationsagent:managesiterule'] = 'Gestiona les regles a nivell de lloc';
$string['notificationsagent:messageagent'] = 'Capacitat per utilitzar l\'acció messageagent';
$string['notificationsagent:numberoftimes'] = 'Capacitat necessària per utilitzar la condició de nombre de vegades';
$string['notificationsagent:ondates'] = 'Capacitat necessària per utilitzar la condició entre dates';
$string['notificationsagent:privateforummessage'] = 'Capacitat per utilitzar l\'acció privateforummessage';
$string['notificationsagent:removeusergroup'] = 'Capacitat per utilitzar l\'acció removeusergroup';
$string['notificationsagent:sessionend'] = 'Capacitat necessària per utilitzar la condició de finalització de la sessió';
$string['notificationsagent:sessionstart'] = 'Capacitat per utilitzar la condició sessionstart';
$string['notificationsagent:shareruleall'] = 'Aprovar la compartició d\'una regla';
$string['notificationsagent:unshareruleall'] = 'Rebutjar la compartició d\'una regla';
$string['notificationsagent:updateruleshare'] = 'Actualitzar l\'estat de compartició d\'una regla';
$string['notificationsagent:updaterulestatus'] = 'Actualitzar l\'estat d\'una regla';
$string['notificationsagent:usergroupadd'] = 'Capacitat necessària per utilitzar la condició dafegir usuari a grup';
$string['notificationsagent:usermessageagent'] = 'Capacitat per utilitzar l\'acció usermessageagent';
$string['notificationsagent:viewassistantreport'] = 'Ver informe de regla';
$string['notificationsagent:viewcourserule'] = 'Capacitat per veure les regles d\'un curs';
$string['notificationsagent:weekdays'] = 'Capacitat necessària per utilitzar la condició de dies de la setmana';
$string['notificationsagent:weekend'] = 'Capacitat necessària per utilitzar la condició del cap de setmana';
$string['notificationsconditionpluginname'] = 'Plugin condition';
$string['pause_after_restore'] = 'Atura les regles després de la restauració';
$string['pause_after_restore_desc'] = 'Atura les regles després de restaurar un curs';
$string['pluginname'] = 'Agent de notificacions';
$string['privacy:metadata:actiondetail'] = 'Detall de l\'acció enviada a l\'usuari.';
$string['privacy:metadata:actionid'] = 'Un identificador per a una acció';
$string['privacy:metadata:courseid'] = 'Un identificador per a un curs';
$string['privacy:metadata:localnotificationsagentreport'] = 'Informe de l\'agent de notificacions.';
$string['privacy:metadata:notificationsagentreport'] = 'Enregistra missatges enviats als usuaris que poden contenir algunes dades d\'usuari.';
$string['privacy:metadata:ruleid'] = 'Un identificador per a una regla';
$string['privacy:metadata:timestamp'] = 'Marca de temps de l\'acció enviada.';
$string['privacy:metadata:userid'] = 'L\'identificador d\'usuari enllaçat a aquesta taula.';
$string['report'] = 'Informe del agente de notificaciones';
$string['rulecancelled'] = 'Regla cancel·lada';
$string['ruledownload'] = 'Exportar regla com';
$string['ruleid'] = 'Id de la regla';
$string['rulename'] = 'Nombre de la regla';
$string['rulesaved'] = 'Regla desada';
$string['settings'] = 'Ajustaments';
$string['shareaccept'] = 'Regla compartida';
$string['shareallcontent'] = 'S\'aprovarà la compartició de la regla {$a->title}, voleu continuar?';
$string['sharealltitle'] = 'Aprobar la compartició de la regla {$a->title}';
$string['sharecontent'] = 'Es compartirà la regla {$a->title} amb l\'administrador, voleu continuar?';
$string['sharereject'] = 'Regla rebutjada';
$string['sharetitle'] = 'Comparteix la regla {$a->title}';
$string['startdate'] = 'Configuració de dates dactivitat';
$string['startdate_desc'] = 'Usar una línia per cada activitat amb el patró: pluginname|tablename|startdate|startend';
$string['status_acceptactivated'] = 'Regla activada';
$string['status_acceptpaused'] = 'Regla pausada';
$string['status_activatecontent'] = 'Voleu activar la regla {$a->title}, voleu continuar?';
$string['status_activatetitle'] = 'Activar regla {$a->title}';
$string['status_active'] = 'Activa';
$string['status_broken'] = 'Trencat';
$string['status_pausecontent'] = 'Voleu pausar la regla {$a->title}, voleu continuar?';
$string['status_paused'] = 'Pausada';
$string['status_pausetitle'] = 'Pausar regla {$a->title}';
$string['status_required'] = 'Obligatòria';
$string['statusactivate'] = 'Activar';
$string['statuspause'] = 'Pausar';
$string['subplugintype_notificationsaction'] = 'Plugin d\'acció';
$string['subplugintype_notificationsaction_plural'] = 'Plugins d\'acció';
$string['subplugintype_notificationsagentaction'] = 'Subplugins action';
$string['subplugintype_notificationscondition'] = 'Plugin de condició';
$string['subplugintype_notificationscondition_plural'] = 'Plugins de condició';
$string['tatasktriggerssk'] = 'Tasca de desencadenadors de notificacions';
$string['timestamp'] = 'Data';
$string['tracelog'] = 'Trace log';
$string['tracelog_desc'] = 'Trace log. Deshabilitar en llocs en producció';
$string['type_rule'] = 'Regla';
$string['type_sharedrule'] = 'Regla compartida';
$string['type_template'] = 'Plantilla';
$string['unshareaccept'] = 'Regla descompartida';
$string['unshareallcontent'] = 'Es rebutjarà la compartició de la regla {$a->title}, voleu continuar?';
$string['unsharealltitle'] = 'Rebutjar la compartició de la regla {$a->title}';
$string['unsharecontent'] = 'S\'ha de descompartir la regla {$a->title} amb l\'administrador, voleu continuar?';
$string['unsharetitle'] = 'Descompartir la regla {$a->title}';
$string['userid'] = 'Id de usuario';
