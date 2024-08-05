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

$string['pluginname'] = 'Jakinarazpen-agentea';

$string['admin_breadcrumb'] = 'Jakinarazpen-agentea Admin';
$string['course_breadcrumb'] = 'Jakinarazpen-agentea';

// Ezarpenak.
$string['settings'] = 'Ezarpenak';
$string['disable_user_use'] = 'Desgaitu erabiltzailearentzat';
$string['disable_user_use_desc'] = 'Erabiltzaileentzako Jakinarazpen Agentearen erabilera desgaitu';
$string['max_rules_cron'] = 'Cron ziklo bakoitzeko gehieneko arau kopurua';
$string['max_rules_cron_desc'] = 'Cron ziklo bakoitzeko gehieneko arau kopurua';
$string['tracelog'] = 'Aztarnaren erregistroa';
$string['tracelog_desc'] = 'Aztarnaren erregistroa. Desgaitu ekoizpen guneetan';
$string['startdate'] = 'Jarduera-dataren ezarpenak';
$string['startdate_desc'] = 'Erabili lerro bat jarduera bakoitzeko ereduarekin: pluginname|tablename|startdate|startend';
$string['pause_after_restore'] = 'Pausatu arauak leheneratu ondoren';
$string['pause_after_restore_desc'] = 'Ikastaro bat leheneratu ondoren pausatu arauak';

// Azpipluginen ezarpenak.
$string['notificationaction_action'] = 'Subplugin motako ekintza ';

$string['managenotificationsactionplugins'] = 'Kudeatu ekintza-pluginak';
$string['managenotificationsconditionplugins'] = 'Kudeatu baldintza-pluginak';

$string['manageactionplugins'] = 'Kudeatu ekintza-pluginak';
$string['manageconditionplugins'] = 'Kudeatu baldintza-pluginak';

$string['actionplugins'] = 'Ekintza-pluginak';
$string['conditionplugins'] = 'Baldintza pluginak';

$string['notificationsactionpluginname'] = 'Plugin ekintza';
$string['notificationsconditionpluginname'] = 'Plugin-baldintza';

$string['hideshow'] = 'Ezkutatu/Erakutsi';

// Zeregin.
$string['tatasktriggerssk'] = 'Jakinarazpen-abiarazleen ataza';
$string['menu'] = 'Nire laguntzailea';
$string['heading'] = 'Jakinarazpen-agentea';

// Egoera Txantiloia.
$string['status_active'] = 'Aktibo';
$string['status_paused'] = 'Pausatuta';
$string['status_required'] = 'Beharrezkoa';
$string['status_broken'] = 'Hautsita';

// Inportatu Txantiloia.
$string['import'] = 'Inportatu';
$string['no_file_selected'] = 'Ez dago fitxategirik hautatu';
$string['import_success'] = 'Araua ondo inportatu da';
$string['import_error'] = 'Ezin izan da araua inportatu, egiaztatu JSON fitxategia';
$string['no_json_file'] = 'Fitxategia ez da JSON';

// Esportatu Txantiloia.
$string['export'] = 'Esportatu';
$string['ruledownload'] = 'Esportatu araua honela';

// Txantiloia esleitu.
$string['assign'] = 'Hautatu';
$string['type_template'] = 'Txantiloia';
$string['type_rule'] = 'Araua';
$string['type_sharedrule'] = 'Arau partekatua';
$string['fulltemplate'] = 'Txantiloia';

// Baldintza pluginak.
$string['condition_days'] = 'Egunak';
$string['condition_hours'] = 'Orduak';
$string['condition_minutes'] = 'Minututuak';
$string['condition_seconds'] = 'Segunduak';
$string['condition_grade'] = 'Titulazioa';

