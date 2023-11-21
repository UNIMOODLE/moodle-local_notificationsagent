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
 * Module javascript to place new actions.
 *
 * @module   mod_simplemod/notification_newaction
 * @category  Classes - autoloading
 * @copyright 2023, ISYC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 require(['core/copy_to_clipboard']);

 define([], function() {

    return {

        init: function() {
            let newActionButton = document.querySelector('[id*="newaction_button"]');
            newActionButton.addEventListener('click', function() {
                let newActionSelect = document.querySelector('[id*="newaction_select"]');
                let $title = newActionSelect.options[newActionSelect.selectedIndex].text;
                let $formDefault = [];

                if (window.location.href.includes('action=edit')) {
                    let url = new URL(window.location.href);
                    var ruleid = url.searchParams.get('ruleid');
                    if (ruleid) {
                        $formDefault.push("[id]ruleid[/id][value]"+ruleid+"[/value]");
                    }
                }

                let formNotif = document.querySelector('form[action*="notificationsagent"].mform');
                Array.from(formNotif.elements).forEach((element) => {
                    if(element.id){
                        $formDefault.push("[id]"+element.id+"[/id][value]"+element.value+"[/value]");
                    }
                });
                let $elements = JSON.parse(newActionSelect.value.split(':')[1]);
                let $name = newActionSelect.options[newActionSelect.selectedIndex].value.substring(0, newActionSelect.options[newActionSelect.selectedIndex].value.indexOf(':['));
                let data = {
                    key: 'action',
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
