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

use core_availability\info;

class mod_ac_availability_info extends info {
    protected $cm;

    // You would probably define more suitable parameters here about the
    // specific thing you're controlling availability for.
    public function __construct($courseid, $availability) {
        // Get course details.
        $course = get_course($courseid);
        parent::__construct($course, true, $availability);
    }

    protected function get_thing_name() {
        // This may be used in error messages etc. You would probably use
        // the name of the thing you're controlling availability for.
        return 'ac';
    }

    protected function set_in_database($availability) {
        // This function should save the availability settings back to database.
        // It's needed if doing an update after restore, so you do need to
        // implement it.
    }

    public function get_context() {
        return \context_module::instance($this->cm->id);
    }

    protected function get_view_hidden_capability() {
    }

    private function set_mod_info() {
        $modinfo = get_fast_modinfo($this->course);
        $this->modinfo = $modinfo;
    }

    // I didn't bother to implement filter_user_list, so it's using the default
    // which considers only this condition. You might want to make a
    // filter_user_list that takes into account the course-module's permissions
    // too (like how the info_module class includes the section), if you expect
    // to actually use the 'list users who can access this' APIs.

    public function get_full_information_format($complementary) {

        // Moodle requisite.
        $this->set_mod_info();

        $result = [];

        $getavailabilitytree = $this->get_availability_tree();
        $children = $getavailabilitytree->get_all_children('core_availability\tree');
        if (!empty($children[$complementary])) {
            $conditions = $children[$complementary]->get_all_children('core_availability\condition');
            list($innernot) = $children[$complementary]->get_logic_flags(
                $complementary == notificationplugin::CAT_EXCEPTION_CHILDREN
            );
            foreach ($conditions as $child) {
                $childdescription = $child->get_description(true, $innernot, $this);
                $formatinfo = $this->format_info($childdescription, $this->course);
                $result[] = strip_tags($formatinfo);
            }
        }

        return $result;
    }

    public static function is_empty($availability) {
        $result = true;
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

        return $result;
    }

}
