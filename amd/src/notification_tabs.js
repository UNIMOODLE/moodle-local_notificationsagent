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

require(['core/copy_to_clipboard']);

define([], function () {

    /**
     * FormCondition object.
     * @param {String} idButton The button ID.
     * @param {String} idSelect The select ID.
     * @param {String} key Value for form.
     */
    var FormCondition = function (idButton, idSelect, key) {
        this.init(idButton, idSelect, key);
        this.registerEvents();
    };

    /** @var {Boolean} The FormCondition button selector. */
    FormCondition.prototype.buttonSelector = false;

    FormCondition.prototype.registerEvents = function () {
        var self = this;
        window.addEventListener("beforeunload", (event) => {
            if (self.buttonSelector) {
                event.stopImmediatePropagation();
            }
            self.buttonSelector = false;
        }, true);
    }

    FormCondition.prototype.init = function (idButton, idSelect, key) {
        var self = this;

        let button = document.querySelector('[id*="' + idButton + '"]');
        button.addEventListener('click', function () {
            self.buttonSelector = true
            let select = document.querySelector('[id*="' + idSelect + '"]');

            // if select option is AC
            let selectDataset = select.options[select.selectedIndex].dataset;
            if (selectDataset.type) {
                let selectValue = select.options[select.selectedIndex].value;
                document.getElementById(selectValue).click();
                return
            }

            let $title = select.options[select.selectedIndex].text;
            let $formDefault = [];

            if (window.location.href.includes('action=edit')) {
                let url = new URL(window.location.href);
                var ruleid = url.searchParams.get('ruleid');
                if (ruleid) {
                    $formDefault.push("[id]ruleid[/id][value]" + ruleid + "[/value]");
                }
            }

            let formNotif = document.querySelector('form[action*="notificationsagent"].mform');
            Array.from(formNotif.elements).forEach((element) => {
                if (element.id) {
                    $formDefault.push("[id]" + element.id + "[/id][value]" + element.value + "[/value]");
                }
            });
            let $elements = JSON.parse(select.value.split(':')[1]);
            let $name = select.options[select.selectedIndex].value.substring(0, select.options[select.selectedIndex].value.indexOf(':['));
            let $availabilityconditionsjson = document.getElementById('id_availabilityconditionsjson').value
            let data = {
                key: key,
                action: 'new',
                title: $title,
                elements: $elements,
                name: $name,
                formDefault: $formDefault,
                availabilityconditionsjson: $availabilityconditionsjson
            };

            $.ajax({
                type: "POST",
                url: window.location.pathname.substring(0, '/local/notificationsagent/editrule.php'),
                data: data,
                success: function (event) {
                    if (event.state === 'success') {
                        window.location.reload();
                    }
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    console.log("Status: " + textStatus);
                    console.log(errorThrown);
                },
                dataType: 'json'
            });
        });
    };

    return {

        /**
         * Main initialisation.
         * @param {String} idButton The button ID.
         * @param {String} idSelect The select ID.
         * @param {String} key Value for form.
         * @method init
         */
        init: function (idButton, idSelect, key) {
            // Create instance.
            new FormCondition(idButton, idSelect, key);
        }

    }
});
