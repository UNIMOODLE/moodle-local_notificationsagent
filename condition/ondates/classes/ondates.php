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
 * @package    notificationscondition_ondates
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace notificationscondition_ondates;

use local_notificationsagent\evaluationcontext;
use local_notificationsagent\notificationconditionplugin;
use core_calendar\type_factory;

/**
 * Ondates supluging class
 */
class ondates extends notificationconditionplugin {
    /**
     * Subplugin name
     */
    public const NAME = 'ondates';
    /**
     * Start date
     */
    public const STARTDATE = 'startdate';
    /**
     * End date
     */
    public const ENDDATE = 'enddate';

    /**
     * Subplugin title
     *
     * @return \lang_string|string
     */
    public function get_title() {
        return get_string('conditiontext', 'notificationscondition_ondates');
    }

    /**
     *  Subplugins elements
     *
     * @return string[]
     */
    public function get_elements() {
        return ['FFFF', 'FFFF'];
    }

    /**
     * Evaluates this condition using the context variables or the system's state and the complementary flag.
     *
     * @param evaluationcontext $context |null collection of variables to evaluate the condition.
     *                                    If null the system's state is used.
     *
     * @return bool true if the condition is true, false otherwise.
     */
    public function evaluate(evaluationcontext $context): bool {
        $timeaccess = $context->get_timeaccess();
        $params = $context->get_params();
        $data = json_decode($params, true);
        $startdate = json_decode($data[self::STARTDATE]);
        $enddate = json_decode($data[self::ENDDATE]);

        return self::is_ondates($timeaccess, $startdate, $enddate);
    }

    /**
     * Estimate next time when this condition will be true.
     *
     * @param evaluationcontext $context
     *
     * @return false|int|mixed|null
     */
    public function estimate_next_time(evaluationcontext $context) {
        global $CFG;
        $timeaccess = $context->get_timeaccess();
        $params = $context->get_params();
        $data = json_decode($params, true);
        $startdate = json_decode($data[self::STARTDATE]);
        $enddate = json_decode($data[self::ENDDATE]);

        $isondates = self::is_ondates($timeaccess, $startdate, $enddate);
        // Condition.
        if (!$context->is_complementary()) {
            if ($isondates) {
                return time();
            } else {
                if ($timeaccess > $enddate || $enddate < $startdate) {
                    return false;
                } else if ($startdate < $enddate) {
                    if ($timeaccess >= $startdate) {
                        return time();
                    } else {
                        return $startdate;
                    }
                }
            }
        } else {
            if ($isondates) {
                return ($enddate + 1);
            } else {
                if ($enddate < $startdate) {
                    return time();
                } else if ($startdate < $enddate) {
                    if ($timeaccess < $startdate || $timeaccess > $enddate) {
                        return time();
                    }
                }
            }
        }
    }

    /**
     * Get the UI elements for the subplugin.
     *
     * @param \MoodleQuickForm $mform The form to which the elements will be added.
     * @param int $courseid The course identifier.
     * @param string $type The type of the notification plugin.
     */
    public function get_ui($mform, $courseid, $type) {
        $this->get_ui_title($mform, $type);

        $startdateselector = $mform->createElement(
            'date_selector',
            $this->get_name_ui(self::STARTDATE),
            get_string('editrule_condition_element_startdate', 'notificationscondition_ondates')
        );

        $enddatesselector = $mform->createElement(
            'date_selector',
            $this->get_name_ui(self::ENDDATE),
            get_string('editrule_condition_element_enddate', 'notificationscondition_ondates')
        );

        $mform->insertElementBefore($startdateselector, 'new' . $type . '_group');
        $mform->insertElementBefore($enddatesselector, 'new' . $type . '_group');
    }

    /**
     * Capability for subplugin
     *
     * @param \context $context
     *
     * @return bool
     */
    public function check_capability($context) {
        return has_capability('local/notificationsagent:ondates', $context);
    }

