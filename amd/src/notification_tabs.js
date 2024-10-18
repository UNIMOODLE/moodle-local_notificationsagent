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
 * Module javascript to place new conditions.
 *
 * @module    notification_tabs
 * @category  Classes - autoloading
 * @copyright 2023, ISYC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * @param {String} idButton The button ID.
 * @param {String} idSelect The select ID.
 */
export const init = (idButton, idSelect) => {
    let button = document.querySelector('[id*="' + idButton + '"]');
    button.addEventListener('click', function(ev) {
        let select = document.querySelector('[id*="' + idSelect + '"]');

        // If select option is AC
        let selectDataset = select.options[select.selectedIndex].dataset;
        if (selectDataset.type) {
            ev.preventDefault();
            let selectValue = select.options[select.selectedIndex].value;
            document.getElementById(selectValue).click();
            return;
        }
    });
};

/**
 * Remove initialisation.
 * @param {String} removeSpan The span selector.
 * @param {String} submitRemove The button name.
 * @method init
 */
export const initRemove = (removeSpan, submitRemove) => {
    // Event to remove icon click
    document.querySelectorAll('.' + removeSpan).forEach(function(link) {
        link.addEventListener('click', function(e) {
            let selector = document.querySelector('input[name="' + submitRemove + '"]');
            selector.value = e.target.id;
            selector.click();
        });
    });
};
