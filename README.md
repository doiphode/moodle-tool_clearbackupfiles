![Moodle Plugin CI](https://github.com/doiphode/moodle-tool_clearbackupfiles/actions/workflows/moodle-ci.yml/badge.svg)

# Clear Backup Files (`tool_clearbackupfiles`)

A Moodle admin tool that helps administrators remove old course backup files (`.mbz`) from the server and reclaim storage space.

Particularly useful for development, testing, and staging environments where backup files accumulate over time.

## Features

- **Manual cleanup** — Remove backup files directly from the plugin interface.
- **Cleanup by age** — Delete only files older than a configured number of days.
- **Scheduled cleanup (CRON)** — Automate cleanup via Moodle scheduled tasks.
- **Cleanup statistics** — Reports total files deleted and disk space recovered.
- **Event logging** — Uses Moodle's standard event system to record all cleanup activity.

## Compatibility

| Moodle Version | Supported |
|----------------|-----------|
| Moodle 4.5.x   | Yes       |
| Moodle 5.0.x   | Yes       |
| Moodle 5.2.x   | Yes       |

## Installation

### Via Moodle Plugin Installer

1. Download the plugin ZIP package.
2. Log in as a Moodle administrator.
3. Navigate to **Site administration → Plugins → Install plugins**.
4. Upload the ZIP file and follow the on-screen steps.
5. Complete the upgrade process and save settings.

### Manual Installation

1. Download and extract the plugin source code.
2. Copy the folder into:
   ```
   /admin/tool/clearbackupfiles
   ```
3. Navigate to **Site administration → Notifications** and complete the installation.

## Configuration

After installation, navigate to:

**Site administration → Plugins → Admin tools → Clear backup files**

| Setting | Description | Default |
|---------|-------------|---------|
| **Retention period (days)** | Backup archives at least this many days old (counted from file creation in Moodle file storage) are deleted. Used by both the manual cleanup and the scheduled task. `0` or less selects every backup archive. | `5` |
| **Enable scheduled cleanup** | When enabled, the scheduled task deletes old backup archives automatically. The manual cleanup is always available, independent of this setting. | Off |

## Usage

1. Go to **Site administration → Plugins → Admin tools → Clear backup files**.
2. Set the retention period and optionally enable the scheduled cleanup.
3. Click **Continue to clear backup**.
4. Optionally enable **Cutoff date** and pick a date; backups created on or before 00:00 of that date are deleted. This overrides the retention period for this run only; the setting and the scheduled task are not changed.
5. Click **Delete**. The confirmation shows the cutoff date, the number of files and their total size.
6. Confirm — this operation is **irreversible**.

After cleanup, the plugin displays:

- A list of all deleted backup files with their sizes.
- Total number of files deleted.
- Total disk space recovered.

## Logs

Cleanup activity is recorded through Moodle's standard event system and can be reviewed at:

**Site administration → Reports → Logs**

Two events are logged:

| Event | Description |
|-------|-------------|
| `coursebackup_removed` | Fired for each individual backup file deleted. |
| `clearbackup_completed` | Fired once at the end of a cleanup run with totals. |

## Use Cases

- Development servers refreshed from production dumps.
- Testing and staging environments with accumulated backups.
- Servers with limited storage that need routine maintenance.
- Automated scheduled maintenance via Moodle CRON.

## License

GNU General Public License v3 or later — see [LICENSE](LICENSE).

Copyright &copy; 2015 Shubhendra Doiphode.
