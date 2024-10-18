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
 *
 * @module   local_notificationsagent/assigntemplate
 * @copyright 2023, ISYC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {get_string as getString} from 'core/str';
import Url from 'core/url';

/**
 * Selectors for the Assign Modal.
 *
 * @property {string} selectAllId The element ID of the Select All link.
*/
const selectors = {
    selectAllId: '[id^="select-all-"]',
};

/**
 * Get the count of selected items for all categories.
 *
 * @returns {void}
 */
const getCountAll = async() => {
    const categories = document.querySelectorAll('#category-listing-content-0 > li[id^="listitem-category-"]');
    Array.from(categories).map((category) => {
        getCountBy(category.id.replace('listitem-category-', ''));
    });
};

/**
 * Count selected items in a category.
 *
 * @param {integer} categoryId
 *
 * @returns {void}
 */
const getCountBy = async(categoryId) => {
    // Count selected items for the current category.
    setCount(categoryId);

    // Update the count for the parent category.
    let hasParent = document.getElementById(`checkboxcategory-${categoryId}`).getAttribute('data-parent');
    if (hasParent) {
        let parentCategoryId = hasParent.replace('#category-listing-content-', '');
        setCount(parentCategoryId);
    }

    // Update the count for the children categories.
    let children = document.querySelectorAll(`#category-listing-content-${categoryId} li[id^="listitem-category-"]`);
    children.forEach((child) => {
        setCount(child.id.replace('listitem-category-', ''));
    });
};

/**
 * Display the count of selected items in a category.
 *
 * @param {integer} categoryId
 *
 * @returns {void}
 */
const setCount = async(categoryId) => {
    if (categoryId == 0) {
        return;
    }

    let obj = {};
    obj.categories = document.querySelectorAll(
        `#category-listing-content-${categoryId} > li[id^="listitem-category-"] > div > div > input[type="checkbox"][id^="checkboxcategory-"]:checked`
    ).length;
    obj.courses = document.querySelectorAll(
        `#category-listing-content-${categoryId} > li[id^="listitem-course-"] > div > div > input[type="checkbox"][id^="checkboxcourse-"]:checked`
    ).length;

    document.getElementById(
        `selected-info-${categoryId}`).textContent = await getString('assignselectedinfo', 'local_notificationsagent', obj
    );
    document.getElementById(`selected-info-${categoryId}`).classList.remove("d-none");
};

/**
 * Handles the click event on the Select Courses link.
 * Selects or unselects all courses in the category.
 *
 * @param {Event} event The event object.
 *
 * @returns {void}
 */
const onClickSelectCourses = async(event) => {
    const selectItem = event.target.closest(selectors.selectAllId);
    const categoryId = selectItem.getAttribute('data-category');
    const isCategoryChecked = document.getElementById('checkboxcategory-' + categoryId + '').checked;
    if (isCategoryChecked) {
        return;
    }

    const checkboxes = document.querySelectorAll(
        `#category-listing-content-${categoryId} > li[id^="listitem-course-"] > div > div > input[type="checkbox"][id^="checkboxcourse-"]`
    );
    const isAllSelected = (selectItem.getAttribute('data-forceselected') == 'true') ? false : true;
    selectItem.setAttribute('data-forceselected', isAllSelected);
    selectItem.textContent = isAllSelected ?
        await getString('assignunselectcourses', 'local_notificationsagent') :
        await getString('assignselectcourses', 'local_notificationsagent');

    checkboxes.forEach(function(checkbox) {
        checkbox.checked = isAllSelected;
    });

    getCountBy(categoryId);
};

/**
 * Registers click event listeners for all Select Courses links.
 *
 * @returns {Promise<void>}
 */
const registerEventListeners = async() => {
    const selectAllItems = document.querySelectorAll(selectors.selectAllId);
    Array.from(selectAllItems).map((selectAllItem) => {
        selectAllItem.addEventListener('click', onClickSelectCourses);
    });
};

