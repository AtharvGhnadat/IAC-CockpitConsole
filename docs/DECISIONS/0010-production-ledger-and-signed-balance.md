# Architecture Decision Record (ADR)

## 0010-production-ledger-and-signed-balance

**Status**: Accepted

**Context**: 
We need to process incoming scanner1 trolley events to increment the completed production quantity of cockpits. We must also maintain an accurate ongoing balance of demand vs completed production, and handle situations where production may over-fulfill current demand.

**Decision**: 
1. Valid scanner1 events (`quantity = 10` by business rule) create immutable `production_events` ledger records.
2. The `cockpit_state` table is extended to track `total_produced` cumulatively.
3. The `current_balance` calculation rule is fixed to: `total_requested - total_produced`.
4. The `current_balance` is a signed BIGINT. A negative value signifies extra produced stock beyond requested demand. This is intentional and avoids complex "buffer" tables.
5. All updates to `cockpit_state` during production processing use the same pessimistic write lock (`LockMode::PESSIMISTIC_WRITE`) implemented in Phase 5 to prevent concurrency race conditions between simultaneous PLC requests and scanner scans.

**Consequences**:
- We have a robust, single formula for tracking surplus and shortages.
- All completed production is safely recorded and auditable.
- Extends the core Phase 5 architecture natively.
