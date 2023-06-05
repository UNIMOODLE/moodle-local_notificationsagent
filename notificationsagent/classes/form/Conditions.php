<?php

class Conditions
{

    public function init()
    {
        
    }

    public function checkSession()
    {
        if(session_status() == PHP_SESSION_NONE){
            session_start();
        }
    }

    public function constructConditions(&$mform, $idCourse){
        $this->checkSession();
        //get BD conditions + NOTIFICATIONS_CONDITIONS
        
        //Recorremos el array completo de conditions y lo construimos en un array de mform
        if(isset($_SESSION['NOTIFICATIONS_CONDITIONS'][$idCourse])){
            $conditionsItems = $_SESSION['NOTIFICATIONS_CONDITIONS'][$idCourse];
            $idCondition = 1;
            foreach ($conditionsItems as $key => $condition) {
                $arrayElement = array('count' => '', 'type' => '');
                foreach($condition['elements'] as $element){
                    $arrayElement['countelementsduplicate'][$element] = 0;
                }
                $idElement = 1;

                $conditionRemove = "<i class='btn icon fa fa-trash align-top' id='condition".$key."_remove'></i>";

                /* Cambiar orden elemento array */ 
                /*
                $upbuttondisabled = "";
                $downbuttondisabled = "";
                if($idCondition == 1){
                    $upbuttondisabled = " disabled";
                }
                if($idCondition == count($conditionsItems)){
                    $downbuttondisabled = " disabled";
                }
                $conditionup = "<i class='btn icon fa fa-caret-square-o-up align-top".$upbuttondisabled."' id='condition".$key."_actionup'></i>"; 
                $conditiondown = "<i class='btn icon fa fa-caret-square-o-down align-top".$downbuttondisabled."' id='condition".$key."_actiondown'></i>"; 
                $conditionButtons = $conditionRemove.$conditionup.$conditiondown;*/

                $conditionButtons = $conditionRemove;
                $titleH = '<h5>'.$idCondition.') '.$condition['title'].$conditionButtons.'</h5>';
                $mform->addElement('html', $titleH);

                foreach($condition['elements'] as $element){
                    $arrayElement['count'] = $idElement;
                    $arrayElement['type'] = $element;
                    $duplicate = array_count_values($condition['elements'])[$element] > 1;
                    if($duplicate){
                        $arrayElement['countelementsduplicate'][$element] += 1; 
                    }
                    $this->listOfConditions($mform, $idCourse, $idCondition, $arrayElement, $duplicate);
                    $idElement++;
                }
                $idCondition++;
            }
        }
    }

    public function listOfConditions(&$mform, $idCourse, $id, $arrayElement, $duplicateElement = false){
        global $CFG, $DB, $USER, $COURSE;
        switch($arrayElement['type']){
            case '[TTTT]':
                $typeElement = $arrayElement['type'];
                if($duplicateElement){
                    $typeElement = substr_replace($typeElement, '-'.$arrayElement['countelementsduplicate'][$typeElement], 5, 0);
                }
                $time_group = array();
                $time_group[] =& $mform->createElement('float', 'condition'.$id.'_element'.$arrayElement['count'].'_time_days', '', array('class' => 'mr-2', 'size' => '7', 'maxlength' => '3', 'placeholder' => 'Horas', 'oninput' => 'this.value = this.value.replace(/[^0-9]/g, "").replace(/(\..*)\./g, "$1")'));
                $time_group[] =& $mform->createElement('float', 'condition'.$id.'_element'.$arrayElement['count'].'_time_hours', '', array('class' => 'mr-2', 'size' => '7', 'maxlength' => '2', 'placeholder' => 'Minutos','oninput' => 'this.value = this.value.replace(/[^0-9]/g, "").replace(/(\..*)\./g, "$1")'));
                $time_group[] =& $mform->createElement('float', 'condition'.$id.'_element'.$arrayElement['count'].'_time_minutes', '', array('class' => 'mr-2', 'size' => '7', 'maxlength' => '2', 'placeholder' => 'Segundos','oninput' => 'this.value = this.value.replace(/[^0-9]/g, "").replace(/(\..*)\./g, "$1")'));
                $mform->addGroup($time_group, 'condition'.$id.'_group'.$arrayElement['count'].'time', get_string('editrule_condition_element_time', 'local_notificationsagent', array('typeelement' => $typeElement)), array(' '), false);
                break;
            case '[AAAA]':
                $typeElement = $arrayElement['type'];
                if($duplicateElement){
                    $typeElement = substr_replace($typeElement, '-'.$arrayElement['countelementsduplicate'][$typeElement], 5, 0);
                }
                $listActivities = array();
                foreach (get_all_course_modules($idCourse) as $modulesvalue) {
                    $activity = $modulesvalue->course_module_instance;
                    $listActivities["activity-".$activity->id] = format_string($activity->name."[".$activity->id."]", true);
                }
                $mform->addElement('select', 'condition'.$id.'_element'.$arrayElement['count'].'_activities', get_string('editrule_condition_element_activity', 'local_notificationsagent', array('typeelement' => $typeElement)), $listActivities);
                break;
            case '[FFFF]':
                $typeElement = $arrayElement['type'];
                if($duplicateElement){
                    $typeElement = substr_replace($typeElement, '-'.$arrayElement['countelementsduplicate'][$typeElement], 5, 0);
                }
                $stringDate = get_string('editrule_condition_element_date_from', 'local_notificationsagent', array('typeelement' => $typeElement));
                if($arrayElement['count'] > 1){
                    $stringDate = get_string('editrule_condition_element_date_to', 'local_notificationsagent', array('typeelement' => $typeElement));
                }
                $mform->addElement('date_selector', 'condition'.$id.'_element'.$arrayElement['count'].'_date', $stringDate);
                break;
        }
    }

}
