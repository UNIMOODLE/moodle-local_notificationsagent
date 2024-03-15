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
class local_notificationsagent_renderer extends plugin_renderer_base {

    /**
     * Function for the Tabs.
     *
     * @param string $tabtarget description
     *
     * @return string
     */
    public function tabnav($tabtarget) {
        $classnabdefault = "nav-item nav-link";
        $classnavconditions = ($tabtarget == 'nav-conditions-tab') ? $classnabdefault . ' active' : $classnabdefault;
        $classnavexceptions = ($tabtarget == 'nav-exceptions-tab') ? $classnabdefault . ' active' : $classnabdefault;
        $classnavactions = ($tabtarget == 'nav-actions-tab') ? $classnabdefault . ' active' : $classnabdefault;

        $tab = ' <nav>
            <div class="nav nav-tabs mb-3" id="nav-tab" role="tablist">
                <a class="' . $classnavconditions . '" id="nav-conditions-tab" data-toggle="tab" href="#nav-conditions"
                role="tab" aria-controls="nav-conditions" aria-selected="false">' . get_string(
                'conditions', 'local_notificationsagent'
            ) . '</a>
                <a class="' . $classnavexceptions . '" id="nav-exceptions-tab" data-toggle="tab" href="#nav-exceptions"
                role="tab" aria-controls="nav-exceptions" aria-selected="false">' . get_string(
                'exceptions', 'local_notificationsagent'
            ) . '</a>
                <a class="' . $classnavactions . '" id="nav-actions-tab" data-toggle="tab" href="#nav-actions"
                role="tab" aria-controls="nav-actions" aria-selected="false">
                ' . get_string('actions', 'local_notificationsagent') . '</a>
            </div>
        </nav>';
        return $tab;
    }
}
