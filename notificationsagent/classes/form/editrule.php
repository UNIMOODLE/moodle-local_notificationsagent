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
        global $CFG;
       
        $mform = $this->_form; // Don't forget the underscore! 

        $mform->addElement('text', 'title', get_string('editrule_title', 'local_notificationsagent'), array('size' => '64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('title', PARAM_TEXT);
        } else {
            $mform->setType('title', PARAM_CLEANHTML);
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
                $this->conditions($mform);
        $mform->addElement('html', '
            </div>
            <div class="tab-pane fade" id="nav-exceptions" role="tabpanel" aria-labelledby="nav-exceptions-tab">
        ');
                //$this->exceptions($mform);
        $mform->addElement('html', '
            </div>
            <div class="tab-pane fade" id="nav-actions" role="tabpanel" aria-labelledby="nav-actions-tab">
        ');
                $this->actions($mform);
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

    private function conditions(&$mform){
        global $SESSION;
        //Get new Conditions
        require_once('Conditions.php');
        
        $objConditions = new Conditions();
        echo $objConditions->constructConditions($mform, $SESSION->NOTIFICATIONS['IDCOURSE']);

        //$listconditions = get_conditions_description($SESSION->NOTIFICATIONS['IDCOURSE']);
        $listconditions = notificationsbaseinfo::get_description('condition');

        $listoptionscondition = array();
        foreach ($listconditions as $key => $value) {
            $key = $value['name'] . ':' . json_encode($value['elements']);
            $listoptionscondition[$key] = $value['title'];   
        }
        $newCondition_group = array();
        $newCondition_group[] =& $mform->createElement('select', 'newCondition_select', '', $listoptionscondition, array('class' => 'col-sm-auto p-0 mr-3'));
        $newCondition_group[] =& $mform->createElement('button', 'newCondition_button', get_string('add'));
        $mform->addGroup($newCondition_group, 'newCondition_group', get_string('editrule_newcondition', 'local_notificationsagent'), array('class' => 'mt-5'), false);
    }

    private function exceptions(&$mform){

    }

    private function actions(&$mform){
        global $SESSION;
        //Get new Actions
        require_once('Action.php');
        
        $objAction = new Action();
        echo $objAction->constructAction($mform, $SESSION->NOTIFICATIONS['IDCOURSE']);
        //$listaction = get_all_actions($SESSION->NOTIFICATIONS['IDCOURSE']);
        $listaction = notificationsbaseinfo::get_description('action');
        $listoptionsaction = array();
        foreach ($listaction as $key => $value) {
            $key = $value['name'] . ':' . json_encode($value['elements']);
            $listoptionsaction[$key] = $value['title'];   
        }     
        $newCondition_group = array();
        $newCondition_group[] =& $mform->createElement('select', 'newAction_select', '', $listoptionsaction, array('class' => 'col-sm-auto p-0 mr-3'));
        $newCondition_group[] =& $mform->createElement('button', 'newAction_button', get_string('add'));
        $mform->addGroup($newCondition_group, 'newAction_group', get_string('editrule_newaction', 'local_notificationsagent'), array('class' => 'mt-5'), false);
    }

    //Custom validation should be added here
    function validation($data, $files) {
        return array();
    }
}
