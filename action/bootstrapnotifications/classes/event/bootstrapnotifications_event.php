<?php
// This file is part of the Notifications Agent plugin for Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace notificationsaction_bootstrapnotifications\event;

/**
 * Event triggered when conditions are fullfiled and action is notification to particular user.
 */
class notificationsagent_bootstrapnotifications_event extends \core\event\base {
    /**
     * @inheritDoc
     */
    protected function init() {
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['crud'] = 'r';
        $this->data['other'] = $this->other;
        $this->data['relateduserid'] = $this->relateduserid;
    }

    /**
     * Returns the name of the event.
     *
     * @return string
     */
    public static function get_name() {
        return "bootstrap_notifications_event";
    }

    /**
     * Returns a short description for the event.
     *
     * @return string
     */
    public function get_description() {
        // TODO.
        return "Text: '$this->other' ";
    }

}
