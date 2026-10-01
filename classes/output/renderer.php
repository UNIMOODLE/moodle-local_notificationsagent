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
 * Renderer for local_notificationsagent.
 *
 * @package    local_notificationsagent
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_notificationsagent\output;

use plugin_renderer_base;

/**
 * Renderer for local_notificationsagent.
 */
class renderer extends plugin_renderer_base {
    /**
     * Tabs of the rule edition form.
     *
     * @param string $tabtarget Id of the active tab
     *
     * @return string
     */
    public function tabnav($tabtarget) {
        $tabs = [];
        foreach (['conditions', 'exceptions', 'actions'] as $name) {
            $tabs[] = [
                'name' => $name,
                'label' => get_string($name, 'local_notificationsagent'),
                'active' => $tabtarget == 'nav-' . $name . '-tab',
            ];
        }

        return $this->render_from_template('local_notificationsagent/editrule/tabnav', ['tabs' => $tabs]);
    }

    /**
     * Help about how a rule is evaluated, shown at the end of the rule edition form.
     *
     * @return string
     */
    public function evaluation_help(): string {
        return \html_writer::div($this->help_icon('evaluaterule', 'local_notificationsagent'), 'evaluaterule-help');
    }

    /**
     * Render the category and course picker of the assignment modal.
     *
     * @param category_picker $picker
     *
     * @return string
     */
    protected function render_category_picker(category_picker $picker): string {
        return $this->render_from_template('local_notificationsagent/assign/category_picker', $picker->export_for_template($this));
    }

    /**
     * Render the list items of a category tree, without the picker wrapper.
     *
     * @param array $categories Categories as returned by helper::build_category_array()
     * @param int $parentid Id of the parent category
     *
     * @return string
     */
    public function category_items(array $categories, int $parentid = 0): string {
        $output = '';
        foreach (category_picker::export_categories($categories, $parentid) as $category) {
            $output .= $this->render_from_template('local_notificationsagent/assign/category_item', $category);
        }
        return $output;
    }
}
