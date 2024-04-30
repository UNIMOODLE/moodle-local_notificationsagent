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
 * @package    notificationscondition_usergroupadd
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_notificationsagent\evaluationcontext;
use local_notificationsagent\notificationsagent;
use local_notificationsagent\rule;
use notificationscondition_usergroupadd\usergroupadd;

/**
 * Event handler for usergroupadd subplugin.
 */
class notificationscondition_usergroupadd_observer {

    /**
     * Triggered when 'group_member_added' event is triggered.
     *
     * @param \core\event\group_member_added $event
     */
    public static function group_member_added(\core\event\group_member_added $event) {
        $pluginname = usergroupadd::NAME;
        $courseid = $event->courseid;
        $userid = $event->relateduserid;
        $groupid = $event->objectid;

        $conditions = notificationsagent::get_conditions_by_cm($pluginname, $courseid, $groupid);
        foreach ($conditions as $condition) {
            $subplugin = new usergroupadd($condition->ruleid, $condition->id);
            $context = new evaluationcontext();
            $context->set_params($subplugin->get_parameters());
            $context->set_complementary($subplugin->get_iscomplementary());
            $context->set_timeaccess($event->timecreated);
            $context->set_courseid($courseid);
            $context->set_userid($userid);

            notificationsagent::generate_cache_triggers($subplugin, $context);
        }
    }

    /**
     * Triggered when 'group_deleted' event is triggered.
     *
     * @param \core\event\group_deleted $event
     */
    public static function group_deleted(\core\event\group_deleted $event) {
        global $DB;

        $groupid = $event->objectid;

        $sql = 'SELECT mnc.id, mnc.ruleid AS ruleid
                  FROM {notificationsagent_condition} mnc
                 WHERE mnc.pluginname = :name
                   AND mnc.cmid = :cmid';

        $dataobj = $DB->get_records_sql($sql, [
            'name' => usergroupadd::NAME,
            'cmid' => $groupid,
        ]);

        foreach ($dataobj as $data) {
            $subplugin = new usergroupadd($data->ruleid, $data->id);
            $result = $subplugin->validation($event->courseid);
            if (!$result) {
                update_rule_status::execute(
                    $data->ruleid, rule::PAUSE_RULE,
                );
            }
        }
    }
}
