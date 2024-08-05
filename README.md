# tool_clearbackupfiles

This admin tool clears all backup files from the server. It is useful for freeing up space from dev and test servers once they are refreshed from production.

It also gives a report og how many mbz files were deleting clearing how much space.

Instructions:
the backup files can be deleted from /admin/tool/clearbackupfiles/index.php and you can find logs at /report/log/index.php. The default moodle events is used to create log entry.
