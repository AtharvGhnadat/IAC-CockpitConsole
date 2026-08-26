# Rollback Procedure

If a deployment fails the Post-Deployment checks, or if a critical bug is found immediately after Go-Live, execute this rollback strategy.

## 1. When is Rollback Allowed?
Rollback is allowed **only if** the issue constitutes a BLOCKER (e.g., duplicate dispatches, data loss, application crash). Cosmetic issues should be hot-fixed instead of rolling back the entire application.

## 2. Code Rollback
Check out the previous working tag/commit:
```powershell
git checkout tags/vX.X.X
composer install --no-dev --optimize-autoloader
php bin/console cache:clear --env=prod
```

## 3. Database Migration Implications
> [!WARNING]
> Code rollback may not be safe after an **irreversible database migration** (e.g., dropping a column).

If the latest deployment included a database schema change:
1. **Option A (Reversible)**: Run `php bin/console doctrine:migrations:execute DoctrineMigrations\\VersionXYZ --down` to undo the specific schema change.
2. **Option B (Irreversible or Complex Data Loss)**: Use the `scripts\restore_database.ps1` script to completely restore the database to the exact state it was in before the deployment began. **Note: Any production events recorded since the deployment began will be lost in this scenario.**

## 4. Worker Restart
Always restart the worker after a code rollback to ensure the old code is loaded into memory:
```powershell
Get-Process -Name "php" | Where-Object { $_.CommandLine -match "messenger:consume" } | Stop-Process -Force
```

## 5. Health Verification
Run `php bin/console cockpit:health` to ensure the old version is fully stabilized.
