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
 * @package    local_notificationsagent
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {get_strings as getStrings} from 'core/str';
import Notification from 'core/notification';
import {updateRuleShare} from 'local_notificationsagent/rule/repository';

/**
 * Types of rule sharing
 * 
 * @type {{RULE_SHARE: boolean}}
 */
const RULE_SHARE = {
    SHARED: 0,
    UNSHARED: 1,
};

/**
 * Text to be displayed when sharing, or stop sharing a rule.
 * 
 * @type {{RULE_SHARE_STRING: string}}
 */
const RULE_SHARE_STRING = [
    {
        key: 'shareaccept', component: 'local_notificationsagent',
    },
    {
        key: 'unshareaccept', component: 'local_notificationsagent',
    }
];

export const init = () => {
    $('#shareRuleModal').on('show.bs.modal', function (e) {
        shareRuleBtn = $(e.relatedTarget);
        let id = shareRuleBtn.data('ruleid');
        let shareValue = shareRuleBtn.data('value');

        if (!shareValue) {
            shareValue = RULE_SHARE.SHARED;
        } else {
            shareValue = RULE_SHARE.UNSHARED;
        }

        const modal = $(this);

        const requiredStrings = [
            [
                {key: 'sharetitle', component: 'local_notificationsagent', param: {
                    title: $('#card-' + id + ' .name').text()
                }},
                {key: 'sharecontent', component: 'local_notificationsagent', param: {
                    title: $('#card-' + id + ' .name').text()
                }},
            ],
            [
                {key: 'unsharetitle', component: 'local_notificationsagent', param: {
                    title: $('#card-' + id + ' .name').text()
                }},
                {key: 'unsharecontent', component: 'local_notificationsagent', param: {
                    title: $('#card-' + id + ' .name').text()
                }},
            ]
        ]

        getStrings(requiredStrings[shareValue]).then(([ruleTitle, ruleContent]) => {
            modal.find('.modal-title').text(ruleTitle);
            modal.find('.modal-body > div').text(ruleContent);
        });
    });

    $('#shareRuleModal #acceptShareRule').on('click', (e) => {
        e.preventDefault();
        setRuleShare(shareRuleBtn);
    });
};

/**
 * Changes the sharing status for a given rule
 * 
 * @param {HTMLElement} ruleButton
 * @returns {Promise<void>}
 */
const setRuleShare = async(shareRuleBtn) => {
    let id = shareRuleBtn.data('ruleid');
    let status = shareRuleBtn.data('value');

    if (!status) {
        status = RULE_SHARE.SHARED;
    } else {
        status = RULE_SHARE.UNSHARED;
    }

    $('#shareRuleModal').modal('hide');

    try {
        response = await updateRuleShare(id, status);
        
        if ($.isEmptyObject(response['warnings'])) {
            shareRuleBtn.addClass('d-none');

            getStrings(RULE_SHARE_STRING).then(([ruleShared, ruleUnshared]) => {
                if (status) {
                    $('a[data-ruleid="' + id + '"][data-target="#shareRuleModal"][data-value="0"]').removeClass('d-none');
                    Notification.addNotification({
                        message: ruleUnshared,
                        type: "info"
                    });
                } else {
                    $('a[data-ruleid="' + id + '"][data-target="#shareRuleModal"][data-value="1"]').removeClass('d-none');
                    Notification.addNotification({
                        message: ruleShared,
                        type: "info"
                    });
                }
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