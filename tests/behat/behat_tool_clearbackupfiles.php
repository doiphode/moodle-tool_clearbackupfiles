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

// NOTE: no MOODLE_INTERNAL test here, this file may be required by behat before including /config.php.

require_once(__DIR__ . '/../../../../../lib/behat/behat_base.php');

/**
 * Behat steps for tool_clearbackupfiles.
 *
 * @package    tool_clearbackupfiles
 * @category   test
 * @copyright  2026 ssystems GmbH, Mahmoud Chehada <mchehada@ssystems.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_tool_clearbackupfiles extends behat_base {
    /**
     * Creates a file in the automated course backup area, without running a real backup.
     *
     * @Given /^a backup file "(?P<filename_string>(?:[^"]|\\")*)" created "(?P<days_number>\d+)" days ago exists$/
     * @param string $filename File name
     * @param int $days Age of the file in days
     */
    public function a_backup_file_created_days_ago_exists(string $filename, int $days): void {
        get_file_storage()->create_file_from_string([
            'contextid' => context_system::instance()->id,
            'component' => 'backup',
            'filearea' => 'automated',
            'itemid' => 0,
            'filepath' => '/',
            'filename' => $filename,
            'timecreated' => time() - ($days * DAYSECS),
        ], 'behat backup payload');
    }
}
