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
 * Handles the deletion of backup files.
 *
 * @package    tool_clearbackupfiles
 * @copyright  2015 Shubhendra Doiphode
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_clearbackupfiles_processer {
    /**
     * @var array Contains the deleted files.
     */
    private $deletedfiles = [];

    /**
     * @var int The total size of deleted files in bytes
     */
    private $totalfilesize = 0;

    /**
     * Erases the backup files created on or before the cutoff.
     *
     * @param int|null $cutofftimestamp Unix timestamp; null uses the configured retention period
     * @return void
     */
    public function execute(?int $cutofftimestamp = null) {
        if ($cutofftimestamp === null) {
            $cutofftimestamp = self::get_default_cutoff();
        }

        $backupfiles = \tool_clearbackupfiles\backup_file_selector::get_files_older_than($cutofftimestamp);

        if (!$backupfiles) {
            return null;
        }

        $filestorage = get_file_storage();

        foreach ($backupfiles as $filedata) {
            $backupfile = $filestorage->get_file_by_hash($filedata->pathnamehash);
            if (!$backupfile) {
                // Already removed, for example by a concurrent cleanup run.
                continue;
            }
            $backupfile->delete();

            $file = new stdClass();
            $file->name = $backupfile->get_filename();
            $file->size = $backupfile->get_filesize();

            $this->deletedfiles[] = $file;
            $this->totalfilesize += $file->size;

            \tool_clearbackupfiles\event\coursebackup_removed::create(['other' => [
                'filename' => $file->name,
                'filesize' => self::format_bytes($file->size),
            ]])->trigger();
        }

        \tool_clearbackupfiles\event\clearbackup_completed::create(['other' => [
            'filecount' => count($this->deletedfiles),
            'filesize' => self::format_bytes($this->totalfilesize),
        ]])->trigger();
    }

    /**
     * Returns the information of the deleted files.
     *
     * @return array An array of stdClass objects
     */
    public function get_deleted_files() {
        return $this->deletedfiles;
    }

    /**
     * Returns the total size of the deleted files in bytes
     *
     * @return int
     */
    public function get_total_file_size() {
        return $this->totalfilesize;
    }

    /**
     * Returns the cutoff timestamp derived from the configured retention period.
     *
     * @return int Unix timestamp; backup files created on or before it are selected
     */
    public static function get_default_cutoff(): int {
        $days = (int) get_config('tool_clearbackupfiles', 'days');
        // A retention of 0 days or less selects every backup; never return a cutoff in the future.
        return time() - (max(0, $days) * DAYSECS);
    }

    /**
     * Formats file size values into a human-readable form.
     *
     * @param int $size The file size in bytes
     * @param int $precision The number of digits to round to
     * @return string The human-readable file size
     */
    public static function format_bytes($size, $precision = 2) {
        if ($size <= 0) {
            return '0';
        }
        $base = log($size, 1024);
        $suffixes = ['', 'KB', 'MB', 'GB', 'TB'];
        return round(pow(1024, $base - floor($base)), $precision) . $suffixes[floor($base)];
    }
}
