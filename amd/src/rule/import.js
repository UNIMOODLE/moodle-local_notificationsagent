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

import ModalForm from 'core_form/modalform';
import {get_string as getString} from 'core/str';
import Notification from 'core/notification';

/**
 * Selectors for the Share Button.
 *
 * @property {string} importTemplateModal The element ID of the Share Rule button.
 */
const selectors = {
    importTemplateModal: '.importtemplate',
};

/**
 * Initialises the Share Rule module.
 *
 * @method init
 */
export const init = async() => {
    let importTemplateModal = document.querySelector(selectors.importTemplateModal);

    // In this example we will open the modal dialogue with the form when user clicks on the edit link:
    importTemplateModal.addEventListener('click', e => {
        e.preventDefault();
        const element = e.target;

        const modalForm = new ModalForm({
            modalConfig: {
                title: getString('import_title', 'local_notificationsagent'),
            },
            formClass: 'local_notificationsagent\\form\\import_form',
            args: {courseid: element.getAttribute('data-courseid')},
            saveButtonText: getString('import_apply', 'local_notificationsagent'),
        });

        modalForm.addEventListener(modalForm.events.FORM_SUBMITTED, event => {
            if (event.detail.result) {
                window.location.assign(event.detail.url);
            } else {
                Notification.addNotification({
                    type: 'error',
                    message: event.detail.errors.join('<br>')
                });
            }
        });
        modalForm.show();
    });
};