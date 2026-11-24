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
 * Ad hoc task that runs a confirmed manual cleanup in the background.
 *
 * Uses the same processer and file selection as the synchronous manual cleanup.
 *
 * @package    tool_clearbackupfiles
 * @copyright  2026 ssystems GmbH, Mahmoud Chehada <mchehada@ssystems.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class manual_cleanup_task extends \core\task\adhoc_task {
    /**
     * Creates a task for the given cutoff, run as the given user.
     *
     * @param int $cutoff Unix timestamp; backup files created on or before it are deleted
     * @param int $userid User who requested the cleanup
     * @return self
     */
    public static function create(int $cutoff, int $userid): self {
        $task = new self();
        $task->set_custom_data(['cutoff' => $cutoff]);
        $task->set_userid($userid);
        return $task;
    }

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('manualcleanuptask', 'tool_clearbackupfiles');
    }

    /**
     * Deletes the backup files created on or before the stored cutoff.
     */
    public function execute() {
        $data = $this->get_custom_data();
        $cutoff = (int) ($data->cutoff ?? 0);

        // Do not throw: a retried task would pass the check once the cutoff lies in the past.
        if ($cutoff <= 0 || $cutoff > time()) {
            mtrace(get_string('cutoffdatefuture', 'tool_clearbackupfiles') . ' ' .
                get_string('filedeletedempty', 'tool_clearbackupfiles'));
            return;
        }

        $processer = new \tool_clearbackupfiles_processer();
        $processer->execute($cutoff);

        $a = new \stdClass();
        $a->filecount = count($processer->get_deleted_files());
        $a->filesize = \tool_clearbackupfiles_processer::format_bytes($processer->get_total_file_size());
        mtrace(get_string('filedeletedfooter', 'tool_clearbackupfiles', $a));
    }
}
