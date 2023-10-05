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
 *
 * @module   local_notificationsagent/statusrule
 * @copyright 2023, ISYC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 define(['core/str','core/notification'], function(str, notification) {

    return {

        init: function() {
            $('#statusRuleModal').on('show.bs.modal', function (event) {
                $button = $(event.relatedTarget);
                var idrule = $button.data('idrule');
                var textstatus = $button.data('textstatus');

                var modal = $(this);
                var strings = [
                    {
                        key: 'statustitle',
                        component: 'local_notificationsagent',
                        param: {
                            textstatus: textstatus,
                            title: $('#card-'+idrule+' .name').text()
                        },
                    },
                    {
                        key: 'statuscontent',
                        component: 'local_notificationsagent',
                        param: {
                            textstatus: textstatus.toLowerCase(),
                            title: $('#card-'+idrule+' .name').text()
                        },
                    }
                ];
                str.get_strings(strings).then(function (results) {
                    modal.find('.modal-title').text(results[0]);
                    modal.find('.modal-body > div').text(results[1]);
                });
            });

            $('#statusRuleModal #acceptStatusRule').on('click', function(){
                $('#statusRuleModal').modal('hide');

                //Cuando se haga update del estado en BD correctamente de la regla
                var strings = [
                    {
                        key: 'statusacceptactivated',
                        component: 'local_notificationsagent',
                    },
                    {
                        key: 'statusacceptpaused',
                        component: 'local_notificationsagent',
                    }
                ];
                str.get_strings(strings).then(function (results) {
                    $button.addClass('d-none');
                    if($button.data('valuestatus')){
                        $('a[data-idrule="'+$button.data('idrule')+'"][data-target="#statusRuleModal"][data-valuestatus="0"]').removeClass('d-none');
                        notification.addNotification({
                            message: results[0],
                            type: "info"
                        });
                    }else{
                        $('a[data-idrule="'+$button.data('idrule')+'"][data-target="#statusRuleModal"][data-valuestatus="1"]').removeClass('d-none');
                        notification.addNotification({
                            message: results[1],
                            type: "info"
                        });
                    }
                });
            });
        },
    };
});
