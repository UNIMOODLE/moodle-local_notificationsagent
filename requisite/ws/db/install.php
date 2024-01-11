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

use local_notificationsagent\constants;

/**
 * Custom code to be run on installing the plugin.
 */
function xmldb_notificationsrequisite_ws_install() {
    $roleid = local_notificationsagent_create_role_ws();
    local_notificationsagent_create_user($roleid);
    local_notificationsagent_create_token();
    return true;
}

function local_notificationsagent_create_token() {
    global $DB;
    $user = $DB->get_record('user', ['username' => constants::USERNAME_WS]);
    $service = $DB->get_record('external_services', ['shortname' => constants::SERVICE_WS]);
    $context = context_system::instance();
    external_generate_token(EXTERNAL_TOKEN_PERMANENT, $service, $user->id, $context->id);
}

function local_notificationsagent_create_user($roleid) {
    global $CFG;

    $newuser = new stdClass();
    $newuser->username = constants::USERNAME_WS;
    $newuser->firstname = constants::FIRSTNAME_WS;
    $newuser->lastname = constants::LASTNAME_WS;
    $newuser->email = constants::EMAIL_WS;
    $newuser->auth = 'manual';
    $newuser->policyagreed = 1;
    $newuser->lang = get_newuser_language();
    $newuser->confirmed = 1;
    $newuser->lastip = getremoteaddr();
    $newuser->timecreated = time();
    $newuser->timemodified = $newuser->timecreated;
    $newuser->mnethostid = $CFG->mnet_localhost_id;
    $newuser->id = user_create_user($newuser, false, false);

    $context = context_system::instance();
    role_assign($roleid, $newuser->id, $context->id);
}

function local_notificationsagent_create_role_ws() {
    global $DB;
    $id = create_role(constants::ROLE_WS, constants::ROLE_WS, 'Rol to call '.constants::SERVICE_WS, 'editingteacher');
    set_role_contextlevels($id, [CONTEXT_SYSTEM]);
    $manager = $DB->get_record('role', ['shortname' => 'manager']);
    if ($manager) {
        core_role_set_assign_allowed($id, $manager->id);
        core_role_set_override_allowed($id, $manager->id);
        core_role_set_view_allowed($id, $manager->id);
    }
    $coursecreator = $DB->get_record('role', ['shortname' => 'coursecreator']);
    if ($coursecreator) {
        core_role_set_assign_allowed($id, $coursecreator->id);
        core_role_set_override_allowed($id, $coursecreator->id);
        core_role_set_view_allowed($id, $coursecreator->id);
    }
    $editingteacher = $DB->get_record('role', ['shortname' => 'editingteacher']);
    if ($editingteacher) {
        core_role_set_assign_allowed($id, $editingteacher->id);
        core_role_set_override_allowed($id, $editingteacher->id);
        core_role_set_switch_allowed($id, $editingteacher->id);
        core_role_set_view_allowed($id, $editingteacher->id);
    }
    $teacher = $DB->get_record('role', ['shortname' => 'teacher']);
    if ($teacher) {
        core_role_set_assign_allowed($id, $teacher->id);
        core_role_set_override_allowed($id, $teacher->id);
        core_role_set_switch_allowed($id, $teacher->id);
        core_role_set_view_allowed($id, $teacher->id);
    }
    $student = $DB->get_record('role', ['shortname' => 'student']);
    if ($student) {
        core_role_set_assign_allowed($id, $student->id);
        core_role_set_override_allowed($id, $student->id);
        core_role_set_switch_allowed($id, $student->id);
        core_role_set_view_allowed($id, $student->id);
    }
    $guest = $DB->get_record('role', ['shortname' => 'guest']);
    if ($guest) {
        core_role_set_override_allowed($id, $guest->id);
        core_role_set_switch_allowed($id, $guest->id);
        core_role_set_view_allowed($id, $guest->id);
    }
    $user = $DB->get_record('role', ['shortname' => 'user']);
    if ($user) {
        core_role_set_override_allowed($id, $user->id);
        core_role_set_view_allowed($id, $user->id);
    }
    $frontpage = $DB->get_record('role', ['shortname' => 'frontpage']);
    if ($frontpage) {
        core_role_set_override_allowed($id, $frontpage->id);
        core_role_set_view_allowed($id, $frontpage->id);
    }
    reset_role_capabilities($id);
    $context = context_system::instance();
    assign_capability('moodle/site:viewuseridentity', CAP_ALLOW, $id, $context->id, true);
    assign_capability('moodle/user:viewalldetails', CAP_ALLOW, $id, $context->id, true);
    assign_capability('moodle/webservice:createtoken', CAP_ALLOW, $id, $context->id, true);
    assign_capability('webservice/rest:use', CAP_ALLOW, $id, $context->id, true);

    return $id;
}
