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
 * @package    notificationscondition_ac
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace notificationscondition_ac;

use core_availability\info;
use local_notificationsagent\notificationplugin;
use moodle_exception;

/**
 *  Customtree class
 */
class customtree extends \core_availability\tree {
    /** @var array Children obj conditions
     *
     */
    public static $customchildren;

    /**
     * Decodes availability structure.
     *
     * This function also validates the retrieved data as follows:
     * 1. Data that does not meet the API-defined structure causes a
     *    coding_exception (this should be impossible unless there is
     *    a system bug or somebody manually hacks the database).
     * 2. Data that meets the structure but cannot be implemented (e.g.
     *    reference to missing plugin or to module that doesn't exist) is
     *    either silently discarded (if $lax is true) or causes a
     *    coding_exception (if $lax is false).
     *
     * @param \stdClass $structure Structure (decoded from JSON)
     * @param bool $lax If true, throw exceptions only for invalid structure
     * @param bool $root If true, this is the root tree
     * @return \core_availability\tree Availability tree
     * @throws \coding_exception If data is not valid structure
     */
    public function __construct($structure, $lax = false, $root = true) {
        $this->root = $root;

        // Check object.
        if (!is_object($structure)) {
            throw new \coding_exception('Invalid availability structure (not object)');
        }

        // Extract operator.
        if (!isset($structure->op)) {
            throw new \coding_exception('Invalid availability structure (missing ->op)');
        }
        $this->op = $structure->op;
        if (
            !in_array($this->op, [
                self::OP_AND,
                self::OP_OR,
                self::OP_NOT_AND,
                self::OP_NOT_OR,
            ], true)
        ) {
            throw new \coding_exception('Invalid availability structure (unknown ->op)');
        }

        // For root tree, get show options.
        $this->show = true;
        $this->showchildren = null;
        if ($root) {
            if ($this->op === self::OP_AND || $this->op === self::OP_NOT_OR) {
                // Per-child show options.
                if (!isset($structure->showc)) {
                    throw new \coding_exception(
                        'Invalid availability structure (missing ->showc)'
                    );
                }
                if (!is_array($structure->showc)) {
                    throw new \coding_exception(
                        'Invalid availability structure (->showc not array)'
                    );
                }
                foreach ($structure->showc as $value) {
                    if (!is_bool($value)) {
                        throw new \coding_exception(
                            'Invalid availability structure (->showc value not bool)'
                        );
                    }
                }
                // Set it empty now - add corresponding ones later.
                $this->showchildren = [];
            } else {
                // Entire tree show option. (Note: This is because when you use
                // OR mode, say you have A OR B, the user does not meet conditions
                // for either A or B. A is set to 'show' and B is set to 'hide'.
                // But they don't have either, so how do we know which one to do?
                // There might as well be only one value.).
                if (!isset($structure->show)) {
                    throw new \coding_exception(
                        'Invalid availability structure (missing ->show)'
                    );
                }
                if (!is_bool($structure->show)) {
                    throw new \coding_exception(
                        'Invalid availability structure (->show not bool)'
                    );
                }
                $this->show = $structure->show;
            }
        }

        // Get list of enabled plugins.
        $pluginmanager = \core_plugin_manager::instance();
        $enabled = $pluginmanager->get_enabled_plugins('availability');

        // For unit tests, also allow the mock plugin type (even though it
        // isn't configured in the code as a proper plugin).
        if (PHPUNIT_TEST) {
            $enabled['mock'] = true;
        }

        // Get children.
        if (!isset($structure->c)) {
            throw new \coding_exception('Invalid availability structure (missing ->c)');
        }
        if (!is_array($structure->c)) {
            throw new \coding_exception('Invalid availability structure (->c not array)');
        }
        if (is_array($this->showchildren) && count($structure->showc) != count($structure->c)) {
            throw new \coding_exception('Invalid availability structure (->c, ->showc mismatch)');
        }
        $this->children = [];
        foreach ($structure->c as $index => $child) {
            if (!is_object($child)) {
                throw new \coding_exception('Invalid availability structure (child not object)');
            }

            // First see if it's a condition. These have a defined type.
            if (isset($child->type)) {
                if (!array_key_exists($child->type, $enabled)) {
                    if ($lax) {
                        // On load of existing settings, ignore if class
                        // doesn't exist.
                        continue;
                    } else {
                        throw new \coding_exception('Unknown condition type: ' . $child->type);
                    }
                }
                self::$customchildren[] = $child;
            } else {
                // Not a condition. Must be a subtree.
                $this->children[] = new customtree($child, $lax, false);
            }
            if (!is_null($this->showchildren)) {
                $this->showchildren[] = $structure->showc[$index];
            }
        }
    }
}
