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
 * @package    local_notificationsagent
 * @category   string
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Notifications Agent';

$string['admin_breadcrumb'] = 'Notifications Agent Admin';
$string['course_breadcrumb'] = 'Notifications Agent';

// Settings.
$string['settings'] = 'Settings';
$string['disable_user_use'] = 'Disable user use';
$string['disable_user_use_desc'] = 'Disable user use of notifications agent';
$string['max_rules_cron'] = 'Maximum number of rules per cron cycle';
$string['max_rules_cron_desc'] = 'Maximum number of rules per cron cycle';
$string['tracelog'] = 'Trace log';
$string['tracelog_desc'] = 'Trace log. Disable on a production site';
$string['startdate'] = 'Activity dates config';
$string['startdate_desc'] = 'Use a line for each activity with the pattern: pluginname|tablename|startdate|startend';
$string['pause_after_restore'] = 'Pause rules after restore';
$string['pause_after_restore_desc'] = 'Pause rules after restoring a course';

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
$string['tasktriggers'] = 'Notifications triggers task';
$string['menu'] = 'My assistant';
$string['heading'] = 'Notifications Agent';

// Status Template.
$string['status_active'] = 'Active';
$string['status_paused'] = 'Paused';
$string['status_required'] = 'Required';
$string['status_broken'] = 'Broken';

// Import Template.
$string['import'] = 'Import';
$string['no_file_selected'] = 'No file selected';
$string['import_success'] = 'Rule imported successfuly';
$string['import_error'] = 'Cannot import rule, check your JSON file';
$string['no_json_file'] = 'File is not a JSON';

// Export Template.
$string['export'] = 'Export';
$string['ruledownload'] = 'Export rule as';

// Assign Template.
$string['assign'] = 'Select';
$string['type_template'] = 'template';
$string['type_rule'] = 'rule';
$string['type_sharedrule'] = 'shared rule';
$string['fulltemplate'] = 'Template';

// Share modal.
$string['sharetitle'] = 'Share rule {$a->title}';
$string['sharecontent'] = 'You are going to share the rule {$a->title} with the administrator, do you want to continue?';
$string['unsharetitle'] = 'Unshare rule {$a->title}';
$string['unsharecontent'] = 'You are going to stop sharing the rule {$a->title} with the administrator, do you want to continue?';
$string['shareaccept'] = 'Rule shared';
$string['unshareaccept'] = 'Rule unshared';
$string['sharereject'] = 'Rule rejected';

// Share all modal.
$string['sharealltitle'] = 'Approve the shared rule {$a->title}';
$string['shareallcontent'] = 'You are going to approve the shared rule {$a->title}, do you want to continue?';

// Unshare all modal.
$string['unsharealltitle'] = 'Reject the shared rule {$a->title}';
$string['unshareallcontent'] = 'You are going to reject the shared rule {$a->title}, do you want to continue?';

// Condition plugins.
$string['condition_days'] = 'Days';
$string['condition_hours'] = 'Hours';
$string['condition_minutes'] = 'Minutes';
$string['condition_seconds'] = 'Seconds';
$string['condition_grade'] = 'Grade';

// EditRule.
$string['editrule_clonerule'] = 'Add rule';
$string['editrule_newrule'] = 'New rule';
$string['editrule_reports'] = 'Reports';
$string['editrule_activaterule'] = 'Activate';
$string['editrule_pauserule'] = 'Pause';
$string['editrule_editrule'] = 'Edit';
$string['editrule_reportrule'] = 'Report';
$string['editrule_deleterule'] = 'Delete';
$string['editrule_newtemplate'] = 'New template';
$string['editrule_title'] = 'Title';
$string['editrule_type'] = 'Rule type';
$string['editrule_usetemplate'] = 'Create from this template';
$string['editrule_sharerule'] = 'Share';
$string['editrule_unsharerule'] = 'Unshare';
$string['editrule_shareallrule'] = 'Share';
$string['editrule_unshareallrule'] = 'Unshare';
$string['editrule_sharedallrule'] = 'Shared';
$string['editrule_timesfired'] = 'No. of executions';
$string['editrule_runtime'] = 'Interval';
$string['editrule_orderby'] = 'Order by';

