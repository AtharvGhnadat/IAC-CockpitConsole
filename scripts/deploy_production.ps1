# deploy_production.ps1
# CockpitConsole Production Deployment Helper Script

$ErrorActionPreference = "Stop"

Write-Host "=========================================" -ForegroundColor Cyan
Write-Host " CockpitConsole Production Deploy Helper" -ForegroundColor Cyan
Write-Host "=========================================" -ForegroundColor Cyan
Write-Host "This script will execute Composer install, Doctrine migrations, and cache warmup."
Write-Host ""

$confirmation = Read-Host "Is APP_ENV=prod and have you taken a database backup? (YES/NO)"
if ($confirmation -cne "YES") {
    Write-Host "Deployment cancelled. Please backup your database first."
    exit 0
}

try {
    Write-Host "`n1. Installing Production Dependencies..."
    # We use php.exe explicitly to avoid environment PATH confusion if composer uses a different one
    C:\xampp\php\php.exe C:\ProgramData\ComposerSetup\bin\composer.phar install --no-dev --optimize-autoloader -n 

    Write-Host "`n2. Running Database Migrations..."
    C:\xampp\php\php.exe bin/console doctrine:migrations:migrate -n --env=prod

    Write-Host "`n3. Clearing and Warming Cache..."
    C:\xampp\php\php.exe bin/console cache:clear --env=prod

    Write-Host "`n4. Restarting Messenger Worker..."
    # Depending on how the worker is hosted (Task Scheduler vs NSSM), we kill the current process.
    # The supervisor (e.g., Task Scheduler repeating every 1 minute) will restart it.
    Get-Process -Name "php" -ErrorAction SilentlyContinue | Where-Object { $_.CommandLine -match "messenger:consume" } | Stop-Process -Force
    Write-Host "Worker process killed. Supervisor should restart it shortly."

    Write-Host "`n=========================================" -ForegroundColor Green
    Write-Host " DEPLOYMENT COMPLETED SUCCESSFULLY" -ForegroundColor Green
    Write-Host "=========================================" -ForegroundColor Green
    Write-Host "Please perform manual health checks."

} catch {
    Write-Error "Deployment failed: $_"
    exit 1
}
