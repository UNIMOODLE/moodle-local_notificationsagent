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
require_once('notificationplugin.php');
require_once('plugininfo/notificationsbaseinfo.php');

use local_notificationsagent\plugininfo\notificationsbaseinfo;
abstract class notificationactionplugin extends notificationplugin {

    const PLACEHOLDERS
        = array(
            'User_FirstName', 'User_LastName_s', 'User_Email', 'User_Username', 'User_Address', 'Course_FullName', 'Course_Url'
        );

    abstract public function get_title();
    abstract public function get_elements();

    /**
     * Returns the main plugin type qualifier.
     * @return string "condition" or "action".
     */
    final public function get_type() {
        return parent::CAT_ACTION;
    }
    /** Returns subtype string for building classnames, filenames, modulenames, etc.
     * @return string subplugin type. "messageagent"
     */
    abstract public function get_subtype();
    /*
     * Check whether a user has capabilty to use an action.
     */
    abstract public function check_capability();

    /*
     * Show placeholder where needed
     */
    public function placeholders(&$mform, $idaction) {

        $mform->addElement('html', "<div class='form-group row fitem'> <div class='col-md-3'></div>
        <div class='col-md-9'><div class='notificationvars' id='notificationvars_".$idaction."'>");
        $optioncount = 0;
        foreach (self::PLACEHOLDERS as $option) {
            $mform->addElement('html', "<a href='#' data-text='$option' class='clickforword'><span>$option</span></a> ");
            $optioncount++;
        }
        $mform->addElement('html', "</div></div></div>");
    }

    public static function create_subplugins($records) {

        $subplugins = array();
        global $DB;
        foreach ($records as $record) {
            // TODO SET CACHE.
            $rule = $DB->get_record('notificationsagent_rule', ['ruleid' => $record->ruleid]);
            $subplugin = notificationsbaseinfo::instance($rule, $record->type, $record->pluginname);
            if (!empty($subplugin)) {
                $subplugin->set_pluginname($record->pluginname);
                $subplugin->set_id($record->id);
                $subplugin->set_parameters($record->parameters);
                $subplugin->set_type($record->type);
                $subplugin->set_ruleid($record->ruleid);

                $subplugins[] = $subplugin;
            }
        }
        return $subplugins;
    }

    public static function create_subplugin($id) {
        global $DB;
        // Find type of subplugin.
        $record = $DB->get_record('notificationsagent_action', array('id' => $id));
        $subplugins = create_subplugins(array($record));
        return $subplugins[0];
    }

}


