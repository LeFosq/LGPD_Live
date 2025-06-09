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

    private function cpf_validator($cpf){
        // Function to validate CPF
        $pure_cpf = preg_replace('/\D/', '', $cpf);
        $numbers_cpf = str_split($pure_cpf);
        $cpf_unique = array_unique($numbers_cpf);
        $return_vector = [];
        if(count($cpf_unique) == 1){

            $return_vector[] = false;// Invalid CPF, all digits are the same
            for ($i = 0; $i <= 2; $i++){
                $return_vector[] = $numbers_cpf[$i+3];
            }
            return $return_vector;
        }
        for($i = 0; $i < 9; $i++) {
            $sum = $sum + ($numbers_cpf[$i] * (10 - $i));
        }
        $remainder = ($sum * 10) % 11;
        if ($remainder != $numbers_cpf[9]) {
            $return_vector[] = false;
            for ($i = 0; $i <= 2; $i++){
                $return_vector[] = $numbers_cpf[$i+3];
            }
            return $return_vector;
        }
        $sum = 0;
        for ($i = 0; $i < 10; $i++) {
            $sum = $sum + $numbers_cpf[$i] * (11 - $i);
        }
        $remainder = ($sum * 10) % 11;
        if ($remainder == $numbers_cpf[10]) {
            $return_vector[] = true;
            for ($i = 0; $i <= 2; $i++){
                $return_vector[] = $numbers_cpf[$i+3];
            }
            return $return_vector;
        }
        $return_vector[] = false;
        for ($i = 0; $i <= 2; $i++){
            $return_vector[] = $numbers_cpf[$i+3];
        }
        return $return_vector;
    }

    private function admin_notification($formattedTypeList, $pageurl, $a){
        $admins = get_admins();
        foreach($admins as $admin){
            $message = new \core\message\message();
            $message->component = 'filter_lgpdlive';
            $message->name = 'lgpdlive';
            $message->userfrom = \core_user::get_noreply_user();
            $message->userto = $admin;
            $message->subject = get_string('message_subject', 'filter_lgpdlive', $a);
            $message->fullmessage = get_string('full_message', 'filter_lgpdlive', $a);
            $message->fullmessageformat = FORMAT_HTML;
            $message->fullmessagehtml = '<p class="alert alert-warning" role="alert">' .
                '<strong>' . get_string('Warning', 'filter_lgpdlive') . '</strong> ' .
                get_string('may_contain', 'filter_lgpdlive') . ' <strong>' . $formattedTypeList . '</strong>' .
                '<br><small>URL: <a href="' . $pageurl . '">' . $pageurl . '</a></small>' .
                '</p>';
            $message->smallmessage = 'Sensitive information was detected in the text in a page:';
            $message->notification = 1;
            message_send($message);
        }
    }

    private function valid_cpf_notification($pageurl){
        $admins = get_admins();
        foreach($admins as $admin){
            $message = new \core\message\message();
            $message->component = 'filter_lgpdlive';
            $message->name = 'lgpdlive';
            $message->userfrom = \core_user::get_noreply_user();
            $message->userto = $admin;
            $message->subject = get_string('cpf_message_subject', 'filter_lgpdlive');
            $message->fullmessage = get_string('cpf_full_message', 'filter_lgpdlive');
            $message->fullmessageformat = FORMAT_HTML;
            $message->fullmessagehtml = '<p class="alert alert-warning" role="alert">' .
                '<strong>' . get_string('Warning', 'filter_lgpdlive') . '</strong> ' .
                get_string('may_contain_cpf', 'filter_lgpdlive') .
                '<br><small>URL: <a href="' . $pageurl . '">' . $pageurl . '</a></small>' .
                '</p>';
            $message->smallmessage = 'Valid CPF was detected in the text in a page:';
            $message->notification = 1;
            message_send($message);
        }
    }

    private $detectedDatatypes = [];

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
        $custompatterns = []; // Array to store the regex patterns
        $datatypes = []; // Array to store the datatypes
        $matches = []; // Array to store the matches from regex

        //Remove any blank spaces and filter empty lines
        $lines = array_map('trim', explode("\n", $customRegexString));
        foreach ($lines as $line){
            if(!empty($line)){
                if(preg_match('/^(\w+)::(.*)$/', $line, $matches)){
                $datatypes[] = $matches[1]; // Add the datatype to the array
                $custompatterns[] = $matches[2]; // Add the regex pattern to the array
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

        $this->detectedDatatypes = [];

        $text = preg_replace_callback(
            $custompatterns,
            function ($match) use ($custompatterns, $datatypes, $replacementHTML) {
                // Logic to check which pattern matched
                foreach ($custompatterns as $key => $pattern) {
                    if (preg_match($pattern, $match[0])) {
                        // Stores the detected datatype
                        $this->detectedDatatypes[] = $datatypes[$key];
                        if ($datatypes[$key] == 'CPF')
                        {
                            $check_array = $this->cpf_validator($match[0]);
                            if($check_array[0] == true){
                                print_object('CPF válido: ' . $match[0]);
                                $replacementCPF = sprintf(
                                    '***.%s%s%s.***-**',
                                    $check_array[1],
                                    $check_array[2],
                                    $check_array[3]
                                );
                                global $PAGE;
                                $pageurlx = $PAGE->url;
                                $this->valid_cpf_notification($pageurlx);
                                return $replacementCPF;
                            }
                        }
                        break;
                    }
                }
                // Retorna sempre o mesmo HTML de substituição
                return $replacementHTML;
            },
            $text
        );

        $unique_types = array_unique($this->detectedDatatypes);
        $typelist = array_filter($unique_types);
        if (!empty($typelist)) {
            global $PAGE;
            $pageurl = $PAGE->url;
            $formattedTypeList = implode(', ', $typelist);
            $a = new stdClass();
            $a->types = $formattedTypeList;
            $a->url   = (string)$pageurl;
        }

        if ($text !== $originalText) {
            $this->admin_notification($formattedTypeList, $pageurl, $a);
        }
        return $text;
    }
}
