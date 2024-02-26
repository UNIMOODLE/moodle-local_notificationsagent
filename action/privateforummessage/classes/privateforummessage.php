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

namespace notificationsaction_privateforummessage;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../lib.php');

use local_notificationsagent\rule;
use local_notificationsagent\notificationactionplugin;

class privateforummessage extends notificationactionplugin {

    /** @var UI ELEMENTS */
    public const NAME = 'privateforummessage';
    public const UI_MESSAGE = 'message';
    public const UI_TITLE = 'title';

    public function get_title() {
        return get_string('privateforummessage_action', 'notificationsaction_privateforummessage');
    }

    public function get_elements() {
        return ['[FFFF]', '[TTTT]', '[BBBB]'];
    }

    public function get_ui($mform, $id, $courseid, $type) {
        $this->get_ui_title($mform, $id, $type);

        // Title.
        $title = $mform->createElement(
            'text', $this->get_name_ui($id, self::UI_TITLE),
            get_string(
                'editrule_action_element_title', 'notificationsaction_privateforummessage',
                ['typeelement' => '[TTTT]']
            ), ['size' => '64']
        );

        $editoroptions = [
            'maxfiles' => EDITOR_UNLIMITED_FILES,
            'trusttext' => true,
        ];

        // Message.
        $message = $mform->createElement(
            'editor', $this->get_name_ui($id, self::UI_MESSAGE),
            get_string(
                'editrule_action_element_message', 'notificationsaction_privateforummessage',
                ['typeelement' => '[BBBB]']
            ),
            ['class' => 'fitem_id_templatevars_editor'], $editoroptions
        );
        // Forum.
        $forumname = [];
        $forumlist = get_coursemodules_in_course('forum', $courseid);
        foreach ($forumlist as $forum) {
            $forumname[$forum->id] = $forum->name;
        }
        asort($forumname);
        if (empty($forumname)) {
            $forumname['0'] = 'FFFF';
            $forumname['-1'] = 'Announcements';
        }
        $cm = $mform->createElement(
            'select',
            $this->get_name_ui($id, self::UI_ACTIVITY),
            get_string(
                'editrule_action_element_forum',
                'notificationsaction_privateforummessage',
                ['typeelement' => '[FFFF]']
            ),
            $forumname
        );

        $this->placeholders($mform, $id, $type);
        $mform->insertElementBefore($title, 'new' . $type . '_group');
        $mform->insertElementBefore($message, 'new' . $type . '_group');
        $mform->insertElementBefore($cm, 'new' . $type . '_group');
        $mform->setType($this->get_name_ui($id, self::UI_TITLE), PARAM_TEXT);
        $mform->addRule($this->get_name_ui($id, self::UI_TITLE), null, 'required', null, 'client');
        $mform->setType($this->get_name_ui($id, self::UI_MESSAGE), PARAM_RAW);
        $mform->addRule($this->get_name_ui($id, self::UI_MESSAGE), null, 'required', null, 'client');

    }

    public function get_subtype() {
        return get_string('subtype', 'notificationsaction_privateforummessage');
    }

    public function check_capability($context) {
        return has_capability('local/notificationsagent:privateforummessage', $context)
            && has_capability('mod/forum:addnews', $context)
            && has_capability('mod/forum:addquestion', $context)
            && has_capability('mod/forum:startdiscussion', $context);
    }

    /**
     * @param array $params
     *
     * @return mixed
     */
    protected function convert_parameters($id, $params) {
        $params = (array) $params;
        $title = $params[$this->get_name_ui($id, self::UI_TITLE)] ?? 0;
        $message = $params[$this->get_name_ui($id, self::UI_MESSAGE)] ?? 0;
        $forum = $params[$this->get_name_ui($id, self::UI_ACTIVITY)] ?? 0;
        $this->set_parameters(
            json_encode([self::UI_TITLE => $title, self::UI_MESSAGE => $message, self::UI_ACTIVITY => $forum])
        );
        return $this->get_parameters();
    }

