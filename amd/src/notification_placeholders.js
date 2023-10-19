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

/**
 * Module javascript to place the placeholders.
 * Modified version of IOMAD Email template emailvars.
 *
 * @module   mod_simplemod/notification_placeholders
 * @category  Classes - autoloading
 * @copyright 2023, ISYC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define([], function() {

    return {

        /**
         * Setup the classes to editors works with placeholders.
         */
        init: function() {
            var module = this;
            var clickforword = document.getElementsByClassName('clickforword');
            for (var i = 0; i < clickforword.length; i++) {
                clickforword[i].addEventListener('click', function(e) {
                    e.preventDefault(); // To prevent the default behaviour of a tag.
                    var idAction = e.target.closest("div.notificationvars").getAttribute("id").replace("notificationvars_","").split('_')[0];
                    var typeitem = e.target.closest("div.notificationvars").getAttribute("id").replace("notificationvars_","").split('_')[1];
                    module.insertAtCaret(e.target.closest(".fitem").previousElementSibling.getAttribute("id"), "{" + this.getAttribute('data-text') + "}", idAction, typeitem);
                });
            }
        },

        /**
         * Insert the placeholder in selected caret place.
         * @param  {string} myValue
         * @param  {int} idAction
         */
        insertAtCaret: function(idContainerReplace, myValue, idAction, typeitem) {
            var sel, range;

            if(typeitem == 'message'){
                if(window.getSelection().type !== 'None' && 
                    ($(window.getSelection().baseNode).closest("[id^='id_'][id*='_"+idAction+"'][id$='editable']").attr("id") !== undefined || 
                    $(window.getSelection().focusNode).closest("[id^='id_'][id*='_"+idAction+"'][id$='editable']").attr("id") !== undefined)){
                    sel = window.getSelection();
                    if (sel.getRangeAt && sel.rangeCount) {
                        range = sel.getRangeAt(0);
                        range.deleteContents();
                        range.insertNode(document.createTextNode(myValue));

                        for (let position = 0; position != (myValue.length + 1); position++) {
                            sel.modify("move", "right", "character");
                        }
                    }
                }else{
                    var thiselem = document.querySelectorAll("#"+idContainerReplace+" [id^='id_'][id*='_"+idAction+"'][id$='editable']");
                    thiselem[0].appendChild(document.createTextNode(myValue));
                }
            }else if(typeitem == 'text'){
                var thiselem = document.querySelectorAll("#"+idContainerReplace+" [type='"+typeitem+"'][id^='id_'][id*='_"+idAction+"']");
                thiselem[0].value = thiselem[0].value + myValue;
            }
        },
    };
});
