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
 * Selects Moodle core backup archives for cleanup (component/filearea aware).
 *
 * Matches files stored in Moodle's backup file pools (see backup/restorefile.php), not arbitrary
 * course content uploads that happen to use a backup MIME type.
 *
 * @package    tool_clearbackupfiles
 * @copyright  2026 ssystems GmbH, Mahmoud Chehada <mchehada@ssystems.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_file_selector {

    /** @var string Core backup component for course/activity/automated archives */
    public const COMPONENT_BACKUP = 'backup';

    /** @var string User-private backup area component */
    public const COMPONENT_USER = 'user';

    /**
     * Allowed component => filearea pairs (documented default scope).
     *
     * @return array<string, string[]>
     */
    public static function get_allowed_storage_locations(): array {
        return [
            self::COMPONENT_BACKUP => ['course', 'activity', 'automated', 'backup'],
            self::COMPONENT_USER => ['backup'],
        ];
    }

    /**
     * Whether a stored file record is in a deletable backup storage location.
     *
     * @param string $component File component
     * @param string $filearea File area
     * @return bool
     */
    public static function is_deletable_backup_storage(string $component, string $filearea): bool {
        $locations = self::get_allowed_storage_locations();
        if (!isset($locations[$component])) {
            return false;
        }
        return in_array($filearea, $locations[$component], true);
    }

    /**
     * Returns file rows from {files} older than the cutoff timestamp.
     *
     * @param int $cutofftimestamp Unix timestamp; files with timecreated <= this value are returned
     * @return \stdClass[] Records from {files}
     */
    public static function get_files_older_than(int $cutofftimestamp): array {
        global $DB;

        [$sql, $params] = self::get_select_sql($cutofftimestamp);
        return $DB->get_records_sql($sql, $params);
    }

    /**
     * SQL and parameters for backup file selection (shared by manual and scheduled cleanup).
     *
     * @param int $cutofftimestamp Unix timestamp
     * @return array{0: string, 1: array}
     */
    public static function get_select_sql(int $cutofftimestamp): array {
        $conditions = [];
        $params = ['cutofftimestamp' => $cutofftimestamp];
        $index = 0;

        foreach (self::get_allowed_storage_locations() as $component => $fileareas) {
            $areaparams = [];
            foreach ($fileareas as $filearea) {
                $paramname = 'fa' . $index++;
                $areaparams[] = ':' . $paramname;
                $params[$paramname] = $filearea;
            }
            $componentparam = 'comp' . $index++;
            $params[$componentparam] = $component;
            $conditions[] = '(component = :' . $componentparam . ' AND filearea IN (' . implode(', ', $areaparams) . '))';
        }

        $where = implode(' OR ', $conditions);
        $sql = "SELECT *
                  FROM {files}
                 WHERE filename <> '.'
                   AND timecreated <= :cutofftimestamp
                   AND ($where)";

        return [$sql, $params];
    }
}
