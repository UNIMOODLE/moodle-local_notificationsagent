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

namespace local_notificationsagent\form;

use local_notificationsagent\plugininfo\notificationsbaseinfo;
use local_notificationsagent\notificationplugin;
use notificationscondition_ac\mod_ac_availability_info;

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once("$CFG->libdir/formslib.php");
require_once("$CFG->dirroot/local/notificationsagent/lib.php");

class editrule_form extends \moodleform {

    private $_actionbuttons = [];

    // JSON HIDDEN FIELDS.
    public const FORM_JSON_AC = 'availabilityconditionsjson';
    public const FORM_JSON_CONDITION = 'jsoncondition';
    public const FORM_JSON_EXCEPTION = 'jsonexception';
    public const FORM_JSON_ACTION = 'jsonaction';

    // SELECT BUTTONS.
    public const FORM_NEW_CONDITION_SELECT = 'newcondition_select';
    public const FORM_NEW_EXCEPTION_SELECT = 'newexception_select';
    public const FORM_NEW_ACTION_SELECT = 'newaction_select';

    // ADD BUTTONS.
    public const FORM_NEW_CONDITION_BUTTON = 'newcondition_button';
    public const FORM_NEW_EXCEPTION_BUTTON = 'newexception_button';
    public const FORM_NEW_ACTION_BUTTON = 'newaction_button';

    // REMOVE BUTTONS.
    public const FORM_REMOVE_CONDITION_BUTTON = 'remove_condition_button';
    public const FORM_REMOVE_EXCEPTION_BUTTON = 'remove_exception_button';
    public const FORM_REMOVE_ACTION_BUTTON = 'remove_action_button';

    // SPAN (CSS CLASSES).
    public const FORM_REMOVE_CONDITION_SPAN = 'remove-condition-span';
    public const FORM_REMOVE_EXCEPTION_SPAN = 'remove-exception-span';
    public const FORM_REMOVE_ACTION_SPAN = 'remove-action-span';

    // Add elements to form.
    public function definition() {
        global $CFG;

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
        $mform->setDefault('timesfired', $this->_customdata['timesfired']);
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('title', PARAM_TEXT);
            $mform->setType('timesfired', PARAM_TEXT);
        } else {
            $mform->setType('title', PARAM_CLEANHTML);
            $mform->setType('timesfired', PARAM_CLEANHTML);
        }

        $runtimegroup[] = $mform->createElement('float', 'runtime_days', '', [
            'class' => 'mr-2', 'size' => 7, 'maxlength' => '3',
            'placeholder' => get_string('condition_days', 'local_notificationsagent'),
            'value' => 1,
        ]);

        $runtimegroup[] = $mform->createElement('float', 'runtime_hours', '', [
            'class' => 'mr-2', 'size' => 7, 'maxlength' => '2',
            'placeholder' => get_string('condition_hours', 'local_notificationsagent'),
            'value' => 0,
        ]);
        $runtimegroup[] = $mform->createElement('float', 'runtime_minutes', '', [
            'class' => 'mr-2', 'size' => 7, 'maxlength' => '2',
            'placeholder' => get_string('condition_minutes', 'local_notificationsagent'),
            'value' => 0,
        ]);

        $mform->addGroup($runtimegroup, 'runtime_group', get_string('editrule_runtime', 'local_notificationsagent'));

        $mform->addElement('hidden', 'ruleid');
        $mform->setType('ruleid', PARAM_INT);
        $mform->setDefault('ruleid', $this->_customdata["ruleid"]);
        $mform->addElement('hidden', 'courseid');
        $mform->setType('courseid', PARAM_INT);
        $mform->setDefault('courseid', $this->_customdata["courseid"]);
        $mform->addElement('hidden', 'action');
        $mform->setType('action', PARAM_TEXT);
        $mform->setDefault('action', $this->_customdata["getaction"]);
        $mform->addElement('hidden', 'tab-target');
        $mform->setType('tab-target', PARAM_TEXT);
        $mform->setDefault('tab-target', 'nav-conditions-tab');

        $mform->addElement('hidden', 'type', get_string('editrule_type', 'local_notificationsagent'));
        $mform->setType('type', PARAM_INT);
        $mform->setDefault('type', $this->_customdata['type']);

