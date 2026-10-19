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
 * Tests for backup_file_selector.
 *
 * @package    tool_clearbackupfiles
 * @copyright  2026 ssystems GmbH, Mahmoud Chehada <mchehada@ssystems.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \tool_clearbackupfiles\backup_file_selector
 */
class backup_file_selector_test extends \advanced_testcase {

    /**
     * Known backup pools are in scope; arbitrary course uploads are not.
     */
    public function test_is_deletable_backup_storage(): void {
        $this->assertTrue(backup_file_selector::is_deletable_backup_storage('backup', 'course'));
        $this->assertTrue(backup_file_selector::is_deletable_backup_storage('backup', 'automated'));
        $this->assertTrue(backup_file_selector::is_deletable_backup_storage('user', 'backup'));

        $this->assertFalse(backup_file_selector::is_deletable_backup_storage('course', 'overviewfiles'));
        $this->assertFalse(backup_file_selector::is_deletable_backup_storage('mod_resource', 'content'));
    }

    /**
     * Selection uses component/filearea, not MIME type alone.
     */
    public function test_get_files_older_than_excludes_course_uploads(): void {
        $this->resetAfterTest();

        $fs = get_file_storage();
        $course = $this->getDataGenerator()->create_course();
        $context = \context_course::instance($course->id);
        $cutoff = time();
        $oldtime = $cutoff - DAYSECS;

        $fs->create_file_from_string([
            'contextid' => $context->id,
            'component' => 'backup',
            'filearea' => 'course',
            'itemid' => 0,
            'filepath' => '/',
            'filename' => 'in-scope-backup.mbz',
            'timecreated' => $oldtime,
        ], 'backup payload');

        $fs->create_file_from_string([
            'contextid' => $context->id,
            'component' => 'course',
            'filearea' => 'overviewfiles',
            'itemid' => 0,
            'filepath' => '/',
            'filename' => 'decoy-uploaded-backup.mbz',
            'timecreated' => $oldtime,
        ], 'not a real backup store');

        $files = backup_file_selector::get_files_older_than($cutoff);
        $filenames = array_map(static function($file) {
            return $file->filename;
        }, $files);

        $this->assertContains('in-scope-backup.mbz', $filenames);
        $this->assertNotContains('decoy-uploaded-backup.mbz', $filenames);
    }
}
