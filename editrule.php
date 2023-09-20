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
require_once("../../lib/modinfolib.php");
require_once("lib.php");

use local_notificationsagent\Rule;
use local_notificationsagent\notificationplugin;

global $CFG, $DB, $PAGE, $SESSION , $USER;
require_once($CFG->dirroot . '/local/notificationsagent/classes/form/editrule.php');

$courseid = required_param('courseid', PARAM_INT);
$typeaction = required_param('action', PARAM_ALPHANUMEXT);
$SESSION->NOTIFICATIONS['IDCOURSE'] = $courseid;

$cancel = optional_param('cancel', null, PARAM_TEXT);
$submitbutton = optional_param('submitbutton', null, PARAM_TEXT);
$keypost = optional_param('key', null, PARAM_TEXT);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !$cancel && !$submitbutton) {
    function get_string_between($string, $start, $end) {
        $string = ' ' . $string;
        $ini = strpos($string, $start);
        if ($ini == 0) {
            return '';
        }
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;
        return substr($string, $ini, $len);
    }

    $return = [
        'state' => 'error'
    ];
    if (isset($keypost)) {
        $keyelement = $keypost;
        $action = optional_param('action', null, PARAM_TEXT);
        $formdefault = optional_param_array('formDefault', null, PARAM_RAW);
        switch($action) {
            case 'new':
                $listelement = array();
                $listelement += array('title' => optional_param('title', null, PARAM_TEXT));
                $listelement += array('elements' => optional_param_array('elements', null, PARAM_RAW));
                $listelement += array('name' => optional_param('name', null, PARAM_TEXT));
                $SESSION->NOTIFICATIONS[$keyelement][] = $listelement;
                if (isset($SESSION->NOTIFICATIONS[$keyelement])) {
                    if (isset($formdefault)) {
                        unset($SESSION->NOTIFICATIONS['FORMDEFAULT']);
                        foreach ($formdefault as $key => $action) {
                            $SESSION->NOTIFICATIONS['FORMDEFAULT'][get_string_between($action, "[id]", "[/id]")]
                                = get_string_between($action, "[value]", "[/value]");
                        }
                    }
                    $return = [
                        'state' => 'success'
                    ];
                }
                break;
            case 'remove':
                $keyelementsession = optional_param('keyelementsession', null, PARAM_INT);
                unset($SESSION->NOTIFICATIONS[$keyelement][$keyelementsession]);
                $SESSION->NOTIFICATIONS[$keyelement] = array_values($SESSION->NOTIFICATIONS[$keyelement]);
                if (empty($SESSION->NOTIFICATIONS[$keyelement])) {
                    unset($SESSION->NOTIFICATIONS[$keyelement]);
                }
                if (isset($formdefault)) {
                    unset($SESSION->NOTIFICATIONS['FORMDEFAULT']);
                    foreach ($formdefault as $key => $action) {
                        $SESSION->NOTIFICATIONS['FORMDEFAULT'][get_string_between($action, "[id]", "[/id]")]
                            = get_string_between($action, "[value]", "[/value]");
                    }
                }
                $return = [
                    'state' => 'success'
                ];
                break;
        }
        echo json_encode($return);
        die();
    }
}

if (!$courseid) {
    require_login();
    throw new \moodle_exception('needcourseid');
}


