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
 * Starting point.
 *
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

// Requires login and moodle/site:config; also sets context, URL, layout and the admin nav node.
admin_externalpage_setup('tool_clearbackupfiles_cleanup');

$PAGE->set_title(get_string('pluginname', 'tool_clearbackupfiles'));

$pageurl = new moodle_url('/admin/tool/clearbackupfiles/index.php');
$dlt = optional_param('dlt', 0, PARAM_BOOL);

$mform = new confirm_form(null, ['days' => (int) get_config('tool_clearbackupfiles', 'days')]);

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/'));
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'tool_clearbackupfiles'));

if ($dlt) {
    require_sesskey();
    $cutoff = required_param('cutoff', PARAM_INT);
    if ($cutoff <= 0 || $cutoff > time()) {
        throw new moodle_exception('cutoffdatefuture', 'tool_clearbackupfiles', $pageurl);
    }

    $clearfileprocesser = new tool_clearbackupfiles_processer();
    $clearfileprocesser->execute($cutoff);

    $files = $clearfileprocesser->get_deleted_files();
    $filecount = count($files);

    if ($filecount) {
        $data = [];
        foreach ($files as $file) {
            $line = [];
            $line[] = $file->name;
            $line[] = $clearfileprocesser->format_bytes($file->size);
            $data[] = $line;
        }

        $table = new html_table();
        $table->head = [
            get_string('filename', 'tool_clearbackupfiles'),
            get_string('filesize', 'tool_clearbackupfiles'),
        ];
        $table->size = ['60%', '40%'];
        $table->align = ['left', 'left'];
        $table->data = $data;

        $a = new stdClass();
        $a->filecount = $filecount;
        $filesize = $clearfileprocesser->get_total_file_size();
        $a->filesize = tool_clearbackupfiles_processer::format_bytes($filesize);

        echo html_writer::tag('p', get_string('filedeletedheader', 'tool_clearbackupfiles'));
        echo html_writer::table($table);
        echo html_writer::tag('p', get_string('filedeletedfooter', 'tool_clearbackupfiles', $a));
    } else {
        echo html_writer::tag('p', get_string('filedeletedempty', 'tool_clearbackupfiles'));
    }
} else if ($formdata = $mform->get_data()) {
    // An explicit date overrides the configured retention period for this run only.
    if (!empty($formdata->cutoffdate)) {
        $cutoff = (int) $formdata->cutoffdate;
    } else {
        $cutoff = tool_clearbackupfiles_processer::get_default_cutoff();
    }

    $summary = \tool_clearbackupfiles\backup_file_selector::get_summary_older_than($cutoff);

    if ($summary->filecount) {
        $a = new stdClass();
        $a->filecount = $summary->filecount;
        $a->filesize = tool_clearbackupfiles_processer::format_bytes($summary->totalsize);
        $a->cutoff = userdate($cutoff);

        echo $OUTPUT->confirm(
            get_string('confirmdelete', 'tool_clearbackupfiles', $a) . ' ' .
                get_string('warningalert', 'tool_clearbackupfiles'),
            new moodle_url($pageurl, ['dlt' => 1, 'cutoff' => $cutoff, 'sesskey' => sesskey()]),
            $pageurl
        );
    } else {
        echo $OUTPUT->notification(
            get_string('nofilesbeforecutoff', 'tool_clearbackupfiles', userdate($cutoff)),
            \core\output\notification::NOTIFY_INFO
        );
        echo $OUTPUT->continue_button($pageurl);
    }
} else {
    $mform->display();
}

echo $OUTPUT->footer();
