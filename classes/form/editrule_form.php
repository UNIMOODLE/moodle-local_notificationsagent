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
 * Form for editing a notification rule.
 */
class editrule_form extends \moodleform {
    /**
     * Private variable for storing action buttons.
     *
     * @var array Array of action buttons.
     */
    private $actionbuttons = [];

    /**
     * Private variable for $rule.
     *
     * @var \stdClass of rule
     */
    private $rule;

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
        $rule = $this->rule = $this->_customdata['rule'];

        $mform->addElement(
            'text',
            'title',
            get_string('editrule_title', 'local_notificationsagent'),
            ['size' => '64']
        );
        $mform->addRule('title', ' ', 'required');
        $mform->addElement(
            'float',
            'timesfired',
            get_string('editrule_timesfired', 'local_notificationsagent'),
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
            'static',
            'labeldays',
            '',
            get_string('condition_days', 'local_notificationsagent')
        );
        $runtimegroup[] = $mform->createElement('float', 'runtime_days', '', [
            'class' => 'mr-2', 'size' => 7, 'maxlength' => '3',
            'placeholder' => get_string('condition_days', 'local_notificationsagent'),
            'value' => 1,
        ]);

        // Hours.
        $runtimegroup[] = $mform->createElement(
            'static',
            'labelhours',
            '',
            get_string('condition_hours', 'local_notificationsagent'),
        );
        $runtimegroup[] = $mform->createElement('float', 'runtime_hours', '', [
            'class' => 'mr-2', 'size' => 7, 'maxlength' => '2',
            'placeholder' => get_string('condition_hours', 'local_notificationsagent'),
            'value' => 0,
        ]);

        // Minutes.
        $runtimegroup[] = $mform->createElement(
            'static',
            'labelminutes',
            '',
            get_string('condition_minutes', 'local_notificationsagent'),
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
        $this->actionbuttons = $buttonarray;
    }

    /**
     * Elements after data.
     */
    public function definition_after_data() {
        parent::definition_after_data();

        $mform = $this->_form;

        $tab = $mform->getElementValue('tab-target');// Tab selected previously.
        $this->loadtabcontent($tab);

        // LOAD JSON.
        $this->loadjsoncontent(notificationplugin::TYPE_CONDITION);
        $this->loadjsoncontent(notificationplugin::TYPE_EXCEPTION);
        $this->loadjsoncontent(notificationplugin::TYPE_ACTION);
    }

    /**
     * Add or remove subplugins inside rule
     * This method is called from no_submit_button_pressed()
     */
    public function addorremovesubplugin() {
        $mform = $this->_form;

        $clickadd = null;
        // When click registerNoSubmitButton to ADD.
        if ($mform->getSubmitValue(self::FORM_NEW_CONDITION_BUTTON)) {
            $clickadd = self::FORM_NEW_CONDITION_SELECT;
            $type = notificationplugin::TYPE_CONDITION;
        } else if ($mform->getSubmitValue(self::FORM_NEW_EXCEPTION_BUTTON)) {
            $clickadd = self::FORM_NEW_EXCEPTION_SELECT;
            $type = notificationplugin::TYPE_EXCEPTION;
        } else if ($mform->getSubmitValue(self::FORM_NEW_ACTION_BUTTON)) {
            $clickadd = self::FORM_NEW_ACTION_SELECT;
            $type = notificationplugin::TYPE_ACTION;
        }

        if (!is_null($clickadd)) {
            $pluginname = $mform->getSubmitValue($clickadd);
            $this->addjson($type, $pluginname);
        }

        $clickremove = null;
        // When click registerNoSubmitButton to ADD.
        if ($mform->getSubmitValue(self::FORM_REMOVE_CONDITION_BUTTON)) {
            $clickremove = self::FORM_REMOVE_CONDITION_BUTTON;
            $type = notificationplugin::TYPE_CONDITION;
        } else if ($mform->getSubmitValue(self::FORM_REMOVE_EXCEPTION_BUTTON)) {
            $clickremove = self::FORM_REMOVE_EXCEPTION_BUTTON;
            $type = notificationplugin::TYPE_EXCEPTION;
        } else if ($mform->getSubmitValue(self::FORM_REMOVE_ACTION_BUTTON)) {
            $clickremove = self::FORM_REMOVE_ACTION_BUTTON;
            $type = notificationplugin::TYPE_ACTION;
        }

        if (isset($clickremove)) {
            $idtoremove = $mform->getSubmitValue($clickremove);
            $this->removejson($type, $idtoremove);
        }
    }

