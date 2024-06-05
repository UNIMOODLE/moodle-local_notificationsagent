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

declare(strict_types=1);

namespace local_notificationsagent\local\filters;

use context_course;
use MoodleQuickForm;
use core_reportbuilder\local\filters\autocomplete as coreautocomplete;

/**
 * Autocomplete class
 */
class autocomplete extends coreautocomplete {

    /**
     * Overriding Setup form of core autocomplete class
     *
     * @param MoodleQuickForm $mform
     *
     * @return void
     */
    public function setup_form(MoodleQuickForm $mform): void {
        $operatorlabel = get_string('filterfieldvalue', 'core_reportbuilder', $this->get_header());

        $multiple = $this->can_select_multiple($this->name);

        if (!$multiple) {
            $values = $this->get_select_options();
        } else {
            $values = [0 => null] + $this->get_select_options();
        }

        $options = ['multiple' => $multiple, 'noselectionstring ' => ''];

        $mform->addElement('autocomplete', $this->name . '_values', $operatorlabel, $values, $options)
            ->setHiddenLabel(true);

        if (!$multiple) {
            $mform->addRule($this->name . '_values', get_string('err_required', 'form'), 'required');
        }
    }

    /**
     * Get if a selector can be mutlple.
     * This functions avoids the fact that a user can set an empty value in the autocomplete selector and see parts of the report
     * that the  user is not supposed to.
     *
     * @param string $name
     *
     * @return bool
     */
    private function can_select_multiple(string $name): bool {
        global $COURSE;
        $multiple = false;
        $context = context_course::instance($COURSE->id);

        // Let choose any rule in rule selector for this capability.
        if ($name == 'rule:rulename'
            && has_capability(
                'local/notificationsagent:viewcourserule',
                $context
            )
        ) {
            $multiple = true;
        }

        // Let choose any course in course selector for this capability.
        if ($name == 'rule:courseselector'
            && has_capability(
                'local/notificationsagent:manageallrule',
                $context
            )
        ) {
            $multiple = true;
        }

        // Let choose any user in user selector for this capability.
        // Students only see themselves.
        if ($name == 'rule:userfullname'
            && has_capability(
                'local/notificationsagent:viewcourserule',
                $context
            )
        ) {
            $multiple = true;
        }

        return $multiple;
    }
}
