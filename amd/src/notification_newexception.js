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

/**
 * Module javascript to place new exceptions.
 *
 * @module   mod_simplemod/notification_newexception
 * @category  Classes - autoloading
 * @copyright 2023, ISYC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 define([], function() {

    return {

        init: function() {
            let newExceptionButton = document.querySelector('[id*="newconditionexception_button"]');
            newExceptionButton.addEventListener('click', function() {
                let newExceptionSelect = document.querySelector('[id*="newconditionexception_select"]');
                let $title = newExceptionSelect.options[newExceptionSelect.selectedIndex].text;
                let $formDefault = [];
                let formNotif = document.querySelector('form[action*="notificationsagent"].mform');
                Array.from(formNotif.elements).forEach((element) => {
                    if(element.id){
                        $formDefault.push("[id]"+element.id+"[/id][value]"+element.value+"[/value]");
                    }
                });
                let $elements = JSON.parse(newExceptionSelect.value.split(':')[1]);
                let $name = newExceptionSelect.options[newExceptionSelect.selectedIndex].value.substring(0,
                  newExceptionSelect.options[newExceptionSelect.selectedIndex].value.indexOf(':['));
                let data = {
                    key: 'exception',
                    action: 'new',
                    title: $title,
                    elements: $elements,
                    name : $name,
                    formDefault: $formDefault
                };

                $.ajax({
                    type: "POST",
                    url: window.location.pathname.substring(0, '/local/notificationsagent/editrule.php'),
                    data: data,
                    success: function(event) {
                        if(event.state === 'success'){
                            window.location.reload();
                        }
                    },
                    error: function(XMLHttpRequest, textStatus, errorThrown) {
                        console.log("Status: " + textStatus);
                        console.log(errorThrown);
                    },
                    dataType: 'json'
                });
            });
        }
    }
});
