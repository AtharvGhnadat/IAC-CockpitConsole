# Changelog

All notable changes to CockpitConsole will be documented here.

## [Unreleased]

### Added

### Changed

### Fixed

### Security

### Deprecated

### Removed

## [0.5.0] - 2026-08-26

### Added
- `PlcRequestProcessor` to convert raw PLC HTTP events into business-level request ledgers.
- `RequestEvent` immutable ledger table enforcing 1-to-1 processing idempotency.
- `CockpitState` table to maintain the fast read-optimized snapshot of `total_requested` and `current_balance`.
- Pessimistic write locking on `cockpit_state` to prevent race conditions during concurrent PLC events.
- `cockpit:process-pending-plc` recovery command to safely reprocess stranded raw events without duplicates.
- `GET /api/cockpits/{cockpitCode}/state` endpoint to read the current mathematical snapshot.
- Synchronous hook in `DeviceIngestionService` for real-time PLC processing without background queue dependencies.

## [0.4.0] - 2026-08-26

### Added
- User master entity and role mappings.
- Fingerprint mapping tables to map eSSL devices to application users.
- Terminal master entity to map eSSL device IP to a CockpitConsole terminal.
- Strict 1-hour durable terminal sessions driven by server-time.
- Fingerprint event processor to intercept durable `essl` events and create sessions.
- Industrial lock screen (`/lock`) that auto-unlocks via lightweight `/session/status` polling.
- `TerminalAuthorizationListener` to protect dashboard routes using `APP_TERMINAL_ID` without trusting browser cookies.
- Session replacement logic and manual dashboard lock endpoint.
- Authentication audit trail for login approvals and rejections.

## [0.3.0] - 2026-08-26

### Added
- Common HTTP device ingestion architecture (`DeviceIngestionService`).
- Specific validators for `essl`, `plc`, `scanner1`, and `scanner2` payload schemas.
- `DeviceReceiverController` for LAN device ingestion endpoints.
- `plcdata.php` legacy adapter for exact compatibility.
- Monolog configuration for a dedicated `device_ingestion` channel.
- Safe malformed request diagnostic recording.
- Event storage integration with strict `received_at` timestamps and original raw payload preservation.
- Extensive test suites (`DeviceIngestionServiceTest`, `DeviceReceiverTest`).

## [0.2.0] - 2026-08-26

### Added
- Device master registry and Cockpit master structure.
- Cockpit to scanner model mapping structure.
- Durable raw device event journal table.
- Event processing failure foundation.
- Application-level audit foundation.
- Doctrine migrations and entities.
- Basic persistence and determinism tests.

## [0.1.0] - 2026-08-26

### Added
- Initial CockpitConsole production-ready project foundation.