        // JSON WITH SUBPLUGINS SELECTED.
        $mform->addElement('hidden', self::FORM_JSON_CONDITION);
        $mform->setType(self::FORM_JSON_CONDITION, PARAM_RAW);
        $this->setInitialJsonTabValue(
            $this->_customdata[notificationplugin::TYPE_CONDITION], self::FORM_JSON_CONDITION
        );// LOAD JSON FROM DB.
        $mform->addElement('hidden', self::FORM_JSON_EXCEPTION);
        $mform->setType(self::FORM_JSON_EXCEPTION, PARAM_RAW);
        $this->setInitialJsonTabValue(
            $this->_customdata[notificationplugin::TYPE_EXCEPTION], self::FORM_JSON_EXCEPTION
        );// LOAD JSON FROM DB.
        $mform->addElement('hidden', self::FORM_JSON_ACTION);
        $mform->setType(self::FORM_JSON_ACTION, PARAM_RAW);
        $this->setInitialJsonTabValue(
            $this->_customdata[notificationplugin::TYPE_ACTION], self::FORM_JSON_ACTION
        );// LOAD JSON FROM DB.

        // ...registerNoSubmitButton for each tab.
        $mform->registerNoSubmitButton(self::FORM_NEW_CONDITION_BUTTON);
        $mform->registerNoSubmitButton(self::FORM_NEW_EXCEPTION_BUTTON);
        $mform->registerNoSubmitButton(self::FORM_NEW_ACTION_BUTTON);

        // ...registerNoSubmitButton for each subplugin.
        $mform->registerNoSubmitButton(self::FORM_REMOVE_CONDITION_BUTTON);
        $mform->addElement('submit', self::FORM_REMOVE_CONDITION_BUTTON);
        $mform->registerNoSubmitButton(self::FORM_REMOVE_EXCEPTION_BUTTON);
        $mform->addElement('submit', self::FORM_REMOVE_EXCEPTION_BUTTON);
        $mform->registerNoSubmitButton(self::FORM_REMOVE_ACTION_BUTTON);
        $mform->addElement('submit', self::FORM_REMOVE_ACTION_BUTTON);

