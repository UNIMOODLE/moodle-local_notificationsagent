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

$string['pluginname'] = 'Agent de notificacions';

$string['admin_breadcrumb'] = 'Agent de notificacions Admin';
$string['course_breadcrumb'] = 'Agent de notificacions';

// Settings.
$string['settings'] = 'Ajustaments';
$string['disable_user_use'] = 'Deshabiliteu per a l\'usuari';
$string['disable_user_use_desc'] = 'Deshabiliteu l\'ús de l\'agent de notificacions per a l\'usuari';
$string['max_rules_cron'] = 'Màxim nombre de regles per cicle de cron';
$string['max_rules_cron_desc'] = 'Màxim nombre de regles per cicle de cron';
$string['tracelog'] = 'Trace log';
$string['tracelog_desc'] = 'Trace log. Deshabilitar en llocs en producció';
$string['startdate'] = 'Configuració de dates dactivitat';
$string['startdate_desc'] = 'Usar una línia per cada activitat amb el patró: pluginname|tablename|startdate|startend';
$string['pause_after_restore'] = 'Atura les regles després de la restauració';
$string['pause_after_restore_desc'] = 'Atura les regles després de restaurar un curs';

// Subplugins settings.
$string['notificationaction_action'] = 'Subplugins tipus action ';

$string['managenotificationsactionplugins'] = 'Gestionar plugins tipus action';
$string['managenotificationsconditionplugins'] = 'Gestionar plugins tipus condition';

$string['manageactionplugins'] = 'Gestionar plugins action';
$string['manageconditionplugins'] = 'Gestionar plugins condition';

$string['actionplugins'] = 'Plugins tipus action';
$string['conditionplugins'] = 'Plugins tipus condition';

$string['notificationsactionpluginname'] = 'Plugin action';
$string['notificationsconditionpluginname'] = 'Plugin condition';

$string['hideshow'] = 'Amagar/Mostrar';

// Task.
$string['tatasktriggerssk'] = 'Tasca de desencadenadors de notificacions';
$string['menu'] = 'El meu assistent';
$string['heading'] = 'Agent de Notificacions';

// Status Template.
$string['status_active'] = 'Activa';
$string['status_paused'] = 'Pausada';
$string['status_required'] = 'Obligatòria';
$string['status_broken'] = 'Trencat';

// Import Template.
$string['import'] = 'Import';
$string['no_file_selected'] = 'Cap fitxer seleccionat';
$string['import_success'] = 'Regla importada correctament';
$string['import_error'] = 'No s\'ha pogut importar la regla, revisa el fitxer JSON';
$string['no_json_file'] = 'El fitxer no és JSON';

// Export Template.
$string['export'] = 'Exportar';
$string['ruledownload'] = 'Exportar regla com';

// Assign Template.
$string['assign'] = 'Seleccionar';
$string['type_template'] = 'Plantilla';
$string['type_rule'] = 'Regla';
$string['type_sharedrule'] = 'Regla compartida';
$string['fulltemplate'] = 'Plantilla';
// Condition plugins.
$string['condition_days'] = 'Dies';
$string['condition_hours'] = 'Hores';
$string['condition_minutes'] = 'Minuts';
$string['condition_seconds'] = 'Segons';
$string['condition_grade'] = 'Qualificació';

// EditRule.
$string['editrule_clonerule'] = 'Afegir regla';
$string['editrule_newrule'] = 'Nova regla';
$string['editrule_reports'] = 'Informes';
$string['editrule_activaterule'] = 'Activa';
$string['editrule_pauserule'] = 'Pausa';
$string['editrule_editrule'] = 'Edita';
$string['editrule_reportrule'] = 'Informe';
$string['editrule_deleterule'] = 'Esborra';
$string['editrule_newtemplate'] = 'Nova plantilla';
$string['editrule_title'] = 'Títol';
$string['editrule_type'] = 'Tipus de regla';
$string['editrule_usetemplate'] = 'Selecciona';
$string['editrule_sharerule'] = 'Compartir';
$string['editrule_unsharerule'] = 'Descompartir';
$string['editrule_shareallrule'] = 'Compartir';
$string['editrule_unshareallrule'] = 'Descompartir';
$string['editrule_sharedallrule'] = 'Compartit';
$string['editrule_timesfired'] = 'Nombre de vegades execució';
$string['editrule_runtime'] = 'Periocitat';
$string['editrule_orderby'] = 'Ordenar per';

