# Technical Change History

| Date | Version | Change | Reason | Affected Components | Database Impact | Device Impact | Backward Compatibility | Security Impact | Recovery Impact | Testing Performed | Files/Modules Changed |
|---|---|---|---|---|---|---|---|---|---|---|---|
| 2026-08-26 | 0.1.0 | Initial project foundation | Setup CockpitConsole | All | None | None | N/A | Setup baseline | N/A | Smoke test | Foundation files |
| 2026-08-26 | 0.2.0 | Added durable database event foundation | Phase 2 implementation | DB, Entities, Infrastructure | Major: added devices, cockpits, mappings, device_events, failures, audit tables | Enables durable integration | Yes | Preserves raw events | Raw DB journaling enabled | Persistence unit tests written | `src/Entity/*`, `src/Repository/*`, `src/Infrastructure/*`, `migrations/*` |
| 2026-08-26 | 0.3.0 | Added HTTP Device Receiver Architecture | Phase 3 implementation | Controllers, Services, Validators, Tests, plcdata.php | None (uses Phase 2 schema) | Exposes LAN ingestion endpoints | Compatible with legacy PLC path | Basic input bounds and validation limits added | No impact | Unit/Integration Tests | src/Application/*, src/Controller/*, tests/*, plcdata.php |
| 2026-08-26 | 0.4.0 | Terminal-based Authentication | Phase 4 implementation | User, Terminal, Mappings, TerminalSession | Table users, terminals, fingerprint_user_mappings, terminal_sessions | Secure locked dashboard with terminal tracking | Safe, no-session-cookie reliance, strictly 1-hour expiry | Prevents unauthorized dashboard access without blocking ingestion | None | Security tests, UI checks | src/Entity/*, src/Application/Security/* |
| 2026-08-26 | 0.5.0 | PLC Request Ledger | Phase 5 implementation | PlcRequestProcessor, RequestEvent, CockpitState | Table request_events, cockpit_state | Idempotent +1 PLC processing with pessimistic locking | Safe, concurrency-aware processing with distinct ledger and snapshot | Adds foundation for future scanner processing maths | None | Integration tests, idempotency checks | src/Entity/*, src/Application/Processing/* |
| 2026-08-26 | 0.6.0 | Trolley Production Engine | Phase 6 implementation | Scanner1ProductionProcessor, ProductionEvent | Table production_events, cockpit_state.total_produced | Fixed batch size of 10 configuration. Signed negative balance for current_balance. Idempotent processing. Row locking against race conditions. | None | Verification commands added | src/Entity/*, src/Application/Processing/*, src/Command/* |
| 2026-08-26 | 0.7.0 | FIFO Production Queue | Phase 7 implementation | ProductionQueue, FifoQueueService | Table production_queue | Deterministic ordering using timestamps and event ID. | None | Verify command added | src/Entity/*, src/Application/Service/*, src/Command/* |
| 2026-08-26 | 0.8.0 | Scanner 2 Dispatch | Phase 8 implementation | DispatchEvent, Scanner2DispatchProcessor | Table dispatch_events, cockpit_state.total_dispatched, cockpit_state.available_stock | Dispatch processing, Available stock tracking, Stock protection. | None | ProcessPendingDispatchCommand, VerifyInventoryStateCommand | src/Entity/*, src/Application/*, src/Command/* |

### Phase 9: System Health Monitoring
- **Date**: 2026-08-26
- **Version**: 0.9.0
- **Components**: SystemHealthService, DeviceHealth entity, HealthApiController, SystemHealthCommand
- **Schema**: Added \device_health\ table linking to \devices\.
- **Operational Impact**: Operators now see explicit warnings when device data is delayed or database goes offline.
- **Tests**: SystemHealthServiceTest verifies thresholds and warning/critical transitions.

### Phase 10: Final Production Dashboard
- **Date**: 2026-08-26
- **Version**: 1.0.0-rc.1
- **Components**: DashboardSnapshotService, DashboardApiController, DashboardRow, DashboardColumn
- **Schema**: Added \dashboard_rows\ and \dashboard_columns\ for dynamic layouts.
- **Operational Impact**: Operators now have a live, auto-refreshing industrial UI with robust layout logic.
- **Tests**: Validated snapshot endpoints and dashboard layout endpoints.
