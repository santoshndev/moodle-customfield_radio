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

namespace customfield_radio;

use core_customfield_generator;
use core_customfield_test_instance_form;
use stdClass;

/**
 * Functional test for customfield_radio
 *
 * @package    customfield_radio
 * @covers     \customfield_radio\data_controller
 * @covers     \customfield_radio\field_controller
 * @copyright  2025 Santosh N. <santosh.nag2217@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class plugin_test extends \advanced_testcase {

    /** @var stdClass[]  */
    private $courses = [];
    /** @var \core_customfield\category_controller */
    private $cfcat;
    /** @var \core_customfield\field_controller[] */
    private $cfields;
    /** @var \core_customfield\data_controller[] */
    private $cfdata;

    /**
     * Tests set up.
     */
    public function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();

        $this->cfcat = $this->get_generator()->create_category();

        $this->cfields[1] = $this->get_generator()->create_field(
            ['categoryid' => $this->cfcat->get('id'), 'shortname' => 'myfield1', 'type' => 'radio',
                'configdata' => ['options' => "a\nb\nc"]]);
        $this->cfields[2] = $this->get_generator()->create_field(
            ['categoryid' => $this->cfcat->get('id'), 'shortname' => 'myfield2', 'type' => 'radio',
                'configdata' => ['required' => 1, 'options' => "a\nb\nc"]]);
        $this->cfields[3] = $this->get_generator()->create_field(
            ['categoryid' => $this->cfcat->get('id'), 'shortname' => 'myfield3', 'type' => 'radio',
                'configdata' => ['defaultvalue' => 'b', 'options' => "a\nb\nc"]]);

        $this->courses[1] = $this->getDataGenerator()->create_course();
        $this->courses[2] = $this->getDataGenerator()->create_course();
        $this->courses[3] = $this->getDataGenerator()->create_course();

        $this->cfdata[1] = $this->get_generator()->add_instance_data($this->cfields[1], $this->courses[1]->id, 'a');
        $this->cfdata[2] = $this->get_generator()->add_instance_data($this->cfields[1], $this->courses[2]->id, 'a');

        $this->setUser($this->getDataGenerator()->create_user());
    }

    /**
     * Get generator
     * @return core_customfield_generator
     */
    protected function get_generator(): core_customfield_generator {
        return $this->getDataGenerator()->get_plugin_generator('core_customfield');
    }

    /**
     * Test for initialising field and data controllers
     */
    public function test_initialise(): void {
        $f = \core_customfield\field_controller::create($this->cfields[1]->get('id'));
        $this->assertTrue($f instanceof field_controller);

        $f = \core_customfield\field_controller::create(0, (object)['type' => 'radio'], $this->cfcat);
        $this->assertTrue($f instanceof field_controller);

        $d = \core_customfield\data_controller::create($this->cfdata[1]->get('id'));
        $this->assertTrue($d instanceof data_controller);

        $d = \core_customfield\data_controller::create(0, null, $this->cfields[1]);
        $this->assertTrue($d instanceof data_controller);
    }

    /**
     * Test for configuration form functions
     *
     * Create a configuration form and submit it with the same values as in the field
     */
    public function test_config_form(): void {
        $this->setAdminUser();
        $submitdata = (array)$this->cfields[1]->to_record();
        $submitdata['configdata'] = $this->cfields[1]->get('configdata');

        $submitdata = \core_customfield\field_config_form::mock_ajax_submit($submitdata);
        $form = new \core_customfield\field_config_form(null, null, 'post', '', null, true,
            $submitdata, true);
        $form->set_data_for_dynamic_submission();
        $this->assertTrue($form->is_validated());
        $form->process_dynamic_submission();
    }

    /**
     * Test for instance form functions
     */
    public function test_instance_form(): void {
        global $CFG;
        require_once($CFG->dirroot . '/customfield/tests/fixtures/test_instance_form.php');
        $this->setAdminUser();
        $handler = $this->cfcat->get_handler();

        // First try to submit without required field.
        $submitdata = (array)$this->courses[1];
        core_customfield_test_instance_form::mock_submit($submitdata, []);
        $form = new core_customfield_test_instance_form('POST',
            ['handler' => $handler, 'instance' => $this->courses[1]]);
        $this->assertFalse($form->is_validated());

        // Now with required field.
        $submitdata['customfield_myfield2'] = 'c';
        core_customfield_test_instance_form::mock_submit($submitdata, []);
        $form = new core_customfield_test_instance_form('POST',
            ['handler' => $handler, 'instance' => $this->courses[1]]);
        $this->assertTrue($form->is_validated());

        $data = $form->get_data();
        $this->assertNotEmpty($data->customfield_myfield1);
        $this->assertNotEmpty($data->customfield_myfield2);
        $handler->instance_form_save($data);
    }

    /**
     * Test for data_controller::get_value and export_value
     */
    public function test_get_export_value(): void {
        $this->assertEquals('a', $this->cfdata[1]->get_value());
        $this->assertEquals('a', $this->cfdata[1]->export_value());

        // Field without data but with a default value.
        $d = \core_customfield\data_controller::create(0, null, $this->cfields[3]);
        $this->assertEquals('b', $d->get_value());
        $this->assertEquals('b', $d->export_value());
    }

    /**
     * Test getting field options, formatted
     */
    public function test_get_options(): void {
        filter_set_global_state('multilang', TEXTFILTER_ON);
        filter_set_applies_to_strings('multilang', true);

        $field = $this->get_generator()->create_field([
            'categoryid' => $this->cfcat->get('id'),
            'type' => 'radio',
            'shortname' => 'myradio',
            'configdata' => [
                'options' => <<<EOF
                    <span lang="en" class="multilang">Beginner</span><span lang="es" class="multilang">Novato</span>
                    <span lang="en" class="multilang">Intermediate</span><span lang="es" class="multilang">Intermedio</span>
                    <span lang="en" class="multilang">Advanced</span><span lang="es" class="multilang">Avanzado</span>
                EOF,
            ],
        ]);

        $this->assertEquals([
            '',
            'Beginner',
            'Intermediate',
            'Advanced',
        ], $field->get_options());
    }

    /**
     * Data provider for {@see test_parse_value}
     *
     * @return array
     */
    public static function parse_value_provider(): array {
        return [
            ['Red', 1],
            ['Blue', 2],
            ['Green', 3],
            ['Mauve', 0],
        ];
    }

    /**
     * Test field parse_value method
     *
     * @param string $value
     * @param int $expected
     *
     * @dataProvider parse_value_provider
     */
    public function test_parse_value(string $value, int $expected): void {
        $field = $this->get_generator()->create_field([
            'categoryid' => $this->cfcat->get('id'),
            'type' => 'radio',
            'shortname' => 'myradio',
            'configdata' => [
                'options' => "Red\nBlue\nGreen",
            ],
        ]);

        $this->assertSame($expected, $field->parse_value($value));
    }

    /**
     * Deleting fields and data
     */
    public function test_delete(): void {
        $this->cfcat->get_handler()->delete_all();
    }
}
