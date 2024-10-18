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
import {unshareAllRule} from 'local_notificationsagent/rule/repository';

/**
 * Selectors for the Unshare All Button.
 *
 * @property {string} unshareAllRuleId The element ID of the Unshare Rule button.
 */
const selectors = {
    unshareAllRuleId: '[id^="unshare-all-rule-"]:not(.disabled)',
};

/**
 * Initialises the Unshare All Rule module.
 *
 * @method init
 */
export const init = async() => {
    let ununshareAllItems = document.querySelectorAll(selectors.unshareAllRuleId);

    ununshareAllItems.forEach((unshareAllItem) => {
        unshareAllItem.addEventListener('click', async function() {
            await showModal(unshareAllItem);
        });
    });
};

/**
 *
 * Shows the unshare all modal for a given rule.
 *
 * @param {HTMLElement} unshareAllItem
 * @returns {Promise<void>}
 */
const showModal = async(unshareAllItem) => {
    let ruleObj = {};

    ruleObj.id = unshareAllItem.dataset.ruleid;
    ruleObj.title = document.querySelector('#card-' + ruleObj.id + ' .name').textContent;
    ruleObj.name = await getString('unsharealltitle', 'local_notificationsagent', ruleObj);

    ModalFactory.create({
        type: ModalFactory.types.SAVE_CANCEL,
        title: ruleObj.name,
        body: Templates.render('local_notificationsagent/modal/unshareall', {
            rule: ruleObj,
        }),
    }).then(function(modal) {
        modal.setSaveButtonText(getString('editrule_unshareallrule', 'local_notificationsagent'));

        // Handle save event.
        modal.getRoot().on(ModalEvents.save, function() {
            setUnshareAllRule(ruleObj.id);
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
 * Rejects the sharing for a given rule.
 *
 * @param {integer} id Rule id.
 * @returns {Promise<void>}
 */
const setUnshareAllRule = async(id) => {
    try {
       let response = await unshareAllRule(id);

        if ($.isEmptyObject(response['warnings'])) {
            getString('sharereject', 'local_notificationsagent').then(ruleUnshared => {
                document.querySelector('#card-' + id).remove();

                Notification.addNotification({
                    message: ruleUnshared,
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
