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
 * @package    notificationsaction_bootstrapnotifications
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace notificationsaction_bootstrapnotifications;

use local_notificationsagent\rule;
use local_notificationsagent\notificationactionplugin;

class bootstrapnotifications extends notificationactionplugin {

    /** @var UI ELEMENTS */
    public const NAME = 'bootstrapnotifications';
    public const UI_MESSAGE = 'message';

    public function get_ui($mform, $id, $courseid, $type) {
        $this->get_ui_title($mform, $id, $type);

        $element = $mform->createElement(
            'text', $this->get_name_ui($id, self::UI_MESSAGE),
            get_string(
                'editrule_action_element_text', 'notificationsaction_bootstrapnotifications',
                ['typeelement' => '[TTTT]']
            ), ['size' => '64']
        );

        $this->placeholders($mform, $id, $type);
        $mform->insertElementBefore($element, 'new' . $type . '_group');
        $mform->setType($this->get_name_ui($id, self::UI_MESSAGE), PARAM_TEXT);
        $mform->addRule($this->get_name_ui($id, self::UI_MESSAGE), '- You must supply a value here.', 'required');
    }

    /**
     * @return lang_string|string
     */
    public function get_subtype() {
        return get_string('subtype', 'notificationsaction_bootstrapnotifications');
    }

    /**
     * Subplugin title
     *
     * @return \lang_string|string
     */
    public function get_title() {
        return get_string('bootstrapnotifications_action', 'notificationsaction_bootstrapnotifications');
    }

    /**
     *  Subplugins elements
     *
     * @return string[]
     */
    public function get_elements() {
        return ['[TTTT]'];
    }

    public function check_capability($context) {
        return has_capability('local/notificationsagent:bootstrapnotifications', $context);
    }

    /**
     * @param array $params
     *
     * @return mixed
     */
    protected function convert_parameters($id, $params) {
        $params = (array) $params;
        $message = $params[$this->get_name_ui($id, self::UI_MESSAGE)] ?? 0;
        $this->set_parameters(json_encode([self::UI_MESSAGE => $message]));
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

        $paramstoteplace = [shorten_text(str_replace('{' . Rule::SEPARATOR . '}', ' ', $jsonparams->{self::UI_MESSAGE}))];

        $humanvalue = str_replace($this->get_elements(), $paramstoteplace, $this->get_title());

        $content[] = $humanvalue;
    }

    public function execute_action($context, $params) {
        global $USER;

        $placeholdershuman = json_decode($params);

        if ($USER->id == $context->get_userid()) {
            echo \core\notification::success(format_text($placeholdershuman->{self::UI_MESSAGE}));
            return true;
        }
    }

    public function is_generic() {
        return true;
    }
}
