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
 * @category   string
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class restore_local_notificationsagent_plugin extends restore_local_plugin {

    protected function define_course_plugin_structure() {
        return [
            new restore_path_element('local_notificationsagent_rule',
                $this->get_pathfor('/rules/rule')
            ),
            new restore_path_element('local_notificationsagent_rule_context',
                $this->get_pathfor('/rules/rule/contexts/context')
            ),
            new restore_path_element('local_notificationsagent_rule_condition',
                $this->get_pathfor('/rules/rule/conditions/condition')
            ),
            new restore_path_element('local_notificationsagent_rule_action',
                $this->get_pathfor('/rules/rule/actions/action')
            ),
            new restore_path_element('local_notificationsagent_rule_launched',
                $this->get_pathfor('/rules/rule/launcheds/launched')
            ),
        ];
    }

    public function process_local_notificationsagent_rule($data) {
        global $DB;

        $record = new \stdClass;
        $record->name = $data['name'];
        $record->description = $data['description'];
        $record->status = $data['status'];
        $record->createdby = $data['createdby'];
        $record->createdat = $data['createdat'];
        $record->shared = $data['shared'];
        $record->defaultrule = $data['defaultrule'];
        $record->template = $data['template'];
        $record->forced = $data['forced'];
        $record->timesfired = $data['timesfired'];
        $record->runtime = $data['runtime'];

        $newruleid = $DB->insert_record('notificationsagent_rule', $record);

        $this->set_mapping('notificationsagent_rule', $data['id'], $newruleid, false);
    }

    public function process_local_notificationsagent_rule_context($data) {
        global $DB;

        $record = new \stdClass;
        $record->ruleid = $this->get_mappingid('notificationsagent_rule', $data['ruleid']);
        $record->contextid = $data['contextid'];
        if ($this->task->get_courseid() == SITEID) {
            $record->objectid = $data['objectid'];
        } else {
            $record->objectid = $this->get_mappingid('course', $data['objectid']);
        }

        $DB->insert_record('notificationsagent_context', $record);
    }

    public function process_local_notificationsagent_rule_condition($data) {
        global $DB;

        $record = new \stdClass;
        $record->ruleid = $this->get_mappingid('notificationsagent_rule', $data['ruleid']);
        $record->pluginname = $data['pluginname'];
        $record->type = $data['type'];
        $record->parameters = $data['parameters'];
        $record->cmid = $data['cmid'];
        $record->complementary = $data['complementary'];

        $newconditionid = $DB->insert_record('notificationsagent_condition', $record);

        $this->set_mapping('notificationsagent_condition', $data['id'], $newconditionid, false);
    }

    public function process_local_notificationsagent_rule_action($data) {
        global $DB;

        $record = new \stdClass;
        $record->ruleid = $this->get_mappingid('notificationsagent_rule', $data['ruleid']);
        $record->pluginname = $data['pluginname'];
        $record->type = $data['type'];
        $record->parameters = $data['parameters'];

        $DB->insert_record('notificationsagent_action', $record);
    }

    public function process_local_notificationsagent_rule_launched($data) {
        global $DB;

        // Only import the history of rules launched if the source and target course is the same.
        if ($this->task->get_old_courseid() == $this->task->get_courseid()) {
            $record = new \stdClass;
            $record->ruleid = $this->get_mappingid('notificationsagent_rule', $data['ruleid']);
            $record->courseid = $this->get_mappingid('course', $data['courseid']);
            $record->userid = $data['userid'];
            $record->timesfired = $data['timesfired'];
            $record->timecreated = $data['timecreated'];
            $record->timemodified = $data['timemodified'];

            $DB->insert_record('notificationsagent_launched', $record);
        }
    }
}
