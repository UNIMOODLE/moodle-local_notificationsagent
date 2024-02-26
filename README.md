# Notifications Agent #

Main plug-in in the Notification Agent project

## Installing via uploaded ZIP file ##

1. Log in to your Moodle site as an admin and go to _Site administration >
   Plugins > Install plugins_.
2. Upload the ZIP file with the plugin code. You should only be prompted to add
   extra details if your plugin type is not automatically detected.
3. Check the plugin validation report and finish the installation.

## Installing manually ##

The plugin can be also installed by putting the contents of this directory to

    {your/moodle/dirroot}/local/notificationsagent

Afterwards, log in to your Moodle site as an admin and go to _Site administration >
Notifications_ to complete the installation.

Alternatively, you can run

    $ php admin/cli/upgrade.php

to complete the installation from the command line.

## Plugin testing

### PHPUNIT

On your Moodle installation run the command to setup the enviroment:
```sh
php admin/tool/phpunit/cli/init.php
```

This command will run all the test in the plug-in and subplugin suites.
```sh
vendor/bin/phpunit --testdox --group notificationsagent
```

If we only need to test one of the suites:
```sh
vendor/bin/phpunit --testdox --testsuite local_notificationsagent_testsuite
```

Or one particular test:
```sh
vendor/bin/phpunit --testdox local/notificationsagent/condition/weekend/tests/weekend_test.php
```

Some test use some uopz funcionality. Install it with the following commad:
```sh
pecl install uopz
```

For further information, visit:

- <https://moodledev.io/general/development/tools/phpunit>
- <https://docs.phpunit.de/>
- <https://pecl.php.net/package/uopz>

## License ##

2023 ISYC

This program is free software: you can redistribute it and/or modify it under
the terms of the GNU General Public License as published by the Free Software
Foundation, either version 3 of the License, or (at your option) any later
version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY
WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with
this program.  If not, see <https://www.gnu.org/licenses/>.