    /**
     * Convert parameters for the notification plugin.
     *
     * This method should take an identifier and parameters for a notification
     * and convert them into a format suitable for use by the plugin.
     *
     * @param mixed $params The parameters associated with the notification.
     *
     * @return mixed The converted parameters.
     */
    public function convert_parameters($params) {
        $params = (array) $params;
        $startdate = $params[$this->get_name_ui(self::STARTDATE)] ?? 0;
        $enddate = $params[$this->get_name_ui(self::ENDDATE)] ?? 0;
        $enddate = strtotime('tomorrow', $enddate) - 1;
        $this->set_parameters(json_encode([self::STARTDATE => $startdate, self::ENDDATE => $enddate]));
        return $this->get_parameters();
    }

    /**
     * Process and replace markups in the supplied content.
     *
     * This function should handle any markup logic specific to a notification plugin,
     * such as replacing placeholders with dynamic data, formatting content, etc.
     *
     * @param array $content The content to be processed, passed by reference.
     * @param int $courseid The ID of the course related to the content.
     * @param mixed $options Additional options if any, null by default.
     *
     * @return void Processed content with markups handled.
     */
    public function process_markups(&$content, $courseid, $options = null) {
        $jsonparams = json_decode($this->get_parameters());
        $date1 = userdate($jsonparams->{self::STARTDATE});
        $date2 = userdate($jsonparams->{self::ENDDATE});
        $elements = $this->get_elements();
        $humanvalue = $this->replace_first($elements[0], $date1, $this->get_title());
        $humanvalue = $this->replace_first($elements[1], $date2, $humanvalue);
        $content[] = $humanvalue;
    }

    /**
     * Replace first instance of string
     *
     * @param string $search String you want to change.
     * @param string $replace String to be changed.
     * @param string $subject Main string.
     *
     * @return string String changed after replace.
     */
    public function replace_first($search, $replace, $subject) {
        return implode($replace, explode($search, $subject, 2));
    }

    /**
     * Whether a subplugins is generic
     *
     * @return true
     */
    public function is_generic() {
        return true;
    }

    /**
     * Determine if is ondates given starttime, endtime and current time.
     *
     * @param int $today
     * @param int $startdate
     * @param int $enddate
     *
     * @return bool
     */
    public static function is_ondates($today, $startdate, $enddate): bool {
        $isondates = false;
        $today >= $startdate && $today <= $enddate ? $isondates = true : '';
        return $isondates;
    }

    /**
     * Validation subplugin
     * If this method overrides, call to parent::validation
     *
     * @param int $courseid Course id
     * @param array $array The array to be modified by reference. If is null, validation is not being called from the
     *                                  form and return directly
     * @param bool $onlyverifysiteid Default false. If true, only SITEID is verified
     *
     * @return bool
     */
    public function validation($courseid, &$array = null, $onlyverifysiteid = false) {
        if (($validation = parent::validation($courseid, $array)) === 'break') {
            return true;
        }

        // If it false from parent and $array is null, return.
        if (is_null($array) && !$validation) {
            return $validation;
        }

        $data = json_decode($this->get_parameters(), true);

        $startdatedata = $data[self::STARTDATE];
        $enddatedata = $data[self::ENDDATE];

        if ($startdatedata > $enddatedata) {
            $array[$this->get_name_ui(self::STARTDATE)] = get_string(
                'validation_editrule_form_supported_invalid_date',
                'notificationscondition_ondates'
            );
            $validation = false;
        }
        return $validation;
    }

    /**
     * load array data for form
     *
     * @return array
     */
    public function load_dataform() {
        $params = json_decode($this->get_parameters(), true);
        $startdate = $params[self::STARTDATE];
        $enddate = $params[self::ENDDATE];
        $arrayparams[$this->get_name_ui(self::STARTDATE)] = [
                'day' => date('d', $startdate), 'month' => date('m', $startdate), 'year' => date('Y', $startdate),
        ];
        $arrayparams[$this->get_name_ui(self::ENDDATE)] = [
                'day' => date('d', $enddate), 'month' => date('m', $enddate), 'year' => date('Y', $enddate),
        ];

        return $arrayparams;
    }

    /**
     * Update any necessary ids and json parameters in the database.
     * It is called near the completion of course restoration.
     *
     * @param string $restoreid Restore identifier
     * @param integer $courseid Course identifier
     * @param \base_logger $logger Logger if any warnings
     *
     * @return bool False if restore is not required
     */
    public function update_after_restore($restoreid, $courseid, \base_logger $logger) {
        return false;
    }
}
