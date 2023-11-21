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
 * @module    local_notificationsagent/rule/delete
 * @copyright 2023 ISYC <soporte@isyc.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {get_string as getString, get_strings as getStrings} from 'core/str';
import Notification from 'core/notification';
import {checkRuleContext, deleteRule} from 'local_notificationsagent/rule/repository';

/**
 * Registers the click event listener for the delete button of a rule
 */
export const init = () => {
    $('#deleteRuleModal').on('show.bs.modal', function (e) {
        ruleButton = $(e.relatedTarget);
        let id = ruleButton.data('ruleid');
        let ruleType = ruleButton.data('type');

        const modal = $(this);

        hasRuleContext(ruleButton).then(hasContext => {
            if (hasContext) {
                requiredStrings = [
                    {key: 'deletetitle', component: 'local_notificationsagent', param: {
                        title: $('#card-' + id + ' .name').text(),
                        type: ruleType,
                    }},
                    {key: 'deletecontent_hascontext', component: 'local_notificationsagent', param: {
                        title: $('#card-' + id + ' .name').text(),
                        type: ruleType,
                    }},
                ];
            } else {
                requiredStrings = [
                    {key: 'deletetitle', component: 'local_notificationsagent', param: {
                        title: $('#card-' + id + ' .name').text(),
                        type: ruleType,
                    }},
                    {key: 'deletecontent_nocontext', component: 'local_notificationsagent', param: {
                        title: $('#card-' + id + ' .name').text(),
                        type: ruleType,
                    }},
                ];
            }
    
            getStrings(requiredStrings).then(([ruleTitle, ruleContent]) => {
                modal.find('.modal-title').text(ruleTitle);
                modal.find('.modal-body > div').text(ruleContent);
            });
        });
    });

    $('#deleteRuleModal #acceptDeleteRule').on('click', (e) => {
        e.preventDefault();

        setDeleteRule(ruleButton);
    });
};

/**
 * Check if the rule has any other context before deleting
 * 
 * @param {HTMLElement} ruleButton
 * @returns {boolean} Has it context?
 */
const hasRuleContext = async(ruleButton) => {
    let id = ruleButton.data('ruleid');
    let hasContext = false;

    try {
        response = await checkRuleContext(id);
        
        if ($.isEmptyObject(response['warnings'])) {
            hasContext = response['hascontext'];
        } else {
            Notification.addNotification({
                message: response['warnings'][0].message,
                type: 'error'
            });
        }
    } catch (exception) {
        Notification.exception(exception);
    }

    return hasContext;
};


/**
 * Deletes a given rule
 * 
 * @param {HTMLElement} ruleButton
 * @returns {Promise<void>}
 */
const setDeleteRule = async(ruleButton) => {
    let id = ruleButton.data('ruleid');

    $('#deleteRuleModal').modal('hide');

    try {
        response = await deleteRule(id);
        
        if ($.isEmptyObject(response['warnings'])) {
            ruleButton.addClass('d-none');
    
            getString('deleteaccept', 'local_notificationsagent').then(ruleDeleted => {
                ruleButton.addClass('d-none');
        
                $('div[id="card-' + id + '"]').remove();
               
                Notification.addNotification({
                    message: ruleDeleted,
                    type: 'info'
                }); 
            });
        } else {
            Notification.addNotification({
                message: response['warnings'][0].message,
                type: 'error'
            });
        }
    } catch (exception) {
        Notification.exception(exception);
    }
};