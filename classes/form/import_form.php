<?php
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
 * Version details
 *
 * @package    local_notificationsagent
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_notificationsagent\form;

use context;
use moodle_exception;
use moodle_url;
use core_form\dynamic_form;
use local_notificationsagent\rule;

/**
 * Import modal form
 */
class import_form extends dynamic_form {

    /**
     * Process the form submission
     *
     * @return array
     * @throws moodle_exception
     */
    public function process_dynamic_submission(): array {
        global $CFG, $DB;

        $context = $this->get_context_for_dynamic_submission();
        $courseid = $context->instanceid;

        // Read json file.
        $filepath = $this->save_temp_file('importfile');
        $file = file_get_contents($filepath);
        $data = json_decode($file, true);

        array_walk_recursive($data, function(&$value) {
            $value = html_entity_decode(format_text($value, FORMAT_HTML), ENT_QUOTES);
        });

        $data = $this->array_to_object($data, 0);

        $data->courseid = $courseid;
        $rule = new rule();
        $rule->save_form($data);

        $returnurl = new moodle_url('/local/notificationsagent/index.php', [
            'courseid' => $courseid,
            'statusmsg' => 'import_success',
        ]);
        return [
            'result' => true,
            'url' => $returnurl->out(false),
        ];
    }

    /**
     * Array to obj function
     * @param $array
     * @param $recursive
     * @param $countrecursive
     * @return \stdClass
     */
    public function array_to_object($array, $recursive = true, &$countrecursive = 0) {
        $obj = new \stdClass();

        foreach ($array as $k => $v) {
            if (strlen($k)) {
                if (is_array($v) && (($recursive === true) || ($countrecursive < $recursive))) {
                    $countrecursive++;
                    $obj->{$k} = $this->array_to_object($v, $countrecursive);
                } else {
                    $obj->{$k} = $v;
                }
            }
        }

        return $obj;
    }

    /**
     * Get context
     *
     * @return context
     */
    protected function get_context_for_dynamic_submission(): context {
        $courseid = $this->optional_param('courseid', null, PARAM_INT);
        $context = \context_course::instance($courseid);
        return $context;
    }

    /**
     * Set data
     *
     * @return void
     */
    public function set_data_for_dynamic_submission(): void {
        $data = (object) [
            'courseid' => $this->optional_param('courseid', 0, PARAM_INT),
        ];
        $this->set_data($data);
    }

    /**
     * Has access ?
     *
     * @return void
     * @throws moodle_exception
     */
    protected function check_access_for_dynamic_submission(): void {
        if (!has_capability('local/notificationsagent:importrule', $this->get_context_for_dynamic_submission())) {
            throw new moodle_exception('importrulemissingcapability', 'local_notificationsagent');
        }
    }

    /**
     * Get page URL
     *
     * @return moodle_url
     */
    protected function get_page_url_for_dynamic_submission(): moodle_url {
        $courseid = $this->optional_param('courseid', null, PARAM_INT);
        return new moodle_url('/local/notificationsagent/index.php', ['courseid' => $courseid]);
    }

    /**
     * Form definition
     *
     * @return void
     */
    protected function definition() {
        $mform = $this->_form;
        $mform->addElement('html', \html_writer::div(get_string('import_desc', 'local_notificationsagent'), 'py-3'));
        $mform->addElement('hidden', 'courseid');
        $mform->setType('courseid', PARAM_INT);

        $mform->addElement('filepicker', 'importfile', get_string('import_choose', 'local_notificationsagent'), null,
            ['accepted_types' => '.json']);
        $mform->addRule('importfile', null, 'required');
    }
}
