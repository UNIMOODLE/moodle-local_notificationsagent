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

namespace notificationsaction_usermessageagent;

use local_notificationsagent\rule;
use local_notificationsagent\notificationsagent;
use local_notificationsagent\notificationactionplugin;

class usermessageagent extends notificationactionplugin {

    /** @var UI ELEMENTS */
    public const NAME = 'usermessageagent';
    public const UI_MESSAGE = 'message';
    public const UI_TITLE = 'title';

    public function get_ui($mform, $id, $courseid, $type) {
        $this->get_ui_title($mform, $id, $type);
        global $USER;

        // Title.
        $title = $mform->createElement(
            'text', $this->get_name_ui($id, self::UI_TITLE),
            get_string(
                'editrule_action_element_title', 'notificationsaction_usermessageagent',
                ['typeelement' => '[TTTT]']
            ), ['size' => '64']
        );

        $editoroptions = [
            'maxfiles' => EDITOR_UNLIMITED_FILES,
            'trusttext' => true,
        ];

        // Message.
        $message = $mform->createElement(
            'editor', $this->get_name_ui($id, self::UI_MESSAGE),
            get_string(
                'editrule_action_element_message', 'notificationsaction_usermessageagent',
                ['typeelement' => '[BBBB]']
            ),
            ['class' => 'fitem_id_templatevars_editor'], $editoroptions
        );

        // Users.
        $context = \context_course::instance($courseid);

        $listusers = [];
        $enrolledusers = [];

        if (has_capability('local/notificationsagent:managecourserule', $context)) {
            $enrolledusers = get_enrolled_users($context);

            foreach ($enrolledusers as $uservalue) {
                $listusers[$uservalue->id] = format_string(
                    $uservalue->firstname . " " . $uservalue->lastname . " [" . $uservalue->email . "]", true
                );
            }
        } else if (has_capability('local/notificationsagent:manageownrule', $context)) {
            // User view - restricted to own user.
            $enrolledusers = notificationsagent::get_usersbycourse($context);

            foreach ($enrolledusers as $uservalue) {
                if ($uservalue->id == $USER->id) {
                    $listusers[$uservalue->id] = format_string(
                        $uservalue->firstname . " " . $uservalue->lastname . " [" . $uservalue->email . "]", true
                    );
                }
            }
        }

        if (empty($listusers)) {
            $listusers['0'] = 'UUUU';
        }
        asort($listusers);

        $user = $mform->createElement(
            'select', $this->get_name_ui($id, self::UI_USER),
            get_string(
                'editrule_action_element_user', 'notificationsaction_addusergroup',
                ['typeelement' => '[UUUU]']
            ),
            $listusers
        );
        $this->placeholders($mform, $id, $type);
        $mform->insertElementBefore($title, 'new' . $type . '_group');
        $mform->insertElementBefore($message, 'new' . $type . '_group');
        $mform->insertElementBefore($user, 'new' . $type . '_group');
        $mform->setType($this->get_name_ui($id, self::UI_TITLE), PARAM_TEXT);
        $mform->addRule($this->get_name_ui($id, self::UI_TITLE), null, 'required', null, 'client');
        $mform->setType($this->get_name_ui($id, self::UI_MESSAGE), PARAM_RAW);
        $mform->addRule($this->get_name_ui($id, self::UI_MESSAGE), null, 'required', null, 'client');
    }

    /**
     * @return lang_string|string
     */
    public function get_subtype() {
        return get_string('subtype', 'notificationsaction_usermessageagent');
    }

    public function get_title() {
        return get_string('usermessageagent_action', 'notificationsaction_usermessageagent');
    }

    public function get_elements() {
        return ['[TTTT]', '[BBBB]', '[UUUU]'];
    }

    public function check_capability($context) {
        return has_capability('local/notificationsagent:usermessageagent', $context)
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
        $user = $params[$this->get_name_ui($id, self::UI_USER)] ?? 0;

        $this->set_parameters(json_encode([self::UI_TITLE => $title, self::UI_MESSAGE => $message, self::UI_USER => $user]));
        return $this->get_parameters();
    }

    public function process_markups(&$content, $courseid, $options = null) {
        global $DB;

        $jsonparams = json_decode($this->get_parameters());

        $name = "[UUUU]";
        if ($user = $DB->get_record('user', ['id' => $jsonparams->{self::UI_USER}], 'firstname, lastname')) {
            $name = $user->firstname . " " . $user->lastname;
        }

        $message = $jsonparams->{self::UI_MESSAGE}->text ?? '';
        $paramstoteplace = [
            shorten_text(str_replace('{' . rule::SEPARATOR . '}', ' ', $jsonparams->{self::UI_TITLE})),
            shorten_text(format_string(str_replace('{' . rule::SEPARATOR . '}', ' ', $message))),
            shorten_text($name),
        ];

        $humanvalue = str_replace($this->get_elements(), $paramstoteplace, $this->get_title());

        array_push($content, $humanvalue);
    }

    public function execute_action($context, $params) {
        // Send notification to a particular user enrolled on the received course in the event.
        $placeholdershuman = json_decode($params);
        $sendmessage = notificationactionplugin::get_message_by_timesfired($context, $placeholdershuman->{self::UI_MESSAGE});

        $message = new \core\message\message();
        $message->component = 'notificationsaction_usermessageagent'; // Your plugin's name.
        $message->name = 'particular_message'; // Your notification name from message.php.
        $message->userfrom = \core_user::get_noreply_user(); // If the message is 'from' a specific user you can set them here.
        $message->userto = $placeholdershuman->{self::UI_USER};
        $message->subject = format_text($placeholdershuman->{self::UI_TITLE}); // Será nuestro TTTT.
        $message->fullmessage = format_text($sendmessage); // Será nuestro BBBB.
        $message->fullmessageformat = FORMAT_MARKDOWN;
        $message->fullmessagehtml = format_text('<p>' . $sendmessage . '</p>');
        $message->smallmessage = 'small message';
        $message->notification = 1; // Because this is a notification generated from Moodle, not a user-to-user message.
        $message->contexturl = (new \moodle_url('/course/view.php?id=' . $context->get_courseid()))->out(
            false
        ); // A relevant URL for the notification.
        $message->contexturlname = 'Course'; // Link title explaining where users get to for the contexturl.
        // The integer ID of the new message or false if there was a problem (with submitted data or sending the message to
        // the message processor).
        return message_send($message);
    }

    public function is_generic() {
        return false;
    }

    /**
     * Returns the parameters to be replaced in the placeholders
     *
     * @return string $json Parameters
     */
    public function get_parameters_placeholders() {
        $parameters = json_decode($this->get_parameters());

        return json_encode([
            self::UI_TITLE => $parameters->{self::UI_TITLE},
            self::UI_MESSAGE => $parameters->{self::UI_MESSAGE}->text,
            self::UI_USER => $parameters->{self::UI_USER},
        ]);
    }

    /**
     * Check if the action will be sent once or not
     *
     * @param integer $userid User id
     *
     * @return bool $sendonce Will the action be sent once?
     */
    public function is_send_once($userid) {
        return true;
    }
}
