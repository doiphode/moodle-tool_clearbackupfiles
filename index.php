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

require_once(dirname(__FILE__) . '/../../../config.php');
require_once("$CFG->libdir/filestorage/file_storage.php");
global $CFG, $DB, $EXTDB; // Globals
$backupfiles = $DB->get_records_sql(
    "SELECT * from mdl_files sq where sq.mimetype like '%backup%'"
);

print '<h2>Clear backup files</h2>';

if (!$backupfiles) {
    print '<p>There are no backup files.</p>';
    return;
}

print '<p>The deleted course backup files during this operation are as follows: </p>';

$i = 0;
$filesize = 0;
foreach ($backupfiles as $key => $filedata) {
    $fs = get_file_storage();
    $file = $fs->get_file_by_hash($filedata->pathnamehash);
    $file->delete();
    $i++;
    $filesize+ = $file->get_filesize();
    \tool_clearbackupfiles\event\coursebackup_removed::create(array('other' => array(
        'filename' => $file->get_filename(),
        'filesize' => formatBytes($file->get_filesize())
    )))->trigger();
    print "<i> Filename: ".$file->get_filename()." Filesize:".formatBytes($file->get_filesize())."</i></br>";

}
\tool_clearbackupfiles\event\clearbackup_completed::create(array('other' => array(
    'filecount' => $i,
    'filesize' => formatBytes($filesize)
)))->trigger();
print "<p>In total, ".$i." backup files were deleted and ".formatBytes($filesize).' of server space was cleared.';

function formatBytes($size, $precision = 2) {
    $base = log($size, 1024);
    $suffixes = array('', 'KB', 'MB', 'GB', 'TB');
    return round(pow(1024, $base - floor($base)), $precision) . $suffixes[floor($base)];
}