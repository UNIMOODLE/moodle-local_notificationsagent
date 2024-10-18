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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * A javascript module to handle course ajax actions.
 *
 * @module    local_notificationsagent/rule/repository
 * @copyright 2023 ISYC <soporte@isyc.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Ajax from 'core/ajax';

/**
 * Update the status of a rule
 *
 * @param {Number} ruleid The rule ID.
 * @param {Boolean} status Whether to set as paused or resumed rule.
 * @returns {object} jQuery promise
 */
const updateRuleStatus = (ruleid, status) => {
    const request = {
        methodname: 'local_notificationsagent_update_rule_status',
        args: {
            ruleid,
            status,
        }
    };
    return Ajax.call([request])[0];
};

/**
 * Check if a rule has any other context
 *
 * @param {Number} ruleid The rule ID.
 * @returns {object} jQuery promise
 */
const checkRuleContext = (ruleid) => {
    const request = {
        methodname: 'local_notificationsagent_check_rule_context',
        args: {
            ruleid,
        }
    };
    return Ajax.call([request])[0];
};

/**
 * Delete a rule
 *
 * @param {Number} ruleid The rule ID.
 * @returns {object} jQuery promise
 */
const deleteRule = (ruleid) => {
    const request = {
        methodname: 'local_notificationsagent_delete_rule',
        args: {
            ruleid,
        }
    };
    return Ajax.call([request])[0];
};

/**
 * Update the sharing status of a rule
 *
 * @param {Number} ruleid The rule ID.
 * @param {Boolean} status Whether to set as shared or unshared rule.
 * @returns {object} jQuery promise
 */
const updateRuleShare = (ruleid, status) => {
    const request = {
        methodname: 'local_notificationsagent_update_rule_share',
        args: {
            ruleid,
            status,
        }
    };
    return Ajax.call([request])[0];
};

/**
 * Approve the sharing of a rule
 *
 * @param {Number} ruleid The rule ID.
 * @returns {object} jQuery promise
 */
const shareAllRule = (ruleid) => {
    const request = {
        methodname: 'local_notificationsagent_share_rule_all',
        args: {
            ruleid,
        }
    };
    return Ajax.call([request])[0];
};

/**
 * Reject the sharing of a rule
 *
 * @param {Number} ruleid The rule ID.
 * @returns {object} jQuery promise
 */
const unshareAllRule = (ruleid) => {
    const request = {
        methodname: 'local_notificationsagent_unshare_rule_all',
        args: {
            ruleid,
        }
    };
    return Ajax.call([request])[0];
};

/**
 * Reject the sharing of a rule
 *
 * @param {String} sessionname The session name.
 * @param {Number} orderid The order ID.
 * @param {Number} courseid The course ID
 * @returns {object} jQuery promise
 */
const saveOrderSession = (sessionname, orderid, courseid) => {
    const request = {
        methodname: 'local_notificationsagent_manage_sessions',
        args: {
            sessionname,
            orderid,
            courseid
        }
    };
    return Ajax.call([request])[0];
};

export default {
    updateRuleStatus, deleteRule, updateRuleShare,
    shareAllRule, unshareAllRule,
    checkRuleContext, saveOrderSession
};
