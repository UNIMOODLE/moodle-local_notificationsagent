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
 * @package    notificationsaction_bootstrapnotifications
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use notificationsaction_bootstrapnotifications\bootstrapmessages;

/**
 * Observer for the notificationsaction_bootstrapnotifications plugin.
 */
class notificationsaction_bootstrapnotifications_observer {
    /**
     * A function to handle the course_viewed event.
     *
     * @param \core\event\course_viewed $event The course_viewed event object
     *
     * @return false|void
     * @throws coding_exception
     */
    public static function course_viewed(\core\event\course_viewed $event) {
        if ($event->courseid == SITEID) {
            return false;
        }

        $messages = bootstrapmessages::get_records(['userid' => $event->userid, 'courseid' => $event->courseid]);

        foreach ($messages as $message) {
            \core\notification::success($message->get('message'));
            $message->delete();
        }
    }
}
