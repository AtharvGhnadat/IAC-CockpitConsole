# Pre-Deployment Checklist

Before deploying a new version of CockpitConsole to production, verify the following:

- [ ] **Correct release checked out**: The target branch/tag (e.g., `v1.0.0`) is checked out.
- [ ] **Git working tree clean**: `git status` shows no uncommitted changes.
- [ ] **VERSION verified**: `VERSION` matches the expected release.
- [ ] **Phase 11 tests passed**: Concurrency and safety validations are clean.
- [ ] **No blocker defects**: No known CRITICAL/BLOCKER bugs remain open.
- [ ] **Production .env configured**: `APP_ENV=prod` and `APP_DEBUG=0` are strictly set in `.env.local`.
- [ ] **Database backup completed**: A verified manual or scheduled backup ran successfully today.
- [ ] **Backup verified**: The file size is > 0 and the `.sql` file exists in `C:\CockpitConsoleBackups\Database`.
- [ ] **Disk space sufficient**: At least 10GB free on the target server.
- [ ] **Apache running**: XAMPP Apache is active.
- [ ] **MariaDB running**: XAMPP MySQL is active.
- [ ] **Worker supervision configured**: Windows Task Scheduler has the messenger worker task enabled.
- [ ] **Device endpoints known**: The IP addresses of the PLCs and Scanners are documented.
- [ ] **Rollback release available**: A plan exists in case deployment fails.