// Condition.
$string['editrule_generalconditions'] = 'General conditions';
$string['editrule_newcondition'] = 'New condition:';
$string['editrule_condition_title_tocloseactivity'] = 'There is less than [TTTT] left for the closing of the activity [AAAA]';
$string['editrule_condition_title_usercompleteactivity'] = 'User has completed the activity [AAAA]';
$string['editrule_condition_title_activeactivity'] = 'Activity [AAAA] is available';
$string['editrule_condition_title_betweendates'] = 'We are between the date [FFFF-1] and [FFFF-2]';
$string['editrule_condition_title_accessforumactivitiescomplete'] = 'Complete all activities to access this forum [FFFF]';
$string['editrule_condition_title_forumnotanswer']
    = 'A thread opened by the user in the forum [FFFF] without replying for more than [TTTT] time';

$string['editrule_condition_element_time'] = 'Time {$a->typeelement}:';
$string['editrule_condition_element_activity'] = 'Activity {$a->typeelement}:';

$string['editrule_condition_error'] = 'You must add at least one condition';

// Actions.
$string['editrule_newaction'] = 'New action:';
$string['editrule_action_title_individualnotification'] = 'Send individual notification with title [TTTT] and message [BBBB]';
$string['editrule_action_title_notificationtouser']
    = 'Send notification to a specific user [UUUU] with title [TTTT] and message [BBBB]';
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

$string['editrule_action_error'] = 'You must add at least one action';

// Rule.
$string['rulecancelled'] = 'Rule cancelled';

$string['Rule cancelled_help'] = 'Rule cancelled';
$string['rulesaved'] = 'Rule saved';

// Rule errors.
$string['editrule_required_error'] = 'Required field';
$string['editrule_runtime_error'] = 'If ' . $string['editrule_timesfired'] . ' is > 0, must indicate an interval';
$string['editrule_execution_error'] = $string['editrule_timesfired'] . ' between {$a->minimum} and {$a->maximum}';

// Card content.
$string['cardif'] = 'If all of:';
$string['cardunless'] = 'Unless any of:';
$string['cardthen'] = 'Then:';
$string['cardsharedby'] = 'Shared by {$a->username} in course {$a->coursename}';

// Card Condition time.
$string['card_day'] = 'day';
$string['card_day_plural'] = 'days';
$string['card_hour'] = 'hour';
$string['card_hour_plural'] = 'hours';
$string['card_minute'] = 'minute';
$string['card_minute_plural'] = 'minutes';
$string['card_second'] = 'second';
$string['card_second_plural'] = 'seconds';

// Status modal.
$string['status_pausetitle'] = 'Pause rule {$a->title}';
$string['status_activatetitle'] = 'Activate rule {$a->title}';
$string['status_pausecontent'] = 'You are going to pause rule {$a->title}, do you want to continue?';
$string['status_activatecontent'] = 'You are going to activate rule {$a->title}, do you want to continue?';
$string['status_acceptactivated'] = 'Rule activated';
$string['status_acceptpaused'] = 'Rule paused';
$string['statuspause'] = 'Pause';
$string['statusactivate'] = 'Activate';

// Delete modal.
$string['deletetitle'] = 'Delete {$a->type} {$a->title}';
$string['deletecontent_nocontext'] = 'You are going to delete the {$a->type} {$a->title}, do you want to continue?';
$string['deletecontent_hascontext']
    = 'The {$a->type} {$a->title} you want to delete is associated with other contexts, do you want to continue?';
$string['deleteaccept'] = 'Rule deleted';

// Assign modal.
$string['assignassign'] = 'Assign: ';
$string['assigncancel'] = 'Cancel';
$string['assignsave'] = 'Save changes';
$string['assignforced'] = 'Assign rule as forced';
$string['assignselectcourses'] = 'Select courses';
$string['assignunselectcourses'] = 'Unselect courses';
$string['assignselectedinfo'] = '{$a->courses} courses and {$a->categories} categories selected';

