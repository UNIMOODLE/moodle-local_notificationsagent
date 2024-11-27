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

namespace local_notificationsagent;

use local_notificationsagent\form\editrule_form;
use moodle_exception;
use local_notificationsagent\notificationsagent;
use local_notificationsagent\notificationplugin;

/**
 * Abstract class representing a notification action plugin.
 *
 * This class provides the necessary structure and methods that all notification action
 * plugins should inherit and implement according to their specific needs.
 */
abstract class notificationactionplugin extends notificationplugin {
    /**
     * Constructor for the class.
     *
     * @param int|stdClass $rule object from DB table 'notificationsagent_rule' or just a rule id
     * @param int $id If is numeric => value is already in DB
     *
     */
    public function __construct($rule, $id = null) {
        parent::__construct($rule, $id);

        if (is_numeric($id)) {
            global $DB;
            if ($subplugin = $DB->get_record('notificationsagent_action', ['id' => $id])) {
                $this->set_id($subplugin->id);
                $this->set_pluginname($subplugin->pluginname);
                $this->set_ruleid($subplugin->ruleid);
                $this->set_type($subplugin->type);
                $this->set_parameters($subplugin->parameters);
            }
        }
    }

    /**
     * Get the title of the notification action plugin.
     *
     * @return string Title of the plugin.
     */
    abstract public function get_title();

    /**
     * Get the elements for the notification action plugin.
     *
     * @return array elements as an associative array.
     */
    abstract public function get_elements();

    /**
     * Returns the main plugin type qualifier.
     *
     * @return string "condition" or "action".
     */
    final public function get_type() {
        return parent::TYPE_ACTION;
    }

    /**
     * Checks whether a user has the capability to use an action within a given context.
     *
     * @param \context $context The context to check the capability in.
     *
     * @return bool True if the user has the capability, false otherwise.
     */
    abstract public function check_capability($context);

    /**
     * Generate placeholders for the given form and insert them before a specific group.
     *
     * @param \moodleform $mform the moodle form object
     * @param string $type the type of the form
     * @param bool $showuserplaceholders
     */
    public function placeholders($mform, $type, $showuserplaceholders) {
        $id = $this->get_id();
        $placeholders = \html_writer::start_tag(
            'div',
            ["id" => "fgroup_id_" . $id . "_" . $this->get_subtype() . "_placeholders", "class" => "form-group row fitem"]
        );
        $placeholders .= \html_writer::start_tag('div', ["class" => "col-md-12"]);
        $placeholders .= \html_writer::start_tag(
            'div',
            ["class" => "notificationvars", "id" => "notificationvars_" . $id . "_" . $type]
        );
        foreach (rule::get_placeholders($showuserplaceholders) as $option) {
            $clipboardtargetid = "notificationvars_" . $id . "_" . $type . "_" . $option;
            $placeholdercodeelement = \html_writer::tag('span',
            '{' . $option . '}',
                 [
                    'id' => $clipboardtargetid,
                    'style' => 'display: none;',
                ]);
            $placeholdername = get_string(
                $option == rule::SEPARATOR ? 'placeholder_Separator' : 'placeholder_' . $option,
                'local_notificationsagent');
            $placeholders .= \html_writer::tag(
                'button',
                $placeholdername . $placeholdercodeelement,
                [
                            "id" => $clipboardtargetid . 'button',
                            "class" => "badge  badge-placeholder",
                            "data-action" => "copytoclipboard",
                            "data-clipboard-target" => '#' . $clipboardtargetid,
                    ]
            );

        }
        $placeholders .= \html_writer::end_tag('div');
        $placeholders .= \html_writer::end_tag('div');
        $placeholders .= \html_writer::end_tag('div');

        $group = $mform->createElement('html', $placeholders);

        $mform->insertElementBefore($group, 'new' . $type . '_group');
    }

    /**
     * Execute an action with the given parameters in the specified context.
     *
     * @param evaluationcontext $context The context in which the action is executed.
     * @param string $params An associative array of parameters for the action.
     *
     * @return mixed The result of the action execution.
     */
    abstract public function execute_action($context, $params);

