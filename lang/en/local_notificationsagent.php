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

$string['Rule cancelled_help'] = 'Rule cancelled';
$string['actiondetail'] = 'Action detail';
$string['actionerror'] = 'Error while performing the action ';
$string['actionid'] = 'Action id';
$string['actionplugins'] = 'Action plugins';
$string['actions'] = 'Actions';
$string['admin_breadcrumb'] = 'Notifications Agent Admin';
$string['assign'] = 'Select';
$string['assignassign'] = 'Assign: ';
$string['assigncancel'] = 'Cancel';
$string['assignforced'] = 'Assign rule as forced';
$string['assignsave'] = 'Save changes';
$string['assignselectcourses'] = 'Select courses';
$string['assignselectedinfo'] = '{$a->courses} courses and {$a->categories} categories selected';
$string['assignunselectcourses'] = 'Unselect courses';
$string['brokenrulebody'] = 'Rule:  {$a->rule} of the course :{$a->course} has been paused,';
$string['cachedef_action'] = 'List of actions.';
$string['cachedef_condition'] = 'List of conditions.';
$string['cachedef_course'] = 'Course data.';
$string['cachedef_launched'] = 'List of users which periodicity has started.';
$string['card_day'] = 'day';
$string['card_day_plural'] = 'days';
$string['card_hour'] = 'hour';
$string['card_hour_plural'] = 'hours';
$string['card_minute'] = 'minute';
$string['card_minute_plural'] = 'minutes';
$string['card_second'] = 'second';
$string['card_second_plural'] = 'seconds';
$string['cardif'] = 'If all of:';
$string['cardsharedby'] = 'Shared by {$a->username} in course {$a->coursename}';
$string['cardthen'] = 'Then:';
$string['cardunless'] = 'Unless any of:';
$string['condition_days'] = 'Days';
$string['condition_grade'] = 'Grade';
$string['condition_hours'] = 'Hours';
$string['condition_minutes'] = 'Minutes';
$string['condition_seconds'] = 'Seconds';
$string['conditionplugins'] = 'Condition plugins';
$string['conditions'] = 'Conditions';
$string['course_breadcrumb'] = 'Notifications Agent';
$string['courseid'] = 'Course id';
$string['deleteaccept'] = 'Rule deleted';
$string['deletecontent_hascontext']
    = 'The {$a->type} {$a->title} you want to delete is associated with other contexts, do you want to continue?';
$string['deletecontent_nocontext'] = 'You are going to delete the {$a->type} {$a->title}, do you want to continue?';
$string['deletetitle'] = 'Delete {$a->type} {$a->title}';
$string['disable_user_use'] = 'Disable user use';
$string['disable_user_use_desc'] = 'Disable user use of notifications agent';
$string['editrule_action_error'] = 'You must add at least one action';
$string['editrule_activaterule'] = 'Activate';
$string['editrule_clonerule'] = 'Add rule';
$string['editrule_condition_error'] = 'You must add at least one condition';
$string['editrule_deleterule'] = 'Delete';
$string['editrule_editrule'] = 'Edit';
$string['editrule_execution_error'] = '{$a->timesfired} between {$a->minimum} and {$a->maximum}';
$string['editrule_generalconditions'] = 'General conditions';
$string['editrule_newaction'] = 'New action:';
$string['editrule_newcondition'] = 'New condition:';
$string['editrule_newrule'] = 'New rule';
$string['editrule_newtemplate'] = 'New template';
$string['editrule_orderby'] = 'Order by';
$string['editrule_pauserule'] = 'Pause';
$string['editrule_reportrule'] = 'Report';
$string['editrule_reports'] = 'Reports';
$string['editrule_required_error'] = 'Required field';
$string['editrule_runtime'] = 'Interval';
$string['editrule_runtime_error'] = 'If {$a->timesfired} is > 0, must indicate an interval';
$string['editrule_shareallrule'] = 'Share';
$string['editrule_sharedallrule'] = 'Shared';
$string['editrule_sharerule'] = 'Share';
$string['editrule_timesfired'] = 'No. of executions';
$string['editrule_title'] = 'Title';
$string['editrule_type'] = 'Rule type';
$string['editrule_unshareallrule'] = 'Unshare';
$string['editrule_unsharerule'] = 'Unshare';
$string['editrule_usetemplate'] = 'Create from this template';
$string['evaluaterule'] = 'Evaluate rule';
$string['evaluaterule_help'] =
    'Rule is evaluated as follows:
     ([Condition 1] **AND** [Condition 2]  **AND** ... ) **AND NOT** ([Exception 1] **OR** [Exception 2] ...) -> [Action 1]->[Action 2]';
