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
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_notificationsagent\rule;

require(__DIR__ . '/../../config.php');

global $DB;
require_login();
$ruleid = required_param('ruleid', PARAM_INT);
$courseid = required_param('courseid', PARAM_INT);
$capability = 'local/notificationsagent:exportrule';
$context = \context_course::instance($courseid);
$json = [];

if (!has_capability($capability, $context)) {
    require_capability($capability, $context);
}
$ruleparams = ['id' => $ruleid];
$rule = new rule($ruleid);
$dataexport = $rule->load_dataexport();
$rs = json_encode($dataexport);

header('Content-disposition: attachment; filename=rule_' . $ruleid . '_export.json');
header('Content-type: application/json');
echo $rs;
