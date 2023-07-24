<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * sessiostart sessionstart.php description here.
 *
 * @package    sessiostart
 * @copyright  2023 fernando <fpano@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . "/local/notificationsagent/classes/notificationactivityconditionplugin.php");
use local_notificationsagent\notification_activityconditionplugin;
class notificationsagent_condition_sessionstart extends notification_activityconditionplugin {

    public function get_description() {
        return array(
            'title' => self::get_title(),
            'elements' => self::get_elements(),
            'name' => self::get_subtype()
        );
    }
    protected function get_mod_name() {
        return get_string('modname', 'notificationscondition_sessionstart');
    }

    public function get_title() {
        return get_string('conditiontext', 'notificationscondition_sessionstart');
    }

    public function get_elements() {
        return array('[TTTT]');
    }

    public function get_subtype() {
        return get_string('subtype', 'notificationscondition_sessionstart');
    }

    /** Evaluates this condition using the context variables or the system's state and the complementary flag.
     *
     * @param \EvaluationContext $context |null collection of variables to evaluate the condition.
     *                                    If null the system's state is used.
     *
     * @return bool true if the condition is true, false otherwise.
     */
    public function evaluate(EvaluationContext $context): bool {
        // TODO: Implement evaluate() method.
    }

    /** Returns the name of the plugin
     *
     * @return string
     */
    public function get_name() {
        return get_string('pluginname', 'notificationscondition_sessionstart');
    }

    public function get_ui($mform, $id,$courseid) {
        // TODO $id.
        $timegroup = array();
        $timegroup[] =& $mform->createElement('float', 'condition'.$id.'_element'.'4'.'_time_days', '',
            array('class' => 'mr-2', 'size' => '7', 'maxlength' => '3', '
            placeholder' => 'Horas',
                'oninput' => 'this.value = this.value.replace(/[^0-9]/g, "").replace(/(\..*)\./g, "$1")'));
        $timegroup[] =& $mform->createElement('float', 'condition'.$id.'_element'.'4'.'_time_hours', '',
            array('class' => 'mr-2', 'size' => '7', 'maxlength' => '2', 'placeholder' => 'Minutos',
                'oninput' => 'this.value = this.value.replace(/[^0-9]/g, "").replace(/(\..*)\./g, "$1")'));
        $mform->setDefault('condition'.$id.'_element'.'2'.'_time_hours', '4'.'_time_hours');
        $timegroup[] =& $mform->createElement('float', 'condition'.$id.'_element'.'2'.'_time_minutes', '',
            array('class' => 'mr-2', 'size' => '7', 'maxlength' => '2',
                'placeholder' => 'Segundos',
                'oninput' => 'this.value = this.value.replace(/[^0-9]/g, "").replace(/(\..*)\./g, "$1")'));

        $mform->addGroup($timegroup, 'condition'.$id.'_group'.'4'.'time',
            get_string('editrule_condition_element_time', 'notificationscondition_sessionstart',
                array('typeelement' => '[TTTT]')));

        $mform->setDefault('condition'.$id.'_element'.'2'.'_time_minutes', '_time_minutes');
    }
}
