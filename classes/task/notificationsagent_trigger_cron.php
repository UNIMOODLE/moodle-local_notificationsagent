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
// Project implemented by the \"Recovery, Transformation and Resilience Plan.
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
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_notificationsagent\task;

defined('MOODLE_INTERNAL') || die();
global $CFG;

use core\task\scheduled_task;
use local_notificationsagent\engine\notificationsagent_engine;

/*
 * Para asegurar un proceso de reevaluación oportuno, se implementa un proceso cron que periódicamente chequea la tabla de control
 * de verificación de reglas. Este proceso se encarga de reevaluar las reglas que han sido programadas para verificar sus
 * condiciones en el momento correspondiente, garantizando así la precisión y puntualidad de las notificaciones.
 * */

class notificationsagent_trigger_cron extends scheduled_task {
    public function get_name() {
        return get_string('tasktriggers', 'local_notificationsagent');
    }

    public function execute() {
        global $CFG, $DB;
        require_once($CFG->dirroot . '/local/notificationsagent/lib.php');
        $timestarted = $this->get_timestarted();
        $classname = get_called_class();
        $component = $this->get_component();
        $lastruntime = $DB->get_records(
            'task_log',
            [
                'classname' => $classname,
                'component' => $component,
            ],
            'id DESC',
            'timestart',
            '1',
            '1'

        );
        $tasklastrunttime = reset($lastruntime)->timestart;
        custom_mtrace("Task started-> " . $timestarted);
        // Rules in the interval  $timestarted and $tasklastrunttime.
        $triggers = get_rulesbytimeinterval($timestarted, $tasklastrunttime);
        // Evalutate rules.
        foreach ($triggers as $trigger) {
            notificationsagent_engine::notificationsagent_engine_evaluate_rule(
                [$trigger->ruleid], $timestarted, $trigger->userid, $trigger->courseid, $trigger->conditionid
            );
        }
        custom_mtrace("Task finished-> " . time());
    }
}
