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

use local_notificationsagent\plugininfo\notificationsbaseinfo;

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once("$CFG->libdir/formslib.php");
require_once("$CFG->dirroot/local/notificationsagent/lib.php");

class editrule extends \moodleform {

    // Add elements to form.
    public function definition() {
        global $CFG, $SESSION, $COURSE;

        $mform = $this->_form; // Don't forget the underscore!

        $mform->addElement(
            'text', 'title', get_string('editrule_title', 'local_notificationsagent'),
            ['size' => '64']
        );
        $mform->addRule('title', null, 'required', null, 'client');
        $mform->addElement(
            'text', 'timesfired', get_string('editrule_timesfired', 'local_notificationsagent'),
            ['size' => '5']
        );
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('title', PARAM_TEXT);
            $mform->setType('timesfired', PARAM_TEXT);
        } else {
            $mform->setType('title', PARAM_CLEANHTML);
            $mform->setType('timesfired', PARAM_CLEANHTML);
        }
        if (!empty($SESSION->NOTIFICATIONS['FORMDEFAULT']['id_title'])) {
            $mform->setDefault('title',
            $SESSION->NOTIFICATIONS['FORMDEFAULT']['id_title']);
        }
        if (!empty($SESSION->NOTIFICATIONS['FORMDEFAULT']['id_timesfired'])) {
            $mform->setDefault('timesfired',
            $SESSION->NOTIFICATIONS['FORMDEFAULT']['id_timesfired']);
        } else {
            $mform->setDefault('timesfired', $this->_customdata['timesfired']);
        }

        $runtimegroup[] = $mform->createElement('float', 'runtime_days', '', [
            'class' => 'mr-2', 'size' => 7, 'maxlength' => '3',
            'placeholder' => get_string('condition_days', 'local_notificationsagent'),
            'value' => $SESSION->NOTIFICATIONS['FORMDEFAULT']['id_runtime_group_runtime_days'] ?? 1,
        ]);

        $runtimegroup[] = $mform->createElement('float', 'runtime_hours', '', [
            'class' => 'mr-2', 'size' => 7, 'maxlength' => '2',
            'placeholder' => get_string('condition_hours', 'local_notificationsagent'),
            'value' => $SESSION->NOTIFICATIONS['FORMDEFAULT']['id_runtime_group_runtime_hours'] ?? 0,
        ]);
        $runtimegroup[] = $mform->createElement('float', 'runtime_minutes', '', [
            'class' => 'mr-2', 'size' => 7, 'maxlength' => '2',
            'placeholder' => get_string('condition_minutes', 'local_notificationsagent'),
            'value' => $SESSION->NOTIFICATIONS['FORMDEFAULT']['id_runtime_group_runtime_minutes'] ?? 0,
        ]);

        $mform->addGroup($runtimegroup, 'runtime_group', get_string('editrule_runtime', 'local_notificationsagent'));

        $mform->addElement('hidden', 'courseid', $COURSE->id);
        $mform->setType('courseid', PARAM_INT);

        $mform->addElement('hidden', 'type', get_string('editrule_type', 'local_notificationsagent'));
        $mform->setType('type', PARAM_INT);
        $mform->setDefault('type', $this->_customdata['type']);