// Condition.
$string['editrule_generalconditions'] = 'Condicions generals';
$string['editrule_newcondition'] = 'Nova condició:';
$string['editrule_condition_title_tocloseactivity'] = 'Queda menys de [TTTT] per tancar l\'activitat [AAAA]';
$string['editrule_condition_title_usercompleteactivity'] = 'L\'usuari té completada l\'activitat [AAAA]';
$string['editrule_condition_title_activeactivity'] = 'L\'activitat [AAAA] està disponible';
$string['editrule_condition_title_betweendates'] = 'Estem entre la data [FFFF-1] i [FFFF-2]';
$string['editrule_condition_title_accessforumactivitiescomplete']
    = 'Completa totes les activitats per accedir a aquest fòrum [FFFF]';
$string['editrule_condition_title_forumnotanswer']
    = 'Un fil obert per l\'usuari al fòrum [FFFF] sense respondre més de [TTTT] temps';

$string['editrule_condition_element_time'] = 'Temps {$a->typeelement}:';
$string['editrule_condition_element_activity'] = 'Activitat {$a->typeelement}:';
$string['editrule_condition_element_date_from'] = get_string('from') . ' {$a->typeelement}:';
$string['editrule_condition_element_date_to'] = get_string('to') . ' {$a->typeelement}:';

$string['editrule_condition_error'] = 'Heu d\'afegir almenys una condició';

// Actions.
$string['editrule_newaction'] = 'Nova acció:';
$string['editrule_action_title_individualnotification'] = 'Enviar notificació individual amb títol [TTTT] i missatge [BBBB]';
$string['editrule_action_title_notificationtouser']
    = 'Enviar notificació a un usuari concret [UUUU] amb títol [TTTT] i missatge [BBBB]';
$string['editrule_action_title_postgeneralforum'] = 'Publicar un post general al fòrum [FFFF] amb títol [TTTT] i missatge [BBBB]';
$string['editrule_action_title_postprivateforum'] = 'Publicar un post privat al fòrum [FFFF] amb títol [TTTT] i missatge [BBBB]';
$string['editrule_action_title_addusertogroup'] = 'Afegir un usuari a grup [GGGG]';
$string['editrule_action_title_removeuserfromgroup'] = 'Eliminar un usuari d\'un grup [GGGG]';
$string['editrule_action_title_bootstrapnotification'] = 'Notificació bootstrap';

$string['editrule_action_element_title'] = 'Títol {$a->typeelement}:';
$string['editrule_action_element_message'] = 'Missatge {$a->typeelement}:';
$string['editrule_action_element_user'] = 'Usuari {$a->typeelement}:';
$string['editrule_action_element_forum'] = 'Fòrum {$a->typeelement}:';
$string['editrule_action_element_group'] = 'Grup {$a->typeelement}';

$string['subplugintype_notificationsagentaction'] = 'Subplugins action';

$string['editrule_action_error'] = 'Heu d\'afegir almenys una acció';

// Rule.
$string['rulecancelled'] = 'Regla cancel·lada';
$string['rulesaved'] = 'Regla desada';

// Rule errors.
$string['editrule_required_error'] = 'Camp requerit';
$string['editrule_runtime_error'] = 'Si ' . $string['editrule_timesfired'] . ' és > 0, ha d\'indicar un interval';
$string['editrule_execution_error'] = $string['editrule_timesfired'] . ' entre {$a->minimum} i {$a->maximum}';

// Card content.
$string['cardif'] = 'Si tot:';
$string['cardunless'] = 'Llevat que cap de:';
$string['cardthen'] = 'Aleshores:';
$string['cardsharedby'] = 'Compartida per {$a->username} al curs {$a->coursename}';

// Card Condition time.
$string['card_day'] = 'dia';
$string['card_day_plural'] = 'dies';
$string['card_hour'] = 'hora';
$string['card_hour_plural'] = 'hores';
$string['card_minute'] = 'minut';
$string['card_minute_plural'] = 'minuts';
$string['card_second'] = 'segon';
$string['card_second_plural'] = 'segons';

// Status modal.
$string['status_pausetitle'] = 'Pausar regla {$a->title}';
$string['status_activatetitle'] = 'Activar regla {$a->title}';
$string['status_pausecontent'] = 'Voleu pausar la regla {$a->title}, voleu continuar?';
$string['status_activatecontent'] = 'Voleu activar la regla {$a->title}, voleu continuar?';
$string['status_acceptactivated'] = 'Regla activada';
$string['status_acceptpaused'] = 'Regla pausada';
$string['statuspause'] = 'Pausar';
$string['statusactivate'] = 'Activar';

// Delete modal.
$string['deletetitle'] = 'Esborra la {$a->type} {$a->title}';
$string['deletecontent_nocontext'] = 'S\'esborrarà {$a->type} {$a->title}, voleu continuar?';
$string['deletecontent_hascontext']
    = 'La {$a->type} {$a->title} que voleu suprimir, està associada a altres contextos, voleu continuar?';
$string['deleteaccept'] = 'Regla esborrada';