    /**
     * Check if the action will be sent once or not
     *
     * @param integer $userid User id
     *
     * @return bool $sendonce Will the action be sent once?
     */
    public function is_send_once($userid) {
        return $userid == notificationsagent::GENERIC_USERID ? false : true;
    }

    /**
     * Gets the message to send depending on the timesfired of the rule and the user
     *
     * @param evaluationcontext $context Evaluation Context
     * @param string $message Message
     *
     * @return string $result Message to sent
     */
    public static function get_message_by_timesfired($context, $message) {
        $delimiter = '/{' . rule::SEPARATOR . '}|&lt;!-- pagebreak --&gt;/';

        $messagesplit = preg_split($delimiter, $message);
        $timesfired = $context->get_rule()->get_timesfired();
        $usertimesfired = $context->get_usertimesfired();
        $countmessagesplit = count($messagesplit);

        if (($timesfired == rule::MINIMUM_EXECUTION) || ($usertimesfired > $countmessagesplit)) {
            $messageindex = rand(0, count($messagesplit) - 1);
        } else {
            $messageindex = $usertimesfired - 1;
        }

        $result = $messagesplit[$messageindex];

        return $result;
    }

    /**
     * Save data to the database.
     *
     * @param string $action
     * @param mixed $data The data to be saved.
     *
     * @return void
     */
    public function save($action, $data) {
        $dataplugin = new \stdClass();
        $dataplugin->ruleid = $this->rule->id;
        $dataplugin->pluginname = get_called_class()::NAME;
        $dataplugin->type = $this->get_type();
        if ($action == editrule_form::FORM_JSON_ACTION_INSERT || $action == editrule_form::FORM_JSON_ACTION_UPDATE) {
            $dataplugin->parameters = $this->convert_parameters($data);
        }

        parent::insert_update_delete($action, $dataplugin);
    }

    /**
     * Returns the parameters to be replaced in the placeholders
     *
     * @return string $json Parameters
     */
    public function get_parameters_placeholders() {
        return $this->get_parameters();
    }

    /**
     * Get the news forum for a given course.
     *
     * @param int $course The course ID.
     *
     * @return int  The forum ID.
     */
    public static function get_news_forum($course) {
        global $CFG;

        require_once($CFG->dirroot . '/mod/forum/lib.php');

        return (forum_get_course_forum($course, 'news'))->id;
    }

    /**
     * Check event observer
     *
     * @param array $params
     *
     * @return bool
     */
    public function check_event_observer($params) {
        return false;
    }

    /**
     * Update any necessary ids and json parameters in the database.
     * It is called near the completion of course restoration.
     *
     * @param string $restoreid Restore identifier
     * @param integer $courseid Course identifier
     * @param \base_logger $logger Logger if any warnings
     *
     * @return bool|void False if restore is not required
     */
    public function update_after_restore($restoreid, $courseid, \base_logger $logger) {
        global $DB;

        $oldcmid = json_decode($this->get_parameters())->{self::UI_ACTIVITY};
        $rec = \restore_dbops::get_backup_ids_record($restoreid, 'course_module', $oldcmid);

        if (!$rec || !$rec->newitemid) {
            // If we are on the same course (e.g. duplicate) then we can just
            // use the existing one.
            if ($DB->record_exists('course_modules', ['id' => $oldcmid, 'course' => $courseid])) {
                return false;
            }
            // Otherwise it's a warning.
            $logger->process(
                'Subplugin (' . $this->get_pluginname() . ')
                has an item on action that was not restored',
                \backup::LOG_WARNING
            );
        } else {
            $newparameters = json_decode($this->get_parameters());
            $newparameters->{self::UI_ACTIVITY} = $rec->newitemid;
            $newparameters = json_encode($newparameters);

            $record = new \stdClass();
            $record->id = $this->get_id();
            $record->parameters = $newparameters;

            $DB->update_record('notificationsagent_action', $record);
        }
    }

    /**
     *  Show placeholders relatives to user fields.
     *
     * @return bool
     */
    public function show_user_placeholders() {
        return true;
    }
}
