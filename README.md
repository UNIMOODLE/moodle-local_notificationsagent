# Moodle Notifications Agent Plugin

## Introduction
The Moodle Notifications Agent plugin enhances communication within Moodle courses by enabling automatic message delivery based on customizable rules. Developed by the [UNIMOODLE consortium](https://unimoodle.gihub.io) of 16 Spanish universities (Valladolid, Complutense de Madrid, País Vasco/EHU, León, Salamanca, Illes Balears, Valencia, Rey Juan Carlos, La Laguna, Zaragoza, Málaga, Córdoba, Extremadura, Vigo, Las Palmas y Burgos). Notifications Agent helps educators keep students informed about important events efficiently.

[<img src="https://unimoodle.github.io/assets/images/unimoodle-primarylogo-rgb-1200x353.png" height="70px"/>](https://unimoodle.github.io)

[Project's web page](https://unimoodle.github.io/moodle-local_notificationsagent/).

## Key Features
- **Automated Notifications**: Set rules to send messages automatically based on various conditions.
- **User-Friendly Interface**: Create and manage notifications through an intuitive UI.
- **Mobile Compatibility**: Fully functional on mobile devices.
- **Extensible**: Supports additional subplugins for extending conditions and actions.
- **Platform-ready**: Create templates and force rules from site-admin settings.
- **Open Source**: Distributed under an open-source license.

## Who Should Use This?
Ideal for educational institutions and organizations utilizing Moodle, seeking to improve timely and relevant communication with students.

## System Requirements
Compatible with Moodle 4.1 and newer versions.

## Detailed Documentation
For comprehensive usage instructions, visit the [official documentation](https://unimoodle.github.io/moodle-local_notificationsagent/).

## Contribution Guidelines
Contributions are welcome! Please refer to our contributing guidelines on GitHub for more information.

## Credits
This plugin is developed and maintained by the UNIMOODLE consortium. Special thanks to all contributors who have helped in its development.

Notifications Agent was designed by [UNIMOODLE Universities Group](https://unimoodle.github.io/) 

<img src="https://unimoodle.github.io/assets/images/allunimoodle-2383x376.png" height="120px" />

Notifications Agent was implemented by Moodle's Partner [ISYC](https://isyc.com/)

<img src="https://unimoodle.github.io/assets/images/logo-isyc-1.png" height="70px" />

This project was funded by the European Union Next Generation Program.

<img src="https://unimoodle.github.io/assets/images/unidigital-footer2024-1466x187.png" height="70px" />

## License
This project is licensed under the GNU General Public License. Please see the LICENSE file for details.

2023 UNIMOODLE

This program is free software: you can redistribute it and/or modify it under
the terms of the GNU General Public License as published by the Free Software
Foundation, either version 3 of the License, or (at your option) any later
version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY
WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with
this program.  If not, see <https://www.gnu.org/licenses/>.

## Installation Guide
1. Download the plugin from the [GitHub repository](https://github.com/unimoodle/moodle-local_notificationsagent).
2. Unzip the plugin files into the `/local` directory of your Moodle installation.
3. Complete the installation process via the Moodle admin interface.

## Installing via uploaded ZIP file ##

1. Log in to your Moodle site as an admin and go to _Site administration >
   Plugins > Install plugins_.
2. Upload the ZIP file with the plugin code. You should only be prompted to add
   extra details if your plugin type is not automatically detected.
3. Check the plugin validation report and finish the installation.


## Plugin testing

### PHPUNIT

On your Moodle installation run the command to setup the enviroment:
```sh
php admin/tool/phpunit/cli/init.php
```

This command will run all the test in the plug-in and subplugin suites.
```sh
vendor/bin/phpunit --group notificationsagent
```

If we only need to test one of the suites:
```sh
vendor/bin/phpunit --testsuite local_notificationsagent_testsuite
```

Or one particular test:
```sh
vendor/bin/phpunit local/notificationsagent/condition/weekend/tests/weekend_test.php
```

Some test use some uopz funcionality. Install it with the following commad:
```sh
pecl install uopz
```

For further information, visit:

- <https://moodledev.io/general/development/tools/phpunit>
- <https://docs.phpunit.de/>
- <https://pecl.php.net/package/uopz>

### PHPDOC
Generate Phpdoc documentation. Run this command on plugin directory.
```sh
docker run -ti -u $UID:$UID --rm -v $(pwd):/data phpdoc/phpdoc -t phpdoc
```
