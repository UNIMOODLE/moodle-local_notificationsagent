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
use local_notificationsagent\rule;
use notificationscondition_ac\custominfo;

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once("$CFG->libdir/formslib.php");
require_once("$CFG->dirroot/local/notificationsagent/lib.php");

/**
 * Form for editing a notification rule.
 */
class editrule_form extends \moodleform {

    /**
     * Private variable for storing action buttons.
     *
     * @var array Array of action buttons.
     */
    private $_actionbuttons = [];

    /**
     * Private variable for $rule.
     *
     * @var \stdClass of rule
     */
    private $_rule;

    // JSON HIDDEN FIELDS.
    /**
     * JSON hidden field for availability conditions.
     */
    public const FORM_JSON_AC = 'availabilityconditionsjson';

    /**
     * JSON hidden field for conditions.
     */
    public const FORM_JSON_CONDITION = 'jsoncondition';

    /**
     * JSON hidden field for exceptions.
     */
    public const FORM_JSON_EXCEPTION = 'jsonexception';

    /**
     * JSON hidden field for actions.
     */
    public const FORM_JSON_ACTION = 'jsonaction';

    // SELECT BUTTONS.
    /**
     * Select button for adding a new condition.
     */
    public const FORM_NEW_CONDITION_SELECT = 'newcondition_select';

    /**
     * Select button for adding a new exception.
     */
    public const FORM_NEW_EXCEPTION_SELECT = 'newexception_select';

    /**
     * Select button for adding a new action.
     */
    public const FORM_NEW_ACTION_SELECT = 'newaction_select';

    // ADD BUTTONS.
    /**
     * Button for adding a new condition.
     */
    public const FORM_NEW_CONDITION_BUTTON = 'newcondition_button';

    /**
     * Button for adding a new exception.
     */
    public const FORM_NEW_EXCEPTION_BUTTON = 'newexception_button';

    /**
     * Button for adding a new action.
     */
    public const FORM_NEW_ACTION_BUTTON = 'newaction_button';

    // REMOVE BUTTONS.
    /**
     * Button for removing a condition.
     */
    public const FORM_REMOVE_CONDITION_BUTTON = 'remove_condition_button';

    /**
     * Button for removing an exception.
     */
    public const FORM_REMOVE_EXCEPTION_BUTTON = 'remove_exception_button';

    /**
     * Button for removing an action.
     */
    public const FORM_REMOVE_ACTION_BUTTON = 'remove_action_button';

    // SPAN (CSS CLASSES).
    /**
     * Span (CSS class) for removing a condition.
     */
    public const FORM_REMOVE_CONDITION_SPAN = 'remove-condition-span';

    /**
     * Span (CSS class) for removing an exception.
     */
    public const FORM_REMOVE_EXCEPTION_SPAN = 'remove-exception-span';

    /**
     * Span (CSS class) for removing an action.
     */
    public const FORM_REMOVE_ACTION_SPAN = 'remove-action-span';

    /**
     * Insert action from json
     */
    public const FORM_JSON_ACTION_INSERT = 'insert';
    /**
     * Update action from json
     */
    public const FORM_JSON_ACTION_UPDATE = 'update';
    /**
     * Delete action from json
     */
    public const FORM_JSON_ACTION_DELETE = 'delete';

    /**
     * Add elements to form.
     *
     */
    public function definition() {
        global $CFG;

        $mform = $this->_form; // Don't forget the underscore!
        $rule = $this->_rule = $this->_customdata['rule'];

        $mform->addElement(
            'text', 'title', get_string('editrule_title', 'local_notificationsagent'),
            ['size' => '64']
        );
        $mform->addRule('title', ' ', 'required');
        $mform->addElement(
            'float', 'timesfired', get_string('editrule_timesfired', 'local_notificationsagent'),
            ['size' => '5']
        );
        $mform->setDefault('timesfired', $this->_customdata['timesfired']);
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('title', PARAM_TEXT);
        } else {
            $mform->setType('title', PARAM_CLEANHTML);
        }

        // Days.
        $runtimegroup[] = $mform->createElement(
            'static', 'labeldays', '', get_string('condition_days', 'local_notificationsagent')
        );
        $runtimegroup[] = $mform->createElement('float', 'runtime_days', '', [
            'class' => 'mr-2', 'size' => 7, 'maxlength' => '3',
            'placeholder' => get_string('condition_days', 'local_notificationsagent'),
            'value' => 1,
        ]);

