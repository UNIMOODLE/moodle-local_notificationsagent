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
import {unlinkRule, deleteRule} from 'local_notificationsagent/rule/repository';

/**
 * Types of rule deletion
 * 
 * @type {{RULE_DELETE_TYPE: boolean}}
 */
const RULE_DELETE_TYPE = {
    UNLINKED: 0,
    DELETED: 1,
};

const RULE_UNLINK_STRING = [
    {
        key: 'unlinkaccept', component: 'local_notificationsagent',
    },
    {
        key: 'type_template', component: 'local_notificationsagent',
    }
];

/**
 * Registers the click event listener for the unlink, or delete button of a rule
 */
export const init = () => {
    $('#deleteRuleModal').on('show.bs.modal', function (e) {
        ruleButton = $(e.relatedTarget);
        let id = ruleButton.data('ruleid');
        value = ruleButton.data('value');

        if (!value) {
            value = RULE_DELETE_TYPE.UNLINKED;
        } else {
            value = RULE_DELETE_TYPE.DELETED;
        }

        const modal = $(this);

        const requiredStrings = [
            [
                {key: 'unlinktitle', component: 'local_notificationsagent', param: {
                    title: $('#card-' + id + ' .name').text()
                }},
                {key: 'unlinkcontent', component: 'local_notificationsagent', param: {
                    title: $('#card-' + id + ' .name').text()
                }},
            ],
            [
                {key: 'deletetitle', component: 'local_notificationsagent', param: {
                    title: $('#card-' + id + ' .name').text()
                }},
                {key: 'deletecontent', component: 'local_notificationsagent', param: {
                    title: $('#card-' + id + ' .name').text()
                }},
            ]
        ]

        getStrings(requiredStrings[value]).then(([ruleTitle, ruleContent]) => {
            modal.find('.modal-title').text(ruleTitle);
            modal.find('.modal-body > div').text(ruleContent);
        });
    });

    $('#deleteRuleModal #acceptDeleteRule').on('click', (e) => {
        e.preventDefault();

        if (value == RULE_DELETE_TYPE.UNLINKED) {
            setUnlinkRule(ruleButton);
        } else {
            setDeleteRule(ruleButton);
        }
    });
};

/**
 * Unlinks a given rule from the course
 * 
 * @param {HTMLElement} ruleButton
 * @returns {Promise<void>}
 */
const setUnlinkRule = async(ruleButton) => {
    let id = ruleButton.data('ruleid');

    $('#deleteRuleModal').modal('hide');

    try {
        response = await unlinkRule(id);
        
        if ($.isEmptyObject(response['warnings'])) {
            ruleButton.addClass('d-none');
    
            getStrings(RULE_UNLINK_STRING).then(([ruleUnlinked, ruleTemplateType]) => {
                $('div[id="card-' + id + '"]').find('#card-type').text(ruleTemplateType);
                $('div[id="card-' + id + '"]').removeClass('card card-rule').addClass('card card-template');
                $('div[id="card-' + id + '"]').find('#card-type').removeClass('badge badge-rule').addClass('badge badge-template');
        
                $('a[data-ruleid="' + id + '"][data-target="#deleteRuleModal"][data-value="1"]').removeClass('d-none');
                Notification.addNotification({
                    message: ruleUnlinked,
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

/**
 * Deletes a given rule from the course
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