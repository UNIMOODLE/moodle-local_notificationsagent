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


use notificationsagent\notificationsagent;
use local_notificationsagent\Rule;

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->dirroot . "/local/notificationsagent/classes/notificationactionplugin.php");

class notificationsagent_action_messageagent extends notificationactionplugin {
    public function get_description() {
        return [
            'title' => $this->get_title(),
            'elements' => $this->get_elements(),
            'name' => $this->get_subtype(),
        ];
    }

    public function get_ui($mform, $id, $courseid, $exception) {
        global $SESSION;
        $valuesession = 'id_' . $this->get_subtype() . '_' . $this->get_type() . $exception . $id;

        $mform->addElement('hidden', 'pluginname' . $this->get_type() . $id, $this->get_subtype());
        $mform->setType('pluginname' . $this->get_type() . $id, PARAM_RAW);
        $mform->addElement('hidden', 'type' . $this->get_type() . $id, $this->get_type() . $id);
        $mform->setType('type' . $this->get_type() . $id, PARAM_RAW);

        self::placeholders($mform, 'action' . $id, 'message');

        $mform->addElement(
            'text', $this->get_subtype() . '_' . $this->get_type() . $exception . $id . '_title',
            get_string(
                'editrule_action_element_title', 'notificationsaction_forummessage',
                ['typeelement' => '[TTTT]']
            ), ['size' => '64']
        );
        $mform->addRule(
            $this->get_subtype() . '_' . $this->get_type() . $exception . $id . '_title', null, 'required', null, 'client'
        );
        $mform->setType($this->get_subtype() . '_' . $this->get_type() . $exception . $id . '_title', PARAM_TEXT);

        if (!empty($SESSION->NOTIFICATIONS['FORMDEFAULT'][$valuesession.'_title'])) {
            $mform->setDefault($this->get_subtype() . '_' . $this->get_type() . $exception . $id . '_title',
            $SESSION->NOTIFICATIONS['FORMDEFAULT'][$valuesession . '_title']);
        }

        $editoroptions = [
            'maxfiles' => EDITOR_UNLIMITED_FILES,
            'trusttext' => true,
        ];

        $mform->addElement(
            'editor', $this->get_subtype() . '_' . $this->get_type() . $exception . $id . '_message',
            get_string(
                'editrule_action_element_message', 'notificationsaction_forummessage',
                ['typeelement' => '[BBBB]']
            ),
            ['class' => 'fitem_id_templatevars_editor'], $editoroptions
        )->setValue(!empty($SESSION->NOTIFICATIONS['FORMDEFAULT'][$valuesession . '_message'])
        ? ['text' => $SESSION->NOTIFICATIONS['FORMDEFAULT'][$valuesession . '_message']]
        : null);
        $mform->setType($this->get_subtype() . '_' . $this->get_type() . $exception . $id . '_message', PARAM_RAW);
        $mform->addRule($this->get_subtype() . '_' . $this->get_type() . $exception . $id . '_message',
        null, 'required', null, 'client');

        return $mform;
    }

    /**
     * @return lang_string|string
     */
    public function get_subtype() {
        return get_string('subtype', 'notificationsaction_messageagent');
    }

    /**
     * @return lang_string|string
     */
    public function get_name() {
        return get_string('pluginname', 'notificationsaction_messageagent');
    }

    public function get_title() {
        return get_string('messageagent_action', 'notificationsaction_messageagent');
    }

    public function get_elements() {
        return ['[TTTT]', '[BBBB]'];
    }

    public function check_capability($context) {
        if (has_capability('local/notificationsagent:messageagent', $context) &&
            has_capability('moodle/site:sendmessage', $context)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param array $params
     *
     * @return mixed
     */
    public function convert_parameters($params) {
        $title = "";
        $message = "";

        foreach ($params as $key => $value) {
            if (strpos($key, "title") !== false) {
                $title = $value;
            } else if (strpos($key, "message") !== false) {
                $message = $value["text"];
            }
        }

        return json_encode(['title' => $title, 'message' => $message]);
    }

    public function process_markups(&$content, $params, $courseid, $complementary=null) {

        $jsonparams = json_decode($params);

        $paramstoteplace = [shorten_text(str_replace('{' . Rule::SEPARATOR . '}', ' ', $jsonparams->title)),
            shorten_text(format_string(str_replace('{' . Rule::SEPARATOR . '}', ' ', $jsonparams->message))),
        ];

        $humanvalue = str_replace($this->get_elements(), $paramstoteplace, $this->get_title());

        array_push($content, $humanvalue);
    }

    public function execute_action($context, $params) {
        $placeholdershuman = json_decode($params);
        $sendmessage = notificationactionplugin::get_message_by_timesfired($context, $placeholdershuman->message);

        $message = new \core\message\message();
        $message->name = 'individual_message'; // Your notification name from message.php.
        $message->userto = $context->get_userid();
        $message->userfrom = core_user::get_noreply_user(); // If the message is 'from' a specific user you can set them here.
        $message->component = 'notificationsaction_messageagent'; // Your plugin's name.
        $message->subject = format_text($placeholdershuman->title); // Será nuestro TTTT.
        $message->fullmessage = format_text($sendmessage); // Será nuestro BBBB.
        $message->fullmessageformat = FORMAT_MARKDOWN;
        $message->fullmessagehtml = '<p>' . format_text($sendmessage) . '</p>';
        $message->smallmessage = 'small message';
        $message->notification = 1; // Because this is a notification generated from Moodle, not a user-to-user message.
        $message->contexturl = (new \moodle_url('/course/'))->out(false); // A relevant URL for the notification.
        $message->contexturlname = 'Course list'; // Link title explaining where users get to for the contexturl.

        // The integer ID of the new message or false if there was a problem
        // ... (with submitted data or sending the message to the message processor).
        message_send($message);
    }

    public function is_generic() {
        return true;
    }
}
