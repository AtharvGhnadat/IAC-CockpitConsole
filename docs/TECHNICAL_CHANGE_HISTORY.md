# Technical Change History

| Date | Version | Change | Reason | Affected Components | Database Impact | Device Impact | Backward Compatibility | Security Impact | Recovery Impact | Testing Performed | Files/Modules Changed |
|---|---|---|---|---|---|---|---|---|---|---|---|
| 2026-08-26 | 0.1.0 | Initial project foundation | Setup CockpitConsole | All | None | None | N/A | Setup baseline | N/A | Smoke test | Foundation files |
| 2026-08-26 | 0.2.0 | Added durable database event foundation | Phase 2 implementation | DB, Entities, Infrastructure | Major: added devices, cockpits, mappings, device_events, failures, audit tables | Enables durable integration | Yes | Preserves raw events | Raw DB journaling enabled | Persistence unit tests written | `src/Entity/*`, `src/Repository/*`, `src/Infrastructure/*`, `migrations/*` |
| 2026-08-26 | 0.3.0 | Added HTTP Device Receiver Architecture | Phase 3 implementation | Controllers, Services, Validators, Tests, plcdata.php | None (uses Phase 2 schema) | Exposes LAN ingestion endpoints | Compatible with legacy PLC path | Basic input bounds and validation limits added | No impact | Unit/Integration Tests | src/Application/*, src/Controller/*, tests/*, plcdata.php |
