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
// Project implemented by the \"Recovery, Transformation and Resilience Plan.
// Funded by the European Union - Next GenerationEU\".
//
// Produced by the UNIMOODLE University Group: Universities of
// Valladolid, Complutense de Madrid, UPV/EHU, León, Salamanca,
// Illes Balears, Valencia, Rey Juan Carlos, La Laguna, Zaragoza, Málaga,
// Córdoba, Extremadura, Vigo, Las Palmas de Gran Canaria y Burgos.

/**
 * Version details
 *
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {get_string as getString} from 'core/str';
import Notification from 'core/notification';
import ModalFactory from 'core/modal_factory';
import ModalEvents from 'core/modal_events';
import Templates from 'core/templates';
import {updateRuleStatus} from 'local_notificationsagent/rule/repository';

/**
 * Types of rule states.
 *
 * @type {{STATUS_TYPE: boolean}}
 */
const STATUS_TYPE = {
    RESUMED: 0,
    PAUSED: 1,
};

/**
 * Selectors for the Update Status Button.
 *
 * @property {string} updateStatusRuleId The element ID of the Update Status Rule button.
 */
const selectors = {
    updateStatusRuleId: '[id^="status-rule-"]:not(.disabled)',
    updateStatusRuleDataState: 'data-status',
};

/**
 * Initialises the Update Status Rule module.
 *
 * @method init
 */
export const init = async() => {
    let updateStatusItems = document.querySelectorAll(selectors.updateStatusRuleId);

    updateStatusItems.forEach((updateStatusItem) => {
        updateStatusItem.addEventListener('click', async function() {
            await showModal(updateStatusItem);
        });
    });
};

/**
 *
 * Shows the update status modal for a given rule.
 *
 * @param {HTMLElement} updateStatusItem
 * @returns {Promise<void>}
 */
const showModal = async(updateStatusItem) => {
    let ruleObj = {};

    ruleObj.id = updateStatusItem.dataset.ruleid;
    ruleObj.title = document.querySelector('#card-' + ruleObj.id + ' .name').textContent;
    ruleObj.status = updateStatusItem.dataset.status == STATUS_TYPE.RESUMED ? STATUS_TYPE.RESUMED : STATUS_TYPE.PAUSED;

    if (!ruleObj.status) {
        ruleObj.name = await getString('status_pausetitle', 'local_notificationsagent', ruleObj);
    } else {
        ruleObj.name = await getString('status_activatetitle', 'local_notificationsagent', ruleObj);
    }

    ModalFactory.create({
        type: ModalFactory.types.SAVE_CANCEL,
        title: ruleObj.name,
        body: Templates.render('local_notificationsagent/modal/update_status', {
            rule: ruleObj,
        }),
    }).then(function(modal) {
        let isPaused = !ruleObj.status ? STATUS_TYPE.PAUSED : STATUS_TYPE.RESUMED;
        let updateStatusBtnText = isPaused ?
            getString('statuspause', 'local_notificationsagent') : getString('statusactivate', 'local_notificationsagent');

        modal.setSaveButtonText(updateStatusBtnText);

        // Handle save event.
        modal.getRoot().on(ModalEvents.save, function() {
            setRuleStatus(ruleObj.id, isPaused);
        });

        // Handle hidden event.
        modal.getRoot().on(ModalEvents.hidden, function() {
            // Destroy when hidden.
            modal.destroy();
        });

        modal.show();

        return true;
    });
};

/**
 * Changes the status for a given rule.
 *
 * @param {integer} id Rule id.
 * @param {boolean} status Rule status type.
 * @returns {Promise<void>}
 */
const setRuleStatus = async(id, status) => {
    try {
        let response = await updateRuleStatus(id, status);

        if ($.isEmptyObject(response['warnings'])) {
            let updateStatusItem = document.querySelector('a#status-rule-' + id);
            let updateStatusItemIcon = document.createElement('i');
            let updateStatusItemText = '';
            let updateStatusItemMessage = '';

            let cardBadge = document.querySelector('#card-' + id + ' .badge-status');

            if (!status) {
                updateStatusItemText = await getString('statuspause', 'local_notificationsagent');
                updateStatusItem.setAttribute(selectors.updateStatusRuleDataState, STATUS_TYPE.RESUMED);
                updateStatusItemIcon.className = 'fa fa-pause mr-2';
                updateStatusItemMessage = await getString('status_acceptactivated', 'local_notificationsagent');

                if (!cardBadge.classList.contains('badge-required')) {
                    cardBadge.classList.remove('badge-paused');
                    cardBadge.classList.add('badge-active');
                    cardBadge.querySelector('span').textContent = await getString('status_active', 'local_notificationsagent');
                }
            } else {
                updateStatusItemText = await getString('statusactivate', 'local_notificationsagent');
                updateStatusItem.setAttribute(selectors.updateStatusRuleDataState, STATUS_TYPE.PAUSED);
                updateStatusItemIcon.className = 'fa fa-play mr-2';
                updateStatusItemMessage = await getString('status_acceptpaused', 'local_notificationsagent');

                if (!cardBadge.classList.contains('badge-required')) {
                    cardBadge.classList.remove('badge-active');
                    cardBadge.classList.add('badge-paused');
                    cardBadge.querySelector('span').textContent = await getString('status_paused', 'local_notificationsagent');
                }
            }

            updateStatusItem.innerHTML = '';
            updateStatusItem.appendChild(updateStatusItemIcon);
            updateStatusItem.appendChild(document.createTextNode(updateStatusItemText));

            Notification.addNotification({
                message: updateStatusItemMessage,
                type: 'info'
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
