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
 * LGP Live filter settings
 *
 * @package    filter_lgpdlive
 * @copyright  2024 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;


if ($ADMIN->fulltree) {
    $item = new admin_setting_heading('filter_lgpdlive/heading',
                                      new lang_string('pluginname', 'filter_lgpdlive'),
                                      new lang_string('pluginname_help', 'filter_lgpdlive'));
    $settings->add($item);

    $item = new admin_setting_configcheckbox('filter_lgpdlive/enabled',
                                             new lang_string('enabled', 'filter_lgpdlive'),
                                             new lang_string('enabled_help', 'filter_lgpdlive'),
                                             1);
    $settings->add($item);

    $defaultRegexPatterns = "EMAIL::/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/\n" . // Email
                            "PHONE::/(?:\+\s*55\s*)?\d{2}\s*\d{4,5}\s*\d{4}/\n" .         // Phone (Brazil example)
                            "CPF::/\d{3}[\.]?\d{3}[\.]?\d{3}[-]?\d{2}/\n" .            // CPF (Brazil example)
                            "CEP::/\d{5}[-]?\d{3}/";
    $item = new admin_setting_configtextarea('filter_lgpdlive/customregexlist',
                                             new lang_string('customregexlist', 'filter_lgpdlive'),
                                             new lang_string ('customregexlist_desc', 'filter_lgpdlive'),
                                             $defaultRegexPatterns,
                                             PARAM_RAW);
    $settings->add($item);

    $pagetypes_options = [
        //CONTEXT_SYSTEM => new lang_string('contextsystem', 'filter_lgpdlive'),
        'mod-forum-discuss' => new lang_string('mod-forum-discuss', 'filter_lgpdlive'),
        'lib-ajax-service' => new lang_string('lib-ajax-service', 'filter_lgpdlive'),

    ];
    $default_values = [];

    $item = new admin_setting_configmulticheckbox(
            'filter_lgpdlive/blacklistpagetypes',            // Setting name
            new lang_string('blacklistpagetypes', 'filter_lgpdlive'), // Display name for the setting
            new lang_string('blacklistpagetypes', 'filter_lgpdlive'), // Description
            $default_values,                                             // Default value: an empty array (none blacklisted)
            $pagetypes_options                                // Options array (value => display text)
    );

    $settings->add($item);
} 