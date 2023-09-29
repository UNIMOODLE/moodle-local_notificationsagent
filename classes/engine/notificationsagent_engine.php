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
 * notificationsagent notificationsagent_engine.php description here.
 *
 * @package    notificationsagent
 * @copyright  2023 ISYC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
global $CFG;
require_once($CFG->dirroot . "/local/notificationsagent/classes/evaluationcontext.php");
use local_notificationsagent\Rule;
use local_notificationsagent\EvaluationContext;


class Notificationsagent_engine {

    //RECIBE: ruleid, userid, timeaccess

    // PROCESO.
    // SP->Reaccionar a un evento. Por ej. sessionstart escucha a course_viewed
    // SP->Qué plugins escuchan ese evento. (Tabla evento o función en subplugin) --> sessionstart
    // SP->Reglas que tienen esa condición (plugin) configurada en mdl_not_condition.
            // Mira a qué usuario afecta la regla en action. ¿?
            // Y que afecten al curso courseid

    // Evaluar la regla (engine)
        // Instancia de la regla
        // Preguntar a cada subplugin si la condición se cumple
        // Evaluar el resto de condiciones


    public static function notificationsagent_engine_evaluate_rule($ruleids, $timeaccess, $userid = null) {

        // Del evento que inicia el motor me puede llegar el userid.

        foreach ($ruleids as $ruleid){

            // Create instance of rule.
            // Create context for evaluation.
            // Evaluate each condition and exception.
            // If resutl is true -> action messageaget
            $rule = Rule::create_instance($ruleid);
            $courseid = $rule->get_courseid();
            $context = new EvaluationContext();
            $context->set_userid($userid);
            $context->set_timeaccess($timeaccess) ;
            $context->set_courseid($courseid);

            $result = $rule->evaluate($context);

            // TODO WIP REFACTOR.
            if ($result){
                $actions = $rule->get_actions($ruleid);
                    foreach($actions as $action){
                        $course_context = context_course::instance($courseid);
                        $pluginname = $action->get_subtype();
                        $parameters = $action->get_parameters();

                        global $CFG;
                        require_once($CFG->dirroot . '/local/notificationsagent/action/'
                            . $action->get_subtype() . '/classes/event/' . $action->get_subtype().'_event.php');
                        $eventname = '\notificationsaction_'.$pluginname.'\event\notificationsagent_'.$pluginname.'_event';
                        $event = $eventname::create(
                            array(
                            'courseid'=>$courseid,
                            'context'=>$course_context,
                            'relateduserid' => $userid,
                            // In 'other' fiels we send parameters.
                            ));
                        $event->trigger();
                    }
            }
        }
    }
}
