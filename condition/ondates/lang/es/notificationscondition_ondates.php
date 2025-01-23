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
// Funded by the European Union - Next GenerationEU".
//
// Produced by the UNIMOODLE University Group: Universities of
// Valladolid, Complutense de Madrid, UPV/EHU, León, Salamanca,
// Illes Balears, Valencia, Rey Juan Carlos, La Laguna, Zaragoza, Málaga,
// Córdoba, Extremadura, Vigo, Las Palmas de Gran Canaria y Burgos.

/**
 * Version details
 *
 * @package    notificationscondition_ondates
 * @category   string
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined("MOODLE_INTERNAL") || die();

$string['conditiontext'] = 'Estamos entre [FFFF] y [FFFF].';
$string['editrule_condition_element_enddate'] = 'Fecha de fin';
$string['editrule_condition_element_startdate'] = 'Fecha de inicio';
$string['modname'] = 'ondates';
$string['ondates_crontask'] = 'Ondates cron task';
$string['ondatestag'] = 'Entre fechas';
$string['ondatestext'] = 'Estamos entre fechas ({$a->ondates})';
$string['pluginname'] = 'Condición relativa entre fechas';
$string['privacy:metadata'] = 'El plugin de ondates no almacena ningún dato personal.';
$string['subtype'] = 'ondates';
$string['validation_editrule_form_supported_finished_date'] = 'La fecha de fin no puede ser anterior a la actual';
$string['validation_editrule_form_supported_invalid_date'] = 'La fecha de inicio debe ser anterior a la fecha de fin';
