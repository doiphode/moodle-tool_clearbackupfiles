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

namespace tool_clearbackupfiles;

use tool_clearbackupfiles\task\manual_cleanup_task;

/**
 * Tests for the background manual cleanup task.
 *
 * @package    tool_clearbackupfiles
 * @copyright  2026 ssystems GmbH, Mahmoud Chehada <mchehada@ssystems.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \tool_clearbackupfiles\task\manual_cleanup_task
 */
final class manual_cleanup_task_test extends \advanced_testcase {
    /**
     * Creates an old and a recent backup plus an old decoy outside the backup areas.
     */
    private function create_files(): void {
        $fs = get_file_storage();
        $context = \context_system::instance();
        $files = [
            ['backup', 'automated', 'old-backup.mbz', time() - 10 * DAYSECS],
            ['backup', 'automated', 'recent-backup.mbz', time() - DAYSECS],
            ['course', 'overviewfiles', 'old-decoy.mbz', time() - 10 * DAYSECS],
        ];
        foreach ($files as [$component, $filearea, $filename, $timecreated]) {
            $fs->create_file_from_string([
                'contextid' => $context->id,
                'component' => $component,
                'filearea' => $filearea,
                'itemid' => 0,
                'filepath' => '/',
                'filename' => $filename,
                'timecreated' => $timecreated,
            ], 'payload ' . $filename);
        }
    }

    /**
     * The queued task deletes the same files as the immediate run and logs as the requesting user.
     */
    public function test_queued_task_deletes_files_and_triggers_events(): void {
        global $DB;
        $this->resetAfterTest();
        $this->create_files();
        $admin = get_admin();

        \core\task\manager::queue_adhoc_task(manual_cleanup_task::create(time() - 5 * DAYSECS, $admin->id), true);

        $sink = $this->redirectEvents();
        ob_start();
        $this->runAdhocTasks(manual_cleanup_task::class);
        $output = ob_get_clean();
        $events = $sink->get_events();
        $sink->close();

        $this->assertFalse($DB->record_exists('files', ['filename' => 'old-backup.mbz']));
        $this->assertTrue($DB->record_exists('files', ['filename' => 'recent-backup.mbz']));
        $this->assertTrue($DB->record_exists('files', ['filename' => 'old-decoy.mbz']));
        $this->assertStringContainsString('In total 1 backup files were deleted', $output);

        $completed = array_values(array_filter($events, static function ($event) {
            return $event instanceof event\clearbackup_completed;
        }));
        $this->assertCount(1, $completed);
        $this->assertEquals($admin->id, $completed[0]->userid);
    }

    /**
     * A cutoff in the future completes without deleting and without a retry.
     */
    public function test_future_cutoff_deletes_nothing_and_is_not_retried(): void {
        global $DB;
        $this->resetAfterTest();
        $this->create_files();

        \core\task\manager::queue_adhoc_task(manual_cleanup_task::create(time() + DAYSECS, get_admin()->id));

        ob_start();
        $this->runAdhocTasks(manual_cleanup_task::class);
        $output = ob_get_clean();

        $this->assertStringContainsString('The cutoff date must not be in the future.', $output);
        $this->assertTrue($DB->record_exists('files', ['filename' => 'old-backup.mbz']));
        $this->assertTrue($DB->record_exists('files', ['filename' => 'recent-backup.mbz']));
        $this->assertEmpty(\core\task\manager::get_adhoc_tasks(manual_cleanup_task::class));
    }
}
