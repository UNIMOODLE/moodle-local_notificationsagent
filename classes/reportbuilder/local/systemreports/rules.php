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
 * Version details
 *
 * @package    local_notificationsagent
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_notificationsagent\reportbuilder\local\systemreports;

use core_reportbuilder\system_report;
use local_notificationsagent\local\entities\rule;
use core_reportbuilder\local\entities\course;

/**
 * System report class for listing notification rules.
 */
class rules extends system_report {
    /**
     * A function to initialise the entities, tables, joins, columns, filters, and set downloadable for the report.
     */
    protected function initialise(): void {
        $ruleentity = new rule();

        $narralias = $ruleentity->get_table_alias('notificationsagent_report');
        $this->set_main_table('notificationsagent_report', $narralias);
        $this->add_security_conditions($narralias);
        $this->add_entity($ruleentity);

        $coursentity = new course();
        $coursealias = $coursentity->get_table_alias('course');
        $coursejoin = "JOIN {course} {$coursealias} ON {$coursealias}.id = {$narralias}.courseid";
        $this->add_entity($coursentity->add_join($coursejoin));

        $this->add_columns();
        $this->add_filters();
        $this->set_downloadable(true);
    }

    /**
     * Restrict the report rows to the context course and, without full view capability, to the current user
     *
     * Filters can be changed or reset by the user, so access restrictions must be applied as base conditions.
     *
     * @param string $narralias Alias of the notificationsagent_report table
     */
    private function add_security_conditions(string $narralias): void {
        global $USER;

        $context = $this->get_context();
        if ($context->contextlevel == CONTEXT_COURSE) {
            $this->add_base_condition_simple("{$narralias}.courseid", $context->instanceid);
        }

        if (!$this->can_view_all()) {
            $this->add_base_condition_simple("{$narralias}.userid", $USER->id);
        }
    }

    /**
     * Whether the current user can see the rows of every user in the report context
     *
     * @return bool
     */
    private function can_view_all(): bool {
        $context = $this->get_context();
        if ($context->contextlevel == CONTEXT_COURSE) {
            return has_any_capability(
                ['local/notificationsagent:viewcourserule', 'local/notificationsagent:manageallrule'],
                $context
            );
        }

        return has_capability('local/notificationsagent:manageallrule', $context);
    }

    /**
     * Validates access to view this report
     *
     * This is necessary to implement independently of the page that would typically embed the report because
     * subsequent pages are requested via AJAX requests, and access should be validated each time
     *
     * @return bool
     */
    protected function can_view(): bool {
        return has_capability('local/notificationsagent:viewassistantreport', $this->get_context());
    }

    /**
     * Add columns to the function.
     */
    public function add_columns() {
        $this->add_columns_from_entities(
            [
                        'rule:rulename',
                        'rule:fullnamewithlink',
                        'course:coursefullnamewithlink',
                        'rule:actionname',
                        'rule:actiondetail',
                        'rule:timestamp',
                ]
        );

        $this->set_initial_sort_column('rule:timestamp', SORT_DESC);

        if ($column = $this->get_column('course:coursefullnamewithlink')) {
            $column->set_title(new \lang_string('fullcourse', 'local_notificationsagent'));
        }
    }

    /**
     * Add filters to the function.
     *
     */
    protected function add_filters(): void {
        $filters = ['rule:rulename'];
        if ($this->get_context()->contextlevel != CONTEXT_COURSE) {
            $filters[] = 'course:courseselector';
        }
        if ($this->can_view_all()) {
            $filters[] = 'rule:userfullname';
        }
        $filters[] = 'rule:actiondetail';
        $filters[] = 'rule:timestamp';

        $this->add_filters_from_entities($filters);
    }
}
