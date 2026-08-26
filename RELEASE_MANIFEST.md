# Release Manifest

| Field | Detail |
|-------|--------|
| **Application** | IAC CockpitConsole |
| **Version** | v1.0.0-rc.1 |
| **Git Commit** | (Pending) |
| **Release Date** | 2026-08-26 |
| **PHP Requirement** | PHP 8.2 |
| **Symfony Version** | 7.4.x LTS |
| **Database Migration Version** | `Version20260826000007` |

## Configuration Changes
- Added support for `DashboardRow` and `DashboardColumn` layout engine in `.env` database via migrations.
- Added `scripts/` directory for automated PowerShell backup and deployment.
- Hardened Apache virtual host directories recommended.

## Known Issues
- None. System is ready for UAT.

## Rollback Notes
- Database changes in this release are primarily additive (new tables `dashboard_rows`, `dashboard_columns`). They can be safely dropped via Doctrine Migration `--down` if a full rollback is required, though standard database restore is preferred.