// Editatu Araua.
$string['editrule_clonerule'] = 'Gehitu araua';
$string['editrule_newrule'] = 'Arau berria';
$string['editrule_reports'] = 'Txostenak';
$string['editrule_activaterule'] = 'Aktibatu';
$string['editrule_pauserule'] = 'Eten';
$string['editrule_editrule'] = 'Editatu';
$string['editrule_reportrule'] = 'Txostena';
$string['editrule_deleterule'] = 'Ezabatu';
$string['editrule_newtemplate'] = 'Txantiloi berria';
$string['editrule_title'] = 'Izenburua';
$string['editrule_type'] = 'Arau mota';
$string['editrule_usetemplate'] = 'Hautatu';
$string['editrule_sharerule'] = 'Partekatu';
$string['editrule_unsharerule'] = 'Ez partekatu';
$string['editrule_shareallrule'] = 'Partekatu';
$string['editrule_unshareallrule'] = 'Partekatu gabe';
$string['editrule_sharedallrule'] = 'Partekatua';
$string['editrule_timesfired'] = 'Tiro kopurua';
$string['editrule_runtime'] = 'Aldikotasuna';
$string['editrule_orderby'] = 'Ordenatu';

// Condition.
$string['editrule_generalconditions'] = 'Baldintza orokorrak';
$string['editrule_newcondition'] = 'Baldintza berria:';
$string['editrule_condition_title_tocloseactivity'] = '[TTTT] baino gutxiago falta da [AAAA] jarduera ixteko';
$string['editrule_condition_title_usercompleteactivity'] = 'Erabiltzaileak jarduera burutu du [AAAA]';
$string['editrule_condition_title_activeactivity'] = '[AAAA] jarduera eskuragarri dago';
$string['editrule_condition_title_betweendates'] = '[FFFF-1] eta [FFFF-2] dataren artean gaude';
$string['editrule_condition_title_accessforumactivitiescomplete'] = 'Osatu jarduera guztiak foro honetara sartzeko [FFFF]';
$string['editrule_condition_title_forumnotanswer']
    = 'Erabiltzaileak [FFFF] foroan irekitako haria [TTTT] denbora baino gehiago erantzun gabe';

$string['editrule_condition_element_time'] = 'Denbora {$a->typeelement}:';
$string['editrule_condition_element_activity'] = '{$a->typeelement} jarduera:';
$string['editrule_condition_element_date_from'] = get_string('from') . ' {$a->typeelement}:';
$string['editrule_condition_element_date_to'] = get_string('to') . ' {$a->typeelement}:';

$string['editrule_condition_error'] = 'Gutxienez baldintza bat gehitu behar duzu';

// Ekintzak.
$string['editrule_newaction'] = 'Ekintza berria:';
$string['editrule_action_title_individualnotification']
    = 'Bidali banakako jakinarazpena izenburuarekin [TTTT] eta mezuarekin [BBBB]';
$string['editrule_action_title_notificationtouser']
    = 'Bidali jakinarazpena erabiltzaile jakin bati [UUUU] izenburuarekin [TTTT] eta mezuarekin [BBBB]';
$string['editrule_action_title_postgeneralforum']
    = 'Argitaratu mezu orokor bat foroan [FFFF] izenburuarekin [TTTT] eta mezuarekin [BBBB]';
$string['editrule_action_title_postprivateforum']
    = 'Argitaratu mezu pribatu bat foroan [FFFF] izenburuarekin [TTTT] eta mezuarekin [BBBB]';
$string['editrule_action_title_addusertogroup'] = 'Gehitu erabiltzailea [GGGG] taldean';
$string['editrule_action_title_removeuserfromgroup'] = 'Kendu erabiltzaile bat [GGGG] talde batetik';
$string['editrule_action_title_bootstrapnotification'] = 'Bootstrap jakinarazpena';

$string['editrule_action_element_title'] = '{$a->typeelement} izenburua:';
$string['editrule_action_element_message'] = '{$a->typeelement} mezua:';
$string['editrule_action_element_user'] = '{$a->typeelement} erabiltzailea:';
$string['editrule_action_element_forum'] = 'Foroa {$a->typeelement}:';
$string['editrule_action_element_group'] = '{$a->typeelement} taldea';

$string['subplugintype_notificationsagentaction'] = 'Azpipluginen ekintza';

$string['editrule_action_error'] = 'Gutxienez ekintza bat gehitu behar duzu';

