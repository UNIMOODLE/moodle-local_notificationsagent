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
 * @package     local_notificationsagent
 * @copyright   2023 Proyecto UNIMOODLE
 * @author      UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author      ISYC <soporte@isyc.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @category    admin
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/notificationsagent/adminlib.php');

if ($hassiteconfig) {
    $settingspage = new admin_settingpage(
        'manage_notificationsagent', get_string(
            'settings',
            'local_notificationsagent'
        )
    );

    if ($ADMIN->fulltree) {
        $settingdisableuseruse = new admin_setting_configcheckbox(
            'local_notificationsagent/disable_user_use',
            get_string('disable_user_use', 'local_notificationsagent'),
            get_string('disable_user_use_desc', 'local_notificationsagent'),
            false
        );
        $settingspage->add($settingdisableuseruse);

        $settingstartdate = new admin_setting_configtextarea(
            'local_notificationsagent/startdate',
            get_string('startdate', 'local_notificationsagent'),
            get_string('startdate_desc', 'local_notificationsagent'),
            'mod_assign|assign|allowsubmissionsfromdate|duedate
mod_bigbluebuttonbn|bigbluebuttonbn|openingtime
mod_chat|chat|chattime|
mod_choice|choice|timeopen|timeclose
mod_data|data|timeavailablefrom|timeavailableto
mod_feedback|feedback|timeopen|timeclose
mod_forum|forum|duedate|cutoffdate
mod_glossary|glossary|assesstimestart|assesstimefinish
mod_lesson|lesson|available|deadline
mod_quiz|quiz|timeopen|timeclose
mod_scorm|scorm|timeopen|timeclose
mod_workshop|workshop|submissionstart|submissionend',
            PARAM_RAW,
            10,
            15
        );
        $settingspage->add($settingstartdate);

        $settingstracelog = new admin_setting_configcheckbox(
            'notificationsagent/tracelog',
            get_string('tracelog', 'local_notificationsagent'),
            get_string('tracelog_desc', 'local_notificationsagent'), false
        );

        $settingspage->add($settingstracelog);
    }

    $ADMIN->add(
        'localplugins', new admin_category(
            'notificationscategory',
            get_string('pluginname', 'local_notificationsagent')
        )
    );

    $ADMIN->add('notificationscategory', $settingspage);
    $ADMIN->add(
        'notificationscategory', new admin_externalpage(
            'notificationsexternalpage', get_string(
            'menu',
            'local_notificationsagent'
        ), $CFG->wwwroot . '/local/notificationsagent/index.php'
        )
    );

    // Add subplugins management in settings view.

    $ADMIN->add(
        'notificationscategory', new admin_category(
            'notificationsactionplugins',
            get_string('actionplugins', 'local_notificationsagent')
        )
    );
    $ADMIN->add(
        'notificationsactionplugins',
        new notificationsagent_admin_page_manage_notificationsagent_plugins('notificationsaction')
    );
    $ADMIN->add(
        'notificationscategory', new admin_category(
            'notificationsconditionplugins',
            get_string('conditionplugins', 'local_notificationsagent')
        )
    );
    $ADMIN->add(
        'notificationsconditionplugins',
        new notificationsagent_admin_page_manage_notificationsagent_plugins('notificationscondition')
    );

    foreach (core_plugin_manager::instance()->get_plugins_of_type('notificationsaction') as $plugin) {
        /** @var \local_notificationsagent\plugininfo\notificationsaction $plugin */
        $plugin->load_settings($ADMIN, 'notificationsactionplugins', $hassiteconfig);
    }

    foreach (core_plugin_manager::instance()->get_plugins_of_type('notificationscondition') as $plugin) {
        /** @var \local_notificationsagent\plugininfo\notificationscondition $plugin */
        $plugin->load_settings($ADMIN, 'notificationsconditionplugins', $hassiteconfig);
    }

    $ADMIN->add(
        'notificationscategory', new admin_externalpage(
            'notificationsreport', get_string(
            'report',
            'local_notificationsagent'
        ), $CFG->wwwroot . '/local/notificationsagent/report.php'
        )
    );

    $ADMIN->add(
        'reports', new admin_externalpage(
            'notificationsagent', get_string('report', 'local_notificationsagent'),
            "$CFG->wwwroot/local/notificationsagent/report.php"
        )
    );

}

