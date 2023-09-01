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

/**
 * Plugin administration pages are defined here.
 *
 * @package     local_notificationsagent
 * @category    admin
 * @copyright   2023 ISYC
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/notificationsagent/adminlib.php');

if ($hassiteconfig) {

    $ADMIN->add('localplugins',
        new admin_category('local_notificationsagent_settings', get_string('pluginname',
                'local_notificationsagent')));
    $settingspage = new admin_settingpage('manage_notificationsagent', get_string('pluginname',
        'local_notificationsagent'));

    if ($ADMIN->fulltree) {
        // TODO: Define actual plugin settings page and add it to the tree - {@link https://docs.moodle.org/dev/Admin_settings}.
        // El administrador podrá desactivar el uso para los estudiantes mediante una opción en los
        // “settings” del servidor.

        $settingdisableuseruse = new admin_setting_configcheckbox(
            'local_notificationsagent/disable_user_use',
            get_string('disable_user_use', 'local_notificationsagent'),
            get_string('disable_user_use_desc', 'local_notificationsagent'),
            false
        );
        $settingspage->add($settingdisableuseruse);

        $settingstracelog = new admin_setting_configcheckbox('notificationsagent/tracelog',
            get_string('tracelog', 'local_notificationsagent'),
            get_string('tracelog_desc', 'local_notificationsagent'), false);

        $settingspage->add($settingstracelog);
    }

    $ADMIN->add('localplugins', $settingspage);

    // Add subplugins management in settings view.

    $ADMIN->add('localplugins', new admin_category('notificationsactionplugins',
        get_string('actionplugins', 'local_notificationsagent')));
    $ADMIN->add('notificationsactionplugins',
        new notificationsagent_admin_page_manage_notificationsagent_plugins('notificationsaction'));
    $ADMIN->add('localplugins', new admin_category('notificationsconditionplugins',
        get_string('conditionplugins', 'local_notificationsagent')));
    $ADMIN->add('notificationsconditionplugins',
        new notificationsagent_admin_page_manage_notificationsagent_plugins('notificationscondition'));

    foreach (core_plugin_manager::instance()->get_plugins_of_type('notificationsaction') as $plugin) {
        /** @var \local_notificationsagent\plugininfo\notificationsaction $plugin */
        $plugin->load_settings($ADMIN, 'notificationsactionplugins', $hassiteconfig);
    }

    foreach (core_plugin_manager::instance()->get_plugins_of_type('notificationscondition') as $plugin) {
        /** @var \local_notificationsagent\plugininfo\notificationscondition $plugin */
        $plugin->load_settings($ADMIN, 'notificationsconditionplugins', $hassiteconfig);
    }

}