// Rule.
$string['rulecancelled'] = 'Araua bertan behera utzi da';
$string['rulesaved'] = 'Araua gorde da';

// Rule errors.
$string['editrule_required_error'] = 'Beharrezko eremua';
$string['editrule_runtime_error'] = 'Ez ' . $string['editrule_timesfired'] . ' > 0 da, tarte bat adierazi behar du';
$string['editrule_execution_error'] = $string['editrule_timesfired'] . ' {$a->minimum} eta {$a->maximum} arteko eremua';

// Txartelaren edukia.
$string['cardif'] = 'Guztiak bada:';
$string['cardunless'] = 'Hauetakoren bat izan ezean';
$string['cardthen'] = 'Orduan:';
$string['cardsharedby'] = 'Partekatua {$a->username} ikastaroan {$a->coursename}';

// Txartelaren baldintza-denbora.
$string['card_day'] = 'eguna';
$string['card_day_plural'] = 'egunak';
$string['card_hour'] = 'ordua';
$string['card_hour_plural'] = 'orduak';
$string['card_minute'] = 'minutua';
$string['card_minute_plural'] = 'minutu';
$string['card_second'] = 'segundu';
$string['card_second_plural'] = 'segundoak';

// Egoera modala.
$string['status_pausetitle'] = 'Pausatu araua {$a->title}';
$string['status_activatetitle'] = 'Erregela aktibatu {$a->title}';
$string['status_pausecontent'] = 'Araua geldituko da {$a->title}, jarraitu nahi duzu?';
$string['status_activatecontent'] = '{$a->title} araua aktibatuko da, jarraitu nahi duzu?';
$string['status_acceptactivated'] = 'Erregela aktibatua';
$string['status_acceptpaused'] = 'Erregela pausatua';
$string['statuspause'] = 'Pausatu';
$string['statusactivate'] = 'Activatu';

// Modala ezabatu.
$string['deletetitle'] = 'Ezabatu {$a->type} {$a->title}';
$string['deletecontent_nocontext'] = '{$a->type} {$a->title} ezabatzen ari da, jarraitu nahi duzu?';
$string['deletecontent_hascontext']
    = 'Ezabatu nahi duzun {$a->type} {$a->title} beste testuinguru batzuekin lotuta dago, jarraitu nahi duzu?';
$string['deleteaccept'] = 'Ezabatutako araua';

// Modala esleitu.
$string['assignassign'] = 'Esleitu: ';
$string['assigncancel'] = 'Utzi';
$string['assignsave'] = 'Gorde aldaketak';
$string['assignforced'] = 'Indarrean esleitu araua';

// Partekatu modala.
$string['sharetitle'] = 'Partekatu {$a->title} araua';
$string['sharecontent'] = '{$a->title} araua administratzailearekin partekatzen ari da, jarraitu nahi duzu?';
$string['unsharetitle'] = 'Ez partekatu araua {$a->title}';
$string['unsharecontent'] = '{$a->title} araua administratzailearekin partekatzear dago, jarraitu nahi duzu?';
$string['shareaccept'] = 'Arau partekatua';
$string['unshareaccept'] = 'Partekatu gabeko araua';
$string['sharereject'] = 'Araudia baztertua';

// Partekatu modal guztiak.
$string['sharealltitle'] = 'Onartu {$a->title} partekatze-araua';
$string['shareallcontent'] = '{$a->title} partekatzeko araua onartzear dago, jarraitu nahi duzu?';

// Unshare all modal.
$string['unsharealltitle'] = 'Ukatu {$a->title} partekatzeko araua';
$string['unshareallcontent'] = '{$a->title} partekatzeko araua baztertzen ari da, jarraitu nahi duzu?';

