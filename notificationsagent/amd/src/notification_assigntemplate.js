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

define([], function() {

    return {

        init: function() {
            var module = this;
            $('#assignTemplateModal').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var idtemplate = button.data('idtemplate');
    
                var modal = $(this);
                modal.find('.modal-title > span').text($('#card-'+idtemplate+' .badge').text());
                modal.find('.badge').text($('#card-'+idtemplate+' .badge').text());
                modal.find('.badge').attr('class', 'mr-2 '+$('#card-'+idtemplate+' .badge').attr('class'));
                
                modal.find('.modal-body .name').text($('#card-'+idtemplate+' .name').text());

                /* Rellenar cursos asignados */
                $.ajax({
                    type: "POST",
                    url: '/local/notificationsagent/lib.php',
                    data: {
                        idRule: idtemplate
                    },
                    success: function(listofCoursesAssigned) {
                        listofCoursesAssigned.forEach((idcourse) => {
                            var courseassigned = $('#assignTemplateModal .category-listing input#checkboxcourse-'+idcourse);
                            if(courseassigned.length){
                                courseassigned.prop('checked', true);
                                var arrayCategoriesParents = module.loopatfirstparent(courseassigned.attr('data-parent'));
                                arrayCategoriesParents.forEach((idcategory) => {
                                    module.checkhascategoryparent(idcategory);
                                });
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

                if($(this).attr('data-parent') != '#category-listing-content-0'){
                    var arrayCategoriesParents = module.loopatfirstparent($(this).attr('data-parent'));
                    arrayCategoriesParents.forEach((idcategory) => {
                        module.checkhascategoryparent(idcategory);
                    });
                }

                /* Check all checks true */
                var arrayCategoriesfirstlevel = $('#assignTemplateModal .category-listing input[type=checkbox][data-parent="#category-listing-content-0"]');
                var allelementsatchecked = true;
                arrayCategoriesfirstlevel.each(function(){
                    if(!$(this).prop('checked')){
                        allelementsatchecked = false;
                        return false;
                    }
                });
                if(allelementsatchecked){
                    $('#assignTemplateModal #course-category-select-all').prop('checked', true);
                }else{
                    $('#assignTemplateModal #course-category-select-all').prop('checked', '');
                }
            });

            $('#assignTemplateModal #saveassignTemplateModal').on('click', function(){
                var arrayCourses = [];
                
                var checkassign = $('#assignTemplateModal .category-listing input[id^="checkboxcourse-"]');
                checkassign.each(function(){
                    if($(this).prop('checked') == true){
                        arrayCourses.push($(this).attr('id').replace('checkboxcourse-', ''));
                    }
                });
                console.log(arrayCourses);
                //Se necesitará hacer AJAX para guardar en BD y al guardar sacar un alert de que se ha guardado correctamente después de cerrar el modal
            });
        },
        checkhascategoryparent: function(idparent) {
            var allelementscatchecked = true;
            $('#assignTemplateModal .category-listing #category-listing-content-'+idparent+' .custom-control-input').each(function(){
                if(!$(this).prop('checked')){
                    allelementscatchecked = false;
                    return false;
                }
            });
            if(allelementscatchecked){
                $('#assignTemplateModal .category-listing #listitem-category-'+idparent+' > .category-listing-header .custom-control-input').prop('checked', true);
            }else{
                $('#assignTemplateModal .category-listing #listitem-category-'+idparent+' > .category-listing-header .custom-control-input').prop('checked', '');
            }
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