$string['exceptions'] = 'Exceptions';
$string['export'] = 'Export';
$string['fullaction'] = 'Action';
$string['fullcourse'] = 'Course';
$string['fullrule'] = 'Rule';
$string['fulltemplate'] = 'Template';
$string['fulluser'] = 'User';
$string['heading'] = 'Notifications Agent';
$string['hideshow'] = 'Hide/Show';
$string['id'] = 'id';
$string['import'] = 'Import';
$string['import_apply'] = 'Import and apply';
$string['import_choose'] = 'JSON file';
$string['import_desc'] = 'Select a JSON file to import the rules';
$string['import_error'] = 'Cannot import rule, check your JSON file';
$string['import_success'] = 'Rule imported successfuly';
$string['import_title'] = 'Import rules';
$string['isnotrule'] = 'The given rule id is not a rule.';
$string['manageactionplugins'] = 'Manage action plugins';
$string['manageconditionplugins'] = 'Manage condition plugins';
$string['managenotificationsactionplugins'] = 'Manage action plugins';
$string['managenotificationsconditionplugins'] = 'Manage condition plugins';
$string['max_rules_cron'] = 'Maximum number of rules per cron cycle';
$string['max_rules_cron_desc'] = 'Maximum number of rules per cron cycle';
$string['menu'] = 'My assistant';
$string['messageprovider:notificationsagent_message'] = 'Broken rules notifications';
$string['no_file_selected'] = 'No file selected';
$string['no_json_file'] = 'File is not a JSON';
$string['notificationaction_action'] = 'Action subplugins';
$string['notificationsactionpluginname'] = 'Action plugin';
$string['notificationsagent:activityavailable'] = 'Capability needed in order to use activity available condition';
$string['notificationsagent:activitycompleted'] = 'Capability needed in order to use activitycompleted condition';
$string['notificationsagent:activityend'] = 'Capability needed in order to use activity end condition';
$string['notificationsagent:activitylastsend'] = 'Capability needed in order to use activity lastsend condition';
$string['notificationsagent:activitymodified'] = 'Capability needed in order to use activity modified condition';
$string['notificationsagent:activitynewcontent'] = 'Capability needed in order to use activity new content condition';
$string['notificationsagent:activityopen'] = 'Capability needed in order to use activityopen condition';
$string['notificationsagent:activitysinceend'] = 'Capability needed in order to use activity since end condition';
$string['notificationsagent:activitystudentend'] = 'Capability needed in order to use activity student end condition';
$string['notificationsagent:addusergroup'] = 'Capability needed in order to use addusergroup action';
$string['notificationsagent:assignrule'] = 'Assign a rule';
$string['notificationsagent:bootstrapnotifications'] = 'Capability needed in order to use bootstrapnotifications action';
$string['notificationsagent:calendareventto'] = 'Capability needed in order to use calendar event to condition';
$string['notificationsagent:calendarstart'] = 'Capability needed in order to use calendarstart condition';
$string['notificationsagent:checkrulecontext'] = 'Check the context of a rule';
$string['notificationsagent:courseend'] = 'Capability needed in order to use course end condition';
$string['notificationsagent:coursestart'] = 'Capability needed in order to use coursestart condition';
$string['notificationsagent:createrule'] = 'Create a rule';
$string['notificationsagent:deleterule'] = 'Delete a rule';
$string['notificationsagent:editrule'] = 'Edit a rule';
$string['notificationsagent:exportrule'] = 'Export a rule';
$string['notificationsagent:forcerule'] = 'Force a rule';
$string['notificationsagent:forummessage'] = 'Capability needed in order to use forummessage action';
$string['notificationsagent:forumnoreply'] = 'Capability needed in order to use forum no reply condition';
$string['notificationsagent:importrule'] = 'Import a rule';
$string['notificationsagent:itemgraded'] = 'Capability needed in order to use grade item condition';
$string['notificationsagent:manageallrule'] = 'Cability to manage all rules';
$string['notificationsagent:managecourserule'] = 'Manage rules at course level';
$string['notificationsagent:manageownrule'] = 'Manage your own course rules';
$string['notificationsagent:managesessions'] = 'Cability to save rule order';
$string['notificationsagent:managesiterule'] = 'Manage rules at site level';
$string['notificationsagent:messageagent'] = 'Capability needed in order to use messageagent action';
$string['notificationsagent:nosuchinstance'] = 'No such instance was found.';
$string['notificationsagent:numberoftimes'] = 'Capability needed in order to use number of times condition';
$string['notificationsagent:ondates'] = 'Capability needed in order to use ondates condition';
$string['notificationsagent:privateforummessage'] = 'Capability needed in order to use privateforummessage action';
$string['notificationsagent:removeusergroup'] = 'Capability needed in order to use removeusergroup action';
$string['notificationsagent:sessionend'] = 'Capability needed in order to use session end condition';
$string['notificationsagent:sessionstart'] = 'Capability needed in order to use sessionstart condition';
$string['notificationsagent:shareruleall'] = 'Approve the sharing of a rule';
$string['notificationsagent:unshareruleall'] = 'Reject the sharing of a rule';
$string['notificationsagent:updateruleshare'] = 'Update the sharing state of a rule';
$string['notificationsagent:updaterulestatus'] = 'Update a rule\'s status';
$string['notificationsagent:usergroupadd'] = 'Capability needed in order to use usergroupadd condition';
$string['notificationsagent:usermessageagent'] = 'Capability needed in order to use usermessageagent action';
$string['notificationsagent:viewassistantreport'] = 'View rule report';
$string['notificationsagent:viewcourserule'] = 'Cability to view course rules';
$string['notificationsagent:weekdays'] = 'Capability needed in order to use weekdays condition';
$string['notificationsagent:weekend'] = 'Capability needed in order to use weekend condition';
$string['notificationsconditionpluginname'] = 'Condition plugin';
$string['pause_after_restore'] = 'Pause rules after restore';
$string['pause_after_restore_desc'] = 'Pause rules after restoring a course';
$string['placeholder_Course_Category_Name'] = 'Course category name';
$string['placeholder_Course_FullName'] = 'Course full name';
$string['placeholder_Course_Url'] = 'Course url';
$string['placeholder_Current_time'] = 'Current time';
$string['placeholder_Follow_Link'] = 'Follow link';
$string['placeholder_Separator'] = 'Message separator';
$string['placeholder_Teacher_Address'] = 'Teacher address';
$string['placeholder_Teacher_Email'] = 'Teacher email';
$string['placeholder_Teacher_FirstName'] = 'Teacher first name';
$string['placeholder_Teacher_LastName'] = 'Teacher last name';
$string['placeholder_Teacher_Username'] = 'Teacher username';
$string['placeholder_User_Address'] = 'User address';
$string['placeholder_User_Email'] = 'User email';
$string['placeholder_User_FirstName'] = 'User first name';
$string['placeholder_User_LastName'] = 'User last name';
$string['placeholder_User_Username'] = 'User username';
$string['pluginname'] = 'Notifications Agent';
$string['privacy:metadata:actiondetail'] = 'Detail of the action sent to the user.';
$string['privacy:metadata:actionid'] = 'An id for an action';
$string['privacy:metadata:courseid'] = 'An id for a course';
$string['privacy:metadata:createdat'] = 'Time that the rule was created.';
$string['privacy:metadata:createdby'] = 'The user id linked to the rule.';
$string['privacy:metadata:localnotificationsagentreport'] = 'Notifications agent report.';
$string['privacy:metadata:notificationsagent_cache'] = 'Stores cache data for users.';
$string['privacy:metadata:notificationsagent_cache:cache'] = 'The cached data.';
$string['privacy:metadata:notificationsagent_cache:startdate'] = 'The start date of the cache.';
$string['privacy:metadata:notificationsagent_cache:userid'] = 'The ID of the user associated with the cache.';
$string['privacy:metadata:notificationsagent_launched'] = 'Stores information about notifications launched for users.';
$string['privacy:metadata:notificationsagent_launched:timecreated'] = 'The timestamp when the record was created.';
$string['privacy:metadata:notificationsagent_launched:timemodified'] = 'The timestamp when the record was last modified.';
$string['privacy:metadata:notificationsagent_launched:timesfired'] = 'Number of times the rule was triggered.';
$string['privacy:metadata:notificationsagent_launched:userid'] = 'The ID of the user related to launched rule.';
$string['privacy:metadata:notificationsagent_rule'] = 'Stores rules created by users.';
$string['privacy:metadata:notificationsagent_rule:createdat'] = 'The timestamp when the rule was created.';
$string['privacy:metadata:notificationsagent_rule:createdby'] = 'The ID of the user who created the rule.';
$string['privacy:metadata:notificationsagent_triggers'] = 'Stores triggers associated with users.';
$string['privacy:metadata:notificationsagent_triggers:ruleoff'] = 'The rule associated with the trigger.';
$string['privacy:metadata:notificationsagent_triggers:startdate'] = 'The start date of the trigger.';
$string['privacy:metadata:notificationsagent_triggers:userid'] = 'The ID of the user associated with the trigger.';
$string['privacy:metadata:notificationsagentreport'] = 'Records messages sent to users which might content some user data.';
$string['privacy:metadata:notificationsagentrule'] = 'Stores rules that contains some user data .';
$string['privacy:metadata:ruleid'] = 'An id for a rule';
$string['privacy:metadata:timestamp'] = 'Timestamp of the sent action.';
$string['privacy:metadata:userid'] = 'The user id linked to this table.';
$string['report'] = 'Notifications agent report';
$string['rulecancelled'] = 'Rule cancelled';
$string['ruledownload'] = 'Export rule as';
$string['ruleid'] = 'Rule id';
$string['rulename'] = 'Rule name';
$string['rulesaved'] = 'Rule saved';
$string['settings'] = 'Settings';
$string['shareaccept'] = 'Rule shared';
$string['shareallcontent'] = 'You are going to approve the shared rule {$a->title}, do you want to continue?';
$string['sharealltitle'] = 'Approve the shared rule {$a->title}';
$string['sharecontent'] = 'You are going to share the rule {$a->title} with the administrator, do you want to continue?';
$string['sharereject'] = 'Rule rejected';
$string['sharetitle'] = 'Share rule {$a->title}';
$string['startdate'] = 'Activity dates config';
$string['startdate_desc'] = 'Use a line for each activity with the pattern: pluginname|tablename|startdate|startend';
$string['status_acceptactivated'] = 'Rule activated';
$string['status_acceptpaused'] = 'Rule paused';
$string['status_activatecontent'] = 'You are going to activate rule {$a->title}, do you want to continue?';
$string['status_activatetitle'] = 'Activate rule {$a->title}';
$string['status_active'] = 'Active';
$string['status_broken'] = 'Broken';
$string['status_pausecontent'] = 'You are going to pause rule {$a->title}, do you want to continue?';
$string['status_paused'] = 'Paused';
$string['status_pausetitle'] = 'Pause rule {$a->title}';
$string['status_required'] = 'Required';
$string['statusactivate'] = 'Activate';
$string['statuspause'] = 'Pause';
$string['subplugintype_notificationsaction'] = 'Action plugin';
$string['subplugintype_notificationsaction_plural'] = 'Action plugins';
$string['subplugintype_notificationsagentaction'] = 'Action subplugins';
$string['subplugintype_notificationscondition'] = 'Condition plugin';
$string['subplugintype_notificationscondition_plural'] = 'Condition plugins';
$string['tasktriggers'] = 'Notifications triggers task';
$string['timestamp'] = 'Date';
$string['tracelog'] = 'Trace log';
$string['tracelog_desc'] = 'Trace log. Disable on a production site';
$string['type_rule'] = 'rule';
$string['type_sharedrule'] = 'shared rule';
$string['type_template'] = 'template';
$string['unshareaccept'] = 'Rule unshared';
$string['unshareallcontent'] = 'You are going to reject the shared rule {$a->title}, do you want to continue?';
$string['unsharealltitle'] = 'Reject the shared rule {$a->title}';
$string['unsharecontent'] = 'You are going to stop sharing the rule {$a->title} with the administrator, do you want to continue?';
$string['unsharetitle'] = 'Unshare rule {$a->title}';
$string['userid'] = 'User id';
