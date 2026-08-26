# Architecture Decision Record (ADR)

## 0011-deterministic-fifo-production-queue

**Status**: Accepted

**Context**: 
We need to track and prioritize production for multiple cockpits when PLC demand exceeds production capacity. A FIFO rule is mandated by the business: the oldest unresolved positive shortage dictates which cockpit should be selected for production next. Tie-breaking must be deterministic.

**Decision**: 
1. The `production_queue` table acts as a lifecycle tracker for cockpit shortages.
2. An entry is created strictly when a cockpit's balance transitions from `<= 0` to `> 0`.
3. Subsequent requests for an already positive balance do NOT reset the entry's age, honoring the initial shortage time.
4. The entry is resolved when the balance transitions to `<= 0`.
5. Deterministic tie-breaking is enforced by sorting pending queues by `pending_device_timestamp ASC`, `pending_received_at ASC`, and `pending_event_id ASC`.
6. Only ONE cockpit is allowed to be `in_production` at any time, secured via `LockMode::PESSIMISTIC_WRITE`.

**Consequences**:
- Cockpits correctly wait in line based on when they *first* needed production.
- Extremely large batch demands do not circumvent the queue to bully smaller waiting requests.
- All selection queries are lightning fast and explicitly deterministic, avoiding race conditions.
