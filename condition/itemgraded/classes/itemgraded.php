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
 * @package    notificationscondition_itemgraded
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace notificationscondition_itemgraded;

use local_notificationsagent\notificationsagent;
use local_notificationsagent\rule;
use local_notificationsagent\notificationconditionplugin;
use local_notificationsagent\evaluationcontext;

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->libdir . '/gradelib.php');

/**
 * itemgraded subplugin class
 */
class itemgraded extends notificationconditionplugin {

    /**
     * @var string Subplugin name
     */
    public const NAME = 'itemgraded';

    /**
     * Subplugin title
     *
     * @return \lang_string|string
     */
    public function get_title() {
        return get_string('conditiontext', 'notificationscondition_itemgraded');
    }

    /**
     *  Subplugins elements
     *
     * @return string[]
     */
    public function get_elements() {
        return ['[OOOP]', '[GGGG]', '[AAAA]'];
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
        $gradeisachieved = false;
        $pluginname = $this->get_subtype();
        $conditionid = $this->get_id();
        $courseid = $context->get_courseid();
        $userid = $context->get_userid();
        $params = json_decode($context->get_params());

        $cm = get_coursemodule_from_id(false, $params->{self::UI_ACTIVITY}, 0, false, MUST_EXIST);
        $usergrade = grade_get_grades($courseid, 'mod', $cm->modname, $cm->instance, $userid);

        if (isset($usergrade->items[0]->grades[$userid]->grade)) {
            $gradeisachieved = notificationsagent::evaluate_expression(
                    $params->{self::UI_OP},
                    $usergrade->items[0]->grades[$userid]->grade,
                    $params->{self::UI_GRADE}
            );
        }

        if ($gradeisachieved) {
            $meetcondition = true;
        }

        return $meetcondition;
    }

    /**
     * Estimate next time when this condition will be true
     *
     * @param evaluationcontext $context
     *
     * @return int|null
     */
    public function estimate_next_time(evaluationcontext $context) {
        $estimate = null;

        $isachieved = $this->evaluate($context);

        if ($isachieved && !$context->is_complementary()) {
            return time();
        }

        if (!$isachieved && $context->is_complementary()) {
            return time();
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

        $gradegroup[] = $mform->createElement(
                'select',
                $this->get_name_ui(self::UI_OP),
                [],
                self::OPERATORS
        );
        $gradegroup[] = $mform->createElement(
                'float',
                $this->get_name_ui(self::UI_GRADE),
                '',
                [
                        'class' => 'mr-2', 'size' => '7',
                        'placeholder' => get_string('condition_grade', 'local_notificationsagent'),
                ]
        );
        $group = $mform->createElement(
                'group', $this->get_name_ui($this->get_subtype()),
                get_string('editrule_condition_grade', 'notificationscondition_itemgraded', ['typeelement' => '[GGGG]']),
                $gradegroup, null, false
        );
        $mform->insertElementBefore($group, 'new' . $type . '_group');

        $mform->addGroupRule(
                $this->get_name_ui($this->get_subtype()), get_string('editrule_required_error', 'local_notificationsagent'),
                'required'
        );

        $listactivities = [];
        $items = \grade_item::fetch_all(['courseid' => $courseid, 'itemtype' => 'mod']);
        $items = $items ? $items : [];
        foreach ($items as $i => $item) {
            $cm = get_coursemodule_from_instance(
                    $item->itemmodule,
                    $item->iteminstance,
                    $courseid
            );
            $listactivities[$cm->id] = format_string($item->get_name(true));
        }

        if ($this->rule->template == rule::TEMPLATE_TYPE) {
            $listactivities['0'] = 'AAAA';
        }

        \core_collator::asort($listactivities);

        $element = $mform->createElement(
                'select',
                $this->get_name_ui(self::UI_ACTIVITY),
                get_string(
                        'editrule_condition_activity', 'notificationscondition_itemgraded',
                        ['typeelement' => '[AAAA]']
                ),
                $listactivities
        );

        $mform->insertElementBefore($element, 'new' . $type . '_group');
        $mform->addRule(
                $this->get_name_ui(self::UI_ACTIVITY), get_string('editrule_required_error', 'local_notificationsagent'),
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
        return has_capability('local/notificationsagent:itemgraded', $context);
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
        $activity = $params[$this->get_name_ui(self::UI_ACTIVITY)] ?? 0;
        $op = self::OPERATORS[$params[$this->get_name_ui(self::UI_OP)]] ?? '';
        $grade = $params[$this->get_name_ui(self::UI_GRADE)] ?? 0;
        $this->set_parameters(json_encode([
                self::UI_ACTIVITY => (int) $activity,
                self::UI_OP => (string) $op,
                self::UI_GRADE => (float) $grade,
        ]));
        $this->set_cmid((int) $activity);
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

        $activityname = '[AAAA]';
        $cmid = $jsonparams->{self::UI_ACTIVITY};
        $fastmodinfo = get_fast_modinfo($courseid);
        $activityname = isset($fastmodinfo->cms[$cmid]) ? $fastmodinfo->cms[$cmid]->name : $activityname;

        $paramstoreplace = [$jsonparams->{self::UI_OP}, $jsonparams->{self::UI_GRADE}, $activityname];
        $humanvalue = str_replace($this->get_elements(), $paramstoreplace, $this->get_title());

        $content[] = $humanvalue;
    }

    /**
     * Whether a subluplugin is generic
     *
     * @return bool
     */
    public function is_generic() {
        return false;
    }
}
