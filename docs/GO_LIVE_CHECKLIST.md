# Go-Live Checklist

This checklist must be fully verified by the Technical Lead before declaring the system `READY FOR PRODUCTION`.

- [ ] Phase 11 validation passed
- [ ] No BLOCKER defects
- [ ] No unresolved CRITICAL defects
- [ ] Production version identified (`v1.0.0`)
- [ ] Git commit identified
- [ ] Production environment configured (`.env.local`)
- [ ] `APP_DEBUG=0` disabled
- [ ] Apache hardened (Directories blocked)
- [ ] MariaDB configured (InnoDB, strict mode)
- [ ] Database user configured (`cockpit_app` with restricted grants)
- [ ] Backup successful (`scripts\backup_database.ps1` runs)
- [ ] Restore test successful (Drill completed on test DB)
- [ ] Worker auto-start verified (Task Scheduler configured)
- [ ] Worker restart verified (Time-limit triggers respawn)
- [ ] Windows reboot recovery verified (Apache, MySQL, Worker start on boot)
- [ ] Log rotation verified (Monolog configured in `config/packages/prod/monolog.yaml`)
- [ ] Disk-space monitoring verified
- [ ] PLC endpoint verified (`/plcdata.php`)
- [ ] eSSL verified (TCP/Fingerprint sync)
- [ ] Scanner1 verified (Trolley Production)
- [ ] Scanner2 verified (Dispatch)
- [ ] Fingerprint session verified (Dashboard unlocks)
- [ ] `+1 Request -> -9 Balance` production example verified
- [ ] Dispatch verified
- [ ] FIFO verified
- [ ] Health monitoring verified
- [ ] Dashboard layout verified
- [ ] Ledger Reconciliation clean (`Requested - Produced = Balance`)
- [ ] Rollback procedure documented (`ROLLBACK.md`)
- [ ] Incident response documented (`INCIDENT_RESPONSE.md`)
- [ ] Technical handover ready
