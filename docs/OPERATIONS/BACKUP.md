# Database Backup Procedure

Because CockpitConsole stores immutable event ledgers for production and dispatch traceability, the database is the most critical asset on the server.

## 1. Schedule & Command
Backups are executed daily at 02:00 AM via Windows Task Scheduler executing `scripts\backup_database.ps1`.

## 2. Location
Local Backup Path: `C:\CockpitConsoleBackups\Database\`

## 3. Retention
- The script generates a date-stamped file (e.g., `cockpitconsole_2026-08-26_163000.sql`).
- **Policy**: Keep the last 30 daily backups. Older backups should be archived. (Note: Ensure disk space is monitored).

## 4. Off-Machine Copy Recommendation
A local server failure (e.g., hard drive crash) will destroy both the application and the `C:\` backups. It is **mandatory** for IAC IT to configure a network synchronization tool (e.g., Robocopy or a NAS sync) to pull the contents of `C:\CockpitConsoleBackups\Database\` to an off-machine location daily.

## 5. Verification & Failure Handling
The PowerShell script explicitly checks that the `.sql` file was generated and that its size is greater than 0 bytes before logging a `SUCCESS`. If it fails, the script logs an `ERROR` to `backup_log.txt`. 

During Phase 12 Go-Live, the backup process **must** be verified by performing a Restore Drill (see [RESTORE.md](RESTORE.md)).
