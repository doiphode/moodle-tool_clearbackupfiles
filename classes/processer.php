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

defined('MOODLE_INTERNAL') || die();

class tool_clearbackupfiles_processer {

    private $deletedfiles = array();
    private $totalfilesize = 0;

    public function execute() {
        
        $toolconfig = get_config('tool_clearbackupfiles');
        $days = $toolconfig->days;

        $backupfiles = $this->get_backup_files($days);
        if (!$backupfiles) {
            return null;
        }

        $filestorage = get_file_storage();

        foreach ($backupfiles as $filedata) {
            $backupfile = $filestorage->get_file_by_hash($filedata->pathnamehash);
            $backupfile->delete();

            $file = new stdClass();
            $file->name = $backupfile->get_filename();
            $file->size = $backupfile->get_filesize();


            $this->clearedfiles = array();
            $this->deletedfiles[] = $file;
            $this->totalfilesize += $file->size;

            \tool_clearbackupfiles\event\coursebackup_removed::create(array('other' => array(
                'filename' => $file->name,
                'filesize' => self::format_bytes($file->size)
            )))->trigger();
        }

        \tool_clearbackupfiles\event\clearbackup_completed::create(array('other' => array(
            'filecount' => count($this->clearedfiles),
            'filesize' => self::format_bytes($this->totalfilesize)
        )))->trigger();
    }

    public function get_deleted_files() {
        return $this->deletedfiles;
    }

    public function get_total_file_size() {
        return $this->totalfilesize;
    }

    private function get_backup_files($days) {
        global $DB;

        // Calculate the timestamp for the cutoff date
        $cutofftimestamp = time() - ($days * 24 * 60 * 60);

        // Fetch files from the last specified number of days
        $sql = "SELECT * FROM {files} WHERE mimetype LIKE '%backup%' AND timecreated >= :cutofftimestamp";
        $params = array('cutofftimestamp' => $cutofftimestamp);

        $backupfiles = $DB->get_records_sql($sql, $params);
        return $backupfiles;
    }

    public static function format_bytes($size, $precision = 2) {
        $base = log($size, 1024);
        $suffixes = array('', 'KB', 'MB', 'GB', 'TB');
        return round(pow(1024, $base - floor($base)), $precision) . $suffixes[floor($base)];
    }
}