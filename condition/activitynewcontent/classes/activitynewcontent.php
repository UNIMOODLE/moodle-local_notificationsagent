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
 * @package    notificationscondition_activitynewcontent
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace notificationscondition_activitynewcontent;

use local_notificationsagent\evaluationcontext;
use local_notificationsagent\notificationconditionplugin;
use local_notificationsagent\rule;

/**
 * Activitynewcontent class
 */
class activitynewcontent extends notificationconditionplugin {
    /**
     * Subplugin name
     */
    public const NAME = 'activitynewcontent';
    /**
     * Subplugin modname
     */
    public const MODNAME = 'modname';
    /**
     * Subplugin no used types
     */
    public const UNUSED_TYPES = ['label'];

    /**
     * Subplugin title
     *
     * @return \lang_string|string
     */
    public function get_title() {
        return get_string('conditiontext', 'notificationscondition_activitynewcontent');
    }

    /**
     *  Subplugins elements
     *
     * @return string[]
     */
    public function get_elements() {
        return ['[AAAA]'];
    }

    /** Evaluates this condition using the context variables or the system's state and the complementary flag.
     *
     * @param evaluationcontext $context |null collection of variables to evaluate the condition.
     *                                    If null the system's state is used.
     *
     * @return bool true if the condition is true, false otherwise.
     */
    public function evaluate(evaluationcontext $context): bool {
        global $DB;
        $meetcondition = false;
        $courseid = $context->get_courseid();
        $userid = $context->get_userid();
        $pluginname = $this->get_subtype();
        $conditionid = $this->get_id();

        $timelastsend = $DB->get_field(
            'notificationsagent_cache',
            'startdate',
            ['conditionid' => $conditionid, 'courseid' => $courseid, 'userid' => $userid, 'pluginname' => $pluginname],
        );

        if (!empty($timelastsend)) {
            $meetcondition = true;
        }

        return $meetcondition;
    }

    /**
     * Estimate next time when this condition will be true.
     *
     * @param evaluationcontext $context
     *
     */
    public function estimate_next_time(evaluationcontext $context) {
        $estimate = null;
        // Condition.
        if (!$context->is_complementary() && $context->get_observer()) {
            $estimate = time();
        }

        // Exception.
        if ($context->is_complementary() && !$context->get_observer()) {
            $estimate = time();
        }

        return $estimate;
    }

    /**
     * Get the UI elements for the subplugin.
     *
     * @param \MoodleQuickForm $mform The form to which the elements will be added.
     * @param int $courseid The course identifier.
     * @param string $type The type of the notification plugin.
     */
    public function get_ui($mform, $courseid, $type) {
        $this->get_ui_title($mform, $type);

        // Activity.
        $listactivities = [];
        foreach (get_module_types_names() as $key => $value) {
            if (in_array($key, self::UNUSED_TYPES)) {
                continue;
            }
            $listactivities[$key] = format_string($value);
        }

        // Only is template.
        if ($this->rule->template == rule::TEMPLATE_TYPE) {
            $listactivities['0'] = 'AAAA';
        }

        asort($listactivities);

        $element = $mform->createElement(
            'select',
            $this->get_name_ui(self::MODNAME),
            get_string(
                'editrule_condition_activity',
                'notificationscondition_activitysinceend',
                ['typeelement' => '[AAAA]']
            ),
            $listactivities
        );

        $mform->insertElementBefore($element, 'new' . $type . '_group');
        $mform->addRule(
            $this->get_name_ui(self::MODNAME),
            get_string('editrule_required_error', 'local_notificationsagent'),
            'required'
        );
    }

    /**
     * Sublugin capability
     *
     * @param \context $context
     *
     * @return bool
     */
    public function check_capability($context) {
        return has_capability('notificationscondition/activitynewcontent:activitynewcontent', $context);
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
        $modname = $params[$this->get_name_ui(self::MODNAME)] ?? 0;
        $this->set_parameters(json_encode([self::MODNAME => $modname]));
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

        $paramstoteplace = [$jsonparams->{self::MODNAME}];

        $humanvalue = str_replace($this->get_elements(), $paramstoteplace, $this->get_title());

        $content[] = $humanvalue;
    }

    /**
     * Whether a subluplugin is generic
     *
     * @return bool
     */
    public function is_generic() {
        return true;
    }
}
