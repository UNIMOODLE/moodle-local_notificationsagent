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
 * Extends the core_availability\frontend class to handle custom frontend actions.
 */
class frontendCustom extends \core_availability\frontend {
    /**
     * Includes JavaScript for the main system and all plugins.
     *
     * @param \stdClass $course Course object
     * @param \cm_info|null $cm Course-module currently being edited (null if none)
     * @param \section_info|null $section Section currently being edited (null if none)
     */
    public static function include_all_javascript($course, \cm_info $cm = null, \section_info $section = null) {
        global $PAGE;

        // Prepare array of required YUI modules. It is bad for performance to
        // make multiple yui_module calls, so we group all the plugin modules
        // into a single call (the main init function will call init for each
        // plugin).
        $modules = [
            'moodle-local_notificationsagent-form', 'base', 'node',
            'panel', 'moodle-core-notification-dialogue', 'json',
        ];

        // Work out JS to include for all components.
        $pluginmanager = \core_plugin_manager::instance();
        $enabled = $pluginmanager->get_enabled_plugins('availability');
        $componentparams = new \stdClass();
        foreach ($enabled as $plugin => $info) {
            // Create plugin front-end object.
            $class = '\availability_' . $plugin . '\frontend';
            $frontend = new $class();

            // Add to array of required YUI modules.
            $component = $frontend->get_component();
            $modules[] = 'moodle-' . $component . '-form';

            // Get parameters for this plugin.
            $componentparams->{$plugin} = [
                $component,
                $frontend->allow_add($course, $cm, $section),
                $frontend->get_javascript_init_params($course, $cm, $section),
            ];

            // Include strings for this plugin.
            $identifiers = $frontend->get_javascript_strings();
            $identifiers[] = 'title';
            $identifiers[] = 'description';
            $PAGE->requires->strings_for_js($identifiers, $component);
        }

        // Include all JS (in one call). The init function runs on DOM ready.
        $PAGE->requires->yui_module(
            $modules,
            'M.core_availability.form.init',
            [$componentparams],
            null,
            true
        );

        // Include main strings.
        $PAGE->requires->strings_for_js(
            ['none', 'cancel', 'delete', 'choosedots'],
            'moodle'
        );
        $PAGE->requires->strings_for_js(
            [
                'addrestriction', 'invalid',
                'listheader_sign_before', 'listheader_sign_pos',
                'listheader_sign_neg', 'listheader_single',
                'listheader_multi_after', 'listheader_multi_before',
                'listheader_multi_or', 'listheader_multi_and',
                'unknowncondition', 'hide_verb', 'hidden_individual',
                'show_verb', 'shown_individual', 'hidden_all', 'shown_all',
                'condition_group', 'condition_group_info', 'and', 'or',
                'label_multi', 'label_sign', 'setheading', 'itemheading',
                'missingplugin',
            ],
            'availability'
        );
    }

    /**
     * For use within forms, reports any validation errors from the availability
     * field.
     *
     * @param string $ac Form data fields
     * @param array $errors Error array
     */
    public static function report_validation_errors($ac, array &$errors) {
        // Empty value is allowed!
        if (custominfo::is_empty($ac)) {
            return;
        }

        // Decode value.
        $decoded = json_decode($ac);
        if (!$decoded) {
            // This shouldn't be possible.
            throw new \coding_exception('Invalid JSON from availabilityconditionsjson field');
        }
        if (!empty($decoded->errors)) {
            $error = '';
            foreach ($decoded->errors as $stringinfo) {
                [$component, $stringname] = explode(':', $stringinfo);
                if ($error !== '') {
                    $error .= ' ';
                }
                $error .= get_string($stringname, $component);
            }
            $errors[editrule_form::FORM_JSON_AC] = $error;
        }
    }
}
