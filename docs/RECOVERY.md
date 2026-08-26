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

## PLC State Recovery
If the PLC business processor crashes before completing the transaction, the raw \device_events\ record safely remains unprocessed.
- **Command**: Run \php bin/console cockpit:process-pending-plc\ to identify any stranded \eceived\ or \ailed\ events and process them safely.
- **Idempotency**: Because \equest_events\ guarantees uniqueness by \device_event_id\, running this command repeatedly is completely safe and will not inflate the cockpit balance.

## Production State Recovery
If the production processor crashes before completing the transaction, the raw \device_events\ record safely remains unprocessed.
- **Command**: Run \php bin/console cockpit:process-pending-production\ to identify any stranded \eceived\ or \ailed\ events and process them safely.
- **Verification Command**: Run \php bin/console cockpit:verify-production-state\ to mathematically reconcile the snapshot with the ledgers.

## Phase 8: Dispatch Recovery
- \cockpit:process-pending-dispatch\: Retries stranded scanner2 events. Useful if an event failed due to \INSUFFICIENT_AVAILABLE_STOCK\ and stock has since arrived.
- \cockpit:verify-inventory-state\: Reconciles actual ledgers (production and dispatch) against the fast-read \cockpit_state\ snapshot to ensure mathematical integrity.
