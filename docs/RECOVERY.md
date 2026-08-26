# Recovery Procedures

CockpitConsole is an industrial system where downtime and data loss are unacceptable.
**Dashboard failure must not destroy historical device events.**

## Recovery Hierarchy

If the system state needs to be restored, we follow this hierarchy of truth:
1. **Database current state** - The most immediate source of processed business state.
2. **device_events raw event journal** - Authoritative history. If current state is corrupted, it can theoretically be rebuilt from this append-only journal.
3. **audit_events** - For reviewing manual corrections and session states.
4. **MariaDB backup** - Point-in-time recovery.
5. **MariaDB binary logs** - For recovery between backup intervals (if configured).
6. **Application log files** - For diagnostics of crashes.

**Note**: Full state replay from the `device_events` journal is **Pending implementation**.
