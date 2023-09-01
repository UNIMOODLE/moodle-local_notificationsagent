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
 * Edit
 *
 * @package    local_notificationsagent
 * @copyright  2023 UNIMOODLE
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_notificationsagent\plugininfo\notificationsbaseinfo;

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once("$CFG->libdir/formslib.php");
require_once ("$CFG->dirroot/local/notificationsagent/lib.php");

class editrule extends \moodleform {

    //Add elements to form
    public function definition() {
        global $CFG, $SESSION;
       
        $mform = $this->_form; // Don't forget the underscore! 

        $mform->addElement('text', 'title', get_string('editrule_title', 'local_notificationsagent'), array('size' => '64', 'required' => true ));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('title', PARAM_TEXT);
        } else {
            $mform->setType('title', PARAM_CLEANHTML);
        }
        if(!empty($SESSION->NOTIFICATIONS['FORMDEFAULT']['id_title'])){
            $mform->setDefault('title',
            $SESSION->NOTIFICATIONS['FORMDEFAULT']['id_title']);
        }

        $mform->addElement('html', '
        <nav>
            <div class="nav nav-tabs mb-3" id="nav-tab" role="tablist">
                <a class="nav-item nav-link active" id="nav-conditions-tab" data-toggle="tab" href="#nav-conditions" role="tab" aria-controls="nav-conditions" aria-selected="true">Condiciones</a>
                <a class="nav-item nav-link" id="nav-exceptions-tab" data-toggle="tab" href="#nav-exceptions" role="tab" aria-controls="nav-exceptions" aria-selected="false">Excepciones</a>
                <a class="nav-item nav-link" id="nav-actions-tab" data-toggle="tab" href="#nav-actions" role="tab" aria-controls="nav-actions" aria-selected="false">Acciones</a>
            </div>
        </nav>
        ');
        
        $mform->addElement('html', '
        <div class="tab-content" id="nav-tabContent">
            <div class="tab-pane fade show active" id="nav-conditions" role="tabpanel" aria-labelledby="nav-conditions-tab">
            ');
                //$this->conditions($mform);
        $this->settabcontent("condition",$mform);
        $mform->addElement('html', '
            </div>
            <div class="tab-pane fade" id="nav-exceptions" role="tabpanel" aria-labelledby="nav-exceptions-tab">
        ');
        $this->settabcontent("condition", $mform, "exception");
        $mform->addElement('html', '
            </div>
            <div class="tab-pane fade" id="nav-actions" role="tabpanel" aria-labelledby="nav-actions-tab">
        ');
                //$this->actions($mform);
        $this->settabcontent("action",$mform);

        $mform->addElement('html', '
            </div>
        </div>
        ');
            
        $this->add_action_buttons();
        //Al guardar cambios, borrar todos los $SESSION referentes al formulario de nueva Regla 
        // unset($SESSION->NOTIFICATIONS['CONDITIONS']);
        // unset($SESSION->NOTIFICATIONS['EXCEPTIONS']);
        // unset($SESSION->NOTIFICATIONS['ACTIONS']);
    }

    //Custom validation should be added here
    function validation($data, $files) {
        return array();
    }

    private function settabcontent($type, $mform, $exception = null) {
        global $SESSION;
        //Get new Conditions
        require_once('content.php');
        $obj = new content();
        //$method = 'construct'.$type;
        echo $obj->renderform($mform, $SESSION->NOTIFICATIONS['IDCOURSE'], $type, $exception);
        //$listconditions = get_conditions_description($SESSION->NOTIFICATIONS['IDCOURSE']);
        $list = notificationsbaseinfo::get_description($type);
        $listoptions = array();
        foreach ($list as $key => $value) {
            $key = $value['name'] . ':' . json_encode($value['elements']);
            $listoptions[$key] = $value['title'];
        }
        $new_group = array();
        $new_group[] =& $mform->createElement('select', 'new' . $type . $exception . '_select', '', $listoptions, array('class' => 'col-sm-auto p-0 mr-3'));
        $new_group[] =& $mform->createElement('button', 'new' . $type . $exception . '_button', get_string('add'));

        $mform->addElement('group', 'new' . $type . $exception . '_group', get_string('editrule_new' . $type, 'local_notificationsagent'), $new_group);
    }
}
