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

namespace local_notificationsagent\local\entities;

use core_reportbuilder\local\report\{column, filter};
use core_reportbuilder\local\entities\base;
use core_reportbuilder\local\filters\number;
use core_reportbuilder\local\filters\text;
use core_reportbuilder\local\helpers\format;
use lang_string;

class rule extends base {

    /**
     * Database tables that this entity uses and their default aliases
     *
     * Must be overridden by the entity to list all database tables that it expects to be present in the main
     * SQL or in JOINs added to this entity
     *
     * @return string[] Array of $tablename => $alias
     */
    protected function get_default_table_aliases(): array {
        return [
            'user' => 'naru',
            'course' => 'narc',
            'notificationsagent_rule' => 'narru',
            'notificationsagent_action' => 'nara',
            'notificationsagent_report' => 'narr',
            'notificationsagent_context' => 'narctx',
        ];
    }

    /**
     * The default title for this entity
     *
     * @return lang_string
     */
    protected function get_default_entity_title(): lang_string {
        return new lang_string('pluginname', 'local_notificationsagent');
    }

    /**
     * Initialise the entity, called automatically when it is added to a report
     *
     * This is where entity defines all its columns and filters by calling:
     * - {@see add_column}
     * - {@see add_filter}
     * - etc
     *
     * @return self
     */
    public function initialise(): base {
        $columns = $this->get_all_columns();

        foreach ($columns as $column) {
            $this->add_column($column);
        }

        $filters = $this->get_all_filters();

        foreach ($filters as $filter) {
            $this->add_filter($filter);
        }

        return $this;

    }

    protected function get_all_columns(): array {
        $columns = [];

        $reportalias = $this->get_table_alias('notificationsagent_report');
        $useralias = $this->get_table_alias('user');
        $coursealias = $this->get_table_alias('course');
        $ruleealias = $this->get_table_alias('notificationsagent_rule');
        $actionealias = $this->get_table_alias('notificationsagent_action');

        $userjoin = $this->userjoin();
        $coursejoin = $this->coursejoin();
        $rulejoin = $this->rulejoin();
        $actionjoin = $this->actionjoin();

        $columns[] = (new column(
            'rulename',
            new lang_string('fullrule', 'local_notificationsagent'),
            $this->get_entity_name()
        ))
            ->add_join($rulejoin)
            ->set_type(column::TYPE_TEXT)
            ->set_is_sortable(true)
            ->add_field("{$ruleealias}.name");

        $columns[] = (new column(
            'fulluser',
            new lang_string('fulluser', 'local_notificationsagent'),
            $this->get_entity_name()
        ))
            ->add_join($userjoin)
            ->set_type(column::TYPE_TEXT)
            ->set_is_sortable(true)
            ->add_field("{$useralias}.firstname");

        $columns[] = (new column(
            'courseid',
            new lang_string('courseid', 'local_notificationsagent'),
            $this->get_entity_name()
        ))
            ->set_type(column::TYPE_INTEGER)
            ->set_is_sortable(true)
            ->add_field("{$reportalias}.courseid");

        $columns[] = (new column(
            'actionid',
            new lang_string('actionid', 'local_notificationsagent'),
            $this->get_entity_name()
        ))
            ->set_type(column::TYPE_INTEGER)
            ->set_is_sortable(true)
            ->add_field("{$reportalias}.actionid");

        $columns[] = (new column(
            'actionname',
            new lang_string('fullaction', 'local_notificationsagent'),
            $this->get_entity_name()
        ))
            ->add_join($actionjoin)
            ->set_type(column::TYPE_TEXT)
            ->set_is_sortable(true)
            ->add_field("{$actionealias}.pluginname")
            ->add_callback(static function(string $plugigname): string {
                return get_string('pluginname', 'notificationsaction_' . $plugigname);
            });

        $columns[] = (new column(
            'actiondetail',
            new lang_string('actiondetail', 'local_notificationsagent'),
            $this->get_entity_name()
        ))
            ->set_type(column::TYPE_LONGTEXT)
            ->set_is_sortable(true)
            ->add_field("{$reportalias}.actiondetail");

        $columns[] = (new column(
            'timestamp',
            new lang_string('timestamp', 'local_notificationsagent'),
            $this->get_entity_name()
        ))
            ->set_type(column::TYPE_TIMESTAMP)
            ->set_is_sortable(true)
            ->add_field("{$reportalias}.timestamp")
            ->add_callback([format::class, 'userdate'],
                get_string('strftimedatetimeshortaccurate', 'core_langconfig'));

        return $columns;

    }

    protected function get_all_filters(): array {
        $narralias = $this->get_table_alias('notificationsagent_report');
        $narrualias = $this->get_table_alias('notificationsagent_rule');
        $coursealias = $this->get_table_alias('course');
        $userealias = $this->get_table_alias('user');
        $rulejoin = $this->rulejoin();

        $filters[] = (new filter(
            number::class,
            'ruleid',
            new lang_string('ruleid', 'local_notificationsagent'),
            $this->get_entity_name(),
            "{$narralias}.ruleid"
        ))
            ->add_joins($this->get_joins());

        $filters[] = (new filter(
            text::class,
            'rulename',
            new lang_string('rulename', 'local_notificationsagent'),
            $this->get_entity_name(),
            "{$narrualias}.name"
        ))
            ->add_joins([$rulejoin]);

        $filters[] = (new filter(
            number::class,
            'courseid',
            new lang_string('courseid', 'local_notificationsagent'),
            $this->get_entity_name(),
            "{$narralias}.courseid"
        ))
            ->add_joins($this->get_joins());

        $filters[] = (new filter(
            text::class,
            'fullcourse',
            new lang_string('fullcourse', 'local_notificationsagent'),
            $this->get_entity_name(),
            "{$coursealias}.name"
        ))
            ->add_joins($this->get_joins());

        $filters[] = (new filter(
            text::class,
            'fulluser',
            new lang_string('fulluser', 'local_notificationsagent'),
            $this->get_entity_name(),
            "{$userealias}.lastname"
        ))
            ->add_joins($this->get_joins());

        return $filters;

    }

    public function userjoin() {
        $useralias = $this->get_table_alias('user');
        $rulesreportalias = $this->get_table_alias('notificationsagent_report');
        return "JOIN {notificationsagent_report} {$rulesreportalias}
                    ON {$rulesreportalias}.userid = {$useralias}.id";
    }

    public function coursejoin() {
        $coursealias = $this->get_table_alias('course');
        $rulesreportalias = $this->get_table_alias('notificationsagent_report');
        return "JOIN {notificationsagent_report} {$rulesreportalias}
                    ON {$rulesreportalias}.courseid = {$coursealias}.id";
    }

    public function rulejoin() {
        $rulealias = $this->get_table_alias('notificationsagent_rule');
        $rulesreportalias = $this->get_table_alias('notificationsagent_report');
        return "JOIN {notificationsagent_rule} {$rulealias}
                    ON {$rulesreportalias}.ruleid = {$rulealias}.id";
    }

    public function actionjoin() {
        $actionalias = $this->get_table_alias('notificationsagent_action');
        $rulesreportalias = $this->get_table_alias('notificationsagent_report');
        return "JOIN {notificationsagent_action} {$actionalias}
                    ON {$rulesreportalias}.actionid = {$actionalias}.id";
    }
}

