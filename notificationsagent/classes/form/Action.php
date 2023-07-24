<?php
// This file is part of Moodle - https://moodle.org/
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
class Action {

    public function init() {
    }

    public function constructAction(&$mform, $idCourse) {
        global $SESSION;
        // get BD actions + ACTIONS.

        // Recorremos el array completo de actions y lo construimos en un array de mform.
        if(isset($SESSION->NOTIFICATIONS['ACTIONS'])){
            $actionsItems = $SESSION->NOTIFICATIONS['ACTIONS'];
            $idAction = 1;
            foreach ($actionsItems as $key => $action) {
                $actionRemove = "<i class='btn icon fa fa-trash align-top' id='action".$key."_remove'></i>";
                $actionButtons = $actionRemove;
                $titleH = '<h5>'.$idAction.') '.$action['title'].$actionButtons.'</h5>';
                
                $mform->addElement('html', $titleH);
                //$pluginname[$idAction] =  substr($SESSION->NOTIFICATIONS['FORMDEFAULT']['id_newAction_select'], 0, strpos($SESSION->NOTIFICATIONS['FORMDEFAULT']['id_newAction_select'], ':'));
                $pluginname[$idAction] =  $action['name'];
                $this->listOfActions($mform, $idCourse, $idAction, $pluginname[$idAction]);
                
                $idAction++;
            }
        }
    }


