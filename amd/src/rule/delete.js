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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * @module    local_notificationsagent/rule/delete
 * @copyright 2023 ISYC <soporte@isyc.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {get_string as getString} from 'core/str';
import Notification from 'core/notification';
import ModalFactory from 'core/modal_factory';
import ModalEvents from 'core/modal_events';
import Templates from 'core/templates';
import {checkRuleContext, deleteRule} from 'local_notificationsagent/rule/repository';

/**
 * Selectors for the Delete Button.
 *
 * @property {string} deleteRuleId The element ID of the Delete Rule button.
 */
const selectors = {
    deleteRuleId: '[id^="delete-rule-"]:not(.disabled)',
};

/**
 * Initialises the Delete Rule module.
 *
 * @method init
 */
export const init = async() => {
    let deleteItems = document.querySelectorAll(selectors.deleteRuleId);

    deleteItems.forEach((deleteItem) => {
        deleteItem.addEventListener('click', async function() {
            await showModal(deleteItem);
        });
    });
};

/**
 *
 * Shows the delete modal for a given rule.
 *
 * @param {HTMLElement} deleteItem
 * @returns {Promise<void>}
 */
const showModal = async(deleteItem) => {
    let ruleObj = {};

    ruleObj.id = deleteItem.dataset.ruleid;
    ruleObj.type = deleteItem.dataset.type;
    ruleObj.title = document.querySelector('#card-' + ruleObj.id + ' .name').textContent;

    hasRuleContext(deleteItem).then(hasContext => {
        ruleObj.hascontext = hasContext;

        ModalFactory.create({
            type: ModalFactory.types.SAVE_CANCEL,
            title: getString('deletetitle', 'local_notificationsagent', ruleObj),
            body: Templates.render('local_notificationsagent/modal/delete', {
                rule: ruleObj,
            }),
        }).then(function(modal) {
            modal.setSaveButtonText(getString('delete'));

            // Handle save event.
            modal.getRoot().on(ModalEvents.save, function() {
                setDeleteRule(ruleObj.id);
            });

            // Handle hidden event.
            modal.getRoot().on(ModalEvents.hidden, function() {
                // Destroy when hidden.
                modal.destroy();
            });

            modal.show();

            return true;
        }).catch(Notification.exception);
    });
};

/**
 * Checks if the rule has any other context before deleting
 *
 * @param {HTMLElement} deleteItem Card rule element.
 * @returns {boolean} Has it context?
 */
const hasRuleContext = async(deleteItem) => {
    let id = deleteItem.dataset.ruleid;
    let hasContext = false;

    try {
        let response = await checkRuleContext(id);

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
 * Deletes a given rule.
 *
 * @param {Integer} id Rule id.
 * @returns {Promise<void>}
 */
const setDeleteRule = async(id) => {
    try {
        let response = await deleteRule(id);

        if ($.isEmptyObject(response['warnings'])) {
            getString('deleteaccept', 'local_notificationsagent').then(ruleDeleted => {
                document.querySelector('#card-' + id).remove();

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