// Assign modal.
$string['assignassign'] = 'Assignar: ';
$string['assigncancel'] = 'Cancel·la';
$string['assignsave'] = 'Desa els canvis';
$string['assignforced'] = 'Assignar la regla forçosament';
$string['assignselectcourses'] = 'Marqueu curs';
$string['assignunselectcourses'] = 'Desmarqueu curs';
$string['assignselectedinfo'] = '{$a->courses} cursos i {$a->categories} categories seleccionats';

// Share modal.
$string['sharetitle'] = 'Comparteix la regla {$a->title}';
$string['sharecontent'] = 'Es compartirà la regla {$a->title} amb l\'administrador, voleu continuar?';
$string['unsharetitle'] = 'Descompartir la regla {$a->title}';
$string['unsharecontent'] = 'S\'ha de descompartir la regla {$a->title} amb l\'administrador, voleu continuar?';
$string['shareaccept'] = 'Regla compartida';
$string['unshareaccept'] = 'Regla descompartida';
$string['sharereject'] = 'Regla rebutjada';

// Share all modal.
$string['sharealltitle'] = 'Aprobar la compartició de la regla {$a->title}';
$string['shareallcontent'] = 'S\'aprovarà la compartició de la regla {$a->title}, voleu continuar?';

// Unshare all modal.
$string['unsharealltitle'] = 'Rebutjar la compartició de la regla {$a->title}';
$string['unshareallcontent'] = 'Es rebutjarà la compartició de la regla {$a->title}, voleu continuar?';

// Capabilities.
$string['notificationsagent:createrule'] = 'Crear una regla';
$string['notificationsagent:editrule'] = 'Actualitzar una regla';
$string['notificationsagent:checkrulecontext'] = 'Comprovar el context d\'una regla';
$string['notificationsagent:deleterule'] = 'Esborrar una regla';
$string['notificationsagent:updaterulestatus'] = 'Actualitzar l\'estat d\'una regla';
$string['notificationsagent:exportrule'] = 'Exporta una regla';
$string['notificationsagent:importrule'] = 'Importa una regla';
$string['notificationsagent:assignrule'] = 'Assignar una regla';
$string['notificationsagent:forcerule'] = 'Forçar una regla';
$string['notificationsagent:updateruleshare'] = 'Actualitzar l\'estat de compartició d\'una regla';
$string['notificationsagent:shareruleall'] = 'Aprovar la compartició d\'una regla';
$string['notificationsagent:unshareruleall'] = 'Rebutjar la compartició d\'una regla';
$string['notificationsagent:managesiterule'] = 'Gestiona les regles a nivell de lloc';
$string['notificationsagent:managecourserule'] = 'Gestiona les regles a nivell de curs';
$string['notificationsagent:manageownrule'] = 'Gestionar les regles pròpies en el curs';
$string['notificationsagent:viewassistantreport'] = 'Ver informe de regla';

$string['notificationsagent:activitycompleted'] = 'Capacitat per utilitzar la condició activitycompleted';
$string['notificationsagent:activityopen'] = 'Capacitat per utilitzar la condició activityopen';
$string['notificationsagent:coursestart'] = 'Capacitat per utilitzar la condició coursestart';
$string['notificationsagent:calendarstart'] = 'Capacitat per utilitzar la condició calendarstart';
$string['notificationsagent:sessionstart'] = 'Capacitat per utilitzar la condició sessionstart';
$string['notificationsagent:activityavailable'] = 'Capacitat necessària per utilitzar la condició d\'activitat disponible';
$string['notificationsagent:activityend'] = 'Capacitat necessària per utilitzar la condició de finalització de l\'activitat';
$string['notificationsagent:activitylastsend']
    = 'Capacitat necessària per utilitzar la condició d\'últim enviament de l\'activitat';
$string['notificationsagent:activitymodified'] = 'Capacitat necessària per utilitzar la condició modificada per l\'activitat';
$string['notificationsagent:activitynewcontent']
    = 'Capacitat necessària per utilitzar la condició de contingut nou de l\'activitat';
$string['notificationsagent:activitysinceend'] = 'Capacitat necessària per utilitzar l\'activitat des de la condició final';
$string['notificationsagent:activitystudentend']
    = 'Capacitat necessària per utilitzar la condició final de l\'activitat de l\'estudiant';
