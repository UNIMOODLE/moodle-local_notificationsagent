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
use core_reportbuilder\local\entities\user;

/**
 * local_notificationsagent system_rules.php description here.
 *
 * @package    local_notificationsagent
 * @copyright  2023 fernando <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class rules extends system_report {

    protected function initialise(): void {
        $ruleentity = new rule();
        $narralias = $ruleentity->get_table_alias('notificationsagent_report');
        $actionalias = $ruleentity->get_table_alias('notificationsagent_action');
        $actionjoin = "JOIN {notificationsagent_action} {$actionalias} ON {$narralias}.actionid = {$actionalias}.id";
        $this->set_main_table('notificationsagent_report', $narralias);
        $this->add_entity($ruleentity->add_join($actionjoin));
        $this->add_entity($ruleentity);

        $userentity = new user();
        $useralias = $userentity->get_table_alias('user');
        $userjoin = "JOIN {user} {$useralias} ON {$useralias}.id = {$narralias}.userid";
        $this->add_entity($userentity->add_join($userjoin));

        $coursentity = new course();
        $coursealias = $coursentity->get_table_alias('course');
        $coursejoin = "JOIN {course} {$coursealias} ON {$coursealias}.id = {$narralias}.courseid";
        $this->add_entity($coursentity->add_join($coursejoin));

        $this->add_columns();
        $this->add_filters();
        $this->set_downloadable(true);
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
        return true;
    }

    public function add_columns() {
        $this->add_columns_from_entities(
            [
                'rule:rulename',
                'user:fullname',
                'rule:actionname',
                'course:fullname',
                'rule:actiondetail',
                'rule:timestamp',
            ]
        );

    }

    protected function add_filters(): void {
        $filters = [
            'rule:rulename',
            'course:fullname',
            'user:firstname',
            'user:lastname',
        ];

        $this->add_filters_from_entities($filters);
    }
}
