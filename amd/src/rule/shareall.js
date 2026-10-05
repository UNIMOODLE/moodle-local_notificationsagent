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
import SaveCancelModal from 'core/modal_save_cancel';
import ModalEvents from 'core/modal_events';
import Templates from 'core/templates';
import {shareAllRule} from 'local_notificationsagent/rule/repository';
import $ from 'jquery';
/**
 * Selectors for the Share All Button.
 *
 * @property {string} shareAllRuleId The element ID of the Share Rule button.
 */
const selectors = {
    shareAllRuleId: '[id^="share-all-rule-"]:not(.disabled)',
};

/**
 * Initialises the Share All Rule module.
 *
 * @method init
 */
export const init = async() => {
    let shareAllItems = document.querySelectorAll(selectors.shareAllRuleId);

    shareAllItems.forEach((shareAllItem) => {
        shareAllItem.addEventListener('click', async function() {
            await showModal(shareAllItem);
        });
    });
};

/**
 *
 * Shows the share all modal for a given rule.
 *
 * @param {HTMLElement} shareAllItem
 * @returns {Promise<void>}
 */
const showModal = async(shareAllItem) => {
    try {
        let ruleObj = {};

        ruleObj.id = shareAllItem.dataset.ruleid;
        ruleObj.title = document.querySelector('#card-' + ruleObj.id + ' .name').textContent;
        ruleObj.name = await getString('sharealltitle', 'local_notificationsagent', ruleObj);

        const modal = await SaveCancelModal.create({
            title: ruleObj.name,
            body: await Templates.render('local_notificationsagent/modal/shareall', {
                rule: ruleObj,
            }),
        });
        await modal.setSaveButtonText(await getString('editrule_shareallrule', 'local_notificationsagent'));

        modal.getRoot().on(ModalEvents.save, function() {
            setShareAllRule(ruleObj.id);
        });

        modal.getRoot().on(ModalEvents.hidden, function() {
            modal.destroy();
        });

        modal.show();
    } catch (exception) {
        Notification.exception(exception);
    }
};

/**
 * Approves the sharing for a given rule.
 *
 * @param {integer} id Rule id.
 * @returns {Promise<void>}
 */
const setShareAllRule = async(id) => {
    try {
        let response = await shareAllRule(id);

        if ($.isEmptyObject(response['warnings'])) {
            window.location.reload();
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
