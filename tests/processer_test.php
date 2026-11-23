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

/**
 * Tests for the manual cleanup processer.
 *
 * @package    tool_clearbackupfiles
 * @copyright  2026 ssystems GmbH, Mahmoud Chehada <mchehada@ssystems.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \tool_clearbackupfiles_processer
 */
final class processer_test extends \advanced_testcase {
    /**
     * Creates two backups (10 and 2 days old) and an old decoy outside the backup areas.
     *
     * @return void
     */
    private function create_files(): void {
        $fs = get_file_storage();
        $course = $this->getDataGenerator()->create_course();
        $context = \context_course::instance($course->id);

        $files = [
            ['backup', 'course', 'old-backup.mbz', time() - 10 * DAYSECS],
            ['backup', 'course', 'recent-backup.mbz', time() - 2 * DAYSECS],
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
     * Returns the names of the files deleted by the processer.
     *
     * @param \tool_clearbackupfiles_processer $processer
     * @return string[]
     */
    private function deleted_names(\tool_clearbackupfiles_processer $processer): array {
        return array_map(static function ($file) {
            return $file->name;
        }, $processer->get_deleted_files());
    }

    /**
     * Without a cutoff the configured retention period is used.
     */
    public function test_execute_uses_configured_days_by_default(): void {
        $this->resetAfterTest();
        set_config('days', 5, 'tool_clearbackupfiles');
        $this->create_files();

        $processer = new \tool_clearbackupfiles_processer();
        $processer->execute();

        $this->assertSame(['old-backup.mbz'], $this->deleted_names($processer));
    }

    /**
     * An explicit cutoff overrides the configured retention period.
     */
    public function test_execute_with_explicit_cutoff(): void {
        $this->resetAfterTest();
        set_config('days', 5, 'tool_clearbackupfiles');
        $this->create_files();

        $processer = new \tool_clearbackupfiles_processer();
        $processer->execute(time() - DAYSECS);

        $deleted = $this->deleted_names($processer);
        sort($deleted);
        $this->assertSame(['old-backup.mbz', 'recent-backup.mbz'], $deleted);
        $this->assertSame(5, (int) get_config('tool_clearbackupfiles', 'days'));
    }

    /**
     * Files outside the backup file areas are kept, whatever the cutoff.
     */
    public function test_execute_keeps_files_outside_backup_areas(): void {
        global $DB;
        $this->resetAfterTest();
        $this->create_files();

        $processer = new \tool_clearbackupfiles_processer();
        $processer->execute(time());

        $this->assertNotContains('old-decoy.mbz', $this->deleted_names($processer));
        $this->assertTrue($DB->record_exists('files', ['filename' => 'old-decoy.mbz']));
        $this->assertFalse($DB->record_exists('files', ['filename' => 'old-backup.mbz']));
    }

    /**
     * The default cutoff follows the configured retention period.
     */
    public function test_get_default_cutoff(): void {
        $this->resetAfterTest();
        set_config('days', 3, 'tool_clearbackupfiles');

        $before = time();
        $cutoff = \tool_clearbackupfiles_processer::get_default_cutoff();

        $this->assertGreaterThanOrEqual($before - 3 * DAYSECS, $cutoff);
        $this->assertLessThanOrEqual(time() - 3 * DAYSECS, $cutoff);
    }

    /**
     * A retention of 0 days or less never yields a cutoff in the future.
     */
    public function test_get_default_cutoff_negative_days(): void {
        $this->resetAfterTest();
        set_config('days', -3, 'tool_clearbackupfiles');

        $this->assertLessThanOrEqual(time(), \tool_clearbackupfiles_processer::get_default_cutoff());
    }

    /**
     * Zero bytes do not cause a math warning.
     */
    public function test_format_bytes_zero(): void {
        $this->assertSame('0', \tool_clearbackupfiles_processer::format_bytes(0));
    }
}