// Capabilities.
$string['notificationsagent:createrule'] = 'Create a rule';
$string['notificationsagent:editrule'] = 'Edit a rule';
$string['notificationsagent:checkrulecontext'] = 'Check the context of a rule';
$string['notificationsagent:deleterule'] = 'Delete a rule';
$string['notificationsagent:updaterulestatus'] = 'Update a rule\'s status';
$string['notificationsagent:exportrule'] = 'Export a rule';
$string['notificationsagent:importrule'] = 'Import a rule';
$string['notificationsagent:assignrule'] = 'Assign a rule';
$string['notificationsagent:forcerule'] = 'Force a rule';
$string['notificationsagent:updateruleshare'] = 'Update the sharing state of a rule';
$string['notificationsagent:shareruleall'] = 'Approve the sharing of a rule';
$string['notificationsagent:unshareruleall'] = 'Reject the sharing of a rule';
$string['notificationsagent:managesiterule'] = 'Manage rules at site level';
$string['notificationsagent:managecourserule'] = 'Manage rules at course level';
$string['notificationsagent:manageownrule'] = 'Manage your own course rules';
$string['notificationsagent:viewassistantreport'] = 'View rule report';

$string['notificationsagent:activitycompleted'] = 'Capability needed in order to use activitycompleted condition';
$string['notificationsagent:activityopen'] = 'Capability needed in order to use activityopen condition';
$string['notificationsagent:calendarstart'] = 'Capability needed in order to use calendarstart condition';
$string['notificationsagent:coursestart'] = 'Capability needed in order to use coursestart condition';
$string['notificationsagent:sessionstart'] = 'Capability needed in order to use sessionstart condition';
$string['notificationsagent:activityavailable'] = 'Capability needed in order to use activity available condition';
$string['notificationsagent:activityend'] = 'Capability needed in order to use activity end condition';
$string['notificationsagent:activitylastsend'] = 'Capability needed in order to use activity lastsend condition';
$string['notificationsagent:activitymodified'] = 'Capability needed in order to use activity modified condition';
$string['notificationsagent:activitynewcontent'] = 'Capability needed in order to use activity new content condition';
$string['notificationsagent:activitysinceend'] = 'Capability needed in order to use activity since end condition';
$string['notificationsagent:activitystudentend'] = 'Capability needed in order to use activity student end condition';
$string['notificationsagent:calendareventto'] = 'Capability needed in order to use calendar event to condition';
$string['notificationsagent:courseend'] = 'Capability needed in order to use course end condition';
$string['notificationsagent:forumnoreply'] = 'Capability needed in order to use forum no reply condition';
$string['notificationsagent:numberoftimes'] = 'Capability needed in order to use number of times condition';
$string['notificationsagent:sessionend'] = 'Capability needed in order to use session end condition';
$string['notificationsagent:weekend'] = 'Capability needed in order to use weekend condition';
$string['notificationsagent:itemgraded'] = 'Capability needed in order to use grade item condition';
$string['notificationsagent:weekdays'] = 'Capability needed in order to use weekdays condition';
$string['notificationsagent:ondates'] = 'Capability needed in order to use ondates condition';
$string['notificationsagent:usergroupadd'] = 'Capability needed in order to use usergroupadd condition';

$string['notificationsagent:addusergroup'] = 'Capability needed in order to use addusergroup action';
$string['notificationsagent:bootstrapnotifications'] = 'Capability needed in order to use bootstrapnotifications action';
$string['notificationsagent:forummessage'] = 'Capability needed in order to use forummessage action';
$string['notificationsagent:messageagent'] = 'Capability needed in order to use messageagent action';
$string['notificationsagent:removeusergroup'] = 'Capability needed in order to use removeusergroup action';
$string['notificationsagent:usermessageagent'] = 'Capability needed in order to use usermessageagent action';
$string['notificationsagent:privateforummessage'] = 'Capability needed in order to use privateforummessage action';

