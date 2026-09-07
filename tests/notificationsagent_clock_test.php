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

namespace local_notificationsagent;

/**
 * Tests for Clock API wrapper helpers.
 *
 * @package    local_notificationsagent
 * @group      notificationsagent
 * @covers     \local_notificationsagent\notificationsagent::now
 * @covers     \local_notificationsagent\notificationsagent::get_clock
 */
final class notificationsagent_clock_test extends \advanced_testcase {
    public function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
        $this->getDataGenerator()->create_course();
    }

    /**
     * now() returns a Unix timestamp close to the system time when no mock is set.
     */
    public function test_now_without_mock(): void {
        $before = time();
        $now = notificationsagent::now();
        $after = time();
        $this->assertGreaterThanOrEqual($before, $now);
        $this->assertLessThanOrEqual($after, $now);
    }

    /**
     * now() returns the frozen timestamp when the clock is mocked.
     */
    public function test_now_with_frozen_clock(): void {
        $frozen = 1704099600;
        $this->mock_clock_with_frozen($frozen);
        $this->assertSame($frozen, notificationsagent::now());
        $this->assertSame($frozen, notificationsagent::get_clock()->time());
    }
}
