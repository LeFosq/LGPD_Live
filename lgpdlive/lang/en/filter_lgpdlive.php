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
 * Language strings for the LGP Live filter
 *
 * @package    filter_lgpdlive
 * @copyright  2024 Luiz Fernando Almeida Pinheiro
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['filtername'] = 'LGPD Live';
$string['pluginname'] = 'LGPD Live';
$string['pluginname_help'] = 'This filter allows you to embed LGPD Live content in your Moodle courses.';
$string['enabled'] = 'Enabled';
$string['enabled_help'] = 'Enable or disable the LGPD Live filter.'; 
$string['customregexlist'] = 'Custom Regex Patterns';
$string['customregexlist_desc'] = 'Enter one regex pattern per line. These patterns will be used to find and replace sensitive information. Example: /[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/';
$string['helppage_title'] = 'About Redacted Information';
$string['helppage_content'] = 'The content you see as "[Sensitive Information]" has been automatically redacted. This measure is in place to protect personal or sensitive data in accordance with data protection policies. If you believe you require access to the original information and have a legitimate reason, please contact your system administrator or the relevant data controller for your context.';
$string['helplink_aria_label'] = 'Learn more about why this information is redacted';
$string['sensitive_info_tag_text'] = '[Censored] -> Click to Understand Why';
$string['message_subject'] = 'Sensitive Information Detected';
$string['full_message'] = 'Sensitive information has been detected in moodle. Please review the content and take appropriate action.';
$string['Warning'] = 'Warning: ';
$string['may_contain'] = 'This page may contain sensitive information.';
$string['mod-forum-discuss'] = 'Forum Discussion';
$string['lib-ajax-service'] = 'AJAX Service (Chat)';
$string['blacklistpagetypes'] = 'Blacklist Page Types';
$string['blacklistpagetypes_desc'] = 'Select the page types where the filter shouldnt be applied.';