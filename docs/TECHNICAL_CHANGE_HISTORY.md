# Technical Change History

| Date | Version | Change | Reason | Affected Components | Database Impact | Device Impact | Backward Compatibility | Security Impact | Recovery Impact | Testing Performed | Files/Modules Changed |
|---|---|---|---|---|---|---|---|---|---|---|---|
| 2026-08-26 | 0.1.0 | Initial project foundation | Setup CockpitConsole | All | None | None | N/A | Setup baseline | N/A | Smoke test | Foundation files |
| 2026-08-26 | 0.2.0 | Added durable database event foundation | Phase 2 implementation | DB, Entities, Infrastructure | Major: added devices, cockpits, mappings, device_events, failures, audit tables | Enables durable integration | Yes | Preserves raw events | Raw DB journaling enabled | Persistence unit tests written | `src/Entity/*`, `src/Repository/*`, `src/Infrastructure/*`, `migrations/*` |
