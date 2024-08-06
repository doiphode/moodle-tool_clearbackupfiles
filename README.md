![Moodle Plugin CI](https://github.com/doiphode/moodle-tool_clearbackupfiles/actions/workflows/moodle-ci.yml/badge.svg)

# tool_clearbackupfiles

This admin tool clears all backup files from the server. It is useful for freeing up space from dev and test servers once they are refreshed from production.

It also gives a report of how many .mbz files were deleted and how much space was cleared.

## Instructions
the backup files can be deleted from /admin/tool/clearbackupfiles/index.php and you can find logs at /report/log/index.php. The default moodle events is used to create log entry.