        // Hours.
        $runtimegroup[] = $mform->createElement(
            'static', 'labelhours', '', get_string('condition_hours', 'local_notificationsagent'),
        );
        $runtimegroup[] = $mform->createElement('float', 'runtime_hours', '', [
            'class' => 'mr-2', 'size' => 7, 'maxlength' => '2',
            'placeholder' => get_string('condition_hours', 'local_notificationsagent'),
            'value' => 0,
        ]);

        // Minutes.
        $runtimegroup[] = $mform->createElement(
            'static', 'labelminutes', '', get_string('condition_minutes', 'local_notificationsagent'),
        );
        $runtimegroup[] = $mform->createElement('float', 'runtime_minutes', '', [
            'class' => 'mr-2', 'size' => 7, 'maxlength' => '2',
            'placeholder' => get_string('condition_minutes', 'local_notificationsagent'),
            'value' => 0,
        ]);

        $mform->addGroup($runtimegroup, 'runtime_group', get_string('editrule_runtime', 'local_notificationsagent'));

        $mform->addElement('hidden', 'ruleid');
        $mform->setType('ruleid', PARAM_INT);
        $mform->setDefault('ruleid', $rule->id);
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
        $mform->setDefault('type', $rule->template);

        // JSON WITH SUBPLUGINS SELECTED.
        $mform->addElement('hidden', self::FORM_JSON_CONDITION);
        $mform->setType(self::FORM_JSON_CONDITION, PARAM_RAW);
        $mform->addElement('hidden', self::FORM_JSON_EXCEPTION);
        $mform->setType(self::FORM_JSON_EXCEPTION, PARAM_RAW);
        $mform->addElement('hidden', self::FORM_JSON_ACTION);
        $mform->setType(self::FORM_JSON_ACTION, PARAM_RAW);

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

    /**
     * Elements after data.
     */
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
     * Add or remove subplugins inside rule
     * This method is called from no_submit_button_pressed()
     */
    public function addorremovesubplugin() {
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
     * Add a JSON element to the form based on the type and plugin name.
     *
     * @param string $type       Subplugin type
     * @param string $pluginname Subplugin name
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
        $key = "new" . time();
        $conditionvalue[$key] = ["pluginname" => $pluginname, "action" => self::FORM_JSON_ACTION_INSERT];
        // Set default values for plugin.
        content::set_default_plugin($key, $this->_rule, $this, $pluginname, $type);
        $condition->setValue(json_encode($conditionvalue));
    }

    /**
     * Remove a JSON element to the form based on the type and plugin name.
     *
     * @param string $type Subplugin type
     * @param int    $id   Subplugin id
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
            // If insert, then only remove from json
            if ($conditionvalue[$id]["action"] == self::FORM_JSON_ACTION_INSERT) {
                unset($conditionvalue[$id]);
            } else { // If edit, then remove delete from db
                $conditionvalue[$id]["action"] = self::FORM_JSON_ACTION_DELETE;
            }
        }
        $condition->setValue(json_encode($conditionvalue));
    }

    /**
     * Load the JSON content.
     *
     * @param string $type Subplugin type
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
                // Load all plugins to insert or update
                if ($value["action"] == self::FORM_JSON_ACTION_INSERT || $value["action"] == self::FORM_JSON_ACTION_UPDATE) {
                    content::get_plugin_ui($key, $this->_rule, $mform, $this->_customdata["courseid"], $value["pluginname"], $type);
                }
            }
        }
    }

    /**
     * Load the tab content.
     *
     * @param string $tabtarget Tab element
     */
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

