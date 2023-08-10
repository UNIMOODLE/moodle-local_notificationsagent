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

/**
 * Plugin strings are defined here.
 *
 * @package     local_notificationsagent
 * @category    string
 * @copyright   2023 ISYC
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Notifications Agent';
$string['pluginname1'] = 'notificationsagent';
// Settings.
$string['disable_user_use'] = 'Disable user use';
$string['disable_user_use_desc'] = 'Disable user use of notifications agent';
$string['tracelog'] = 'Trace log';
$string['tracelog_desc'] = 'Trace log. Disable on a production site';

// Subplugins settings.
$string['notificationaction_action'] = 'Action subplugins';

$string['managenotificationsactionplugins'] = 'Manage action plugins';
$string['managenotificationsconditionplugins'] = 'Manage condition plugins';

$string['manageactionplugins'] = 'Manage action plugins';
$string['manageconditionplugins'] = 'Manage condition plugins';

$string['actionplugins'] = 'Action plugins';
$string['conditionplugins'] = 'Condition plugins';

$string['notificationsactionpluginname'] = 'Action plugin';
$string['notificationsconditionpluginname'] = 'Condition plugin';

$string['hideshow'] = 'Hide/Show';

// Task.
$string['task'] = 'Test Task';
$string['menu'] = 'My assistant';
$string['heading'] = 'Notifications Agent';

// Import Template
$string['import'] = 'Import';

// Export Template
$string['export'] = 'Export';

// Assign Template
$string['assign'] = 'Assign';
$string['type_template'] = 'template';
$string['type_rule'] = 'rule';

// EditRule.
    $string['editrule_newrule'] = 'New rule';
    $string['editrule_title'] = 'Title';
    // Condition.
    $string['editrule_newcondition'] = 'New condition:';
    $string['editrule_condition_title_tocloseactivity'] = 'There is less than [TTTT] left for the closing of the activity [AAAA]';
    $string['editrule_condition_title_usercompleteactivity'] = 'User has completed the activity [AAAA]';
    $string['editrule_condition_title_activeactivity'] = 'Activity [AAAA] is available';
    $string['editrule_condition_title_betweendates'] = 'We are between the date [FFFF-1] and [FFFF-2]';
    $string['editrule_condition_title_accessforumactivitiescomplete'] = 'Complete all activities to access this forum [FFFF]';
    $string['editrule_condition_title_forumnotanswer'] = 'A thread opened by the user in the forum [FFFF] without replying for more than [TTTT] time';

        $string['editrule_condition_element_time'] = 'Time {$a->typeelement}:';
        $string['editrule_condition_element_activity'] = 'Activity {$a->typeelement}:';
        $string['editrule_condition_element_date_from'] = get_string('from').' {$a->typeelement}:';
        $string['editrule_condition_element_date_to'] = get_string('to').' {$a->typeelement}:';
    // Actions.
    $string['editrule_newaction'] = 'New action:';
    $string['editrule_action_title_individualnotification'] = 'Send individual notification with title [TTTT] and message [BBBB]';
    $string['editrule_action_title_notificationtouser'] = 'Send notification to a specific user [UUUU] with title [TTTT] and message [BBBB]';
    $string['editrule_action_title_postgeneralforum'] = 'Post a general forum post [FFFF] with title [TTTT] and message [BBBB]';
    $string['editrule_action_title_postprivateforum'] = 'Post a private forum post [FFFF] with title [TTTT] and message [BBBB]';
    $string['editrule_action_title_addusertogroup'] = 'Add a user to group [GGGG]';
    $string['editrule_action_title_removeuserfromgroup'] = 'Remove a user from a group [GGGG]';
    $string['editrule_action_title_bootstrapnotification'] = 'Bootstrap Notification';

        $string['editrule_action_element_title'] = 'Title {$a->typeelement}:';
        $string['editrule_action_element_message'] = 'Message {$a->typeelement}';
        $string['editrule_action_element_user'] = 'User {$a->typeelement}';
        $string['editrule_action_element_forum'] = 'Forum {$a->typeelement}';
        $string['editrule_action_element_group'] = 'Group {$a->typeelement}';


$string['subplugintype_notificationsagentaction'] = 'Action subplugins';
