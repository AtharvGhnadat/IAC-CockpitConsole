# FIFO Order

The deterministic sequence for processing First-In-First-Out events in CockpitConsole relies on three data points in descending priority:

1. **Device event timestamp**: The time reported by the device (`device_timestamp`).
2. **Server received_at**: The time the CockpitConsole recorded the event, preserving microsecond precision (`received_at`).
3. **Monotonic device_events.id**: The strictly incrementing auto-increment primary key ID (`id`).

If device timestamp and receive timestamp are completely identical, the database primary key guarantees the tie-break logic: `event 10542` wins over `event 10543`.

*FIFO selection logic is Pending implementation.*

## Phase 6 Note
- **Implementation**: Pending Phase 7.
- **Dependency**: FIFO will use the transition into positive production requirement (balance > 0) and the oldest unresolved shortage timestamp from the ledger.
