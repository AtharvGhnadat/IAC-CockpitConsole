# Post-Deployment Checklist

Following a successful deployment script execution, verify the following:

- [ ] **Application health**: Run `php bin/console cockpit:health`. Expected: `HEALTHY`.
- [ ] **Database health**: Confirmed via the command above.
- [ ] **Worker backlog**: Run `php bin/console messenger:consume doctrine --time-limit=10`. Expected: Processes pending items without exceptions.
- [ ] **PLC endpoint**: Send a test POST to `http://localhost/IAC/cockpitConsole/plcdata.php`. Verify it returns `HTTP 201`.
- [ ] **eSSL ingestion**: Verify a test fingerprint swipe registers in the database.
- [ ] **Scanner1 ingestion**: Verify a test Trolley scan increments Production.
- [ ] **Scanner2 ingestion**: Verify a test Dispatch scan increments Dispatch.
- [ ] **Fingerprint unlock**: Verify a real fingerprint unlocks the dashboard UI.
- [ ] **Dashboard**: Verify the layout renders correctly with the industrial theme.
- [ ] **FIFO**: Verify queue calculations are deterministic.
- [ ] **Production calculation**: `Balance = Requested - Produced`.
- [ ] **Dispatch calculation**: `Available = Produced - Dispatched`.
- [ ] **Health warnings**: Verify no CRITICAL banners are visible on the dashboard.
- [ ] **Audit/logging**: Verify `var/log/prod.log` has no stack traces or crash reports.
- [ ] **Backup status**: Ensure the `.sql` backup file created earlier is secured.
