<?php
require_once('../../config.php'); // Adjust path if your Moodle root is different

// No specific login requirement for a general help page, but you can add it if needed.
// require_login(); 

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url('/filter/lgpdlive/help.php');
$PAGE->set_title(get_string('helppage_title', 'filter_lgpdlive'));
$PAGE->set_heading(get_string('helppage_title', 'filter_lgpdlive'));
// $PAGE->set_pagelayout('standard'); // Or 'admin', 'incourse', etc. 'standard' is often suitable.

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('helppage_title', 'filter_lgpdlive'));
echo $OUTPUT->box_start();
echo format_text(get_string('helppage_content', 'filter_lgpdlive'), FORMAT_HTML); // Use format_text for proper rendering
echo $OUTPUT->box_end();
echo $OUTPUT->footer();