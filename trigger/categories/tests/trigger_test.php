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

namespace tool_lifecycle\trigger;

use tool_lifecycle\processor;
use tool_lifecycle\response\trigger_response;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../lib.php');
require_once(__DIR__ . '/generator/lib.php');

/**
 * Trigger test for categories trigger.
 *
 * @package    tool_lifecycle_trigger
 * @category   categories
 * @group tool_lifecycle_trigger
 * @copyright  2017 Tobias Reischmann WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_lifecycle_trigger_categories_testcase extends \advanced_testcase {

    private $excludetrigger;
    private $includetrigger;

    private $category;
    private $childcategory;

    /**@var processor*/
    private $processor;

    public function setUp() {
        $this->resetAfterTest(true);
        $this->setAdminUser();

        $generator = $this->getDataGenerator();

        $this->processor = new processor();

        $this->category = $generator->create_category();
        $othercategory = $generator->create_category();
        $this->childcategory = $generator->create_category(array('parent' => $this->category->id));
        $data = array(
            'categories' => $othercategory->id . ',' . $this->category->id,
            'exclude' => true,
        );

        $this->excludetrigger = \tool_lifecycle_trigger_categories_generator::create_trigger_with_workflow($data);

        $data['exclude'] = false;
        $this->includetrigger = \tool_lifecycle_trigger_categories_generator::create_trigger_with_workflow($data);
    }

    /**
     * Tests if courses, which are in the category are correctly triggered.
     */
    public function test_course_has_cat() {

        $course = $this->getDataGenerator()->create_course(array('category' => $this->category->id));

        $recordset = $this->processor->get_course_recordset([$this->excludetrigger],[]);
        foreach ($recordset as $element) {
            $this->assertNotEquals($course->id, $element->id, 'The course should have been excluded by the trigger');
        }
        $recordset->close();
        $recordset = $this->processor->get_course_recordset([$this->includetrigger],[]);
        $found = false;
        foreach ($recordset as $element) {
            if ($course->id === $element->id){
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'The course should not have been excluded by the trigger');
        $recordset->close();
    }

    /**
     * Tests if courses, which are in the category are correctly triggered.
     */
    public function test_course_within_cat() {

        $course = $this->getDataGenerator()->create_course(array('category' => $this->childcategory->id));

        $recordset = $this->processor->get_course_recordset([$this->excludetrigger],[]);
        foreach ($recordset as $element) {
            $this->assertNotEquals($course->id, $element->id, 'The course should have been excluded by the trigger');
        }
        $recordset->close();
        $recordset = $this->processor->get_course_recordset([$this->includetrigger],[]);
        $found = false;
        foreach ($recordset as $element) {
            if ($course->id === $element->id){
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'The course should not have been excluded by the trigger');
        $recordset->close();
    }

    /**
     * Tests if courses, which are not in the category are correctly triggered.
     */
    public function test_course_not_within_cat() {
        $course = $this->getDataGenerator()->create_course();

        $recordset = $this->processor->get_course_recordset([$this->includetrigger],[]);
        foreach ($recordset as $element) {
            $this->assertNotEquals($course->id, $element->id, 'The course should have been excluded by the trigger');
        }
        $recordset->close();
        $recordset = $this->processor->get_course_recordset([$this->excludetrigger],[]);
        $found = false;
        foreach ($recordset as $element) {
            if ($course->id === $element->id){
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'The course should not have been excluded by the trigger');
        $recordset->close();
    }
}