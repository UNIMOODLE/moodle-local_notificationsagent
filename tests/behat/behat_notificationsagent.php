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

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

/**
 * notificationsagent-related steps definitions.
 *
 * @package    local_notificationsagent
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_notificationsagent extends behat_base {
    /**
     * @When /^I click on the input element with placeholder "([^"]*)" inside div with id "([^"]*)"$/
     */
    public function i_click_on_input_element_with_placeholder_inside_div($placeholder, $divid) {
        $selector = sprintf("//div[@id='%s']//input[@placeholder='%s']", $divid, $placeholder);
        $element = $this->getSession()->getPage()->find('xpath', $selector);

        if (!$element) {
            throw new \Exception("Input element with placeholder '$placeholder' inside div with id '$divid' not found");
        }

        $element->click();
    }

    /**
     * Click on the 'More' link if it exists, otherwise click on 'My assistant'.
     *
     * @When /^I click on "More" if it exists otherwise "My assistant"$/
     */
    public function i_click_on_more_if_exists_otherwise_my_assistant() {
        $morebutton = $this->getSession()->getPage()->find('css', '.secondary-navigation .moremenu .more-nav .dropdownmoremenu');

        if ($morebutton !== null && $morebutton->isVisible()) {
            $morebutton->click();
        }

        $this->getSession()->getPage()->findLink('My assistant')->click();
    }
}

