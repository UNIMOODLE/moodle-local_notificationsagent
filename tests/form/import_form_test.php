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

// Project implemented by the "Recovery, Transformation and Resilience Plan.
// Funded by the European Union - Next GenerationEU\".
//
// Produced by the UNIMOODLE University Group: Universities of
// Valladolid, Complutense de Madrid, UPV/EHU, León, Salamanca,
// Illes Balears, Valencia, Rey Juan Carlos, La Laguna, Zaragoza, Málaga,
// Córdoba, Extremadura, Vigo, Las Palmas de Gran Canaria y Burgos.

/**
 * Tests for import_form.
 *
 * @package    local_notificationsagent
 * @copyright  2026 Proyecto UNIMOODLE
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_notificationsagent\form;

use local_notificationsagent\helper\test\phpunitutil;
use local_notificationsagent\rule;

/**
 * Tests for import_form.
 *
 * @group notificationsagent
 */
final class import_form_test extends \advanced_testcase {

    /**
     * Apply the same sanitisation step used during rule import.
     *
     * @param array $data Import payload
     */
    private function sanitise_import_data(array &$data): void {
        $form = new import_form();
        $method = phpunitutil::get_method($form, 'sanitise_import_data');
        $method->invokeArgs($form, [&$data]);
    }

    /**
     * Build an availability JSON payload with a completion condition.
     *
     * @param int $cmid Course module id
     * @return string
     */
    private function get_completion_availability_json(int $cmid): string {
        return '{"op":"&","c":[{"op":"&","c":[{"type":"completion","cm":' . $cmid . ',"e":1}]},'
            . '{"op":"!|","c":[]}],"showc":[true,true],"errors":["availability:error_list_nochildren"]}';
    }

    /**
     * Serialised JSON fields must not be passed through format_text.
     *
     * @covers \local_notificationsagent\form\import_form::sanitise_import_data
     */
    public function test_sanitise_import_data_preserves_serialised_json_fields(): void {
        $this->resetAfterTest();

        $availabilityjson = $this->get_completion_availability_json(76);

        $data = [
            'title' => '<strong>Imported rule</strong>',
            editrule_form::FORM_JSON_AC => $availabilityjson,
            editrule_form::FORM_JSON_CONDITION => '[]',
            editrule_form::FORM_JSON_EXCEPTION => '[]',
            editrule_form::FORM_JSON_ACTION => '{"1":{"pluginname":"messageagent","action":"insert"}}',
            'runtime_group' => [
                'runtime_days' => '1',
            ],
        ];

        $this->sanitise_import_data($data);

        $this->assertSame($availabilityjson, $data[editrule_form::FORM_JSON_AC]);
        $this->assertSame('[]', $data[editrule_form::FORM_JSON_CONDITION]);
        $this->assertSame('[]', $data[editrule_form::FORM_JSON_EXCEPTION]);
        $this->assertSame(
            '{"1":{"pluginname":"messageagent","action":"insert"}}',
            $data[editrule_form::FORM_JSON_ACTION]
        );
        $this->assertIsObject(json_decode($data[editrule_form::FORM_JSON_AC]));
    }

    /**
     * Non-serialised import fields must still be passed through format_text.
     *
     * @covers \local_notificationsagent\form\import_form::sanitise_import_data
     */
    public function test_sanitise_import_data_still_sanitises_display_fields(): void {
        $this->resetAfterTest();

        $rawtitle = '<strong>Imported rule</strong>';
        $rawmessage = '{Course_Url}';
        $expectedtitle = html_entity_decode(format_text($rawtitle, FORMAT_HTML), ENT_QUOTES);
        $expectedmessage = html_entity_decode(format_text($rawmessage, FORMAT_HTML), ENT_QUOTES);
        $availabilityjson = $this->get_completion_availability_json(76);

        $data = [
            'title' => $rawtitle,
            '1045_bootstrapnotifications_message' => $rawmessage,
            editrule_form::FORM_JSON_AC => $availabilityjson,
            editrule_form::FORM_JSON_CONDITION => '[]',
            editrule_form::FORM_JSON_EXCEPTION => '[]',
            editrule_form::FORM_JSON_ACTION => '{"1045":{"pluginname":"bootstrapnotifications","action":"insert"}}',
            'runtime_group' => [
                'runtime_days' => '1',
            ],
        ];

        $this->sanitise_import_data($data);

        $this->assertSame($expectedtitle, $data['title']);
        $this->assertSame($expectedmessage, $data['1045_bootstrapnotifications_message']);
        $this->assertSame(
            html_entity_decode(format_text('1', FORMAT_HTML), ENT_QUOTES),
            $data['runtime_group']['runtime_days']
        );
        $this->assertSame($availabilityjson, $data[editrule_form::FORM_JSON_AC]);
    }

    /**
     * Import sanitisation followed by save_form must persist AC parameters intact.
     *
     * @covers \local_notificationsagent\form\import_form::sanitise_import_data
     * @covers \local_notificationsagent\form\import_form::array_to_object
     * @covers \local_notificationsagent\rule::save_form
     */
    public function test_import_payload_save_form_persists_rule_with_intact_ac_parameters(): void {
        global $USER;

        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $teacher = $this->getDataGenerator()->create_and_enrol($course, 'editingteacher');
        $this->setUser($teacher);

        $quiz = $this->getDataGenerator()->get_plugin_generator('mod_quiz')->create_instance([
            'course' => $course->id,
            'name' => 'Import quiz',
        ]);

        $availabilityjson = $this->get_completion_availability_json($quiz->cmid);
        $data = [
            'title' => 'Imported rule test',
            'type' => (string) rule::RULE_TYPE,
            'timesfired' => '1',
            'runtime_group' => [
                'runtime_days' => '1',
                'runtime_hours' => '0',
                'runtime_minutes' => '0',
            ],
            editrule_form::FORM_JSON_AC => $availabilityjson,
            editrule_form::FORM_JSON_CONDITION => '[]',
            editrule_form::FORM_JSON_EXCEPTION => '[]',
            '1_messageagent_title' => 'Hello',
            '1_messageagent_message' => [
                'text' => 'World',
                'format' => FORMAT_HTML,
            ],
            editrule_form::FORM_JSON_ACTION => json_encode([
                '1' => [
                    'pluginname' => 'messageagent',
                    'action' => editrule_form::FORM_JSON_ACTION_INSERT,
                ],
            ]),
        ];

        $this->sanitise_import_data($data);

        $form = new import_form();
        $importdata = $form->array_to_object($data, 0);
        $importdata->courseid = $course->id;

        $rule = new rule();
        $rule->save_form($importdata);

        $instance = rule::create_instance($rule->get_id());

        $this->assertSame('Imported rule test', $instance->get_name());
        $this->assertNotNull($instance->get_ac());
        $this->assertSame($availabilityjson, $instance->get_ac()->get_parameters());
        $this->assertTrue($instance->validation($course->id));
        $this->assertCount(1, $instance->get_actions());
    }
}
