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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
abstract class notificationplugin {

    const CONFIG_DISABLED = 'disabled';
    const CONFIG_ENABLED = 'enabled';
    const CAT_ACTION = 'Action';
    const CAT_CONDITION = 'Condition';

    /**
     * Returns the main plugin type qualifier.
     * @return string "condition" or "action".
     */
    abstract public function get_type();
    abstract public function get_title();
    abstract public function get_elements();

    /** Returns subtype string for building classnames, filenames, modulenames, etc.
     * @return string subplugin type. "messageagent"
     */
    abstract public function get_subtype();

    /** Returns the name of the plugin
     * @return string
     */
    abstract public function get_name();
    abstract public function get_ui($mform, $id, $courseid);

}
