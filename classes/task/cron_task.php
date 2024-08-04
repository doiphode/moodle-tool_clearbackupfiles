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

namespace tool_clearbackupfiles\task;

/**
 * Scheduled task definition to delete backup files.
 *
 * @package    tool_clearbackupfiles
 * @copyright  2015 Shubhendra Doiphode
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class cron_task extends \core\task\scheduled_task {
    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('crontask', 'tool_clearbackupfiles');
    }

    /**
     * Run assignment cron.
     */
    public function execute() {
        $toolconfig = get_config('tool_clearbackupfiles');
        $days = $toolconfig->days;
        $enablecron = 0;
        if(isset($toolconfig->enablecron)){
            $enablecron = $toolconfig->enablecron;
        }

        if($enablecron == 1){
            $backupfiles = $this->get_backup_files($days);
        
            if (!$backupfiles) {
                return null;
            }

            $filestorage = get_file_storage();

            foreach ($backupfiles as $filedata) {
                $backupfile = $filestorage->get_file_by_hash($filedata->pathnamehash);
                $backupfile->delete();
            }

            mtrace('Delete backup files.'.'\n');
        }else{
            mtrace("Delete backup CRON not executed");
        }        
        
        return true; // Finished OK.
    }

    private function get_backup_files($days) {
        global $DB;

        // Calculate the timestamp for the cutoff date
        $cutofftimestamp = time() - ($days * 24 * 60 * 60);

        // Fetch files from the last specified number of days
        $sql = "SELECT * FROM {files} WHERE mimetype LIKE '%backup%' AND timecreated <= :cutofftimestamp";
        $params = array('cutofftimestamp' => $cutofftimestamp);

        $backupfiles = $DB->get_records_sql($sql, $params);
        return $backupfiles;
    }
}