// Capabilities.
$string['notificationsagent:createrule'] = 'Sortu arau bat';
$string['notificationsagent:etrule'] = 'Eguneratu arau bat';
$string['notificationsagent:checkrulecontext'] = 'Egiaztatu arau baten testuingurua';
$string['notificationsagent:deleterule'] = 'Ezabatu arau bat';
$string['notificationsagent:updaterulestatus'] = 'Eguneratu arau baten egoera';
$string['notificationsagent:exportrule'] = 'Esportatu arau bat';
$string['notificationsagent:importrule'] = 'Inportatu arau bat';
$string['notificationsagent:assignrule'] = 'Esleitu arau bat';
$string['notificationsagent:forcerule'] = 'Behartu arau bat';
$string['notificationsagent:updateruleshare'] = 'Eguneratu arau baten partekatze-egoera';
$string['notificationsagent:shareruleall'] = 'Onartu arau bat partekatzea';
$string['notificationsagent:unshareruleall'] = 'Ukatu arau bat partekatzea';
$string['notificationsagent:managesiterule'] = 'Kudeatu gune-mailako arauak';
$string['notificationsagent:managecourserule'] = 'Kudeatu arauak kurtso mailan';
$string['notificationsagent:manageownrule'] = 'Kudeatu zure arauak ikastaroan';
$string['notificationsagent:viewassistantreport'] = 'Ikusi arauen txostena';

$string['notificationsagent:activitycompleted'] = 'Jarduera amaitutako baldintza erabiltzeko gaitasuna';
$string['notificationsagent:activityopen'] = 'Activityopen baldintza erabiltzeko gaitasuna';
$string['notificationsagent:coursestart'] = 'Ikastaroaren hasierako baldintza erabiltzeko gaitasuna';
$string['notificationsagent:calendarstart'] = 'Calendarstart baldintza erabiltzeko gaitasuna';
$string['notificationsagent:sessionstart'] = 'Sesio hasierako baldintza erabiltzeko gaitasuna';
$string['notificationsagent:activityavailable'] = 'Jarduera erabilgarri dagoen baldintza erabiltzeko gaitasuna behar da';
$string['notificationsagent:activityend'] = 'Jardueraren amaiera-baldintza erabiltzeko gaitasuna behar da';
$string['notificationsagent:activitylastsend'] = 'Aktibitatearen azken bidalketa-baldintza erabiltzeko gaitasuna behar da';
$string['notificationsagent:activitymodified'] = 'Jarduera aldatutako baldintza erabiltzeko gaitasuna behar da';
$string['notificationsagent:activitynewcontent'] = 'Jarduera eduki berriaren baldintza erabiltzeko gaitasuna behar da';
$string['notificationsagent:activitysinceend'] = 'Aktibitatearen amaierako baldintzatik aurrera erabiltzeko gaitasuna behar da';
$string['notificationsagent:activitystudentend'] = 'Jarduera ikaslearen amaiera-baldintza erabiltzeko gaitasuna behar da';
$string['notificationsagent:calendareventto'] = 'Egutegiko gertaerak baldintzatzeko gaitasuna behar da';
$string['notificationsagent:courseend'] = 'Ikastaroaren amaierako baldintza erabiltzeko gaitasuna behar da';
$string['notificationsagent:forumnoreply'] = 'Foroan erantzunik gabeko baldintza erabiltzeko gaitasuna behar da';
$string['notificationsagent:numberoftimes'] = 'Baldintza kopurua erabiltzeko gaitasuna behar da';
$string['notificationsagent:sessionend'] = 'Saio amaierako baldintza erabiltzeko gaitasuna behar da';
$string['notificationsagent:weekend'] = 'Asteburuko baldintza erabiltzeko gaitasuna behar da';
$string['notificationsagent:itemgraded'] = 'Elementu kalifikatzaile-baldintza erabiltzeko beharrezkoa den gaitasuna';
$string['notificationsagent:weekdays'] = 'Asteko egunak baldintza erabiltzeko beharrezko gaitasuna';
$string['notificationsagent:ondates'] = 'Daten artean baldintza erabiltzeko beharrezko gaitasuna';
$string['notificationsagent:usergroupadd'] = 'Erabiltzailea talde-baldintza batera erabiltzeko beharrezko gaitasuna';

