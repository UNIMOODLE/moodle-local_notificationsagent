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
 * @module    local_notificationsagent/statusrule
 * @copyright 2023 ISYC <soporte@isyc.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {get_strings as getStrings} from 'core/str';
import Notification from 'core/notification';
import {updateRuleStatus} from 'local_notificationsagent/rule/repository';

/**
 * Types of rule states
 * 
 * @type {{RULE_STATUS: boolean}}
 */
const RULE_STATUS = {
    RESUMED: 0,
    PAUSED: 1,
};

/**
 * Text to be displayed when pausing, or resuming a rule.
 * 
 * @type {{RULE_STATUS_STRING: string}}
 */
const RULE_STATUS_STRING = [
    {
        key: 'status_paused', component: 'local_notificationsagent',
    },
    {
        key: 'status_active', component: 'local_notificationsagent',
    },
    {
        key: 'statusacceptpaused', component: 'local_notificationsagent',
    },
    {
        key: 'statusacceptactivated', component: 'local_notificationsagent',
    }
];

/**
 * Registers the click event listener for the status button of a rule.
 */
export const init = () => {
    $('#statusRuleModal').on('show.bs.modal', function (e) {
        ruleButton = $(e.relatedTarget);
        let id = ruleButton.data('idrule');
        let statusText = ruleButton.data('textstatus');

        const modal = $(this);

        const requiredStrings = [
            {key: 'statustitle', component: 'local_notificationsagent', param: {
                textstatus: statusText,
                title: $('#card-' + id + ' .name').text()
            }},
            {key: 'statuscontent', component: 'local_notificationsagent', param: {
                textstatus: statusText.toLowerCase(),
                title: $('#card-' + id + ' .name').text()
            }},
        ];

        getStrings(requiredStrings).then(([ruleTitle, ruleContent]) => {
            modal.find('.modal-title').text(ruleTitle);
            modal.find('.modal-body > div').text(ruleContent);
        });
    });

    $('#statusRuleModal #acceptStatusRule').on('click', (e) => {
        e.preventDefault();
        setRuleStatus(ruleButton);
    });
};

/**
 * Changes the status for a given rule.
 * @param {HTMLElement} ruleButton
 * @returns {Promise<void>}
 */
const setRuleStatus = async(ruleButton) => {
    let ruleid = ruleButton.data('idrule');
    let status = ruleButton.data('valuestatus');

    if (!status) {
        status = RULE_STATUS.RESUMED;
    } else {
        status = RULE_STATUS.PAUSED;
    }

    $('#statusRuleModal').modal('hide');

    getStrings(RULE_STATUS_STRING).then(([ruleBadgePaused, ruleBadgeActive, rulePaused, ruleResumed]) => {
        let badgestatus = $('#card-'+ruleid+' .badge-status');
        if (status) {
            badgestatus.removeClass('badge-active');
            badgestatus.addClass('badge-paused');
            badgestatus.find('span').text(ruleBadgePaused);
        } else {
            badgestatus.removeClass('badge-paused');
            badgestatus.addClass('badge-active');
            badgestatus.find('span').text(ruleBadgeActive);
        }

        ruleButton.addClass('d-none');
        if (status) {
            $('a[data-idrule="' + ruleid + '"][data-target="#statusRuleModal"][data-valuestatus="0"]').removeClass('d-none');
            Notification.addNotification({
                message: rulePaused,
                type: "info"
            });
        } else {
            $('a[data-idrule="' + ruleid + '"][data-target="#statusRuleModal"][data-valuestatus="1"]').removeClass('d-none');
            Notification.addNotification({
                message: ruleResumed,
                type: "info"
            });
        }
    });

    try {
        // TODO Display warnings as Notification exception 
        response = await updateRuleStatus(ruleid, status);
    } catch (exception) {
        Notification.exception(exception);
    }
};
