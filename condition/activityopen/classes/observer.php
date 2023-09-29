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
 * activityopen observer.php .
 *
 * @package    activityopen
 * @copyright  2023 fernando
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ .'/../activityopen.php');
require_once(__DIR__ .'/../../../notificationsagent.php');
require_once(__DIR__ .'/../../../classes/engine/notificationsagent_engine.php');
use notificationsagent\notificationsagent;

class notificationscondition_activityopen_observer {

    public static function course_module_updated(\core\event\course_module_updated $event) {

        $courseid = $event->courseid;
        $timeaccess = $event->timecreated;
        $cmid = $event->objectid;

        $timestart = self::get_cm_starttime($cmid);

        $pluginname = get_string('subtype', 'notificationscondition_activityopen');

        $conditions = notificationsagent::get_conditions_by_cm($pluginname, $courseid, $cmid);
        $context = context_course::instance($courseid);
        $enrolledusers = self::get_usersbycourse($context);
        $ruleids = [];

        foreach ($conditions as $condition) {
            $decode = $condition->parameters;
            $pluginname = $condition->pluginname;
            $condtionid = $condition->id;
            $ruleids[] = $condition->ruleid;
            $param = json_decode($decode, true);

            $cache = $timestart + $param['time'];
            foreach ($enrolledusers as $enrolleduser) {
                // Update every time a module is updated
                notificationsagent::set_timer_cache($enrolleduser, $courseid, $cache, $pluginname,$condtionid, true);
            }
        }
        // Call engine with userid, courseid, timecreated
        foreach ($enrolledusers as $enrolleduser) {
            Notificationsagent_engine::notificationsagent_engine_evaluate_rule($ruleids, $timeaccess, $enrolleduser);
        }
    }

    /**
     * @param $context
     *
     * @return array
     */
    public static function get_usersbycourse($context): array {
        $enrolledusers = get_enrolled_users($context);
        $users = [];
        foreach ($enrolledusers as $user) {
            $users[] = $user->id;
        }
        return $users;
    }

    /**
     *  Get startdate from activity
     * @return int
     */
    private static function get_cm_starttime($cmid){
        // Table :course modules

        global $DB;
        $starttime = null;
        $starttime_query = "
                    SELECT mcm.id,instance,module,mm.name 
                      FROM mdl_course_modules mcm
                      JOIN mdl_modules mm ON mm.id = mcm.module
                    WHERE mcm.id = :cmid" ;

        $mod_type = $DB->get_record_sql(
            $starttime_query,
            [
                'cmid' => $cmid,
            ]
        );
        // TODO Remaining activities
        switch ($mod_type->name){
            case 'quiz':
                $starttime = $DB->get_field('quiz','timeopen',['id'=>$mod_type->instance]);
                break;
            case 'lesson':
                $starttime = $DB->get_field('lesson','available',['id'=>$mod_type->instance]);
                break;
            case 'forum':
                $starttime = $DB->get_field('forum','duedate',['id'=>$mod_type->instance]);
                break;
            case 'chat':
                $starttime = $DB->get_field('chat','chattime',['id'=>$mod_type->instance]);
                break;
            case 'assign':
                $starttime = $DB->get_field('assign','duedate',['id'=>$mod_type->instance]);
                break;

        }

        return $starttime;

    }

}
