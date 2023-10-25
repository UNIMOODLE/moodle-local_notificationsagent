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
class Bootstrapnotifications_action {
    private int $relateduserid;
    private string $placeholders;

    public function __construct ($relateduserid, $other) {
        $this->relateduserid = $relateduserid;
        $this->placeholders = $other;
    }

    public function add_bootstrap_notifications() {
        global $USER;
        $placeholdershuman = json_decode($this->placeholders);

        if ($USER->id == $this->relateduserid) {
            echo \core\notification::success(format_text($placeholdershuman->text));
        }
    }

}
