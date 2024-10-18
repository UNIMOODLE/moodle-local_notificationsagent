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
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {saveOrderSession} from 'local_notificationsagent/rule/repository';

/**
 * Selectors for the sort select options.
 *
 * @property {string} sortButtonId The element ID of the Sort Rule button.
 * @property {string} selectOptionId The element ID of the Sort select Rule option.
 */
const selectors = {
    sortButtonId: 'applyordergroupbtn',
    selectOptionId: 'orderrulesid'
};


/**
 * Initialises the Sort rules Rule module.
 *
 * @method init
 */
export const init = async() => {
    await getOrderSession();
    saveOrderSessionId();
};


/**
 *
 * Save key rule ordenation.
 *
 */
const saveOrderSessionId = async() => {
    let buttonapplysort = document.getElementById(selectors.sortButtonId);
    let urlparams = new URLSearchParams(window.location.search);
    let courseid = null;
    if (urlparams.has('courseid')) {
        courseid = urlparams.get('courseid');
    }


    buttonapplysort.addEventListener('click', async() => {
        let orderid = document.getElementById(selectors.selectOptionId).value;
        let sessionname = 'orderid';
        await saveOrderSession(sessionname, orderid, courseid);
        location.reload();
    });

};


/**
 *
 * Save key rule ordenation.
 *
 */
const getOrderSession = async() => {
        let orderid = -1;
        let sessionname = 'orderid';
        let urlparams = new URLSearchParams(window.location.search);
        let courseid = null;
        if (urlparams.has('courseid')) {
            courseid = urlparams.get('courseid');
        }
        let response = await saveOrderSession(sessionname, orderid, courseid);
        if (response.orderid != -1) {
            let select = document.getElementById('orderrulesid');
                let array = [];
                select.options.forEach((element) => {
                    array.push(element.value);
                });
                if (array.includes(response.orderid.toString())) {
                    select.value = response.orderid;
                } else {
                    select.value = array[0];
                }
        }
};
