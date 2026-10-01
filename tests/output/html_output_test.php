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
// Funded by the European Union - Next GenerationEU".
//
// Produced by the UNIMOODLE University Group: Universities of
// Valladolid, Complutense de Madrid, UPV/EHU, León, Salamanca,
// Illes Balears, Valencia, Rey Juan Carlos, La Laguna, Zaragoza, Málaga,
// Córdoba, Extremadura, Vigo, Las Palmas de Gran Canaria y Burgos.

/**
 * Tests that the HTML produced by the plugin output keeps the expected DOM.
 *
 * @package    local_notificationsagent
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_notificationsagent\output;

use local_notificationsagent\form\editrule_form;
use local_notificationsagent\helper\helper;
use local_notificationsagent\helper\test\phpunitutil;
use local_notificationsagent\notificationplugin;
use local_notificationsagent\rule;
use notificationsaction_messageagent\messageagent;
use notificationscondition_weekend\weekend;

/**
 * Compares the rendered HTML with the fixtures in tests/fixtures/output.
 *
 * The comparison is done on a normalised DOM (sorted attributes, collapsed whitespace)
 * because the JavaScript and the Behat tests depend on the ids, classes and data attributes.
 *
 * @group notificationsagent
 */
final class html_output_test extends \advanced_testcase {
    /** @var array Category tree with the same shape returned by helper::build_category_array() */
    public const CATEGORY_TREE = [
        [
            'id' => 11,
            'name' => '<div class="text_to_html">Category &amp; one</div>',
            'categories' => [
                [
                    'id' => 12,
                    'name' => '<div class="text_to_html">Subcategory</div>',
                    'categories' => [],
                    'courses' => [
                        ['id' => 101, 'name' => '<div class="text_to_html">Course A</div>'],
                    ],
                    'count' => 1,
                    'countsubcategoriescourses' => 1,
                ],
                [
                    'id' => 13,
                    'name' => '<div class="text_to_html">Empty subcategory</div>',
                    'categories' => [
                        [
                            'id' => 14,
                            'name' => '<div class="text_to_html">Deep</div>',
                            'categories' => [],
                            'courses' => [
                                ['id' => 103, 'name' => '<div class="text_to_html">Course C</div>'],
                            ],
                            'count' => 1,
                            'countsubcategoriescourses' => 1,
                        ],
                    ],
                    'courses' => [],
                    'count' => 0,
                    'countsubcategoriescourses' => 1,
                ],
            ],
            'courses' => [
                ['id' => 102, 'name' => '<div class="text_to_html">Course &lt;B&gt;</div>'],
            ],
            'count' => 1,
            'countsubcategoriescourses' => 3,
        ],
        [
            'id' => 21,
            'name' => '<div class="text_to_html">Category two</div>',
            'categories' => [],
            'courses' => [],
            'count' => 0,
            'countsubcategoriescourses' => 0,
        ],
    ];

    /**
     * Create a form with the group before which the subplugins insert their elements.
     *
     * @param string $type Subplugin type
     * @return \MoodleQuickForm
     */
    public static function create_subplugin_form(string $type): \MoodleQuickForm {
        global $CFG;
        require_once($CFG->libdir . '/formslib.php');

        $mform = new \MoodleQuickForm('testform', 'post', '');
        $mform->addElement('static', 'new' . $type . '_group', '');
        return $mform;
    }

    /**
     * Concatenate the HTML elements of a form in order.
     *
     * @param \MoodleQuickForm $mform
     * @return string
     */
    public static function collect_html_elements(\MoodleQuickForm $mform): string {
        $html = '';
        foreach ($mform->_elements as $element) {
            if ($element->getType() === 'html') {
                $html .= $element->toHtml();
            }
        }
        return $html;
    }

    /**
     * Assert that the HTML has the same DOM as the fixture.
     *
     * @param string $fixture Fixture file name
     * @param string $html Rendered HTML
     */
    private function assert_same_dom(string $fixture, string $html): void {
        global $CFG;
        $expected = file_get_contents($CFG->dirroot . '/local/notificationsagent/tests/fixtures/output/' . $fixture);
        $this->assertSame($this->normalise_html($expected), $this->normalise_html($html), $fixture);
    }