        $mform->addElement('html', '
        <nav>
            <div class="nav nav-tabs mb-3" id="nav-tab" role="tablist">
                <a class="nav-item nav-link" id="nav-conditions-tab" data-toggle="tab" href="#nav-conditions"
                role="tab" aria-controls="nav-conditions" aria-selected="false">Condiciones</a>
                <a class="nav-item nav-link" id="nav-exceptions-tab" data-toggle="tab" href="#nav-exceptions"
                role="tab" aria-controls="nav-exceptions" aria-selected="false">Excepciones</a>
                <a class="nav-item nav-link" id="nav-actions-tab" data-toggle="tab" href="#nav-actions"
                role="tab" aria-controls="nav-actions" aria-selected="false">Acciones</a>
            </div>
        </nav>
        ');
        $mform->addElement('html', '
            <div class="tab-content" id="nav-tabContent">
        ');
        $mform->addElement('html', '
            <div>
        ');
        $this->settabcontentavailability($mform);
        $mform->addElement('html', '
            </div>
        ');

        $mform->addElement('html', '
            <div class="tab-pane fade show active" id="nav-conditions" role="tabpanel" aria-labelledby="nav-conditions-tab">
        ');
        $this->settabcontent("condition", $mform);
        $mform->addElement('html', '
            </div>
            <div class="tab-pane fade" id="nav-exceptions" role="tabpanel" aria-labelledby="nav-exceptions-tab">
        ');
        $this->settabcontent("condition", $mform, "exception");
        $mform->addElement('html', '
            </div>
            <div class="tab-pane fade" id="nav-actions" role="tabpanel" aria-labelledby="nav-actions-tab">
        ');
        $this->settabcontent("action", $mform);

        // Core_availability conditions.
        $mform->addElement('html', '
            </div>
            <div id="nav-ac">
            ');

        $mform->addElement('html', '
            </div>
        </div>
        ');

        $this->add_action_buttons();
    }

    // Custom validation should be added here.
    public function validation($data, $files) {
        $errors = [];

        // Use this code to validate the 'Restrict access' section.
        // FrontendCustom::report_validation_errors($data, $errors);.

        return $errors;
    }

    private function settabcontent($type, $mform, $exception = null) {
        global $SESSION;

        require_once('content.php');
        $obj = new content();

        echo $obj->renderform($mform, $SESSION->NOTIFICATIONS['IDCOURSE'], $type, $exception);

        $list = notificationsbaseinfo::get_description($type);
        $listoptions = [];
        foreach ($list as $key => $value) {
            $key = $value['name'] . ':' . json_encode($value['elements']);
            $listoptions[$key] = $value['title'];
        }
        $newgroup = [];
        $newgroup[] =& $mform->createElement('select', 'new' . $type . $exception . '_select', '',
            $listoptions, ['class' => 'col-sm-auto p-0 mr-3']
        );
        $newgroup[] =& $mform->createElement('button', 'new' . $type . $exception . '_button', get_string('add'));

        $mform->addElement('group', 'new' . $type . $exception . '_group', get_string('editrule_new' . $type,
            'local_notificationsagent'), $newgroup);
    }

    private function settabcontentavailability($mform) {
        global $COURSE;

        $cm = null;
        $mform->addElement('textarea', 'availabilityconditionsjson',
                'AC');
        frontendCustom::include_all_javascript($COURSE, $cm);
    }
}

class frontendCustom extends \core_availability\frontend {
    /**
     * Includes JavaScript for the main system and all plugins.
     *
     * @param \stdClass $course Course object
     * @param \cm_info $cm Course-module currently being edited (null if none)
     * @param \section_info $section Section currently being edited (null if none)
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
        $PAGE->requires->yui_module($modules,
                'M.core_availability.form.init', [$componentparams], null, true);

        // Include main strings.
        $PAGE->requires->strings_for_js(['none', 'cancel', 'delete', 'choosedots'],
                'moodle');
        $PAGE->requires->strings_for_js([
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
                'availability');
    }

    /**
     * For use within forms, reports any validation errors from the availability
     * field.
     *
     * @param array $data Form data fields
     * @param array $errors Error array
     */
    public static function report_validation_errors(array $data, array &$errors) {
        // Empty value is allowed!
        if ($data['availabilityconditionsjson'] === '') {
            return;
        }

        // Decode value.
        $decoded = json_decode($data['availabilityconditionsjson']);
        if (!$decoded) {
            // This shouldn't be possible.
            throw new \coding_exception('Invalid JSON from availabilityconditionsjson field');
        }
        if (!empty($decoded->errors)) {
            $error = '';
            foreach ($decoded->errors as $stringinfo) {
                list ($component, $stringname) = explode(':', $stringinfo);
                if ($error !== '') {
                    $error .= ' ';
                }
                $error .= get_string($stringname, $component);
            }
            $errors['availabilityconditionsjson'] = $error;
        }
    }
}
