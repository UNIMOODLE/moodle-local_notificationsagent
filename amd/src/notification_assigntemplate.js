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
 *
 * @module   local_notificationsagent/assigntemplate
 * @copyright 2023, ISYC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['core/str'], function(str) {
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
    } 

    return {

        init: function() {
            var module = this;
            var idtemplate;
            $('#assignTemplateModal').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                idtemplate = button.data('idtemplate');
                var isRuleForced;
                var htmlRuleForced;

                let ruleCard = $('div[id="card-' + idtemplate + '"]');
                $('#assignTemplateModal #forced-content').empty();
                if (ruleCard.data('type') === RULE_TYPE[0]) {
                    isRuleForced = ruleCard.data('forced');
                    str.get_string('assignforced', 'local_notificationsagent').then(forcedRule => {
                        htmlRuleForced = '<div class="custom-control custom-checkbox mr-1">';
                            htmlRuleForced += '<input id="forced" type="checkbox" class="custom-control-input">';
                            htmlRuleForced += '<label class="custom-control-label" for="forced">'+forcedRule+'</label>';
                        htmlRuleForced += '</div>';
                        $('#assignTemplateModal #forced-content').append(htmlRuleForced);
                        if (!isRuleForced) {
                            $('#assignTemplateModal #forced-content #forced').prop('checked', true);
                        }
                    });
                }
    
                var modal = $(this);
                modal.find('.modal-title > span').text($('#card-'+idtemplate+' .badge-type').text());
                modal.find('.badge').text($('#card-'+idtemplate+' .badge-type').text());
                modal.find('.badge').attr('class', 'mr-2 '+$('#card-'+idtemplate+' .badge-type').attr('class'));
                
                modal.find('.modal-body .name').text($('#card-'+idtemplate+' .name').text());

                /* Rellenar cursos asignados */
                $.ajax({
                    type: "POST",
                    url: '/local/notificationsagent/assignrule.php',
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
                                $('#listitem-category-' + categoryid + ' input[type="checkbox"]').prop("checked", true);
                            }
                        });

                        data['course'].forEach((courseid) => {
                            let course = $('#assignTemplateModal .category-listing input#checkboxcourse-' + courseid);
                            if (course.length) {
                                course.prop('checked', true);
                            }
                        });
                    },
                    error: function(XMLHttpRequest, textStatus, errorThrown) { 
                        console.log("Status: " + textStatus); 
                        console.log(errorThrown); 
                    },
                    dataType: 'json'
                });
            });
            $('#assignTemplateModal').on('hide.bs.modal', function () {
                $('#assignTemplateModal .custom-control-input').prop('checked', false);
            });
            $('#assignTemplateModal .collapse').on('show.bs.collapse', function () {
                $(this).parents('.listitem-category').removeClass('collapsed');
            });
            $('#assignTemplateModal .collapse').on('hide.bs.collapse', function () {
                $(this).parents('.listitem-category').addClass('collapsed');
            });

            /* checkbox */
            $('#assignTemplateModal #course-category-select-all').on('click', function(){
                var checkassign = $('#assignTemplateModal .category-listing .custom-control-input');
                checkassign.prop('checked', $(this).prop('checked'));
            });
            $('#assignTemplateModal .category-listing').on('change', 'input[type=checkbox]', function () {
                var checkssubcategoriescourses = '#category-listing-content-'+$(this).attr("id").replace('checkboxcategory-', '')+' .custom-control-input';
                $('#assignTemplateModal .category-listing '+checkssubcategoriescourses).prop('checked', $(this).prop('checked'));
            });

            $('#assignTemplateModal #saveassignTemplateModal').on('click', function() {
                var data = {};
                data['category'] = [];
                data['course'] = [];
                var allCategories = [];

                forced = RULE_FORCED_TYPE.NONFORCED;
                if ($('#assignTemplateModal #forced').prop('checked')) {
                    forced = RULE_FORCED_TYPE.FORCED;
                }

                let mainCategories = $('#assignTemplateModal #category-listing-content-0 > li[id^="listitem-category-"]').has('input[id^="checkboxcategory-"]:checked');

                mainCategories.each(function() {
                    let items = $('#' + this.id + ' input[id^="checkboxcategory-"]:checked').map(function() {
                        let id = $(this).attr('id').replace('checkboxcategory-', '');
                        let parent = $(this).data('parent').replace('#category-listing-content-', '');

                        if ($.inArray(id, allCategories) === -1) {
                            allCategories.push(id);
                        }
                        
                        return {id: id, parent: parent};
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
                    
                    return {id: id, category: category};
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
                    url: '/local/notificationsagent/assignrule.php',
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
        },
        loopatfirstparent: function(idparent, arrayParents = []){
            var module = this;
            
            if(idparent != '#category-listing-content-0'){
                arrayParents.push(idparent.replace('#category-listing-content-', ''));
                var dataparent = $('#assignTemplateModal .category-listing #listitem-category-'+idparent.replace('#category-listing-content-', '')+' > .category-listing-header .custom-control-input').attr('data-parent');
                return module.loopatfirstparent(dataparent, arrayParents);
            }else{
                return arrayParents;
            }
        }
    };
});
