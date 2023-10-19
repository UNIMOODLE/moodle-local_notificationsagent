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

defined('MOODLE_INTERNAL') || die();

global $CFG, $SESSION;
require_once($CFG->dirroot . '/local/notificationsagent/classes/notificationactivityconditionplugin.php');
use local_notificationsagent\notification_activityconditionplugin;
use local_notificationsagent\EvaluationContext;

class notificationsagent_condition_activitycompleted extends notification_activityconditionplugin {

    public function get_description() {
        return array(
            'title' => self::get_title(),
            'elements' => self::get_elements(),
            'name' => self::get_subtype()
        );
    }

    protected function get_mod_name() {
        return get_string('modname', 'notificationscondition_activitycompleted');
    }

    public function get_title() {
        return get_string('conditiontext', 'notificationscondition_activitycompleted');
    }

    public function get_elements() {
        return array('[AAAA]');
    }

    public function get_subtype() {
        return get_string('subtype', 'notificationscondition_activitycompleted');
    }

    /** Evaluates this condition using the context variables or the system's state and the complementary flag.
     *
     * @param EvaluationContext $context |null collection of variables to evaluate the condition.
     *                                    If null the system's state is used.
     *
     * @return bool true if the condition is true, false otherwise.
     */
    public function evaluate(EvaluationContext $context): bool {
        global $DB;

        $meetcondition = false;
        $cmiscompleted = false;
        $pluginname = $this->get_subtype();
        $conditionid = $this->get_id();
        $courseid = $context->get_courseid();
        $userid = $context->get_userid();
        $params = json_decode($context->get_params());

        $cacheexists = $DB->record_exists('notificationsagent_cache', [
            'pluginname' => $pluginname, 'conditionid' => $conditionid,
            'courseid' => $courseid, 'userid' => $userid
        ]);

        if (!$cacheexists) {
            list($course, $cm) = get_course_and_cm_from_cmid($params->activity, '', $courseid);

            $cinfo = new completion_info($course);
            $cminfo = $cinfo->get_data($cm, false, $userid);

            if ($cminfo->completionstate > 0) {
                $cmiscompleted = true;
            }
        }

        if ($cacheexists || $cmiscompleted) {
            $meetcondition = true;
        }

        return $meetcondition;
    }

    /** Estimate next time when this condition will be true. */
    public function estimate_next_time(EvaluationContext $context) {
        return null;
    }

    /** Returns the name of the plugin
     *
     * @return string
     */
    public function get_name() {
        return get_string('pluginname', 'notificationscondition_activitycompleted');
    }

    public function get_ui($mform, $id, $courseid, $exception) {
        global $SESSION;

        $mform->addElement('hidden', 'pluginname'.$this->get_type().$exception.$id, $this->get_subtype());
        $mform->setType('pluginname'.$this->get_type().$exception.$id, PARAM_RAW );
        $mform->addElement('hidden', 'type'.$this->get_type().$exception.$id, $this->get_type().$id);
        $mform->setType('type'.$this->get_type().$exception.$id, PARAM_RAW );

        $listactivities = array();
        $modinfo = get_fast_modinfo($courseid);
        foreach ($modinfo->get_cms() as $cm) {
            $listactivities[$cm->id] = format_string($cm->name);
        }
        if (empty($listactivities)) {
            $listactivities['0'] = 'AAAA';
        }
        asort($listactivities);
        $mform->addElement(
            'select', $this->get_subtype().'_' .$this->get_type() .$exception.$id.'_activity',
            get_string(
                'editrule_condition_activity', 'notificationscondition_activitycompleted',
                array('typeelement' => '[AAAA]')
            ),
            $listactivities
        );
        if (!empty($SESSION->NOTIFICATIONS['FORMDEFAULT']['id_'.$this->get_subtype().'_'
                   .$this->get_type().$exception.$id.'_activity'])) {
            $mform->setDefault($this->get_subtype().'_' .$this->get_type() .$exception.$id.'_activity',
            $SESSION->NOTIFICATIONS['FORMDEFAULT']['id_'.$this->get_subtype().'_'.$this->get_type().$exception.$id.'_activity']);
        }
    }

    public function check_capability($context) {
        return has_capability('local/notificationsagent:activitycompleted', $context);
    }

    /**
     * @param array $params
     *
     * @return mixed
     */
    public function convert_parameters($params) {
        $activity = 0;
        foreach ($params as $key => $value) {
            if (strpos($key, "activity") !== false) {
                $activity = $value;
            }
        }

        return json_encode(array('activity' => $activity));
    }

    public function process_markups($params, $courseid) {
        $activityincourse = false;
        $jsonparams = json_decode($params);

        $modinfo = get_fast_modinfo($courseid);
        $types = $modinfo->get_cms();

        foreach ($types as $type) {
            if ($type->id == $jsonparams->activity) {
                $activityname = $type->name;
                $activityincourse = true;
            }
        }

        // Check if activity is found in course, if is not, return [AAAA].
        if (!$activityincourse) {
            $activityname = '[AAAA]';
        }

        $paramstoreplace = [$activityname];
        $humanvalue = str_replace($this->get_elements(), $paramstoreplace, $this->get_title());

        return $humanvalue;
    }
}
