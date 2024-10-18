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
import {updateRuleShare} from 'local_notificationsagent/rule/repository';

/**
 * Types of rule sharing.
 *
 * @type {{SHARING_TYPE: boolean}}
 */
const SHARING_TYPE = {
    SHARED: 0,
    UNSHARED: 1,
};

/**
 * Selectors for the Share Button.
 *
 * @property {string} shareRuleId The element ID of the Share Rule button.
 */
const selectors = {
    shareRuleId: '[id^="share-rule-"]:not(.disabled)',
    shareRuleDataShared: 'data-shared',
};

/**
 * Initialises the Share Rule module.
 *
 * @method init
 */
export const init = async() => {
    let shareItems = document.querySelectorAll(selectors.shareRuleId);

    shareItems.forEach((shareItem) => {
        shareItem.addEventListener('click', async function() {
            await showModal(shareItem);
        });
    });
};

/**
 *
 * Shows the share modal for a given rule.
 *
 * @param {HTMLElement} shareItem
 * @returns {Promise<void>}
 */
const showModal = async(shareItem) => {
    let ruleObj = {};

    ruleObj.id = shareItem.dataset.ruleid;
    ruleObj.title = document.querySelector('#card-' + ruleObj.id + ' .name').textContent;
    ruleObj.shared = shareItem.dataset.shared == SHARING_TYPE.SHARED ? SHARING_TYPE.SHARED : SHARING_TYPE.UNSHARED;

    if (!ruleObj.shared) {
        ruleObj.name = await getString('unsharetitle', 'local_notificationsagent', ruleObj);
    } else {
        ruleObj.name = await getString('sharetitle', 'local_notificationsagent', ruleObj);
    }

    ModalFactory.create({
        type: ModalFactory.types.SAVE_CANCEL,
        title: ruleObj.name,
        body: Templates.render('local_notificationsagent/modal/share', {
            rule: ruleObj,
        }),
    }).then(function(modal) {
        let isShared = !ruleObj.shared ? SHARING_TYPE.UNSHARED : SHARING_TYPE.SHARED;
        let shareBtnText = isShared ?
            getString('editrule_unsharerule', 'local_notificationsagent') :
            getString('editrule_sharerule', 'local_notificationsagent');

        modal.setSaveButtonText(shareBtnText);

        // Handle save event.
        modal.getRoot().on(ModalEvents.save, function() {
            setRuleShare(ruleObj.id, isShared);
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
 * Changes the sharing status for a given rule.
 *
 * @param {integer} id Rule id.
 * @param {boolean} shared Rule shared type.
 * @returns {Promise<void>}
 */
const setRuleShare = async(id, shared) => {
    try {
        let response = await updateRuleShare(id, shared);

        if ($.isEmptyObject(response['warnings'])) {
            let shareItem = document.querySelector('a#share-rule-' + id);
            let shareItemIcon = document.createElement('i');
            let shareItemText = '';
            let shareItemMessage = '';
            let shareItemTag= document.querySelector('div#card-'+id).querySelector('span.badge');
            if (!shared) {
                shareItemTag.textContent = await getString('type_sharedrule', 'local_notificationsagent');
                shareItemTag.classList.add("shared");
                shareItemText = await getString('editrule_unsharerule', 'local_notificationsagent');
                shareItem.setAttribute(selectors.shareRuleDataShared, SHARING_TYPE.SHARED);
                shareItemIcon.className = 'fa fa-chain-broken mr-2';
                shareItemMessage = await getString('shareaccept', 'local_notificationsagent');
            } else {
                shareItemTag.textContent = await getString('type_rule', 'local_notificationsagent');
                shareItemTag.classList.remove("shared");
                shareItemText = await getString('editrule_sharerule', 'local_notificationsagent');
                shareItem.setAttribute(selectors.shareRuleDataShared, SHARING_TYPE.UNSHARED);
                shareItemIcon.className = 'fa fa-link mr-2';
                shareItemMessage = await getString('unshareaccept', 'local_notificationsagent');
            }

            shareItem.innerHTML = '';
            shareItem.appendChild(shareItemIcon);
            shareItem.appendChild(document.createTextNode(shareItemText));

            Notification.addNotification({
                message: shareItemMessage,
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