$string['notificationsagent:calendareventto'] = 'Capacitat necessària per utilitzar l\'esdeveniment del calendari per condicionar';
$string['notificationsagent:courseend'] = 'Capacitat necessària per utilitzar la condició de finalització del curs';
$string['notificationsagent:forumnoreply'] = 'Capacitat necessària per utilitzar la condició sense resposta del fòrum';
$string['notificationsagent:numberoftimes'] = 'Capacitat necessària per utilitzar la condició de nombre de vegades';
$string['notificationsagent:sessionend'] = 'Capacitat necessària per utilitzar la condició de finalització de la sessió';
$string['notificationsagent:weekend'] = 'Capacitat necessària per utilitzar la condició del cap de setmana';
$string['notificationsagent:itemgraded'] = 'Capacitat necessària per utilitzar la condició de element de qualificació';
$string['notificationsagent:weekdays'] = 'Capacitat necessària per utilitzar la condició de dies de la setmana';
$string['notificationsagent:ondates'] = 'Capacitat necessària per utilitzar la condició entre dates';
$string['notificationsagent:usergroupadd'] = 'Capacitat necessària per utilitzar la condició dafegir usuari a grup';

$string['notificationsagent:addusergroup'] = 'Capacitat per utilitzar l\'acció addusergroup';
$string['notificationsagent:bootstrapnotifications'] = 'Capacitat per utilitzar l\'acció bootstrapnotifications';
$string['notificationsagent:forummessage'] = 'Capacitat per utilitzar l\'acció forummessage';
$string['notificationsagent:messageagent'] = 'Capacitat per utilitzar l\'acció messageagent';
$string['notificationsagent:removeusergroup'] = 'Capacitat per utilitzar l\'acció removeusergroup';
$string['notificationsagent:usermessageagent'] = 'Capacitat per utilitzar l\'acció usermessageagent';
$string['notificationsagent:privateforummessage'] = 'Capacitat per utilitzar l\'acció privateforummessage';

$string['notificationsagent:viewcourserule'] = 'Capacitat per veure les regles d\'un curs';
$string['notificationsagent:manageallrule'] = 'Capacitat de gestionar totes les regles';
$string['notificationsagent:managesessions'] = 'Capacitat per desar l\'ordre de les regles';

// Webservices.
$string['nosuchinstance'] = 'No s\'ha trobat aquesta instància.';
$string['isnotrule'] = 'El identificador de regla no pertany a una regla.';

// Report.
$string['rulename'] = 'Nombre de la regla';
$string['report'] = 'Informe del agente de notificaciones';
$string['id'] = 'id';
$string['ruleid'] = 'Id de la regla';
$string['fullrule'] = 'Regla';
$string['userid'] = 'Id de usuario';
$string['fulluser'] = 'Usuario';
$string['fullcourse'] = 'Curs';
$string['courseid'] = 'Id del curs';
$string['actionid'] = 'Ide de la acció';
$string['fullaction'] = 'Acció';
$string['actiondetail'] = 'Detalle de la acció';
$string['timestamp'] = 'Data';

// Nav.
$string['conditions'] = 'Condicions';
$string['exceptions'] = 'Excepcions';
$string['actions'] = 'Accions';

// Memòria cau.
$string['cachedef_launched'] = 'Llista d\'usuaris la periodicitat dels quals ha començat.';

// Privacy.
$string['privacy:metadata:userid'] = 'L\'identificador d\'usuari enllaçat a aquesta taula.';
$string['privacy:metadata:courseid'] = 'Un identificador per a un curs';
$string['privacy:metadata:actionid'] = 'Un identificador per a una acció';
$string['privacy:metadata:ruleid'] = 'Un identificador per a una regla';
$string['privacy:metadata:actiondetail'] = 'Detall de l\'acció enviada a l\'usuari.';
$string['privacy:metadata:notificationsagentreport']
    = 'Enregistra missatges enviats als usuaris que poden contenir algunes dades d\'usuari.';
$string['privacy:metadata:timestamp'] = 'Marca de temps de l\'acció enviada.';
$string['privacy:metadata:localnotificationsagentreport'] = 'Informe de l\'agent de notificacions.';

// Message provider.
$string['messageprovider:notificationsagent_message'] = 'Notificacions de regles no vàlides';
$string['brokenrulebody'] = 'La regla: {$a->rule} del curs :{$a->course} s\'ha pausat.';

// Engine.
$string['actionerror'] = 'Erro ao realizar a acción ';

$string['subplugintype_notificationscondition'] = 'Plugin de condició';
$string['subplugintype_notificationsaction'] = 'Plugin d\'acció';
$string['subplugintype_notificationscondition_plural'] = 'Plugins de condició';
$string['subplugintype_notificationsaction_plural'] = 'Plugins d\'acció';

// Help.
$string['evaluaterule'] = 'Avalua la regla';
$string['evaluaterule_help'] =
    'La regla s\'avalua de la següent manera:
 ([Condició 1] **AND** [Condició 2] **AND** ... ) **I NO** ([Excepció 1] **OR** [Excepció 2] ...) -> [Acció 1]->[Acció 2 ]';