    /**
     * Custom validation should be added here.
     *
     * @param array $data data form
     * @param mixed $files
     *
     * @return array
     * @throws \coding_exception
     */
    public function validation($data, $files) {
        $errors = [];
        
        if ($data["timesfired"] < rule::MINIMUM_EXECUTION || $data["timesfired"] > rule::MAXIMUM_EXECUTION) {
            $errors["timesfired"] = get_string('editrule_execution_error', 'local_notificationsagent', ['minimum' => rule::MINIMUM_EXECUTION, 'maximum' => rule::MAXIMUM_EXECUTION]);
        }

        // If timesfired > 1, runtime > 0.
        if ($data["timesfired"] > 1) {
            if (empty($data["runtime_group"]["runtime_days"]) && empty($data["runtime_group"]["runtime_hours"])
                && empty($data["runtime_group"]["runtime_minutes"])
            ) {
                $errors["runtime_group"] = get_string('editrule_runtime_error', 'local_notificationsagent');
            }
        }
    
        $ac = $data[self::FORM_JSON_AC];
        $jsoncondition = json_decode($data[self::FORM_JSON_CONDITION], true);
        $jsonexception = json_decode($data[self::FORM_JSON_EXCEPTION], true);
        $jsonaction = json_decode($data[self::FORM_JSON_ACTION], true);

        // VALIDATION FOR JSON.
        $countcondition = $this->validationJsonContent(notificationplugin::TYPE_CONDITION, $jsoncondition, $data, $errors);
        $this->validationJsonContent(notificationplugin::TYPE_EXCEPTION, $jsonexception, $data, $errors);
        $countaction = $this->validationJsonContent(notificationplugin::TYPE_ACTION, $jsonaction, $data, $errors);
        
        if (empty($countcondition) && custominfo::is_empty($ac)) {
            $errors["newcondition_group"] = get_string('editrule_condition_error', 'local_notificationsagent');
        }
        
        if (empty($countaction)) {
            $errors["newaction_group"] = get_string('editrule_action_error', 'local_notificationsagent');
        }

        if(!empty($errors)){
            \core\notification::add(implode('<br />', $errors),\core\notification::ERROR);
        }

        return $errors;
    }

    /**
     * Load JSON content for validation.
     *
     * @param array  $type   json type
     * @param array  $json   data form json
     * @param array  $data   data form
     * @param array  $errors Erros
     */
    private function validationJsonContent($type, $json, $data, &$errors) {
        $count = 0;
        if(!empty($json)){
            foreach ($json as $key => $value) {
                if ($value["action"] == self::FORM_JSON_ACTION_INSERT || $value["action"] == self::FORM_JSON_ACTION_UPDATE) {
                    $count++;
                    content::get_validation_form_plugin(
                        $key, $data, $this->_rule, $this->_customdata["courseid"], $value["pluginname"], $type, $errors
                    );
                }
            }
        }
        return $count;
    }

    /**
     * Set the tab content for the given type.
     *
     * @param string $type Subplugin type
     */
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

    /**
     * Sets the availability of tab content based on the global configuration and course context.
     */
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

/**
 * Content class, contains functions to display html content.
 */
class content {

    /**
     * Get the plugin UI.
     *
     * @param mixed       $id         id
     * @param \stdClass   $rule       rule
     * @param \moodleform $mform      Form
     * @param int         $idcourse   course id
     * @param string      $pluginname Subplugin name
     * @param string      $subtype    Subplugin type
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
     * @param mixed       $id         id
     * @param \stdClass   $rule       rule
     * @param \moodleform $form       Form
     * @param string      $pluginname Subplugin name
     * @param string      $subtype    Subplugin type
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
     * @param int       $id         id
     * @param array     $data       data form
     * @param \stdClass $rule       rule
     * @param int       $idcourse   course id
     * @param string    $pluginname Subplugin name
     * @param string    $subtype    Subplugin type
     * @param array     $errors     array of errors
     */
    public static function get_validation_form_plugin($id, $data, $rule, $idcourse, $pluginname, $subtype, &$errors) {
        $typeconditionoraction = $subtype == notificationplugin::TYPE_EXCEPTION ? notificationplugin::TYPE_CONDITION : $subtype;
        if ($subplugin = notificationplugin::create_instance($id, $typeconditionoraction, $pluginname, $rule)) {
            $subplugin->convert_parameters($data);
            $subplugin->validation($idcourse, $errors);
        }
    }

}

/**
 * Extends the core_availability\frontend class to handle custom frontend actions.
 */
class frontendCustom extends \core_availability\frontend {
    /**
     * Includes JavaScript for the main system and all plugins.
     *
     * @param \stdClass          $course  Course object
     * @param \cm_info|null      $cm      Course-module currently being edited (null if none)
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
     * @param string $ac   Form data fields
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
