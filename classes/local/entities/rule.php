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

use core_collator;
use core_reportbuilder\local\entities\base;
use local_notificationsagent\local\filters\autocomplete;
use core_reportbuilder\local\filters\date;
use core_reportbuilder\local\filters\select;
use core_reportbuilder\local\filters\text;
use core_reportbuilder\local\helpers\format;
use core_reportbuilder\local\report\column;
use core_reportbuilder\local\report\filter;
use lang_string;

/**
 * Class of the entities of the rule.
 */
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
            'user' => 'u',
            'course' => 'c',
            'notificationsagent_rule' => 'narru',
            'notificationsagent_action' => 'nara',
            'notificationsagent_report' => 'narr',
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

    /**
     * Get all columns.
     *
     * @return array
     */
    protected function get_all_columns(): array {
        $columns = [];

        $reportalias = $this->get_table_alias('notificationsagent_report');

        $ruleealias = $this->get_table_alias('notificationsagent_rule');
        $actionealias = $this->get_table_alias('notificationsagent_action');

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
            'actionname',
            new lang_string('fullaction', 'local_notificationsagent'),
            $this->get_entity_name()
        ))
            ->add_join($actionjoin)
            ->set_type(column::TYPE_TEXT)
            ->set_is_sortable(true)
            ->add_field("{$actionealias}.pluginname")
            ->add_callback(static function(string $pluginname): string {
                return get_string('pluginname', 'notificationsaction_' . $pluginname);
            });

        $columns[] = (new column(
            'actiondetail',
            new lang_string('actiondetail', 'local_notificationsagent'),
            $this->get_entity_name()
        ))
            ->set_type(column::TYPE_LONGTEXT)
            ->set_is_sortable(true)
            ->add_field("{$reportalias}.actiondetail")
            ->add_callback(static function($str): string {
                $json = json_decode($str, true);
                $result = '';
                if ($json == null) {
                    return $result;
                }
                foreach ($json as $key => $value) {
                    $result .= "  " . $key . " : " . mb_convert_encoding($value, 'UTF-8');
                }
                return $result;
            });

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

    /**
     * Get all filters.
     *
     * @return array
     */
    protected function get_all_filters(): array {
        global $USER, $COURSE;
        $narralias = $this->get_table_alias('notificationsagent_report');
        $narrualias = $this->get_table_alias('notificationsagent_rule');
        $coursealias = $this->get_table_alias('course');
        $useralias = $this->get_table_alias('user');
        $actionealias = $this->get_table_alias('notificationsagent_action');
        $reportalias = $this->get_table_alias('notificationsagent_report');
        $rulejoin = $this->rulejoin();
        $coursecontext = \context_course::instance($COURSE->id);
        // Get rules to view as capability function.
        $viewrules = static function(): array {
            global $DB, $USER, $COURSE;
            $userid = $USER->id;
            $course = $COURSE;
            $coursecontext = \context_course::instance($course->id);
            $options = [];
            // User can see all the rules.
            if (has_capability(
                'local/notificationsagent:manageallrule',
                $coursecontext,
                $userid
            )
            ) {
                $query = 'SELECT id
                                  FROM {notificationsagent_rule}
                               WHERE template = 1';
                $rulenames = $DB->get_fieldset_sql($query);
                // User can see rules of current course.
            } else if (has_capability(
                'local/notificationsagent:viewcourserule',
                $coursecontext,
                $userid
            )
            ) {
                $query
                    = 'SELECT DISTINCT {notificationsagent_report}.ruleid
                         FROM {notificationsagent_report}
                      WHERE {notificationsagent_report}.courseid =' . $COURSE->id;
                $rulenames = $DB->get_fieldset_sql($query);
                // User can see rules of its own.
            } else if (has_capability(
                'local/notificationsagent:manageownrule',
                $coursecontext,
                $userid
            )
            ) {
                $key = implode(',', array_keys(enrol_get_my_courses(['id', 'cacherev'])));
                $query
                    = 'SELECT {notificationsagent_rule}.id
                          FROM {notificationsagent_rule}
                           JOIN {notificationsagent_report}
                              ON {notificationsagent_report}.ruleid = {notificationsagent_rule}.id
                            AND {notificationsagent_rule}.createdby = ' . $userid . '
                        WHERE {notificationsagent_report}.courseid IN (' . $key . ' )';
                $rulenames = $DB->get_fieldset_sql($query);
            }

            foreach ($rulenames as $rulename) {
                if (!empty($rulename)) {
                    $options[$rulename] = \local_notificationsagent\rule::create_instance($rulename)->get_name();
                }
            }
            core_collator::asort($options);
            return $options;
        };
        // Rule name filter.
        $filters[] = (new filter(
            autocomplete::class,
            'rulename',
            new lang_string('rulename', 'local_notificationsagent'),
            $this->get_entity_name(),
            "{$narrualias}.id"
        ))
            ->add_joins($this->get_joins())
            ->set_options_callback($viewrules);
        // Date filter.
        $filters[] = (new filter(
            date::class,
            'timestamp',
            new lang_string('timestamp', 'local_notificationsagent'),
            $this->get_entity_name(),
            "{$narralias}.timestamp"
        ))
            ->add_joins($this->get_joins())
            ->set_limited_operators([
                date::DATE_ANY,
                date::DATE_RANGE,
                date::DATE_PREVIOUS,
                date::DATE_CURRENT,
            ]);
        // Course filter.
        $filters[] = (new filter(
            autocomplete::class,
            'courseselector',
            new lang_string('courses'),
            $this->get_entity_name(),
            "{$coursealias}.id"
        ))
            ->add_joins($this->get_joins())
            ->set_options_callback(static function(): array {
                global $COURSE, $DB;
                $options = [];
                $coursecontext = \context_course::instance($COURSE->id);
                if (has_capability(
                    'local/notificationsagent:manageallrule',
                    $coursecontext
                )
                ) {
                    $query = "SELECT id FROM {course} where id!=" . SITEID;
                    $courses = $DB->get_fieldset_sql($query);

                    foreach ($courses as $course) {
                        $options[$course] = get_course($course)->fullname;
                    }
                } else if (has_capability(
                    'local/notificationsagent:manageownrule',
                    $coursecontext
                )
                ) {
                    $query = "SELECT id FROM {course} WHERE id = ($COURSE->id)";
                    $courses = $DB->get_fieldset_sql($query);
                    foreach ($courses as $course) {
                        $options[$course] = get_course($course)->fullname;
                    }
                }

                return $options;
            });

        // Action detail filter.
        $filters[] = (new filter(
            text::class,
            'actiondetail',
            new lang_string('actiondetail', 'local_notificationsagent'),
            $this->get_entity_name(),
            "{$reportalias}.actiondetail"
        ))
            ->add_joins($this->get_joins());

        // User filter.
        $filters[] = (new filter(
            autocomplete::class,
            'userfullname',
            new lang_string('users'),
            $this->get_entity_name(),
            "{$narralias}.userid"
        ))
            ->add_joins($this->get_joins())
            ->set_options_callback(static function(): array {
                global $USER, $COURSE, $DB;
                $options = [];
                $coursecontext = \context_course::instance($COURSE->id);
                if (has_capability(
                    'local/notificationsagent:manageallrule',
                    $coursecontext
                )
                ) {
                    $query
                        = "SELECT DISTINCT {user}.id, CONCAT({user}.firstname ,' ', {user}.lastname) AS name
                                             FROM {notificationsagent_report}
                                              JOIN {user} ON {user}.id={notificationsagent_report}.userid";

                    $users = $DB->get_recordset_sql($query);

                    foreach ($users as $user) {
                        $options[$user->id] = $user->name;
                    }
                } else if (has_capability(
                    'local/notificationsagent:viewcourserule',
                    $coursecontext
                )
                ) {
                    $key = implode(',', array_keys(enrol_get_my_courses(['id', 'cacherev'])));
                    $query
                        = 'SELECT DISTINCT {user}.id, CONCAT({user}.firstname ," ", {user}.lastname) AS name
                             FROM {notificationsagent_report}
                              JOIN {user} ON {user}.id={notificationsagent_report}.userid
                           WHERE {notificationsagent_report}.courseid IN (' . $key . ' )';;
                    $users = $DB->get_recordset_sql($query);

                    foreach ($users as $user) {
                        $options[$user->id] = $user->name;
                    }
                } else if (has_capability(
                    'local/notificationsagent:manageownrule',
                    $coursecontext
                )
                ) {
                    $options[$USER->id] = $USER->firstname . ' ' . $USER->lastname;
                }

                return $options;
            });

        return $filters;
    }

    /**
     * Rule join.
     *
     * @return string
     */
    public function rulejoin() {
        $rulealias = $this->get_table_alias('notificationsagent_rule');
        $rulesreportalias = $this->get_table_alias('notificationsagent_report');
        return "JOIN {notificationsagent_rule} {$rulealias}
                    ON {$rulesreportalias}.ruleid = {$rulealias}.id";
    }

    /**
     * Rule action join.
     *
     */
    public function actionjoin() {
        $actionalias = $this->get_table_alias('notificationsagent_action');
        $rulesreportalias = $this->get_table_alias('notificationsagent_report');
        return "JOIN {notificationsagent_action} {$actionalias}
                    ON {$rulesreportalias}.actionid = {$actionalias}.id";
    }
}