    /**
     * Add a JSON element to the form based on the type and plugin name.
     *
     * @param string $type Subplugin type
     * @param string $pluginname Subplugin name
     */
    private function addjson($type, $pluginname) {
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
        content::set_default_plugin($key, $this->rule, $this, $pluginname, $type);
        $condition->setValue(json_encode($conditionvalue));
    }

    /**
     * Remove a JSON element to the form based on the type and plugin name.
     *
     * @param string $type Subplugin type
     * @param int $id Subplugin id
     */
    private function removejson($type, $id) {
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
            // If inserted, then only remove from json.
            if ($conditionvalue[$id]["action"] == self::FORM_JSON_ACTION_INSERT) {
                unset($conditionvalue[$id]);
            } else { // If edit, then remove delete from db.
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
    private function loadjsoncontent($type) {
        $mform = $this->_form;
        $name = $type == notificationplugin::TYPE_CONDITION
            ? self::FORM_JSON_CONDITION
            : ($type == notificationplugin::TYPE_EXCEPTION ? self::FORM_JSON_EXCEPTION
                : ($type == notificationplugin::TYPE_ACTION ? self::FORM_JSON_ACTION : ''));
        $json = $mform->getElementValue($name);
        if (!empty($json)) {
            $json = json_decode($json, true);
            foreach ($json as $key => $value) {
                // Load all plugins to insert or update.
                if ($value["action"] == self::FORM_JSON_ACTION_INSERT || $value["action"] == self::FORM_JSON_ACTION_UPDATE) {
                    content::get_plugin_ui($key, $this->rule, $mform, $this->_customdata["courseid"], $value["pluginname"], $type);
                }
            }
        }
    }

    /**
     * Load the tab content.
     *
     * @param string $tabtarget Tab element
     */
    private function loadtabcontent($tabtarget) {
        global $PAGE;

        $mform = $this->_form;

        $render = $PAGE->get_renderer('local_notificationsagent');
        $mform->addElement('html', $render->tabnav($tabtarget));

        $mform->addElement(
            'html',
            '
            <div class="tab-content" id="nav-tabContent">
        '
        );
        $mform->addElement(
            'html',
            '
            <div>
        '
        );
        $this->settabcontentavailability();
        $mform->addElement(
            'html',
            '
            </div>
        '
        );

        $classnabdefault = "tab-pane fade";
        $classnavconditions = ($tabtarget == 'nav-conditions-tab') ? $classnabdefault . ' show active' : $classnabdefault;
        $classnavexceptions = ($tabtarget == 'nav-exceptions-tab') ? $classnabdefault . ' show active' : $classnabdefault;
        $classnavactions = ($tabtarget == 'nav-actions-tab') ? $classnabdefault . ' show active' : $classnabdefault;
        $mform->addElement(
            'html',
            '
            <div class="' . $classnavconditions . '" id="nav-conditions" role="tabpanel" aria-labelledby="nav-conditions-tab">
        '
        );

        $this->settabcontent(notificationplugin::TYPE_CONDITION);

        $mform->addElement(
            'html',
            '
            </div>
            <div class="' . $classnavexceptions . '" id="nav-exceptions" role="tabpanel" aria-labelledby="nav-exceptions-tab">
        '
        );

        $this->settabcontent(notificationplugin::TYPE_EXCEPTION);

        $mform->addElement(
            'html',
            '
            </div>
            <div class="' . $classnavactions . '" id="nav-actions" role="tabpanel" aria-labelledby="nav-actions-tab">
        '
        );

        $this->settabcontent(notificationplugin::TYPE_ACTION);

        // Core_availability conditions.
        $mform->addElement(
            'html',
            '
            </div>
            <div id="nav-ac">
            '
        );

        $mform->addElement(
            'html',
            '
            </div>
        </div>
        '
        );

        $mform->addGroup($this->actionbuttons, 'buttonar', '', [' '], false);
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
            $errors["timesfired"] = get_string(
                'editrule_execution_error',
                'local_notificationsagent',
                ['timesfired' => get_string(
                    'editrule_timesfired',
                    'local_notificationsagent'),
                    'minimum' => rule::MINIMUM_EXECUTION,
                    'maximum' => rule::MAXIMUM_EXECUTION]
            );
        }

        // If timesfired > 1, runtime > 0.
        if ($data["timesfired"] > 1) {
            if (
                empty($data["runtime_group"]["runtime_days"]) && empty($data["runtime_group"]["runtime_hours"])
                && empty($data["runtime_group"]["runtime_minutes"])
            ) {
                $errors["runtime_group"] = get_string('editrule_runtime_error', 'local_notificationsagent',
                    ['timesfired' => get_string(
                    'editrule_timesfired',
                    'local_notificationsagent')]);
            }
        }

        $ac = $data[self::FORM_JSON_AC];
        $jsoncondition = json_decode($data[self::FORM_JSON_CONDITION], true);
        $jsonexception = json_decode($data[self::FORM_JSON_EXCEPTION], true);
        $jsonaction = json_decode($data[self::FORM_JSON_ACTION], true);

        // VALIDATION FOR JSON.
        $countcondition = $this->validationjsoncontent(notificationplugin::TYPE_CONDITION, $jsoncondition, $data, $errors);
        $this->validationjsoncontent(notificationplugin::TYPE_EXCEPTION, $jsonexception, $data, $errors);
        $countaction = $this->validationjsoncontent(notificationplugin::TYPE_ACTION, $jsonaction, $data, $errors);

        if (empty($countcondition) && custominfo::is_empty($ac)) {
            $errors["newcondition_group"] = get_string('editrule_condition_error', 'local_notificationsagent');
        }

        if (empty($countaction)) {
            $errors["newaction_group"] = get_string('editrule_action_error', 'local_notificationsagent');
        }

        if (!empty($errors)) {
            \core\notification::add(implode('<br />', $errors), \core\notification::ERROR);
        }

        return $errors;
    }

    /**
     * Load JSON content for validation.
     *
     * @param array $type json type
     * @param array $json data form json
     * @param array $data data form
     * @param array $errors Erros
     */
    private function validationjsoncontent($type, $json, $data, &$errors) {
        $count = 0;
        if (!empty($json)) {
            foreach ($json as $key => $value) {
                if ($value["action"] == self::FORM_JSON_ACTION_INSERT || $value["action"] == self::FORM_JSON_ACTION_UPDATE) {
                    $count++;
                    content::get_validation_form_plugin(
                        $key,
                        $data,
                        $this->rule,
                        $this->_customdata["courseid"],
                        $value["pluginname"],
                        $type,
                        $errors
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
            $courseid,
            ($type == notificationplugin::TYPE_EXCEPTION ? notificationplugin::TYPE_CONDITION : $type)
        );
        $listoptions = [];
        foreach ($list as $key => $value) {
            $listoptions[$value['name']] = $value['title'];
        }
        $newgroup = [];

        $newgroup[] = $mform->createElement(
            'select',
            'new' . $type . '_select',
            '',
            $listoptions,
            ['class' => 'col-sm-auto p-0 mr-3']
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
            $title .= get_string('conditiontext', 'notificationscondition_ac');
            $title .= \html_writer::end_tag('h5');
            $mform->addElement('textarea', self::FORM_JSON_AC, $title);
            frontendCustom::include_all_javascript($COURSE, $cm);
        }
    }
}
