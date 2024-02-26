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
 * @package    local_notificationsagent
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_notificationsagent\engine;

defined('MOODLE_INTERNAL') || die();
global $CFG;

use local_notificationsagent\rule;
use local_notificationsagent\evaluationcontext;
use local_notificationsagent\notificationsagent;
use local_notificationsagent\notificationplugin;

class notificationsagent_engine {
    public static function notificationsagent_engine_evaluate_rule($ruleids, $timeaccess, $userid, $courseid, $triggercondition) {
        foreach ($ruleids as $ruleid) {
            $rule = rule::create_instance($ruleid);
            $context = new evaluationcontext();
            $context->set_timeaccess($timeaccess);
            $context->set_courseid($courseid);
            $context->set_userid($userid);
            $context->set_rule($rule);
            $context->set_triggercondition($triggercondition);

            if ($userid == notificationsagent::GENERIC_USERID && !$rule->get_isgeneric()) {
                $coursecontext = \context_course::instance($context->get_courseid());
                $users = notificationsagent::get_usersbycourse($coursecontext);
                foreach ($users as $user) {
                    $context->set_userid($user->id);
                    if ($context->is_evaluate($rule)) {
                        $result = $rule->evaluate($context);
                        if ($result) {
                            $context->set_usertimesfired($rule->set_launched($context));
                            $actions = $rule->get_actions();
                            foreach ($actions as $action) {
                                $actionparams = json_decode($action->get_parameters(), true);
                                $hasuser = $actionparams[notificationplugin::UI_USER] ?? false;

                                // If the action has a specific user, send the action only to that user.
                                // otherwise, send the action for each user.
                                if ($hasuser
                                    && !has_capability('local/notificationsagent:managecourserule', $coursecontext, $hasuser)) {
                                    if (($context->get_userid() == notificationsagent::GENERIC_USERID)
                                        || ($context->get_userid() == $hasuser)
                                    ) {
                                        $context->set_userid($hasuser);
                                    } else {
                                        continue;
                                    }
                                }

                                $parameters = $rule->replace_placeholders(
                                    $context,
                                    $action->get_parameters_placeholders(),
                                );
                                $result = $action->execute_action($context, $parameters);
                                if (!$result) {
                                    $parameters = "ERROR" . $parameters;
                                }
                                $rule->record_report(
                                    $ruleid, $context->get_userid(), $context->get_courseid(), $action->get_id(),
                                    $parameters, $timeaccess
                                );
                            }
                        }
                    }
                }
            } else {
                if ($context->is_evaluate($rule)) {
                    $result = $rule->evaluate($context);
                    $contextcourse = \context_course::instance($context->get_courseid());
                    if ($result) {
                        $context->set_usertimesfired($rule->set_launched($context));
                        $actions = $rule->get_actions();
                        foreach ($actions as $action) {
                            $actionparams = json_decode($action->get_parameters(), true);
                            $hasuser = $actionparams[notificationplugin::UI_USER] ?? false;

                            // If the action has a specific user, send the action only to that user.
                            // otherwise, send the action for each user.
                            if ($hasuser &&
                                !has_capability('local/notificationsagent:managecourserule', $contextcourse, $hasuser)) {
                                if (($context->get_userid() == notificationsagent::GENERIC_USERID)
                                    || ($context->get_userid() == $hasuser)
                                ) {
                                    $context->set_userid($hasuser);
                                } else {
                                    continue;
                                }
                            }

                            if ($action->is_send_once($context->get_userid())) {
                                $parameters = $rule->replace_placeholders(
                                    $context,
                                    $action->get_parameters_placeholders(),
                                );
                                $result = $action->execute_action($context, $parameters);
                                if (!$result) {
                                    $parameters = "ERROR to perfom action";
                                }
                                $rule->record_report(
                                    $ruleid, $context->get_userid() == notificationsagent::GENERIC_USERID ? get_admin()->id
                                    : $context->get_userid(), $context->get_courseid(), $action->get_id(),
                                    $parameters, $timeaccess
                                );
                            } else {
                                $coursecontext = \context_course::instance($context->get_courseid());
                                $users = notificationsagent::get_usersbycourse($coursecontext);
                                foreach ($users as $user) {
                                    $context->set_userid($user->id);
                                    $actionparams = json_decode($action->get_parameters(), true);
                                    $hasuser = $actionparams[notificationplugin::UI_USER] ?? false;

                                    // If the action has a specific user, send the action only to that user.
                                    // otherwise, send the action for each user.
                                    if ($hasuser &&
                                        !has_capability('local/notificationsagent:managecourserule', $contextcourse, $hasuser)) {
                                        if (($context->get_userid() == notificationsagent::GENERIC_USERID)
                                            || ($context->get_userid() == $hasuser)
                                        ) {
                                            $context->set_userid($hasuser);
                                        } else {
                                            continue;
                                        }
                                    }
                                    $parameters = $rule->replace_placeholders(
                                        $context,
                                        $action->get_parameters_placeholders(),
                                    );
                                    $result = $action->execute_action($context, $parameters);
                                    if (!$result) {
                                        $parameters = "ERROR to perfom action";
                                    }
                                    $rule->record_report(
                                        $ruleid, $context->get_userid(), $context->get_courseid(), $action->get_id(),
                                        $parameters, $timeaccess
                                    );
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}

