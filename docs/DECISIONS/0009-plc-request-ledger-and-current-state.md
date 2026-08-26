# Architecture Decision Record (ADR)

## 0009-plc-request-ledger-and-current-state

**Status**: Accepted

**Context**: 
We need to process incoming PLC events to increment the requested quantity of production cockpits. We must ensure that we never duplicate requests (idempotency) even if workers crash, and we must safely handle concurrent requests to the same cockpit without lost updates.

**Decision**: 
Raw PLC events (`device_events`) are converted into immutable request ledger events (`request_events`), while the current cockpit state (`cockpit_state`) is maintained separately for fast operational reads.
1. The `request_events` table enforces a strict uniqueness constraint on `device_event_id` to guarantee idempotency. If a worker crashes and retries processing the same device event, the uniqueness constraint or application logic will block a duplicate request from being created.
2. Updates to `cockpit_state` are wrapped in a database transaction with a pessimistic write lock (`LockMode::PESSIMISTIC_WRITE`) to ensure concurrent requests for the same cockpit are queued at the database level and process the math sequentially.

**Consequences**:
- `device_events`, `request_events`, and `cockpit_state` separate concerns: raw ingestion, business history, and current snapshot respectively.
- Future recovery processes can rebuild the `total_requested` field of `cockpit_state` by simply summing the `request_events`.
- Processing relies strictly on database-level constraints for safety, not just application-level code.
