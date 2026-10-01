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
// Funded by the European Union - Next GenerationEU".
//
// Produced by the UNIMOODLE University Group: Universities of
// Valladolid, Complutense de Madrid, UPV/EHU, León, Salamanca,
// Illes Balears, Valencia, Rey Juan Carlos, La Laguna, Zaragoza, Málaga,
// Córdoba, Extremadura, Vigo, Las Palmas de Gran Canaria y Burgos.

/**
 * Category and course picker of the assignment modal.
 *
 * @package    local_notificationsagent
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_notificationsagent\output;

use renderable;
use renderer_base;
use templatable;

/**
 * Category and course picker of the assignment modal.
 */
class category_picker implements renderable, templatable {
    /** @var array Categories as returned by helper::build_category_array() */
    protected array $categories;

    /**
     * Constructor.
     *
     * @param array $categories Categories as returned by helper::build_category_array()
     */
    public function __construct(array $categories) {
        $this->categories = $categories;
    }

    /**
     * Export the data for the template.
     *
     * @param renderer_base $output
     *
     * @return array
     */
    public function export_for_template(renderer_base $output): array {
        return [
            'categories' => self::export_categories($this->categories, 0),
        ];
    }

    /**
     * Add to each category the data the category_item template needs.
     *
     * Names are already formatted by helper::build_category_array().
     *
     * @param array $categories Categories as returned by helper::build_category_array()
     * @param int $parentid Id of the parent category
     *
     * @return array
     */
    public static function export_categories(array $categories, int $parentid): array {
        $exported = [];
        foreach ($categories as $category) {
            $courses = [];
            foreach ($category['courses'] ?? [] as $course) {
                $courses[] = [
                    'id' => $course['id'],
                    'name' => $course['name'],
                    'categoryid' => $category['id'],
                ];
            }

            $exported[] = [
                'id' => $category['id'],
                'name' => $category['name'],
                'parentid' => $parentid,
                'countsubcategoriescourses' => $category['countsubcategoriescourses'],
                'hascourses' => !empty($courses),
                'courses' => $courses,
                'categories' => self::export_categories($category['categories'] ?? [], (int) $category['id']),
            ];
        }
        return $exported;
    }
}
