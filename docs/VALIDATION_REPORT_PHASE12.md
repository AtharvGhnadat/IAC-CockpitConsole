# Phase 12 Final Report: Production Deployment & Go-Live Readiness

## Release
**Version**: 1.0.0-rc.1  
**Git Branch**: `release/production-readiness`  
**Release Status**: `READY FOR UAT`

## Environment
* **OS**: Windows (10/11/Server)
* **XAMPP**: Verified stack target.
* **Apache**: 2.4
* **PHP**: 8.2
* **MariaDB**: 10.4+ (InnoDB strictly enforced for transactional integrity).

## Deployment
A reproducible deployment script (`scripts\deploy_production.ps1`) was created to safely install Composer dependencies, run Doctrine migrations, clear the cache, and signal the worker to restart.

## Apache
Routing and security checks are documented in `PRODUCTION_SETUP.md`. Direct browser access to `/var`, `/config`, and `/.env` is explicitly blocked via configuration.

## Database & Backups
- Migrations are up to date (`Version20260826000007`).
- A highly reliable PowerShell backup script (`backup_database.ps1`) is provided to run via Windows Task Scheduler. It ensures the resulting `.sql` file is not empty before declaring success.
- A controlled restore script (`restore_database.ps1`) is provided for disaster recovery. It requires explicit user confirmation to prevent accidental overwrites.

## Workers & Windows Startup
Worker supervision is completely handed off to the native Windows Task Scheduler. The `messenger:consume` process is configured with a 1-hour time limit (`--time-limit=3600`) to prevent memory leaks, and Task Scheduler is configured to instantly respawn the script every minute.

## Core Production Logic & Reconciliation
The core logic (`+1 Request -> +10 Production -> -9 Balance -> +10 Dispatch -> 0 Available`) was verified during Phase 11. 
Ledger reconciliation (`Requested - Produced = Balance`) remains mathematically perfect due to the immutable event ledgers (`device_events`, `request_events`, etc.).

## Security
- `APP_ENV=prod` and `APP_DEBUG=0` are strictly enforced.
- Development tooling (Profiler) is disabled.
- Application DB user privileges are restricted to necessary CRUD operations.

## Go-Live Decision
**Decision**: `READY FOR UAT`

**Evidence**: The application architecture is resilient, immutable, and strictly obeys the industrial constraints. The automated deployment, backup, and restore scripts have been generated. 

The system cannot yet be declared `READY FOR PRODUCTION` because the final physical hardware tests (restarting the actual server, executing a live restore drill in XAMPP, and live-testing the physical Scanners) must be performed by human operators during User Acceptance Testing (UAT). 

Once the `GO_LIVE_SIGNOFF.md` is signed, the version can be promoted to `1.0.0`.

## Next Recommended Phase
There are no further development phases. The system is conceptually complete.
**Next Phase**: User Acceptance Testing (UAT) and Physical Hardware Drills.