    public function listOfActions(&$mform, $idCourse, $id, $pluginname){
        global $CFG, $DB, $USER, $COURSE, $SESSION;
        $context = \context_course::instance($idCourse);
        $subtype='action'; // TODO
            
            require_once($CFG->dirroot . '/local/notificationsagent/' . $subtype . '/' . $pluginname . '/' . $pluginname . '.php');
            $pluginclass = 'notificationsagent_' . $subtype . '_' . $pluginname;
            $pluginobj = new $pluginclass();
            $pluginobj->get_ui($mform, $id, $idCourse);

        //switch($arrayElement['type']){
        //    case '[TTTT]':
        //        $typeElement = $arrayElement['type'];
        //        if($duplicateElement){
        //            $typeElement = substr_replace($typeElement, '-'.$arrayElement['countelementsduplicate'][$typeElement], 5, 0);
        //        }
        //        $mform->addElement('text', 'action'.$id.'_element'.$arrayElement['count'].'_title', get_string('editrule_action_element_title', 'local_notificationsagent', array('typeelement' => $typeElement)), array('size' => '64'));
        //        if (!empty($CFG->formatstringstriptags)) {
        //            $mform->setType('action'.$id.'_element'.$arrayElement['count'].'_title', PARAM_TEXT);
        //        } else {
        //            $mform->setType('action'.$id.'_element'.$arrayElement['count'].'_title', PARAM_CLEANHTML);
        //        }
        //        if(!empty($SESSION->NOTIFICATIONS['FORMDEFAULT']['id_action'.$id.'_element'.$arrayElement['count'].'_title'])){
        //            $mform->setDefault('action'.$id.'_element'.$arrayElement['count'].'_title', $SESSION->NOTIFICATIONS['FORMDEFAULT']['id_action'.$id.'_element'.$arrayElement['count'].'_title']);
        //        }
        //        break;
        //    case '[BBBB]':
        //        $typeElement = $arrayElement['type'];
        //        if($duplicateElement){
        //            $typeElement = substr_replace($typeElement, '-'.$arrayElement['countelementsduplicate'][$typeElement], 5, 0);
        //        }
        //        $editoroptions = array(
        //            'maxfiles' => EDITOR_UNLIMITED_FILES,
        //            'trusttext' => true
        //        );
        //        if(!empty($SESSION->NOTIFICATIONS['FORMDEFAULT']['id_action'.$id.'_element'.$arrayElement['count'].'_message'])){
        //            $mform->addElement('editor', 'action'.$id.'_element'.$arrayElement['count'].'_message', get_string('editrule_action_element_message', 'local_notificationsagent', array('typeelement' => $typeElement)),
        //            ['class' => 'fitem_id_templatevars_editor'], $editoroptions)->setValue( array('text' => $SESSION->NOTIFICATIONS['FORMDEFAULT']['id_action'.$id.'_element'.$arrayElement['count'].'_message']) );
        //        }else{
        //            $mform->addElement('editor', 'action'.$id.'_element'.$arrayElement['count'].'_message', get_string('editrule_action_element_message', 'local_notificationsagent', array('typeelement' => $typeElement)),
        //            ['class' => 'fitem_id_templatevars_editor'], $editoroptions);
        //        }
        //        $mform->setType('action'.$id.'_message', PARAM_RAW);
        //        $this->placeholders($mform, 'action'.$id);
        //        break;
        //    case '[UUUU]':
        //        $typeElement = $arrayElement['type'];
        //        if($duplicateElement){
        //            $typeElement = substr_replace($typeElement, '-'.$arrayElement['countelementsduplicate'][$typeElement], 5, 0);
        //        }
        //        $enrolledusers = get_enrolled_users($context);
        //        $listUsers = array();
        //        foreach ($enrolledusers as $uservalue) {
        //            $listUsers["user-".$uservalue->id] = format_string($uservalue->firstname." ".$uservalue->lastname." [".$uservalue->email."]", true);
        //        }
        //        $mform->addElement('select', 'action'.$id.'_element'.$arrayElement['count'].'_user', get_string('editrule_action_element_user', 'local_notificationsagent', array('typeelement' => $typeElement)), $listUsers);
        //        if(!empty($SESSION->NOTIFICATIONS['FORMDEFAULT']['id_action'.$id.'_element'.$arrayElement['count'].'_user'])){
        //            $mform->setDefault('action'.$id.'_element'.$arrayElement['count'].'_user', $SESSION->NOTIFICATIONS['FORMDEFAULT']['id_action'.$id.'_element'.$arrayElement['count'].'_user']);
        //        }
        //        break;
        //    case '[FFFF]':
        //        $typeElement = $arrayElement['type'];
        //        if($duplicateElement){
        //            $typeElement = substr_replace($typeElement, '-'.$arrayElement['countelementsduplicate'][$typeElement], 5, 0);
        //        }
        //        $modinfo = get_fast_modinfo($COURSE);
        //        $forums = $DB->get_records_sql("
        //            SELECT f.*,
        //                d.maildigest
        //            FROM {forum} f
        //            LEFT JOIN {forum_digests} d ON d.forum = f.id AND d.userid = ?
        //            WHERE f.course = ?
        //        ", array($USER->id, $idCourse));
        //        $listForums = array();
        //        foreach ($modinfo->get_instances_of('forum') as $forumid => $cm) {
        //            $forum = $forums[$forumid];
        //            $listForums["forum-".$forum->id] = format_string($forum->name, true);
        //        }
        //        $mform->addElement('select', 'action'.$id.'_element'.$arrayElement['count'].'_forum', get_string('editrule_action_element_forum', 'local_notificationsagent', array('typeelement' => $typeElement)), $listForums);
        //        if(!empty($SESSION->NOTIFICATIONS['FORMDEFAULT']['action'.$id.'_element'.$arrayElement['count'].'_forum'])){
        //            $mform->setDefault('action'.$id.'_element'.$arrayElement['count'].'_forum', $SESSION->NOTIFICATIONS['FORMDEFAULT']['id_action'.$id.'_element'.$arrayElement['count'].'_forum']);
        //        }
        //        break;
        //    case '[GGGG]':
        //        $typeElement = $arrayElement['type'];
        //        if($duplicateElement){
        //            $typeElement = substr_replace($typeElement, '-'.$arrayElement['countelementsduplicate'][$typeElement], 5, 0);
        //        }
        //        $grupos = array();
        //        $list_groups  = groups_get_all_groups($COURSE->id);
        //        foreach ($list_groups as $group){
        //            $grupos["grupo-".$group->id] = format_string($group->name);
        //        }
        //        $mform->addElement('select', 'action'.$id.'_element'.$arrayElement['count'].'_group', get_string('editrule_action_element_group', 'local_notificationsagent', array('typeelement' => $typeElement)), $grupos);
        //        if(!empty($SESSION->NOTIFICATIONS['FORMDEFAULT']['action'.$id.'_element'.$arrayElement['count'].'_group'])){
        //            $mform->setDefault('action'.$id.'_element'.$arrayElement['count'].'_group', $SESSION->NOTIFICATIONS['FORMDEFAULT']['id_action'.$id.'_element'.$arrayElement['count'].'_group']);
        //        }
        //}
    }

    /**
     * Add notification placeholder fields in form fields.
     *
     * @param  mixed $mform
     * @return void
     */
    private function placeholders(&$mform, $idaction) {
        $vars = array(
            'User_FirstName', 'User_LastName_s', 'User_Email', 'User_Username', 'User_Institution', 'User_Department', 'User_Address', 'User_City', 'User_Country', 
            'Course_FullName', 'Course_ShortName', 'Course_Url',
            'Site_FullName', 'Site_ShortName');
        $mform->addElement('html', "<div class='form-group row fitem'> <div class='col-md-3'></div>
        <div class='col-md-9'><div class='notificationvars' id='notificationvars_".$idaction."'>");
        $optioncount = 0;
        foreach ($vars as $option) {
            $mform->addElement('html', "<a href='#' data-text='$option' class='clickforword'><span>$option</span></a> ");
            $optioncount++;
        }
        $mform->addElement('html', "</div></div></div>");
    }

}

