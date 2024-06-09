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
 * @package    tool_clearbackupfiles
 * @copyright  2015 Shubhendra Doiphode
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', false);

require(__DIR__ . '/../../../config.php');

require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/filestorage/file_storage.php');
require_once("$CFG->libdir/formslib.php");

require_once("$CFG->dirroot/admin/tool/clearbackupfiles/confirm_form.php");

$context = context_system::instance();

$PAGE->set_context($context);
$PAGE->set_url(new moodle_url($CFG->wwwroot . '/admin/tool/clearbackupfiles/index.php'));
$PAGE->set_title(get_string('pluginname', 'tool_clearbackupfiles'));
$PAGE->set_pagelayout('admin');

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'tool_clearbackupfiles'));

$submitbutton = optional_param('submitbutton','', PARAM_TEXT);
$cancelbutton = optional_param('cancel','', PARAM_TEXT);
$dlt = optional_param('dlt',0, PARAM_INT);



$returnurl = new moodle_url($CFG->wwwroot);


$mform = new confirm_form();

if ($submitbutton=='' && $dlt==0 && $cancelbutton!='Cancel') {
    $mform->display();
}

if ($mform->is_cancelled()) {
    redirect($returnurl);
} else if ($formdata = $mform->get_data()) {

    echo $OUTPUT->confirm(get_string('warningalert', 'tool_clearbackupfiles'), $CFG->wwwroot . '/admin/tool/clearbackupfiles/index.php?dlt=1', $CFG->wwwroot . '/admin/tool/clearbackupfiles/index.php');

}

if ($dlt==1) {
    $clearfileprocesser = new tool_clearbackupfiles_processer();
    $clearfileprocesser->execute();

    $files = $clearfileprocesser->get_deleted_files();
    $filecount = count($files);

    if ($filecount) {
        $data = array();
        foreach ($files as $file) {
            $line = array();
            $line[] = $file->name;
            $line[] = $clearfileprocesser->format_bytes($file->size);
            $data[] = $line;
        }

        $table = new html_table();
        $table->head = array(
            get_string('filename', 'tool_clearbackupfiles'),
            get_string('filesize', 'tool_clearbackupfiles')
        );
        $table->size = array('60%', '40%');
        $table->align = array('left', 'left');
        $table->data = $data;

        $a = new StdClass();
        $a->filecount = $filecount;
        $filesize = $clearfileprocesser->get_total_file_size();
        $a->filesize = tool_clearbackupfiles_processer::format_bytes($filesize);

        echo html_writer::tag('p', get_string('filedeletedheader', 'tool_clearbackupfiles'));
        echo html_writer::table($table);
        echo html_writer::tag('p', get_string('filedeletedfooter', 'tool_clearbackupfiles', $a));
    } else {
        echo html_writer::tag('p', get_string('filedeletedempty', 'tool_clearbackupfiles'));
    }
}
echo $OUTPUT->footer();