    /**
     * Normalise an HTML fragment so that only the DOM is compared.
     *
     * @param string $html
     * @return string
     */
    private function normalise_html(string $html): string {
        $doc = new \DOMDocument();
        $previous = libxml_use_internal_errors(true);
        $doc->loadHTML(
            '<?xml encoding="UTF-8"><div id="normalise-root">' . $html . '</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = (new \DOMXPath($doc))->query('//div[@id="normalise-root"]')->item(0);
        return $this->serialise_children($root);
    }

    /**
     * Serialise the children of a node with sorted attributes and collapsed whitespace.
     *
     * @param \DOMNode $node
     * @return string
     */
    private function serialise_children(\DOMNode $node): string {
        $output = '';
        foreach ($node->childNodes as $child) {
            if ($child instanceof \DOMText) {
                $text = trim(preg_replace('/\s+/u', ' ', $child->textContent));
                if ($text !== '') {
                    $output .= htmlspecialchars($text);
                }
            } else if ($child instanceof \DOMElement) {
                $attributes = [];
                foreach ($child->attributes as $attribute) {
                    $attributes[$attribute->name] = trim(preg_replace('/\s+/u', ' ', $attribute->value));
                }
                ksort($attributes);
                $output .= '<' . $child->tagName;
                foreach ($attributes as $name => $value) {
                    $output .= ' ' . $name . '="' . htmlspecialchars($value) . '"';
                }
                $output .= '>' . $this->serialise_children($child) . '</' . $child->tagName . '>';
            }
        }
        return $output;
    }

    /**
     * Category items keep the legacy DOM.
     *
     * @covers \local_notificationsagent\helper\helper::build_output_categories
     */
    public function test_category_items(): void {
        $this->resetAfterTest();
        $this->assert_same_dom('category_items.html', helper::build_output_categories(self::CATEGORY_TREE));
    }

    /**
     * The category picker keeps the legacy DOM.
     *
     * @covers \local_notificationsagent\output\category_picker
     * @covers \local_notificationsagent\output\renderer
     */
    public function test_category_picker(): void {
        global $PAGE;
        $this->resetAfterTest();

        $renderer = $PAGE->get_renderer('local_notificationsagent');
        $this->assert_same_dom('category_picker.html', $renderer->render(new category_picker(self::CATEGORY_TREE)));
    }

    /**
     * Data provider for test_tabnav.
     *
     * @return array
     */
    public static function tabnav_provider(): array {
        return [
            'Conditions' => ['nav-conditions-tab', 'tabnav_nav-conditions-tab.html'],
            'Exceptions' => ['nav-exceptions-tab', 'tabnav_nav-exceptions-tab.html'],
            'Actions' => ['nav-actions-tab', 'tabnav_nav-actions-tab.html'],
            'None' => ['', 'tabnav_none.html'],
        ];
    }

    /**
     * The tab navigation keeps the legacy DOM.
     *
     * @covers \local_notificationsagent\output\renderer::tabnav
     * @dataProvider tabnav_provider
     *
     * @param string $target Active tab
     * @param string $fixture Fixture file name
     */
    public function test_tabnav(string $target, string $fixture): void {
        global $PAGE;
        $this->resetAfterTest();

        $this->assert_same_dom($fixture, $PAGE->get_renderer('local_notificationsagent')->tabnav($target));
    }

    /**
     * The HTML of the rule edition form keeps the legacy DOM.
     *
     * @covers \local_notificationsagent\form\editrule_form::definition_after_data
     */
    public function test_editrule_form(): void {
        $this->resetAfterTest();
        $this->setAdminUser();

        $course = self::getDataGenerator()->create_course();
        $form = new editrule_form(new \moodle_url('/'), [
            'rule' => (new rule())->to_record(),
            'timesfired' => rule::MINIMUM_EXECUTION,
            'courseid' => $course->id,
            'getaction' => 'add',
        ]);
        $form->definition_after_data();
        $mform = phpunitutil::get_property($form, '_form');

        $this->assert_same_dom('editrule_form.html', self::collect_html_elements($mform));
    }

    /**
     * The action placeholders keep the legacy DOM.
     *
     * @covers \local_notificationsagent\notificationactionplugin::placeholders
     */
    public function test_placeholders(): void {
        $this->resetAfterTest();

        $action = new messageagent((new rule())->to_record());
        $action->set_id(5);
        foreach ([true => 'placeholders_user.html', false => 'placeholders_nouser.html'] as $showuser => $fixture) {
            $mform = self::create_subplugin_form(notificationplugin::TYPE_ACTION);
            $action->placeholders($mform, notificationplugin::TYPE_ACTION, (bool) $showuser);
            $this->assert_same_dom($fixture, self::collect_html_elements($mform));
        }
    }

    /**
     * The subplugin title keeps the legacy DOM.
     *
     * @covers \local_notificationsagent\notificationplugin::get_ui_title
     */
    public function test_subplugin_title(): void {
        $this->resetAfterTest();

        $action = new messageagent((new rule())->to_record());
        $action->set_id(5);
        $mform = self::create_subplugin_form(notificationplugin::TYPE_ACTION);
        phpunitutil::get_method($action, 'get_ui_title')->invoke($action, $mform, notificationplugin::TYPE_ACTION);
        $this->assert_same_dom('subplugin_title_action.html', self::collect_html_elements($mform));

        $condition = new weekend((new rule())->to_record());
        $condition->set_id(7);
        $mform = self::create_subplugin_form(notificationplugin::TYPE_EXCEPTION);
        phpunitutil::get_method($condition, 'get_ui_title')->invoke($condition, $mform, notificationplugin::TYPE_EXCEPTION);
        $this->assert_same_dom('subplugin_title_exception.html', self::collect_html_elements($mform));
    }
}