$string['notificationsagent:viewcourserule'] = 'Cability to view course rules';
$string['notificationsagent:manageallrule'] = 'Cability to manage all rules';
$string['notificationsagent:managesessions'] = 'Cability to save rule order';

// Webservices.
$string['notificationsagent:nosuchinstance'] = 'No such instance was found.';
$string['isnotrule'] = 'The given rule id is not a rule.';

// Report.
$string['rulename'] = 'Rule name';
$string['report'] = 'Notifications agent report';
$string['id'] = 'id';
$string['ruleid'] = 'Rule id';
$string['fullrule'] = 'Rule';
$string['userid'] = 'User id';
$string['fulluser'] = 'User';
$string['fullcourse'] = 'Course';
$string['courseid'] = 'Course id';
$string['actionid'] = 'Action id';
$string['fullaction'] = 'Action';
$string['actiondetail'] = 'Action detail';
$string['timestamp'] = 'Date';

// Nav.
$string['conditions'] = 'Conditions';
$string['exceptions'] = 'Exceptions';
$string['actions'] = 'Actions';

// Cache.
$string['cachedef_launched'] = 'List of users which periodicity has started.';

// Privacy.
$string['privacy:metadata:userid'] = 'The user id linked to this table.';
$string['privacy:metadata:courseid'] = 'An id for a course';
$string['privacy:metadata:actionid'] = 'An id for an action';
$string['privacy:metadata:ruleid'] = 'An id for a rule';
$string['privacy:metadata:actiondetail'] = 'Detail of the action sent to the user.';
$string['privacy:metadata:notificationsagentreport'] = 'Records messages sent to users which might content some user data.';
$string['privacy:metadata:timestamp'] = 'Timestamp of the sent action.';
$string['privacy:metadata:localnotificationsagentreport'] = 'Notifications agent report.';

// Message provider.
$string['messageprovider:notificationsagent_message'] = 'Broken rules notifications';
$string['brokenrulebody'] = 'Rule:  {$a->rule} of the course :{$a->course} has been paused,';

// Engine.
$string['actionerror'] = 'Error while performing the action ';

$string['subplugintype_notificationscondition'] = 'Condition plugin';
$string['subplugintype_notificationsaction'] = 'Action plugin';
$string['subplugintype_notificationscondition_plural'] = 'Condition plugins';
$string['subplugintype_notificationsaction_plural'] = 'Action plugins';

// Help.
$string['evaluaterule'] = 'Evaluate rule';
$string['evaluaterule_help'] =
    'Rule is evaluated as follows:
     ([Condition 1] **AND** [Condition 2]  **AND** ... ) **AND NOT** ([Exception 1] **OR** [Exception 2] ...) -> [Action 1]->[Action 2]';
// Placeholders.
// 'User_FirstName', 'User_LastName', 'User_Email', 'User_Username', 'User_Address',
//'Course_FullName', 'Course_Url', 'Course_Category_Name', 'Teacher_FirstName', 'Teacher_LastName',
//'Teacher_Email','Teacher_Username', 'Teacher_Address', 'Current_time', self::SEPARATOR, 'Follow_Link',
$string['placeholder_User_FirstName'] = 'User first name';
$string['placeholder_User_LastName'] = 'User last name';
$string['placeholder_User_Email'] = 'User email';
$string['placeholder_User_Username'] = 'User username';
$string['placeholder_User_Address'] = 'User address';
$string['placeholder_Course_FullName'] = 'Course full name';
$string['placeholder_Course_Url'] = 'Course url';
$string['placeholder_Course_Category_Name'] = 'Course category name';
$string['placeholder_Teacher_FirstName'] = 'Teacher first name';
$string['placeholder_Teacher_LastName'] = 'Teacher last name';
$string['placeholder_Teacher_Email'] = 'Teacher email';
$string['placeholder_Teacher_Username'] = 'Teacher username';
$string['placeholder_Teacher_Address'] = 'Teacher address';
$string['placeholder_Current_time'] = 'Current time';
$string['placeholder_Follow_Link'] = 'Follow link';
$string['placeholder_Separator'] = 'Message separator';