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
 * @package    local_notificationsagent
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {get_strings as getStrings} from 'core/str';
import Notification from 'core/notification';
import {shareAllRule} from 'local_notificationsagent/rule/repository';

export const init = () => {
    $('#shareAllRuleModal').on('show.bs.modal', function (e) {
        shareAllRuleBtn = $(e.relatedTarget);
        let id = shareAllRuleBtn.data('ruleid');

        const modal = $(this);

        const requiredStrings = [
            {key: 'sharealltitle', component: 'local_notificationsagent', param: {
                title: $('#card-' + id + ' .name').text()
            }},
            {key: 'shareallcontent', component: 'local_notificationsagent', param: {
                title: $('#card-' + id + ' .name').text()
            }},
        ];

        getStrings(requiredStrings).then(([ruleTitle, ruleContent]) => {
            modal.find('.modal-title').text(ruleTitle);
            modal.find('.modal-body > div').text(ruleContent);
        });
    });

    $('#shareAllRuleModal #acceptShareAllRule').on('click', (e) => {
        e.preventDefault();
        setShareAllRule(shareAllRuleBtn);
    });
};

/**
 * Approves the sharing status for a given rule
 * 
 * @param {HTMLElement} ruleButton
 * @returns {Promise<void>}
 */
const setShareAllRule = async(shareAllRuleBtn) => {
    let id = shareAllRuleBtn.data('ruleid');

    $('#shareAllRuleModal').modal('hide');

    try {
        response = await shareAllRule(id);
        
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
