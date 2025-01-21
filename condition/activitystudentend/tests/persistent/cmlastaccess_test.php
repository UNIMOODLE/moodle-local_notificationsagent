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
 * Version details
 *
 * @package    notificationscondition_activitystudentend
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace notificationscondition_activitystudentend\persistent;

use local_notificationsagent\evaluationcontext;
use local_notificationsagent\form\editrule_form;
use local_notificationsagent\notificationplugin;
use local_notificationsagent\helper\test\phpunitutil;
use local_notificationsagent\rule;
use notificationsaction_bootstrapnotifications\bootstrapmessages;
use notificationsaction_bootstrapnotifications\bootstrapnotifications;
use local_notificationsagent\helper\test\mock_base_logger;

/**
 * Test boostrapnotificationss
 *
 * @group notificationsagent
 */
final class cmlastaccess_test extends \advanced_testcase {
    /**
     * Test define properties.
     *
     * @covers      \notificationscondition_activitystudentend\persistent\cmlastaccess
     *

     */
    public function test_define_properties(): void {
        // Test persistant.
        $this->assertTrue(cmlastaccess::has_property('userid'));
        $this->assertTrue(cmlastaccess::has_property('courseid'));
        $this->assertTrue(cmlastaccess::has_property('idactivity'));
        $this->assertTrue(cmlastaccess::has_property('firstaccess'));
    }
}
