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

use local_notificationsagent\constants;

defined('MOODLE_INTERNAL') || die();

$functions = [
    'local_notificationsagent_update_rule_status' => [
        'classname' => \local_notificationsagent\external\update_rule_status::class,
        'method' => 'update_status',
        'description' => 'Update the status of a rule',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => 'local/notificationsagent:updaterulestatus',
    ],
    'local_notificationsagent_check_rule_context' => [
        'classname' => \local_notificationsagent\external\check_rule_context::class,
        'method' => 'check_rule_context',
        'description' => 'Check if the rule has a context other than the main context',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => 'local/notificationsagent:checkrulecontext',
    ],
    'local_notificationsagent_delete_rule' => [
        'classname' => \local_notificationsagent\external\delete_rule::class,
        'method' => 'delete_rule',
        'description' => 'Delete a rule',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => 'local/notificationsagent:deleterule',
    ],
    'local_notificationsagent_update_rule_share' => [
        'classname' => \local_notificationsagent\external\update_rule_share::class,
        'method' => 'update_rule_share',
        'description' => 'Update the sharing state of a rule',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => 'local/notificationsagent:updateruleshare',
    ],
    'local_notificationsagent_share_rule_all' => [
        'classname' => \local_notificationsagent\external\share_rule_all::class,
        'method' => 'share_rule_all',
        'description' => 'Approve the sharing of a rule',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => 'local/notificationsagent:shareruleall',
    ],
];

$services = [
    // The name of the service.
    // This does not need to include the component name.
    constants::SERVICE_WS => [

        // A list of external functions available in this service.
        'functions' => [
            'mod_forum_add_discussion',
        ],

        // If set, the external service user will need this capability to access
        // any function of this service.
        // For example: 'local_groupmanager/integration:access'.
        'requiredcapability' => 0,

        // If enabled, the Moodle administrator must link a user to this service from the Web UI.
        'restrictedusers' => 0,

        // Whether the service is enabled by default or not.
        'enabled' => 1,

        // This field os optional, but requried if the `restrictedusers` value is
        // set, so as to allow configuration via the Web UI.
        'shortname' => constants::SERVICE_WS,

        // Whether to allow file downloads.
        'downloadfiles' => 0,

        // Whether to allow file uploads.
        'uploadfiles'  => 0,
    ],
];
