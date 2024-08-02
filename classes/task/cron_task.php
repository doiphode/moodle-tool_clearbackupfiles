<?php
namespace tool_clearbackupfiles\task;
defined('MOODLE_INTERNAL') || die();

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
        global $DB,$USER;

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
