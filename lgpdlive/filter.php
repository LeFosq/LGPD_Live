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
 * LGP Live filter
 *
 * @package    filter_lgpdlive
 * @copyright  2025 Luiz Fernando Almeida Pinheiro
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class filter_lgpdlive extends moodle_text_filter {
    /**
     * Apply the filter to the text
     *
     * @param string $text to be processed by the text
     * @param array $options filter options
     * @return string text after processing
     */
    private function cansee_sensitive_information(){
        //Get the system context, as the filter applies globally
        $context = context_system::instance();
        return has_capability('filter/lgpdlive:cansee_sensitive_information', $context);
    }

    public function filter($text, array $options = array()) {
        // Check if the filter is enabled using the custom setting
        if (!get_config('filter_lgpdlive', 'enabled')) {
            return $text;
        }
        if($this->cansee_sensitive_information()){
            return $text;
        }
        $blacklisted_pagetypes_str = get_config('filter_lgpdlive', 'blacklistpagetypes');
        $blacklisted_keys = [];
        global $PAGE;
        if (!empty($blacklisted_pagetypes_str)){
            $blacklisted_keys_str_array = explode(',', $blacklisted_pagetypes_str);
            $blacklisted_keys = array_map('trim', $blacklisted_keys_str_array);
        }
        if (isset($PAGE)){
            $current_pagetype = $PAGE->pagetype;
            if(trim($current_pagetype) == trim($blacklisted_keys[0])){
                return $text;
            }
        }
      
       //Get custom Regex from settings
       $customRegexString = get_config('filter_lgpdlive', 'customregexlist');
       $custompatterns = [];

       //Remove any blank spaces and filter empty lines
       $lines = array_map('trim', explode("\n", $customRegexString));

       foreach ($lines as $line){
        if(!empty($line)){
            if(preg_match('/^\/.+\/[a-zA-Z]*$/', $line)){
                $custompatterns[] = $line;
            }
            else{

            }
        }
       }

        $replacementTextString = get_string('sensitive_info_tag_text', 'filter_lgpdlive'); // Text to replace sensitive information
        $helpLinkAriaLabel = get_string('helplink_aria_label', 'filter_lgpdlive'); // Aria label for the help link
        $helpPageUrl = new moodle_url('/filter/lgpdlive/help.php'); // URL for the help page

        // String to replace the sensitive information
        $replacementHTML = '<a href="' . $helpPageUrl->out(false) . '" aria-label="' . htmlspecialchars($helpLinkAriaLabel) . '" title="' . htmlspecialchars($helpLinkAriaLabel) . '">' . htmlspecialchars($replacementTextString) . '</a>';

        $originalText = $text; // Stores the original text


        // Replace sensitive information with secure placeholders
        $text = preg_replace($custompatterns, $replacementHTML, $text);

        if ($text !== $originalText) {
            global $PAGE;
            $pageurl = $PAGE->url;
            $admins = get_admins();
            foreach($admins as $admin){
                global $USER;
                $message = new \core\message\message();
                $message->component = 'filter_lgpdlive';
                $message->name = 'lgpdlive';
                $message->userfrom = \core_user::get_noreply_user();
                $message->userto = $admin;
                //$message->subject = get_string('message_subject', 'filter_lgpdlive');
                $message->subject = get_string('message_subject', 'filter_lgpdlive');
                $message->fullmessage = get_string('full_message', 'filter_lgpdlive');
                $message->fullmessageformat = FORMAT_MARKDOWN;
                $message->fullmessagehtml = '<p class="alert alert-warning" role="alert">
                                            <strong>' . get_string('Warning', 'filter_lgpdlive') . '</strong>' . get_string('may_contain', 'filter_lgpdlive') . ' <br>
                                            <small>URL: <a href="' . $pageurl . '">' . $pageurl . '</a></small>
                                            </p>';
                $message->smallmessage = 'Sensitive information was detected in the text in a page';
                $message->notification = 1;
                message_send($message);
            }
        }

        return $text;
    }
}
