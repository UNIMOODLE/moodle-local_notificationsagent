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
 * Editrule
 *
 * @package    local_notificationsagent
 * @copyright  2023 UNIMOODLE
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("../../config.php");
require_once('renderer.php');
require_once ("../../lib/modinfolib.php");
require_once ("lib.php");

use local_notificationsagent\Rule;
use local_notificationsagent\notificationplugin;

global $CFG, $DB, $PAGE, $SESSION , $USER;
require_once($CFG->dirroot . '/local/notificationsagent/classes/form/editrule.php');

$courseid = required_param('courseid', PARAM_INT);
$typeaction = required_param('action', PARAM_ALPHANUMEXT);
$SESSION->NOTIFICATIONS['IDCOURSE'] = $courseid;

if($_SERVER['REQUEST_METHOD'] == 'POST' && (!isset($_POST['cancel']) && !isset($_POST['submitbutton']))){
    function get_string_between($string, $start, $end){
        $string = ' ' . $string;
        $ini = strpos($string, $start);
        if ($ini == 0) return '';
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;
        return substr($string, $ini, $len);
    }

    $return = [
        'state' => 'error'
    ];
    if(isset($_POST['key'])){
        $keyelement = $_POST['key'];
        switch($_POST['action']){
            case 'new':
                $listelement = array();
                $listelement += array('title' => $_POST['title']);
                $listelement += array('elements' => $_POST['elements']);
                $listelement += array('name' => $_POST['name']);
                $SESSION->NOTIFICATIONS[$keyelement][] = $listelement;
                if(isset($SESSION->NOTIFICATIONS[$keyelement])){
                    if(isset($_POST['formDefault'])){
                        unset($SESSION->NOTIFICATIONS['FORMDEFAULT']);
                        foreach ($_POST['formDefault'] as $key => $action) {
                            $SESSION->NOTIFICATIONS['FORMDEFAULT'][get_string_between($action, "[id]", "[/id]")] = get_string_between($action, "[value]", "[/value]");
                        }
                    }
                    $return = [
                        'state' => 'success'
                    ];
                }
                break;
            case 'remove':
                $keyelementsession = $_POST['keyelementsession'];
                unset($SESSION->NOTIFICATIONS[$keyelement][$keyelementsession]);
                $SESSION->NOTIFICATIONS[$keyelement] = array_values($SESSION->NOTIFICATIONS[$keyelement]);
                if(empty($SESSION->NOTIFICATIONS[$keyelement])){
                    unset($SESSION->NOTIFICATIONS[$keyelement]);
                }
                if(isset($_POST['formDefault'])){
                    unset($SESSION->NOTIFICATIONS['FORMDEFAULT']);
                    foreach ($_POST['formDefault'] as $key => $action) {
                        $SESSION->NOTIFICATIONS['FORMDEFAULT'][get_string_between($action, "[id]", "[/id]")] = get_string_between($action, "[value]", "[/value]");

                    }
                }
                $return = [
                    'state' => 'success'
                ];
                break;
            /* Cambiar orden elemento array */
            /*
            case 'up':
                $keyelementsession = $_POST['keyelementsession'];
                moveElementArray($SESSION->NOTIFICATIONS[$keyelement], $keyelementsession, $keyelementsession-1);

                break;
            case 'down':
                $keyelementsession = $_POST['keyelementsession'];
                moveElementArray($SESSION->NOTIFICATIONS[$keyelement], $keyelementsession, $keyelementsession+1);
                break;
            */
        }
        echo json_encode($return);
        die();
    }
    
}

if (!$courseid) {
    require_login();
    throw new \moodle_exception('needcourseid');
}


if (!$course = $DB->get_record('course', array('id'=>$courseid))) {
    throw new \moodle_exception('invalidcourseid');
}
require_login($course);
$context = context_course::instance($course->id);

$info = get_fast_modinfo($course);

$PAGE->set_course($course);
$PAGE->set_url(new moodle_url('/local/notificationsagent/editrule.php', array('courseid' => $course->id, 'action' => $typeaction)));
$PAGE->set_title($course->shortname);
$PAGE->set_heading($course->fullname);
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('editrule_newrule', 'local_notificationsagent')." - ".get_string('heading', 'local_notificationsagent'));
$PAGE->set_heading(get_string('heading', 'local_notificationsagent')." - ".get_string('editrule_newrule', 'local_notificationsagent'));
$PAGE->navbar->add(get_string('heading', 'local_notificationsagent'));
$PAGE->requires->js_call_amd('local_notificationsagent/notification_newaction', 'init');
$PAGE->requires->js_call_amd('local_notificationsagent/notification_newcondition', 'init');
$PAGE->requires->js_call_amd('local_notificationsagent/notification_newexception', 'init');
$PAGE->requires->js_call_amd('local_notificationsagent/notification_editruleformactions', 'init');
$PAGE->requires->js_call_amd('local_notificationsagent/notification_placeholders', 'init');

