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

namespace local_notificationsagent\form;

use local_notificationsagent\plugininfo\notificationsbaseinfo;
use local_notificationsagent\notificationplugin;
use local_notificationsagent\rule;
use notificationscondition_ac\custominfo;

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once("$CFG->libdir/formslib.php");
require_once("$CFG->dirroot/local/notificationsagent/lib.php");

/**
 * Content class, contains functions to display html content.
 */
class content {
    /**
     * Get the plugin UI.
     *
     * @param mixed $id id
     * @param \stdClass $rule rule
     * @param \moodleform $mform Form
     * @param int $idcourse course id
     * @param string $pluginname Subplugin name
     * @param string $subtype Subplugin type
     */
    public static function get_plugin_ui($id, $rule, $mform, $idcourse, $pluginname, $subtype) {
        $typeconditionoraction = $subtype == notificationplugin::TYPE_EXCEPTION ? notificationplugin::TYPE_CONDITION : $subtype;
        if ($subplugin = notificationplugin::create_instance($id, $typeconditionoraction, $pluginname, $rule)) {
            $subplugin->get_ui($mform, $idcourse, $subtype);
        }
    }

    /**
     * Set the default plugin for a form and id.
     *
     * @param mixed $id id
     * @param \stdClass $rule rule
     * @param \moodleform $form Form
     * @param string $pluginname Subplugin name
     * @param string $subtype Subplugin type
     */
    public static function set_default_plugin($id, $rule, $form, $pluginname, $subtype) {
        $typeconditionoraction = $subtype == notificationplugin::TYPE_EXCEPTION ? notificationplugin::TYPE_CONDITION : $subtype;
        if ($subplugin = notificationplugin::create_instance($id, $typeconditionoraction, $pluginname, $rule)) {
            $subplugin->set_default($form);
        }
    }

    /**
     * Get the validation form plugin.
     *
     * @param int $id id
     * @param array $data data form
     * @param \stdClass $rule rule
     * @param int $idcourse course id
     * @param string $pluginname Subplugin name
     * @param string $subtype Subplugin type
     * @param array $errors array of errors
     */
    public static function get_validation_form_plugin($id, $data, $rule, $idcourse, $pluginname, $subtype, &$errors) {
        $typeconditionoraction = $subtype == notificationplugin::TYPE_EXCEPTION ? notificationplugin::TYPE_CONDITION : $subtype;
        if ($subplugin = notificationplugin::create_instance($id, $typeconditionoraction, $pluginname, $rule)) {
            $subplugin->convert_parameters($data);
            $subplugin->validation($idcourse, $errors);
        }
    }
}
