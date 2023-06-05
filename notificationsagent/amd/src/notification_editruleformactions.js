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
 * Module javascript to place conditionactions.
 *
 * @module   mod_simplemod/notification_conditionactions
 * @category  Classes - autoloading
 * @copyright 2023, ISYC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 define([], function() {

    return {

        init: function() {

            let buttonActions = document.querySelectorAll("h5>i.icon[id*='_']:not(.disabled)");
            buttonActions.forEach( function(button) {
                button.addEventListener('click', function(event) {
                    let $typesection = event.target.id.split('_')[0].replace(/[0-9]/g, '');
                    let $actionelement = event.target.id.split('_')[1];
                    let $keyelementsession = parseInt(event.target.id.replace($typesection,'').replace('_remove',''));

                    let data = {
                        key: 'NOTIFICATIONS_'+$typesection.toUpperCase()+'S',
                        action: $actionelement,
                        keyelementsession: $keyelementsession
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
            });
        }
    }
});