if (!$course = $DB->get_record('course', array('id' => $courseid))) {
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
$PAGE->set_title(
    get_string('editrule_newrule', 'local_notificationsagent') . " - " . get_string('heading', 'local_notificationsagent')
);
$PAGE->set_heading(
    get_string('heading', 'local_notificationsagent') . " - " . get_string('editrule_newrule', 'local_notificationsagent')
);
$PAGE->navbar->add(get_string('heading', 'local_notificationsagent'));
$PAGE->requires->js_call_amd('local_notificationsagent/notification_newaction', 'init');
$PAGE->requires->js_call_amd('local_notificationsagent/notification_newcondition', 'init');
$PAGE->requires->js_call_amd('local_notificationsagent/notification_newexception', 'init');
$PAGE->requires->js_call_amd('local_notificationsagent/notification_editruleformactions', 'init');
$PAGE->requires->js_call_amd('local_notificationsagent/notification_placeholders', 'init');

$mform = new editrule(new moodle_url('/local/notificationsagent/editrule.php', array('courseid' => $course->id, 'action' => $typeaction)));

// Form processing and displaying is done here.
if ($mform->is_cancelled()) {
    // No reenvía bien con cancelar, entra en el $_POST.
    $PAGE->set_url(new moodle_url('/local/notificationsagent/index.php', array('courseid' => $course->id)));
    redirect(new moodle_url('/local/notificationsagent/index.php', array('courseid' => $course->id)), get_string('rulecancelled', 'local_notificationsagent'));
} else if ($fromform = $mform->get_data()) {
    $data = new stdClass;
    $data->courseid = $courseid;
    $data->name = $fromform->title;
    $data->createdat = time();
    $data->createdby = $USER->id;
    // TODO Refactor.
    $plugindata = array();
    $plugincount = array();
    $dataplugin = new stdClass();

    foreach ($fromform as $key => $value) {
        if (strpos($key, "pluginname") === 0) {
            $pluginname = $value;

            if (!isset($plugincount[$pluginname])) {
                $plugincount[$pluginname] = 1;
            } else {
                $plugincount[$pluginname]++;
            }

            $currentpluginkey = $pluginname . $plugincount[$pluginname];
            $plugindata[$currentpluginkey] = array('type' => '');
            $plugindata[$currentpluginkey]["complementary"] = 0;
        } else if (strpos($key, "type") === 0 && isset($currentpluginkey)) {
            $plugintype = $value;
            $plugindata[$currentpluginkey]['type'] = $plugintype;

        } else if (
            isset($currentpluginkey) &&
            (strpos($key, 'condition') !== false || strpos($key, 'action') !== false
                || strpos($key, 'conditionexception') !== false)
        ) {
            $subkeywithoutpluginame = str_replace($pluginname . '_', '', $key);
            $plugindata[$currentpluginkey][$subkeywithoutpluginame] = $value;
            
        }

        if (preg_match("/conditionexception\d+$/", $key) && isset($currentpluginkey)) {
            $plugindata[$currentpluginkey]["complementary"] = 1;
        }
    }

    $countCondition = 0;
    $countAction = 0;

    foreach ($plugindata as $item) {
        $type = $item["type"];
        if (strpos($type, "condition") !== false) {
            $countCondition++;
        } elseif (strpos($type, "action") !== false) {
            $countAction++;
        }
    }

    if($countCondition >= 1 && $countAction >=1){
        $ruleid = $DB->insert_record('notificationsagent_rule', $data);
        foreach ($plugindata as $currentpluginkey => $plugindatum) {
            $dataplugin = new \stdClass();
            $dataplugin->ruleid = $ruleid;
            $dataplugin->pluginname = preg_replace('/\d+$/', '', $currentpluginkey);
            $plugintype = preg_replace('/\d+$/', '', $plugindatum['type']);
            $dataplugin->type = $plugintype;
            $dataplugin->complementary = $plugindatum['complementary'];
            // Ruta y creación de objetos de plugin.
            $rule = new \stdClass();
            require_once($CFG->dirroot . '/local/notificationsagent/' . $plugintype . '/' . $dataplugin->pluginname . '/'
                . $dataplugin->pluginname . '.php');

            $pluginclass = 'notificationsagent_' . $plugintype . '_' . $dataplugin->pluginname;
            $pluginobj = new $pluginclass($rule);
            $dataplugin->parameters = $pluginobj->convert_parameters($plugindatum);
            if ($dataplugin->type === \notificationplugin::CAT_ACTION) {
                $DB->insert_record('notificationsagent_action', $dataplugin);
            }else{
                $DB->insert_record('notificationsagent_condition', $dataplugin);
            }
        }
        // In this case you process validated data. $mform->get_data() returns data posted in form.
        $PAGE->set_url(new moodle_url('/local/notificationsagent/index.php', array('courseid' => $course->id)));
        redirect(new moodle_url('/local/notificationsagent/index.php', array('courseid' => $course->id)),  get_string('rulesaved', 'local_notificationsagent'));
    }else{
        $SESSION->NOTIFICATIONS['FORMDEFAULT']['id_title'] = $fromform->title;
        redirect(new moodle_url('/local/notificationsagent/editrule.php', array('courseid' => $course->id, 'action' => $typeaction)),"You must add at least one condition and one action");
    }
} else {
    // This branch is executed if the form is submitted but the data doesn't validate and the form should be redisplayed
    // or on the first display of the form.
}

$output = $PAGE->get_renderer('local_notificationsagent');

echo $output->header();

// Set default data (if any).
$mform->set_data($mform);

$mform->display();

echo $output->footer();