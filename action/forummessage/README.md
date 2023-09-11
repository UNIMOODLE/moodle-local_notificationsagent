# Forum Message #

This plugin is part of the Notifications Agent plugin.

The purpose of the plugin is to post a message to a forum when a series of conditions are met.

## Requisites ##

### Setup manual webservice ###
Site administration ‣ Web services ‣ General view (admin/settings.php?section=webservicesoverview)

1. Enable web services - Yes
2. Enable protocols - rest
3. Crete role 'Webservice'. Define a new role called 'Webservice' with the required capabilities:</br>
```
moodle/site:viewuseridentity 
moodle/user:viewalldetails
moodle/webservice:createtoken 
webservice/rest:use
```
4. Create a specific user.
   Crete a new user called "your_ws_username" with email "youremail" and a generated random password.
5. Add user in new role globally, with the necessary permissions (webservice/rest:use) (admin/roles/assign.php?contextid=1)

6. Select a service.
   Create a new external service called "your_ws_name" checking "Enabled" and "Authorised users only" options.
7. Add functions. Add the required WS functions (admin/webservice/service_functions.php?id=):
```
mod_forum_add_discussion
```
8. Select a specific user. Select as "Authorized user" of "your_ws_name" the webservice user.
9. Create a token for a user. Select the webservice user and "your_ws_name" webservice without IP restrictions nor dates.

The generated token is located in Site administration ‣ Web services ‣ Manage tokens

#### NOTE ####

It is possible that using "your_ws_name" we obtain a 'Policy not accepted' error, depending on how is Moodle setup. Login with 'your_ws_username' and accept them, or in the database change to 1 in 'policyagreed' field in the table 'mdl_user'.

## Installing via uploaded ZIP file ##

1. Log in to your Moodle site as an admin and go to _Site administration >
   Plugins > Install plugins_.
2. Upload the ZIP file with the plugin code. You should only be prompted to add
   extra details if your plugin type is not automatically detected.
3. Check the plugin validation report and finish the installation.

## Installing manually ##

The plugin can be also installed by putting the contents of this directory to

    {your/moodle/dirroot}/local/forummessage

Afterwards, log in to your Moodle site as an admin and go to _Site administration >
Notifications_ to complete the installation.

Alternatively, you can run

    $ php admin/cli/upgrade.php

to complete the installation from the command line.

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
