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
// Project implemented by the \"Recovery, Transformation and Resilience Plan.
// Funded by the European Union - Next GenerationEU\".
//
// Produced by the UNIMOODLE University Group: Universities of
// Valladolid, Complutense de Madrid, UPV/EHU, León, Salamanca,
// Illes Balears, Valencia, Rey Juan Carlos, La Laguna, Zaragoza, Málaga,
// Córdoba, Extremadura, Vigo, Las Palmas de Gran Canaria y Burgos

/**
 * Version details
 *
 * @package    local_notificationsagent
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_notificationsagent;

use local_notificationsagent\Rule;

class EvaluationContext {

    private $userid; // Evento.
    private $courseid; // Evento o regla.
    private $timeaccess; // Evento.
    private $params; // Los que vienen del plugin.

    /**
     * @return mixed
     */
    public function get_userid() {
        return $this->userid;
    }

    /**
     * @param mixed $userid
     */
    public function set_userid($userid): void {
        $this->userid = $userid;
    }

    /**
     * @return mixed
     */
    public function get_courseid() {
        return $this->courseid;
    }

    /**
     * @param mixed $courseid
     */
    public function set_courseid($courseid): void {
        $this->courseid = $courseid;
    }

    /**
     * @return mixed
     */
    public function get_timeaccess() {
        return $this->timeaccess;
    }

    /**
     * @param mixed $timeaccess
     */
    public function set_timeaccess($timeaccess): void {
        $this->timeaccess = $timeaccess;
    }

    /**
     * @return mixed
     */
    public function get_params() {
        return $this->params;
    }

    /**
     * @param mixed $params
     */
    public function set_params($params): void {
        $this->params = $params;
    }

}
