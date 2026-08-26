# Database Restore Procedure

If the production database is corrupted, lost, or needs to be rolled back to a previous day's state, follow these exact steps.

## 1. Prerequisites
- You must have administrative access to the Windows Server.
- All production operations must be halted (Turn off the PLCs or disconnect the LAN cable if necessary to prevent data mismatch during restore).
- You must locate a verified `.sql` backup file in `C:\CockpitConsoleBackups\Database\`.

## 2. Restoring to Production
The provided PowerShell script includes a safety lock to prevent accidental overwrites.

1. Open PowerShell as Administrator.
2. Navigate to the application root:
   ```powershell
   cd C:\xampp\htdocs\IAC\CockpitConsole
   ```
3. Execute the script:
   ```powershell
   .\scripts\restore_database.ps1 -BackupFile C:\CockpitConsoleBackups\Database\cockpitconsole_YOUR_DATE.sql
   ```
4. The script will warn you that this is a **DESTRUCTIVE RESTORE OPERATION**. Type `YES` to confirm.
5. Wait for the `SUCCESS` message.

## 3. Post-Restore Validation
1. **Schema Validation**: Run `php bin/console doctrine:schema:validate`.
2. **Application Health**: Run `php bin/console cockpit:health`. Ensure the database connection is green.
3. **Worker Restart**: Because the database state changed, restart the background worker:
   ```powershell
   Get-Process -Name "php" | Where-Object { $_.CommandLine -match "messenger:consume" } | Stop-Process -Force
   ```
4. Log into the Dashboard and verify the production values match the expectation for the time the backup was taken.

## 4. Restore Drill (Testing)
To perform a restore test *without* destroying production:
1. Create a test database via XAMPP MySQL Shell: `CREATE DATABASE cockpit_test;`
2. Temporarily change your `.env.local` to point to `cockpit_test`.
3. Run the restore script.
4. Verify the application loads correctly.
5. **CRITICAL**: Revert `.env.local` to point back to the real production database when done.
