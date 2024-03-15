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
 * @package    notificationsaction_messageagent
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace notificationsaction_messageagent;

use local_notificationsagent\evaluationcontext;
use local_notificationsagent\rule;
use local_notificationsagent\notificationactionplugin;

class messageagent extends notificationactionplugin {

    /** @var UI ELEMENTS */
    public const NAME = 'messageagent';
    public const UI_MESSAGE = 'message';
    public const UI_TITLE = 'title';

    public function get_ui($mform, $id, $courseid, $type) {
        $this->get_ui_title($mform, $id, $type);

        $title = $mform->createElement(
            'text', $this->get_name_ui($id, self::UI_TITLE),
            get_string(
                'editrule_action_title', 'notificationsaction_messageagent',
                ['typeelement' => '[TTTT]']
            ), ['size' => '64']
        );

        $editoroptions = [
            'maxfiles' => EDITOR_UNLIMITED_FILES,
            'trusttext' => true,
        ];

        $message = $mform->createElement(
            'editor', $this->get_name_ui($id, self::UI_MESSAGE),
            get_string(
                'editrule_action_message', 'notificationsaction_messageagent',
                ['typeelement' => '[BBBB]']
            ),
            ['class' => 'fitem_id_templatevars_editor'], $editoroptions
        );
        $this->placeholders($mform, $id, $type);
        $mform->insertElementBefore($title, 'new' . $type . '_group');
        $mform->insertElementBefore($message, 'new' . $type . '_group');
        $mform->setType($this->get_name_ui($id, self::UI_TITLE), PARAM_TEXT);
        $mform->addRule($this->get_name_ui($id, self::UI_TITLE), null, 'required', null, 'client');
        $mform->setType($this->get_name_ui($id, self::UI_MESSAGE), PARAM_RAW);
        $mform->addRule($this->get_name_ui($id, self::UI_MESSAGE), null, 'required', null, 'client');
    }

    /**
     * @return lang_string|string
     */
    public function get_subtype() {
        return get_string('subtype', 'notificationsaction_messageagent');
    }

    /**
     * Subplugin title
     *
     * @return \lang_string|string
     */
    public function get_title() {
        return get_string('messageagent_action', 'notificationsaction_messageagent');
    }

    /**
     *  Subplugins elements
     *
     * @return string[]
     */
    public function get_elements() {
        return ['[TTTT]', '[BBBB]'];
    }

    public function check_capability($context) {
        return has_capability('local/notificationsagent:messageagent', $context)
            && has_capability('moodle/site:sendmessage', $context);
    }

    /**
     * @param array $params
     *
     * @return mixed
     */
    protected function convert_parameters($id, $params) {
        $params = (array) $params;
        $title = $params[$this->get_name_ui($id, self::UI_TITLE)] ?? 0;
        $message = $params[$this->get_name_ui($id, self::UI_MESSAGE)] ?? 0;
        $this->set_parameters(json_encode(['title' => $title, 'message' => $message]));
        return $this->get_parameters();
    }

    /**
     * Process and replace markups in the supplied content.
     *
     * This function should handle any markup logic specific to a notification plugin,
     * such as replacing placeholders with dynamic data, formatting content, etc.
     *
     * @param array $content  The content to be processed, passed by reference.
     * @param int   $courseid The ID of the course related to the content.
     * @param mixed $options  Additional options if any, null by default.
     *
     * @return void Processed content with markups handled.
     */
    public function process_markups(&$content, $courseid, $options = null) {
        $jsonparams = json_decode($this->get_parameters());

        $message = $jsonparams->{self::UI_MESSAGE}->text ?? '';
        $paramstoteplace = [
            shorten_text(str_replace('{' . rule::SEPARATOR . '}', ' ', $jsonparams->{self::UI_TITLE})),
            shorten_text(format_string(str_replace('{' . rule::SEPARATOR . '}', ' ', $message))),
        ];

        $humanvalue = str_replace($this->get_elements(), $paramstoteplace, $this->get_title());
        $content[] = $humanvalue;
    }

    /**
     * Sending a message
     *
     * @param evaluationcontext $context
     * @param                   $params
     *
     * @return false|int|mixed
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    public function execute_action($context, $params) {
        $placeholdershuman = json_decode($params);
        $sendmessage = notificationactionplugin::get_message_by_timesfired($context, $placeholdershuman->message);

        $message = new \core\message\message();
        $message->name = 'individual_message'; // Your notification name from message.php.
        $message->userto = $context->get_userid();
        $message->userfrom = \core_user::get_noreply_user(); // If the message is 'from' a specific user you can set them here.
        $message->component = 'notificationsaction_messageagent'; // Your plugin's name.
        $message->subject = format_text($placeholdershuman->title); // Será nuestro TTTT.
        $message->fullmessage = format_text($sendmessage); // Será nuestro BBBB.
        $message->fullmessageformat = FORMAT_MARKDOWN;
        $message->fullmessagehtml = '<p>' . format_text($sendmessage) . '</p>';
        $message->smallmessage = 'small message';
        $message->notification = 1; // Because this is a notification generated from Moodle, not a user-to-user message.
        $message->contexturl = (new \moodle_url('/course/view.php?id=' . $context->get_courseid()))->out(
            false
        ); // A relevant URL for the notification.
        $message->contexturlname = get_string('course'); // Link title explaining where users get to for the contexturl.

        // The integer ID of the new message or false if there was a problem
        // ... (with submitted data or sending the message to the message processor).
        return message_send($message);
    }

    public function is_generic() {
        return true;
    }

    /**
     * Returns the parameters to be replaced in the placeholders
     *
     * @return string $json Parameters
     */
    public function get_parameters_placeholders() {
        $parameters = json_decode($this->get_parameters());

        return json_encode([
            'title' => $parameters->{self::UI_TITLE},
            'message' => $parameters->{self::UI_MESSAGE}->text,
        ]);
    }
}