        // Create submit and cancel button.
        $buttonarray = [];
        $buttonarray[] = $mform->createElement('submit', 'submitbutton', get_string('savechanges'));
        $buttonarray[] = $mform->createElement('cancel');
        $this->_actionbuttons = $buttonarray;
    }

    public function definition_after_data() {
        parent::definition_after_data();

        $mform = $this->_form;

        $tab = $mform->getElementValue('tab-target');// Tab selected previously.
        $this->loadTabContent($tab);

        // LOAD JSON.
        $this->loadJsonContent(notificationplugin::TYPE_CONDITION);
        $this->loadJsonContent(notificationplugin::TYPE_EXCEPTION);
        $this->loadJsonContent(notificationplugin::TYPE_ACTION);
    }

    /**
     * Set JSON fields form with BBDD values
     */
    private function setInitialJsonTabValue($fromdb, $element) {
        $mform = $this->_form;

        if (!is_null($fromdb)) {
            $array = [];
            foreach ($fromdb as $condition) {
                $condition->set_ui($array);
            }

            if (!empty($array)) {
                $mform->setDefault($element, json_encode($array));
            }
        }
    }

    /**
     * Add or remove subplugins inside rule
     * This method is called from no_submit_button_pressed()
     */
    public function addOrRemoveSubplugin() {
        $mform = $this->_form;

        // When click registerNoSubmitButton to ADD.
        $mform->getSubmitValue(self::FORM_NEW_CONDITION_BUTTON) ? ($clickadd = self::FORM_NEW_CONDITION_SELECT and
            $type = notificationplugin::TYPE_CONDITION) : null;
        $mform->getSubmitValue(self::FORM_NEW_EXCEPTION_BUTTON) ? ($clickadd = self::FORM_NEW_EXCEPTION_SELECT and
            $type = notificationplugin::TYPE_EXCEPTION) : null;
        $mform->getSubmitValue(self::FORM_NEW_ACTION_BUTTON) ? ($clickadd = self::FORM_NEW_ACTION_SELECT and
            $type = notificationplugin::TYPE_ACTION) : null;
        if (isset($clickadd)) {
            $pluginname = $mform->getSubmitValue($clickadd);
            $this->addJson($type, $pluginname);
        }

        // When click registerNoSubmitButton to REMOVE.
        $mform->getSubmitValue(self::FORM_REMOVE_CONDITION_BUTTON) ? ($clickremove = self::FORM_REMOVE_CONDITION_BUTTON and
            $type = notificationplugin::TYPE_CONDITION) : null;
        $mform->getSubmitValue(self::FORM_REMOVE_EXCEPTION_BUTTON) ? ($clickremove = self::FORM_REMOVE_EXCEPTION_BUTTON and
            $type = notificationplugin::TYPE_EXCEPTION) : null;
        $mform->getSubmitValue(self::FORM_REMOVE_ACTION_BUTTON) ? ($clickremove = self::FORM_REMOVE_ACTION_BUTTON and
            $type = notificationplugin::TYPE_ACTION) : null;
        if (isset($clickremove)) {
            $idtoremove = $mform->getSubmitValue($clickremove);
            $this->removeJson($type, $idtoremove);
        }
    }

    /**
     * Add new plugin to JSON
     */
    private function addJson($type, $pluginname) {
        $mform = $this->_form;

        $jsonelement = $type == notificationplugin::TYPE_CONDITION
            ? self::FORM_JSON_CONDITION
            : ($type == notificationplugin::TYPE_EXCEPTION ? self::FORM_JSON_EXCEPTION
                : ($type == notificationplugin::TYPE_ACTION ? self::FORM_JSON_ACTION : ''));
        $condition = $mform->getElement($jsonelement);
        $conditionvalue = [];
        if (!empty($condition->getValue())) {
            $conditionvalue = json_decode($condition->getValue(), true);
        }
        $key = time();
        $conditionvalue[$key] = ["pluginname" => $pluginname];
        // Set default values for plugin
        content::set_default_plugin($this, $key, $pluginname, $type);
        $condition->setValue(json_encode($conditionvalue));
    }

    /**
     * Remove plugin from JSON
     */
    private function removeJson($type, $id) {
        $mform = $this->_form;

        $jsonelement = $type == notificationplugin::TYPE_CONDITION
            ? self::FORM_JSON_CONDITION
            : ($type == notificationplugin::TYPE_EXCEPTION ? self::FORM_JSON_EXCEPTION
                : ($type == notificationplugin::TYPE_ACTION ? self::FORM_JSON_ACTION : ''));
        $condition = $mform->getElement($jsonelement);
        $conditionvalue = [];
        if (!empty($condition->getValue())) {
            $conditionvalue = json_decode($condition->getValue(), true);
        }
        if (isset($conditionvalue[$id])) {
            unset($conditionvalue[$id]);
        }
        $condition->setValue(json_encode($conditionvalue));
    }

    /**
     * Load current JSON value and the UI from each plugin
     */
    private function loadJsonContent($type) {
        $mform = $this->_form;
        $name = $type == notificationplugin::TYPE_CONDITION
            ? self::FORM_JSON_CONDITION
            : ($type == notificationplugin::TYPE_EXCEPTION ? self::FORM_JSON_EXCEPTION
                : ($type == notificationplugin::TYPE_ACTION ? self::FORM_JSON_ACTION : ''));
        $json = $mform->getElementValue($name);
        if (!empty($json)) {
            $json = json_decode($json, true);
            foreach ($json as $key => $value) {
                content::get_plugin_ui($mform, $key, $this->_customdata["courseid"], $value["pluginname"], $type);
            }
        }
    }

    private function loadTabContent($tabtarget) {
        global $PAGE;

        $mform = $this->_form;

        $render = $PAGE->get_renderer('local_notificationsagent');
        $mform->addElement('html', $render->tabnav($tabtarget));

        $mform->addElement(
            'html', '
            <div class="tab-content" id="nav-tabContent">
        '
        );
        $mform->addElement(
            'html', '
            <div>
        '
        );
        $this->settabcontentavailability();
        $mform->addElement(
            'html', '
            </div>
        '
        );

        $classnabdefault = "tab-pane fade";
        $classnavconditions = ($tabtarget == 'nav-conditions-tab') ? $classnabdefault . ' show active' : $classnabdefault;
        $classnavexceptions = ($tabtarget == 'nav-exceptions-tab') ? $classnabdefault . ' show active' : $classnabdefault;
        $classnavactions = ($tabtarget == 'nav-actions-tab') ? $classnabdefault . ' show active' : $classnabdefault;
        $mform->addElement(
            'html', '
            <div class="' . $classnavconditions . '" id="nav-conditions" role="tabpanel" aria-labelledby="nav-conditions-tab">
        '
        );

        $this->settabcontent(notificationplugin::TYPE_CONDITION);

        $mform->addElement(
            'html', '
            </div>
            <div class="' . $classnavexceptions . '" id="nav-exceptions" role="tabpanel" aria-labelledby="nav-exceptions-tab">
        '
        );

        $this->settabcontent(notificationplugin::TYPE_EXCEPTION);

        $mform->addElement(
            'html', '
            </div>
            <div class="' . $classnavactions . '" id="nav-actions" role="tabpanel" aria-labelledby="nav-actions-tab">
        '
        );

        $this->settabcontent(notificationplugin::TYPE_ACTION);

        // Core_availability conditions.
        $mform->addElement(
            'html', '
            </div>
            <div id="nav-ac">
            '
        );

        $mform->addElement(
            'html', '
            </div>
        </div>
        '
        );

        $mform->addGroup($this->_actionbuttons, 'buttonar', '', [' '], false);
    }

    // Custom validation should be added here.
    public function validation($data, $files) {
        $errors = [];

        // Use this code to validate the 'Restrict access' section.
        // FrontendCustom::report_validation_errors($data, $errors);.

        // Count ac/conditions and actions > 0.
        $ac = $data[self::FORM_JSON_AC];
        $jsoncondition = $data[self::FORM_JSON_CONDITION];
        $jsonaction = $data[self::FORM_JSON_ACTION];

        if (empty($jsoncondition) && mod_ac_availability_info::is_empty($ac)) {
            $errors["newcondition_group"] = get_string('editrule_condition_error', 'local_notificationsagent');
        }
        if (empty($jsonaction)) {
            $errors["newaction_group"] = get_string('editrule_action_error', 'local_notificationsagent');
        }

        return $errors;
    }

    private function settabcontent($type) {
        $mform = $this->_form; // Don't forget the underscore!
        $courseid = $this->_customdata["courseid"];

        $list = notificationsbaseinfo::get_description(
            $courseid, ($type == notificationplugin::TYPE_EXCEPTION ? notificationplugin::TYPE_CONDITION : $type)
        );
        $listoptions = [];
        foreach ($list as $key => $value) {
            $listoptions[$value['name']] = $value['title'];
        }
        $newgroup = [];

        $newgroup[] = $mform->createElement(
            'select', 'new' . $type . '_select', '',
            $listoptions, ['class' => 'col-sm-auto p-0 mr-3']
        );
        $newgroup[] = $mform->createElement('submit', 'new' . $type . '_button', get_string('add'));

        $mform->addGroup($newgroup, 'new' . $type . '_group', '', [' '], false);
    }

    private function settabcontentavailability() {
        global $CFG, $COURSE;

        $mform = $this->_form; // Don't forget the underscore!

        if (!empty($CFG->enableavailability)) {
            $cm = null;
            $title = \html_writer::start_tag('h5');
            $title .= get_string('conditiontext', 'notificationscondition_ac');;
            $title .= \html_writer::end_tag('h5');
            $mform->addElement('textarea', self::FORM_JSON_AC, $title);
            frontendCustom::include_all_javascript($COURSE, $cm);
        }
    }
}