    public function process_markups(&$content, $courseid, $options = null) {
        $jsonparams = json_decode($this->get_parameters(), false);

        // Check if activity is found, if is not, return [FFFF].
        $activityname = '[FFFF]';
        $cmid = $jsonparams->{self::UI_ACTIVITY};
        if ($cmid && $forum = get_fast_modinfo($courseid)->get_cm($cmid)) {
            $activityname = $forum->name;
        }

        $message = $jsonparams->{self::UI_MESSAGE}->text ?? '';
        $paramstoteplace = [
            $activityname,
            shorten_text(str_replace('{' . rule::SEPARATOR . '}', ' ', $jsonparams->{self::UI_TITLE})),
            shorten_text(format_string(str_replace('{' . rule::SEPARATOR . '}', ' ', $message))),
        ];

        $humanvalue = str_replace($this->get_elements(), $paramstoteplace, $this->get_title());

        array_push($content, $humanvalue);
    }

    public function execute_action($context, $params) {
        // Post a message on a forum.

        global $DB;
        $placeholdershuman = json_decode($params);
        $postsubject = format_text($placeholdershuman->title, FORMAT_PLAIN);
        $postmessage = notificationactionplugin::get_message_by_timesfired($context, $placeholdershuman->message);
        $time = 0;
        $modinfo = get_fast_modinfo($context->get_courseid(), -1);
        $forumid = $modinfo->get_cm($placeholdershuman->{self::UI_ACTIVITY})->instance;
        $userid = $context->get_userid();
        $courseid = $context->get_courseid();
        // Set up the Moodle Web Services client.

        $timenow = time();
        $pluginname = 'forumnoreply';

        $conditions = $context->get_rule()->get_conditions();

        foreach ($conditions as $condition) {
            if ($condition->get_pluginname() == $pluginname) {
                $decode = $condition->get_parameters();
                $param = json_decode($decode);
                $time = $param->{self::UI_TIME};
                break;
            }
        }

        $sqlthreads = "SELECT DISTINCT fd.id AS discussionid, fp.id AS postid
                      FROM {forum_discussions} fd
                      JOIN {forum_posts} fp ON fp.discussion=fd.id AND fp.parent = 0
                 LEFT JOIN {forum_posts} fp2 ON fp.id = fp2.parent
                     WHERE fd.forum = :forum
                       AND fd.course = :course
                       AND fd.timestart >= :timestart
                       AND (fd.timeend = :timeend OR fd.timeend > :timenow)
                       AND timemodified < :timenowandtime
                       AND fp2.id IS NULL
                       AND fd.userid = :usertrigger
            ";

        $threads = $DB->get_record_sql($sqlthreads, [
            'forum' => $forumid,
            'course' => $courseid,
            'timestart' => 0,
            'timeend' => 0,
            'timenow' => $timenow,
            'timenowandtime' => $timenow + $time,
            'usertrigger' => $userid,
        ]);

        if (empty($threads)) {
            return false;
        }

        $obj = new \stdClass();
        $obj->discussion = $threads->discussionid;
        $obj->parent = $threads->postid;
        $obj->privatereplyto = $userid;
        $obj->userid = get_admin()->id;
        $obj->created = $timenow;
        $obj->modified = $timenow;
        $obj->mailed = FORUM_MAILED_PENDING;
        $obj->subject = $postsubject;
        $obj->message = $postmessage;
        $obj->forum = $forumid;
        $obj->course = $courseid;

        return notificationsaction_privateforummessage_forum_add_post($obj);

    }

    public function is_generic() {
        return false;
    }

    /**
     * Returns the parameters to be replaced in the placeholders
     *
     * @return string $json Parameters
     */
    public function get_parameters_placeholders() {
        $parameters = json_decode($this->get_parameters());

        return json_encode([
            'title' => $parameters->{self::UI_TITLE},
            'message' => $parameters->{self::UI_MESSAGE}->text,
            'cmid' => $parameters->{self::UI_ACTIVITY},
        ]);
    }
}
