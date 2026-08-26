# backup_database.ps1
# CockpitConsole Production Backup Script
# This script should be scheduled via Windows Task Scheduler.

$ErrorActionPreference = "Stop"

# Configuration
$MySqlDumpPath = "C:\xampp\mysql\bin\mysqldump.exe"
$BackupDir = "C:\CockpitConsoleBackups\Database"
$DatabaseUser = "root" # Replace with production user in real environment
$DatabaseName = "cockpit_console"
$DateStamp = Get-Date -Format "yyyy-MM-dd_HHmmss"
$BackupFile = "$BackupDir\cockpitconsole_$DateStamp.sql"
$LogFile = "$BackupDir\backup_log.txt"

# Ensure directory exists
if (!(Test-Path -Path $BackupDir)) {
    New-Item -ItemType Directory -Path $BackupDir | Out-Null
}

try {
    Write-Output "[$DateStamp] Starting backup for $DatabaseName..." | Out-File -FilePath $LogFile -Append
    
    # Run mysqldump
    & $MySqlDumpPath -u $DatabaseUser $DatabaseName --result-file=$BackupFile 2>> $LogFile

    # Verification
    if (Test-Path $BackupFile) {
        $fileSize = (Get-Item $BackupFile).length
        if ($fileSize -gt 0) {
            Write-Output "[$DateStamp] SUCCESS: Backup saved to $BackupFile ($fileSize bytes)" | Out-File -FilePath $LogFile -Append
            Write-Host "Backup successful: $BackupFile"
            exit 0
        } else {
            Write-Output "[$DateStamp] ERROR: Backup file is empty!" | Out-File -FilePath $LogFile -Append
            Write-Host "Backup failed: File is empty."
            Remove-Item $BackupFile
            exit 1
        }
    } else {
        Write-Output "[$DateStamp] ERROR: Backup file was not created!" | Out-File -FilePath $LogFile -Append
        Write-Host "Backup failed: File not created."
        exit 1
    }
} catch {
    Write-Output "[$DateStamp] EXCEPTION: $_" | Out-File -FilePath $LogFile -Append
    Write-Host "Backup encountered an error. Check $LogFile."
    exit 1
}