class content {

    private static function instance_subplugin($pluginname, $subtype) {
        $rule = new \stdClass();
        $rule->id = null;
        $type = ($subtype == notificationplugin::TYPE_ACTION ? notificationplugin::TYPE_ACTION
            : notificationplugin::TYPE_CONDITION);
        $pluginclass = '\notifications' . $type . '_' . $pluginname . '\\' . $pluginname;
        if (class_exists($pluginclass)) {
            return new $pluginclass($rule);
        }
        return false;
    }

    public static function get_plugin_ui($mform, $id, $idcourse, $pluginname, $subtype) {
        if ($pluginobj = self::instance_subplugin($pluginname, $subtype)) {
            $pluginobj->get_ui($mform, $id, $idcourse, $subtype);
        }
    }

    public static function set_default_plugin($form, $id, $pluginname, $subtype) {
        if ($pluginobj = self::instance_subplugin($pluginname, $subtype)) {
            $pluginobj->set_default($form, $id);
        }
    }

}

class frontendCustom extends \core_availability\frontend {
    /**
     * Includes JavaScript for the main system and all plugins.
     *
     * @param \stdClass     $course  Course object
     * @param \cm_info      $cm      Course-module currently being edited (null if none)
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
        $PAGE->requires->yui_module(
            $modules,
            'M.core_availability.form.init', [$componentparams], null, true
        );

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
     * @param array $data   Form data fields
     * @param array $errors Error array
     */
    public static function report_validation_errors(array $data, array &$errors) {
        // Empty value is allowed!
        if ($data[editrule_form::FORM_JSON_AC] === '') {
            return;
        }

        // Decode value.
        $decoded = json_decode($data[editrule_form::FORM_JSON_AC]);
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
            $errors[editrule_form::FORM_JSON_AC] = $error;
        }
    }
}