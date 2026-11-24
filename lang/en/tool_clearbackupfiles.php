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
 * English language strings
 *
 * @package    tool_clearbackupfiles
 * @copyright  2015 Shubhendra Doiphode
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['backupcompletedlog'] = 'During this operation {$a->filecount} course backup files of total size {$a->filesize} were deleted.';
$string['backupremovedlog'] = 'Course backup file {$a->filename} of size {$a->filesize} was deleted.';
$string['backupstoragescope'] = 'Cleanup applies only to archives in Moodle backup storage: backup component areas (course, activity, automated, backup) and the user private backup area (user/backup). Files in other areas (for example course overview files) are never selected, even if they use a backup MIME type.';
$string['backupstoragescopeheading'] = 'Which files are affected';

$string['cleanupalreadyqueued'] = 'A background cleanup is already queued or waiting for a retry, so no new one was added. Check the task logs for its status.';
$string['cleanupqueued'] = 'The cleanup was queued as a background task and runs with the next cron run. Failures and progress are shown in the task logs; deleted files are listed in the site logs.';
$string['clearbackupcompleted'] = 'Clear backup completed';
$string['confirmdelete'] = 'This permanently deletes {$a->filecount} backup files ({$a->filesize}) created on or before {$a->cutoff}. Deleted backups cannot be restored.';
$string['confirmqueue'] = 'A background task will permanently delete up to {$a->filecount} backup files ({$a->filesize}) created on or before {$a->cutoff} with the next cron run. Deleted backups cannot be restored.';
$string['continuetoclearbackup'] = 'Continue to clear backup';
$string['coursebackupremoved'] = 'Course backup deleted';
$string['crontask'] = 'Clear backup (.mbz) files task';
$string['cutoffdate'] = 'Cutoff date';
$string['cutoffdate_help'] = 'Optional. If enabled, this run deletes backup archives created on or before 00:00 of the selected date (backups from that day itself are kept) instead of using the configured retention period. It applies to this manual run only: the plugin setting and the scheduled task are not changed. The same backup file areas are affected as in the scheduled cleanup.';
$string['cutoffdatefuture'] = 'The cutoff date must not be in the future.';
$string['cutoffdefault'] = 'If no date is set, backup archives that are at least {$a} days old are deleted (configured retention period).';
$string['cutoffdefaultall'] = 'If no date is set, every backup archive is deleted, including backups created moments ago, because the configured retention period is 0 days or less.';

$string['days'] = 'Retention period (days)';
$string['daysdesc'] = 'Backup archives that are at least this many days old are deleted. The age is counted from the time the file was created in Moodle file storage, not from the course or activity dates. This value is used by both the manual cleanup page and the scheduled task. A value of 0 or less selects every backup archive, including backups created moments ago. Only Moodle backup file areas are affected; uploaded .mbz files in course content areas are not removed. Deleted backups cannot be restored, so test the value on a staging site first.';

$string['enablecron'] = 'Enable scheduled cleanup';
$string['enablecrondesc'] = 'If enabled, the scheduled task "{$a}" automatically deletes backup archives that reached the retention period each time it runs. Check and adjust how often it runs on the Scheduled tasks admin page. If disabled, the task runs but deletes nothing. The manual cleanup page is always available and does not depend on this setting. Unlike the manual cleanup, the scheduled task shows no report of deleted files and does not log the plugin events.';

$string['filedeletedempty'] = 'There are no backup files to delete.';
$string['filedeletedfooter'] = 'In total {$a->filecount} backup files were deleted and {$a->filesize} of server space was cleared.';
$string['filedeletedheader'] = 'The course backup files deleted during this operation are as follows:';
$string['filename'] = 'File name';
$string['filesize'] = 'File size';

$string['manualcleanuptask'] = 'Manual backup cleanup (background)';

$string['nofilesbeforecutoff'] = 'There are no backup files created on or before {$a}.';

$string['pluginname'] = 'Clear backup files';

$string['runmode'] = 'Run';
$string['runmode_help'] = 'Choose "Now" to delete the files in this page request and see the list of deleted files. Choose "In the background" for large cleanups: an ad hoc task deletes the files with the next cron run, using the same cutoff and the same file selection. Cron must be running. Failures are shown in the task logs (and the ad hoc tasks page); deleted files are recorded in the site logs.';
$string['runmodenow'] = 'Now';
$string['runmodequeue'] = 'In the background (ad hoc task)';

$string['warningalert'] = 'Are you sure you want to continue?';
$string['warningmsg'] = 'Please note, clearing of backup files is an irreversible process, you will not be able to restore deleted backup files.';
