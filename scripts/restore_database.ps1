# restore_database.ps1
# CockpitConsole Production Restore Script
# IMPORTANT: This is a manual-execution script. Never schedule this.

param (
    [Parameter(Mandatory=$true)]
    [string]$BackupFile,
    
    [Parameter(Mandatory=$false)]
    [switch]$ConfirmRestore
)

$ErrorActionPreference = "Stop"

# Configuration
$MySqlPath = "C:\xampp\mysql\bin\mysql.exe"
$DatabaseUser = "root"
$DatabaseName = "cockpit_console"

if (!(Test-Path $BackupFile)) {
    Write-Error "Backup file not found: $BackupFile"
    exit 1
}

Write-Host "========================================" -ForegroundColor Red
Write-Host "  WARNING: DESTRUCTIVE RESTORE OPERATION" -ForegroundColor Red
Write-Host "========================================" -ForegroundColor Red
Write-Host "Target Database: $DatabaseName"
Write-Host "Backup File: $BackupFile"
Write-Host ""

if (!$ConfirmRestore) {
    $confirmation = Read-Host "Are you ABSOLUTELY sure you want to overwrite $DatabaseName? Type 'YES' to proceed"
    if ($confirmation -cne "YES") {
        Write-Host "Restore cancelled."
        exit 0
    }
}

try {
    Write-Host "Restoring..."
    # Execute the restore
    Get-Content $BackupFile | & $MySqlPath -u $DatabaseUser $DatabaseName
    
    Write-Host "SUCCESS: Database restored from $BackupFile" -ForegroundColor Green
    exit 0
} catch {
    Write-Error "Restore failed: $_"
    exit 1
}
