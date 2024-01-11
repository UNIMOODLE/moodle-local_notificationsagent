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
// Project implemented by the \"Recovery, Transformation and Resilience Plan.
// Funded by the European Union - Next GenerationEU\".
//
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
defined('MOODLE_INTERNAL') || die();
require_once('notificationplugin.php');
require_once('plugininfo/notificationsbaseinfo.php');

use local_notificationsagent\plugininfo\notificationsbaseinfo;
use local_notificationsagent\Rule;
use notificationsagent\notificationsagent;
abstract class notificationactionplugin extends notificationplugin {

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
    abstract public function check_capability($context);

    /*
     * Show placeholder where needed
     */
    public function placeholders(&$mform, $idaction, $typeitem) {

        $mform->addElement('html', "<div class='form-group row fitem'>
        <div class='col-md-12'><div class='notificationvars' id='notificationvars_".$idaction."_".$typeitem."'>");
        foreach (Rule::get_placeholders() as $option) {
            $clipboardtarget = "#notificationvars_" . $idaction . "_" . $typeitem . "_" . $option;
            $mform->addElement('html', "<a href='#' id='notificationvars_" . $idaction . "_" . $typeitem . "_" . $option
                . "' data-text='$option' data-action='copytoclipboard' data-clipboard-target='$clipboardtarget'
                class='clickforword'><span>{" . $option . "}</span></a> "
            );
        }
        $mform->addElement('html', "</div></div></div>");
    }

    public static function create_subplugins($records) {

        $subplugins = [];
        global $DB;
        foreach ($records as $record) {
            $rule = $DB->get_record('notificationsagent_rule', ['id' => $record->ruleid]);
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
        $record = $DB->get_record('notificationsagent_action', ['id' => $id]);
        $subplugins = self::create_subplugins([$record]);
        return $subplugins[0];
    }

    abstract public function execute_action($context, $params);

    /**
     * Check if the action will be sent once or not
     * @param integer $userid User id
     *
     * @return bool $sendonce Will the action be sent once?
     */
    public function is_send_once($userid) {
        return $userid == notificationsagent::GENERIC_USERID ? false : true;
    }

    /**
     * Gets the message to send depending on the timesfired of the rule and the user
     * @param object $context Evaluation Context
     * @param string $message Message
     *
     * @return string $result Message to sent
     */
    public static function get_message_by_timesfired($context, $message) {
        $delimiter = '/{' . Rule::SEPARATOR . '}|&lt;!-- pagebreak --&gt;/';

        $messagesplit = preg_split($delimiter, $message);

        if ($context->get_rule()->get_timesfired() == Rule::MINIMUM_EXECUTION) {
            $messageindex = rand(0, count($messagesplit) - 1);
        } else {
            $messageindex = min($context->get_usertimesfired(), count($messagesplit)) - 1;
        }
        $result = $messagesplit[$messageindex];

        return $result;
    }
}
