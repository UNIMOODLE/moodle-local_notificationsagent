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
 * local_notificationsagent webservice definitions.
 *
 * @package    local_notificationsagent
 * @copyright  2023 ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = array(
    'local_notificationsagent_update_rule_status' => array(
        'classname' => \local_notificationsagent\external\update_rule_status::class,
        'method' => 'update_status',
        'description' => 'Update the status of a rule',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => 'local/notificationsagent:updaterulestatus',
    ),
    'local_notificationsagent_unlink_rule' => array(
        'classname' => \local_notificationsagent\external\unlink_rule::class,
        'method' => 'unlink_rule',
        'description' => 'Unlink the rule from the course',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => 'local/notificationsagent:unlinkrule',
    ),
    'local_notificationsagent_delete_rule' => array(
        'classname' => \local_notificationsagent\external\delete_rule::class,
        'method' => 'delete_rule',
        'description' => 'Delete a rule',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => 'local/notificationsagent:deleterule',
    )
);
