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
 * Version details.
 * @package    tool_clearbackupfiles
 * @copyright  2015 Shubhendra Doiphode
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$string['pluginname'] = 'Clear backup files';
$string['coursebackupremoved'] = 'Course backup deleted';
$string['backupremovedlog'] = 'Course backup file {$a->filename} of size {$a->filesize} was deleted.';
$string['clearbackupcompleted'] = 'Clear backup completed';
$string['backupcompletedlog'] = 'During this operation {$a->filecount} course backup files of total size {$a->filesize} were deleted.';
$string['filename'] = 'File name';
$string['filesize'] = 'File size';
$string['filedeletedheader'] = 'The course backup files deleted during this operation are as follows:';
$string['filedeletedfooter'] = 'In total {$a->filecount} backup files were deleted and {$a->filesize} of server space was cleared.';
$string['filedeletedempty'] = 'There are no backup files to delete.';

$string['warningmsg'] = 'Please note, clearing of backup files is an irreversible process, you will not be able to restore deleted backup files.';
$string['warningalert'] = 'Are you sure you want to continue?';
$string['days'] = 'Days';
$string['daysdesc'] = 'Number of days of backup to clear';
$string['continuetoclearbackup'] = 'Continue to clear backup';
$string['enablecron'] = 'Enable CRON';
$string['enablecrondesc'] = '';