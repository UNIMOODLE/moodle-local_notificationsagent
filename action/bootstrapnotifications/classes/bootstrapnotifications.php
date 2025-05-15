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
 * @package    notificationsaction_bootstrapnotifications
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace notificationsaction_bootstrapnotifications;

use local_notificationsagent\rule;
use local_notificationsagent\notificationactionplugin;
use notificationsaction_bootstrapnotifications\bootstrapmessages;

/**
 * Class representing a bootstrapnotifications action plugin.
 */
class bootstrapnotifications extends notificationactionplugin {
    /** @var UI ELEMENTS */
    public const NAME = 'bootstrapnotifications';
    /** @var UI ELEMENTS */
    public const UI_MESSAGE = 'message';

    /**
     * Get the elements for the bootstrapnotifications plugin.
     *
     * @param \moodleform $mform
     * @param int $courseid
     * @param int $type
     */
    public function get_ui($mform, $courseid, $type) {
        $this->get_ui_title($mform, $type);

        $element = $mform->createElement(
            'text',
            $this->get_name_ui(self::UI_MESSAGE),
            get_string(
                'editrule_action_element_text',
                'notificationsaction_bootstrapnotifications',
                ['typeelement' => '[TTTT]']
            ),
            ['size' => '64']
        );

        $this->placeholders($mform, $type, $this->show_user_placeholders());
        $mform->insertElementBefore($element, 'new' . $type . '_group');
        $mform->setType($this->get_name_ui(self::UI_MESSAGE), PARAM_TEXT);
        $mform->addRule(
            $this->get_name_ui(self::UI_MESSAGE),
            get_string('editrule_required_error', 'local_notificationsagent'),
            'required'
        );
    }

    /**
     * Get the title of the notification action plugin.
     *
     * @return string Title of the plugin.
     */
    public function get_title() {
        return get_string('bootstrapnotifications_action', 'notificationsaction_bootstrapnotifications');
    }

    /**
     * Get the elements for the notification action plugin.
     *
     * @return array elements as an associative array.
     */
    public function get_elements() {
        return ['[TTTT]'];
    }

    /**
     * Sublugin capability
     *
     * @param \context $context
     *
     * @return bool
     */
    public function check_capability($context) {
        return has_capability('notificationsaction/bootstrapnotifications:bootstrapnotifications', $context);
    }

    /**
     * Convert parameters for the notification plugin.
     *
     * This method should take an identifier and parameters for a notification
     * and convert them into a format suitable for use by the plugin.
     *
     * @param mixed $params The parameters associated with the notification.
     *
     * @return mixed The converted parameters.
     */
    public function convert_parameters($params) {
        $params = (array) $params;
        $message = $params[$this->get_name_ui(self::UI_MESSAGE)] ?? 0;
        $this->set_parameters(json_encode([self::UI_MESSAGE => $message]));
        return $this->get_parameters();
    }

    /**
     * Process and replace markups in the supplied content.
     *
     * This function should handle any markup logic specific to a notification plugin,
     * such as replacing placeholders with dynamic data, formatting content, etc.
     *
     * @param array $content The content to be processed, passed by reference.
     * @param int $courseid The ID of the course related to the content.
     * @param mixed $options Additional options if any, null by default.
     *
     * @return void Processed content with markups handled.
     */
    public function process_markups(&$content, $courseid, $options = null) {
        $jsonparams = json_decode($this->get_parameters());

        $paramstoteplace =
            [shorten_text(str_replace('{' . rule::SEPARATOR . '}', ' ', strip_tags(format_text($jsonparams->{self::UI_MESSAGE}))))];

        $humanvalue = str_replace($this->get_elements(), $paramstoteplace, $this->get_title());

        $content[] = $humanvalue;
    }

    /**
     * Execute an action with the given parameters in the specified context.
     *
     * @param evaluationcontext $context The context in which the action is executed.
     * @param string $params An associative array of parameters for the action.
     *
     * @return mixed The result of the action execution.
     */
    public function execute_action($context, $params) {
        $placeholdershuman = json_decode($params);
        $sendmessage = notificationactionplugin::get_message_by_timesfired($context, $placeholdershuman->{self::UI_MESSAGE});

        $bootstrap = new bootstrapmessages();
        $bootstrap->set('userid', $context->get_userid());
        $bootstrap->set('courseid', $context->get_courseid());
        $bootstrap->set('message', format_text($sendmessage));
        $bootstrap->save();
        return true;
    }

    /**
     * Check if the action is generic or not.
     *
     * @return bool
     */
    public function is_generic() {
        return true;
    }

    /**
     * Update any necessary ids and json parameters in the database.
     * It is called near the completion of course restoration.
     *
     * @param string $restoreid Restore identifier
     * @param int $courseid Course identifier
     * @param \base_logger $logger Logger if any warnings
     *
     * @return bool|void False if restore is not required
     */
    public function update_after_restore($restoreid, $courseid, \base_logger $logger) {
        return false;
    }
}
