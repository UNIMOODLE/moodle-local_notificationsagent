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
 * @package    notificationscondition_ac
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace notificationscondition_ac;

use core_availability\info;
use local_notificationsagent\notificationplugin;
use moodle_exception;

/**
 * This class extends the core_availability\info class to support the local_notificationsagent plugin.
 */
class custominfo extends info {
    /**
     * @var stdClass The course module object.
     */
    protected $cm;

    /**
     * Construct the object with the given course ID and availability.
     *
     * @param int $courseid Course id
     * @param string $availability Availability
     */
    public function __construct($courseid, $availability) {
        // Get course details.
        $course = get_course($courseid);
        parent::__construct($course, true, $availability);
    }

    /**
     * This may be used in error messages etc. You would probably use
     * the name of the thing you're controlling availability for.
     *
     * @return string
     */
    protected function get_thing_name() {
        return 'ac';
    }

    /**
     * This function should save the availability settings back to database.
     * It's needed if doing an update after restore, so you do need to
     * implement it.
     *
     * @param string $availability
     *
     * @return void
     */
    protected function set_in_database($availability) {
    }

    /**
     * Get the context of the module.
     *
     * @return \context_module The context module instance
     */
    public function get_context() {
        return \context_module::instance($this->cm->id);
    }

    /**
     * Returns the capability that controls whether users can see the activity
     * completion information.
     *
     */
    protected function get_view_hidden_capability() {
    }

    /**
     *
     * Set module information for the PHP function.
     *
     * @param int $nouser
     */
    private function set_mod_info($nouser = 0) {
        $modinfo = get_fast_modinfo($this->get_course(), $nouser);
        $this->modinfo = $modinfo;
    }

    // I didn't bother to implement filter_user_list, so it's using the default
    // which considers only this condition. You might want to make a
    // filter_user_list that takes into account the course-module's permissions
    // too (like how the info_module class includes the section), if you expect
    // to actually use the 'list users who can access this' APIs.
    /**
     * Get the full information format.
     *
     * @param int $complementary Complementary condition
     *
     * @return array
     */
    public function get_full_information_format($complementary) {
        // Moodle requisite.
        $this->set_mod_info();

        $result = [];

        $getavailabilitytree = $this->get_availability_tree();
        $children = $getavailabilitytree->get_all_children('core_availability\tree');
        if (!empty($children[$complementary])) {
            $conditions = $children[$complementary]->get_all_children('core_availability\condition');
            [$innernot] = $children[$complementary]->get_logic_flags(
                $complementary == notificationplugin::COMPLEMENTARY_EXCEPTION
            );
            foreach ($conditions as $child) {
                $childdescription = $child->get_description(true, $innernot, $this);
                $formatinfo = $this->format_info($childdescription, $this->get_course());
                $result[] = strip_tags($formatinfo);
            }
        }

        return $result;
    }

    /**
     * Validation subplugin only for AC
     *
     * @return bool
     */
    public function validation() {
        global $DB;

        // Moodle requisite.
        $this->set_mod_info(-1);

        // Course data.
        $course = $this->get_course();
        $modinfo = $this->get_modinfo();
        $courseid = $modinfo->get_course_id();

        // Conditions.
        customtree::$customchildren = []; // Empty.
        $tree = new customtree(json_decode($this->availability));
        $childrens = $tree::$customchildren;
        foreach ($childrens as $child) {
            $type = $child->type; // Completion//grade//group//grouping.
            // Look for a plugin of this type.
            $classname = '\availability_' . $type . '\condition';
            try {
                $instance = new $classname($child);

                if ($type == 'completion') {
                    [$selfcmid, $selfsectionid] = $instance->get_selfids($this);
                    $cmid = $instance->get_cmid($course, $selfcmid, $selfsectionid);

                    if (!array_key_exists($cmid, $modinfo->cms) || $modinfo->cms[$cmid]->deletioninprogress) {
                        return false;
                    }
                } else if ($type == 'grade') {
                    $gradeitemid = $child->id;
                    // Get all grade item names from cache, or using db query.
                    $cache = \cache::make('availability_grade', 'items');
                    if (($cacheditems = $cache->get($courseid)) === false) {
                        // We cache the whole items table not the name; the format_string
                        // call for the name might depend on current user (e.g. multilang)
                        // and this is a shared cache.
                        $cacheditems = $DB->get_records('grade_items', ['courseid' => $courseid]);
                        $cache->set($courseid, $cacheditems);
                    }

                    // Return name from cached item or a lang string.
                    if (!array_key_exists($gradeitemid, $cacheditems)) {
                        return false;
                    }
                } else if ($type == 'group') {
                    if ($groupid = $child->id) {
                        if (!groups_group_exists($groupid)) {
                            return false;
                        }
                    }
                } else if ($type == 'grouping') {
                    if ($groupingid = $child->id) {
                        if (!$DB->record_exists('groupings', ['id' => $groupingid])) {
                            return false;
                        }
                    }
                }
            } catch (moodle_exception $e) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if the given availability is empty by traversing the tree structure and checking for empty children.
     *
     * @param mixed $availability The availability data to be checked.
     *
     * @return bool Returns true if the availability is empty, false otherwise.
     */
    public static function is_empty($availability) {
        $result = true;
        if (!empty($availability)) {
            $tree = new \core_availability\tree(json_decode($availability));
            $children = $tree->get_all_children('core_availability\tree');
            if (!empty($children)) {
                foreach ($children as $child) {
                    if (!$child->is_empty()) {
                        $result = false;
                        break;
                    }
                }
            }
        }

        return $result;
    }
}