$string['notificationsagent:addusergroup'] = 'Addusergroup ekintza erabiltzeko gaitasuna';
$string['notificationsagent:bootstrapnotifications'] = 'Bootstrapnotifications ekintza erabiltzeko gaitasuna';
$string['notificationsagent:forummessage'] = 'Forummessage ekintza erabiltzeko gaitasuna';
$string['notificationsagent:messageagent'] = 'Messageagent ekintza erabiltzeko gaitasuna';
$string['notificationsagent:removeusergroup'] = 'removeusergroup ekintza erabiltzeko gaitasuna';
$string['notificationsagent:usermessageagent'] = 'Usermessageagent ekintza erabiltzeko gaitasuna';
$string['notificationsagent:privateforummessage'] = 'Privateforummessage ekintza erabiltzeko gaitasuna';

$string['notificationsagent:viewcourserule'] = 'Ikastaroaren arauak ikusteko gaitasuna';
$string['notificationsagent:manageallrule'] = 'Arau guztiak kudeatzeko gaitasuna';
$string['notificationsagent:managesessions'] = 'Arau-ordena gordetzeko gaitasuna';

// Webzerbitzuak.
$string['nosuchinstance'] = 'Instantzia hau ez da aurkitu.';
$string['isnotrule'] = 'Arau-identifikatzaile hau ez dago arau bati.';

// Report.
$string['rulename'] = 'Arauaren izena';
$string['report'] = 'Jakinarazpen-agentearen txostena';
$string['id'] = 'id';
$string['ruleid'] = 'Arauaren ID';
$string['fullrule'] = 'Araua';
$string['userid'] = 'Erabiltzaile ID';
$string['fulluser'] = 'Erabiltzailea';
$string['fullcourse'] = 'Ikastaroa';
$string['courseid'] = 'Ikastaroaren IDa';
$string['actionid'] = 'Ekintzaren IDa';
$string['fullaction'] = 'Ekintza';
$string['actiondetail'] = 'Ekintzaren xehetasuna';
$string['timestamp'] = 'Data';

// Nab.
$string['conditions'] = 'Baldintzak';
$string['exceptions'] = 'Salbuespenak';
$string['actions'] = 'Ekintzak';

// Cachea.
$string['cachedef_launched'] = 'Errepikapena hasi den erabiltzaileen zerrenda.';

// Privacy.
$string['privacy:metadata:userid'] = 'Taula honi loturiko erabiltzailearen IDa.';
$string['privacy:metadata:courseid'] = 'Ikastaro baten ID bat';
$string['privacy:metadata:actionid'] = 'Ekintza baten ID bat';
$string['privacy:metadata:ruleid'] = 'Arau baten ID bat';
$string['privacy:metadata:actiondetail'] = 'Erabiltzaileari bidalitako ekintzaren xehetasuna.';
$string['privacy:metadata:notificationsagentreport']
    = 'Erabiltzaileei bidalitako mezuak erregistratzen ditu, erabiltzailearen datu batzuk eduki ditzaketenak.';
$string['privacy:metadata:timestamp'] = 'Bidalitako ekintzaren denbora-zigilua.';
$string['privacy:metadata:localnotificationsagentreport'] = 'Jakinarazpen-agentearen txostena.';

// Message provider.
$string['messageprovider:notificationsagent_message'] = 'Arau baliogabeen jakinarazpenak';
$string['brokenrulebody'] = 'Araua: {$a->rule} ikastaroaren :{$a->course} pausatu egin da';

// Engine.
$string['actionerror'] = 'Errorea ekintza burutzean ';

$string['subplugintype_notificationscondition'] = 'Baldintzaren plugina';
$string['subplugintype_notificationsaction'] = 'Ekintzaren plugina';
$string['subplugintype_notificationscondition_plural'] = 'Baldintzen pluginak';
$string['subplugintype_notificationsaction_plural'] = 'Ekintza-pluginak';

// Help.
$string['evaluaterule_help'] =
    'Araua honela ebaluatzen da:
 ([1 Baldintza] **ETA** [2. Baldintza] **ETA** ... ) **ETA EZ** ([1. Salbuespena] **OR** [2. Salbuespena] ...) -> [1. Ekintza]->[2. Ekintza ]';