/**
 * Initialise the module.
 * @method init
 *
 */
export const init = () => {

    /**
 * Types of rule type
 *
 * @type {{RULE_TYPE: boolean}}
 */
    const RULE_TYPE = [
        'rule',
        'template'
    ];

    const ACTION = [
        'SHOW_CONTEXT',
        'SET_CONTEXT',
    ];

    const RULE_FORCED_TYPE = {
        FORCED: 0,
        NONFORCED: 1,
    };

    registerEventListeners();

    var idtemplate;
    $('#assignTemplateModal').on('show.bs.modal', function(event) {
        resetDefaultCheckboxes('input[type="checkbox"][id^="checkboxcategory-"]');
        resetDefaultCheckboxes('input[type="checkbox"][id^="checkboxcourse-"]');
        resetDefaultSelectCourses();

        var button = $(event.relatedTarget);
        idtemplate = button.data('idtemplate');
        var isRuleForced;
        var htmlRuleForced;

        let ruleCard = $('div[id="card-' + idtemplate + '"]');
        $('#assignTemplateModal #forced-content').empty();
        if (ruleCard.data('type') === RULE_TYPE[0]) {
            isRuleForced = ruleCard.data('forced');
            getString('assignforced', 'local_notificationsagent').then(forcedRule => {
                htmlRuleForced = '<div class="custom-control custom-checkbox mr-1">';
                htmlRuleForced += '<input id="forced" type="checkbox" class="custom-control-input">';
                htmlRuleForced += '<label class="custom-control-label" for="forced">' + forcedRule + '</label>';
                htmlRuleForced += '</div>';
                $('#assignTemplateModal #forced-content').append(htmlRuleForced);
                if (!isRuleForced) {
                    $('#assignTemplateModal #forced-content #forced').prop('checked', true);
                }
            });
        }

        var modal = $(this);
        modal.find('.modal-title > span').text($('#card-' + idtemplate + ' .badge-type').text());
        modal.find('.badge').text($('#card-' + idtemplate + ' .badge-type').text());
        modal.find('.badge').attr('class', 'mr-2 ' + $('#card-' + idtemplate + ' .badge-type').attr('class'));

        modal.find('.modal-body .name').text($('#card-' + idtemplate + ' .name').text());

        /* Rellenar cursos asignados */
        $.ajax({
            type: "POST",
            url: Url.relativeUrl('/local/notificationsagent/assignrule.php'),
            data: {
                ruleid: idtemplate,
                action: ACTION[0]
            },
            success: function(data) {
                data['category'].forEach((categoryid) => {
                    let category = $('#assignTemplateModal .category-listing input#checkboxcategory-' + categoryid);
                    if (category.length) {
                        category.prop('checked', true);
                        $('#category-listing-content-' + categoryid + ' input[type="checkbox"]').prop("checked", true);
                        $('#category-listing-content-' + categoryid + ' input[type="checkbox"]').prop("disabled", true);
                        $('#listitem-category-' + categoryid + ' input[type="checkbox"]').prop("checked", true);
                    }
                });

                data['course'].forEach((courseid) => {
                    let course = $('#assignTemplateModal .category-listing input#checkboxcourse-' + courseid);
                    if (course.length) {
                        course.prop('checked', true);
                    }
                });

                // After displaying the selected info, display the count of selected items.
                getCountAll();
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                console.log("Status: " + textStatus);
                console.log(errorThrown);
            },
            dataType: 'json'
        });
    });
    $('#assignTemplateModal').on('hide.bs.modal', function() {
        $('#assignTemplateModal .custom-control-input').prop('checked', false);
    });
    $('#assignTemplateModal .collapse').on('show.bs.collapse', function() {
        $(this).parents('.listitem-category').removeClass('collapsed');
    });
    $('#assignTemplateModal .collapse').on('hide.bs.collapse', function() {
        $(this).parents('.listitem-category').addClass('collapsed');
    });

    /* checkbox */
    $('#assignTemplateModal #course-category-select-all').on('click', function() {
        var checkassign = $('#assignTemplateModal .category-listing .custom-control-input');
        checkassign.prop('checked', $(this).prop('checked'));

        // After checking Select all, display the count of selected items.
        getCountAll();
    });
    $('#assignTemplateModal .category-listing').on('change', 'input[type=checkbox]', function() {
        var checkssubcategoriescourses =
            '#category-listing-content-' + $(this).attr("id").replace('checkboxcategory-', '') + ' .custom-control-input';
        $('#assignTemplateModal .category-listing ' + checkssubcategoriescourses).prop('checked', $(this).prop('checked'));
        $('#assignTemplateModal .category-listing ' + checkssubcategoriescourses).prop('disabled', $(this).prop('checked'));

        // After checking any category or course box, display the count of selected items of its category.
        getCountBy(this.getAttribute("data-category"));
    });

    $('#assignTemplateModal #saveassignTemplateModal').on('click', function() {
        var data = {};
        data['category'] = [];
        data['course'] = [];
        var allCategories = [];

        let forced = RULE_FORCED_TYPE.NONFORCED;
        if ($('#assignTemplateModal #forced').prop('checked')) {
            forced = RULE_FORCED_TYPE.FORCED;
        }

        let mainCategories = $('#assignTemplateModal #category-listing-content-0 > li[id^="listitem-category-"]')
            .has('input[id^="checkboxcategory-"]:checked');

        mainCategories.each(function() {
            let items = $('#' + this.id + ' input[id^="checkboxcategory-"]:checked').map(function() {
                let id = $(this).attr('id').replace('checkboxcategory-', '');
                let parent = $(this).data('parent').replace('#category-listing-content-', '');

                if ($.inArray(id, allCategories) === -1) {
                    allCategories.push(id);
                }

                return { id: id, parent: parent };
            }).get();

            var parents = $.map(items, function(item) {
                return item.id;
            });

            $.grep(items, function(item) {
                if ($.inArray(item.parent, parents) === -1) {
                    data['category'].push(item.id);
                }
            });
        });

        let courses = $('#assignTemplateModal .category-listing input[id^="checkboxcourse-"]:checked').map(function() {
            let id = $(this).attr('id').replace('checkboxcourse-', '');
            let category = $(this).data('parent').replace('#category-listing-content-', '');

            return { id: id, category: category };
        }).get();

        if ($.isEmptyObject(data['category'])) {
            $.each(courses, function(index, course) {
                data['course'].push(course.id);
            });
        } else {
            $.grep(courses, function(course) {
                if ($.inArray(course.category, allCategories) === -1) {
                    data['course'].push(course.id);
                }
            });
        }

        $.ajax({
            type: "POST",
            url: Url.relativeUrl('/local/notificationsagent/assignrule.php'),
            data: {
                ruleid: idtemplate,
                category: data['category'],
                course: data['course'],
                forced: forced,
                action: ACTION[1]
            },
            success: function() {
                window.location.reload();
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                console.log("Status: " + textStatus);
                console.log(errorThrown);
            },
            dataType: 'json'
        });

    });

    /**
     * Reset default checkboxes.
     * @param {string} selector
     */
    const resetDefaultCheckboxes = (selector) => {
        document.querySelectorAll(selector).forEach(checkbox => {
            checkbox.checked = false;
            checkbox.disabled = false;
        });
    };

    /**
     * Resets all Select Courses links to their default state.
     *
     * @return {Promise<void>}
     */
    const resetDefaultSelectCourses = async() => {
        const selectItems = document.querySelectorAll(selectors.selectAllId);

        Promise.all(Array.from(selectItems).map(async(selectItem) => {
            selectItem.setAttribute('data-forceselected', 'false');
            selectItem.textContent = await getString('assignselectcourses', 'local_notificationsagent');
        }));
    };
};
