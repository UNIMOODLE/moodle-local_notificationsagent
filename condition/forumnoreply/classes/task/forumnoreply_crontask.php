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
 * @package    notificationscondition_forumnoreply
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace notificationscondition_forumnoreply\task;

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->dirroot . '/local/notificationsagent/lib.php');

use core\task\scheduled_task;
use local_notificationsagent\notificationplugin;
use local_notificationsagent\notificationsagent;

/**
 *  Scheduled task for forumnoreply subplugin
 */
class forumnoreply_crontask extends scheduled_task {

    /**
     * Get a descriptive name for the task (shown to admins)
     *
     * @return string
     */
    public function get_name() {
        return get_string('forumnoreply_crontask', 'notificationscondition_forumnoreply');
    }

    /**
     * Throw exceptions on errors (the job will be retried).
     *
     * @throws \moodle_exception
     */
    public function execute() {
        global $DB;
        custom_mtrace("forumnoreply start");

        $pluginname = 'forumnoreply';
        $conditions = notificationsagent::get_conditions_by_plugin($pluginname);

        foreach ($conditions as $condition) {
            $courseid = $condition->courseid;
            $conditionid = $condition->id;
            $decode = $condition->parameters;
            $param = json_decode($decode, true);
            $timenow = $this->get_timestarted();

            $modinfo = get_fast_modinfo($courseid);
            $foroid = $modinfo->get_cm($param[notificationplugin::UI_ACTIVITY])->instance;

            $sql = "SELECT DISTINCT fd.id, fd.timemodified as timemodified, fd.userid
                      FROM {forum_discussions} fd
                      JOIN {forum_posts} fp ON fp.discussion=fd.id AND fp.parent = 0
                 LEFT JOIN {forum_posts} fp2 ON fp.id = fp2.parent
                     WHERE fd.forum = :forum
                       AND fd.course = :course
                       AND fd.timestart >= :timestart
                       AND (fd.timeend = :timeend OR fd.timeend > :timenow)
                       AND :timenow2 >= fd.timemodified + CAST( :timenowandtime AS INTEGER )
                       AND fp2.id IS NULL
                 ";

            $threads = $DB->get_records_sql(
                $sql,
                [
                    'forum' => $foroid,
                    'course' => $courseid,
                    'timestart' => 0,
                    'timeend' => 0,
                    'timenow' => $timenow,
                    'timenow2' => $timenow,
                    'timenowandtime' => $param[notificationplugin::UI_TIME],
                ]
            );

            foreach ($threads as $thread) {
                if (!notificationsagent::was_launched_indicated_times(
                        $condition->ruleid, $condition->ruletimesfired, $courseid, $thread->userid
                    )
                    && !notificationsagent::is_ruleoff($condition->ruleid, $thread->userid)
                ) {
                    notificationsagent::set_timer_cache(
                        $thread->userid, $courseid, $thread->timemodified + $param[notificationplugin::UI_TIME], $pluginname,
                        $conditionid
                    );
                    notificationsagent::set_time_trigger($condition->ruleid, $conditionid, $thread->userid, $courseid, $timenow);
                }
            }
        }
        custom_mtrace("forumnoreply end ");
    }
}