$mform = new editrule('/local/notificationsagent/editrule.php?courseid='.$course->id.'&action='.$typeaction);

//Form processing and displaying is done here
if ($mform->is_cancelled()) {
    //No reenvía bien con cancelar, entra en el $_POST
    $PAGE->set_url(new moodle_url('/local/notificationsagent/index.php', array('courseid' => $course->id)));
    redirect($CFG->wwwroot . '/local/notificationsagent/index.php?courseid='.$course->id, 'Se ha cancelado');
} else if ($fromform = $mform->get_data()) {
    $data = new stdClass;
    $data->courseid = $courseid;
    $data->name = $fromform->title;
    $data->createdat= time();
    $data->createdby = $USER->id;
    $ruleid = $DB->insert_record('notifications_rule', $data);
    // TODO Refactor.
    $pluginData = array();
    $processedPlugins = array();
    $pluginCount = array();
    $dataplugin = new stdClass();
    
    foreach ($fromform as $key => $value) {
        if (strpos($key, "pluginname") === 0) {
            $pluginname = $value;
            
            if (!isset($pluginCount[$pluginname])) {
                $pluginCount[$pluginname] = 1;
            } else {
                $pluginCount[$pluginname]++;
            }
    
            $currentPluginKey = $pluginname . $pluginCount[$pluginname];
            $pluginData[$currentPluginKey] = array('type' => '');
            $pluginData[$currentPluginKey]["complementary"] = 0;
        }elseif (strpos($key, "type") === 0 && isset($currentPluginKey)) {
            $plugintype = $value;
            $pluginData[$currentPluginKey]['type'] = $plugintype;
            
        }elseif (
            isset($currentPluginKey) &&
            (strpos($key, 'condition') !== false || strpos($key, 'action') !== false || strpos($key, 'conditionexception') !== false)
        ) {
            $subkeyWithoutPluginName = str_replace($pluginname . '_', '', $key);
            $pluginData[$currentPluginKey][$subkeyWithoutPluginName] = $value;
        } 
   
        if (strpos($key, "conditionexception") !== false && isset($currentPluginKey)) {
            $pluginData[$currentPluginKey]["complementary"] = 1;
        } 
       
    }
    
    foreach ($pluginData as $currentPluginKey => $pluginDatum) {
        $dataplugin = new \stdClass();
        $dataplugin->ruleid = $ruleid;
        $dataplugin->pluginname = preg_replace('/\d+$/', '', $currentPluginKey);
        $plugintype = preg_replace('/\d+$/', '', $pluginDatum['type']);
        $dataplugin->type =  $plugintype;
        $dataplugin->complementary = $pluginDatum['complementary'];
        // Ruta y creación de objetos de plugin
        $rule = new \stdClass();
        require_once($CFG->dirroot . '/local/notificationsagent/' . $plugintype . '/' . $dataplugin->pluginname  . '/' . $dataplugin->pluginname  . '.php');
       
        $pluginclass = 'notificationsagent_' . $plugintype . '_' . $dataplugin->pluginname;
        $pluginobj = new $pluginclass($rule);
        $dataplugin->parameters = $pluginobj->get_parameters($pluginDatum);
        $DB->insert_record('notifications_rule_plugins', $dataplugin);
       
    }
  
   

    
   

    //In this case you process validated data. $mform->get_data() returns data posted in form.
    $PAGE->set_url(new moodle_url('/local/notificationsagent/index.php', array('courseid' => $course->id)));
    redirect($CFG->wwwroot . '/local/notificationsagent/index.php?courseid='.$course->id, 'Se ha guardado');
} else {
    // this branch is executed if the form is submitted but the data doesn't validate and the form should be redisplayed
    // or on the first display of the form.
}


$output = $PAGE->get_renderer('local_notificationsagent');

echo $output->header();

//Set default data (if any)
$mform->set_data($mform);

$mform->display();

echo $output->footer();
