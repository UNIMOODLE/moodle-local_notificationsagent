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

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->dirroot . "/local/notificationsagent/classes/notificationactionplugin.php");
use local_notificationsagent\Rule;
use notificationsagent\notificationsagent;

class notificationsagent_action_bootstrapnotifications extends notificationactionplugin {

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

        self::placeholders($mform, 'action' . $id, 'text');

        $mform->addElement(
            'text', $this->get_subtype() . '_' . $this->get_type() . $exception . $id .'_text',
            get_string(
                'editrule_action_element_text', 'notificationsaction_bootstrapnotifications',
                ['typeelement' => '[TTTT]']
            ), ['size' => '64']
        );
        $mform->addRule(
            $this->get_subtype() . '_' . $this->get_type() . $exception . $id . '_text', null, 'required', null, 'client'
        );

            $mform->setType($this->get_subtype() . '_' . $this->get_type() . $exception . $id . '_text', PARAM_TEXT);
        if (!empty($SESSION->NOTIFICATIONS['FORMDEFAULT'][$valuesession . '_text'])) {
            $mform->setDefault($this->get_subtype() . '_' . $this->get_type() . $exception . $id . '_text',
            $SESSION->NOTIFICATIONS['FORMDEFAULT'][$valuesession . '_text']);
        }

        return $mform;
    }

    /**
     * @return lang_string|string
     */
    public function get_subtype() {
        return get_string('subtype', 'notificationsaction_bootstrapnotifications');
    }

    /**
     * @return lang_string|string
     */
    public function get_name() {
        return get_string('pluginname', 'notificationsaction_bootstrapnotifications');
    }

    public function get_title() {
        return get_string('bootstrapnotifications_action', 'notificationsaction_bootstrapnotifications');
    }

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
    public function convert_parameters($params) {
        $text = "";

        foreach ($params as $key => $value) {
            if (strpos($key, "text") !== false) {
                $text = $value;
            }
        }

        return json_encode(['text' => $text]);
    }

    public function process_markups(&$content, $params, $courseid, $complementary=null) {

        $jsonparams = json_decode($params);

        $paramstoteplace = [shorten_text(str_replace('{' . Rule::SEPARATOR . '}', ' ', $jsonparams->text))];

        $humanvalue = str_replace($this->get_elements(), $paramstoteplace, $this->get_title());

        array_push($content, $humanvalue);
    }

    public function execute_action($context, $params) {
        global $USER;

        $placeholdershuman = json_decode($params);

        if ($USER->id == $context->get_userid()) {
            echo \core\notification::success(format_text($placeholdershuman->text));
        }
    }

    public function is_generic() {
        return true;
    }
}
