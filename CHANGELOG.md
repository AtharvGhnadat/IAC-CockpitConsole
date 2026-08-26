# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0-rc.1] - 2026-08-26
### Added
- Final production operational dashboard UI
- Dynamic responsive CSS Grid layout engine based on database config
- Dashboard Snapshot API for highly optimized live data polling
- Dark mode, high-contrast industrial metric panels
- Stale-data detection & visual system warning banners

## [0.9.0] - 2026-08-26
### Added
- System health monitoring (Device activity, processing backlogs, database availability)
- DeviceHealth entity and tracking in DeviceIngestionService
- Configurable thresholds for offline/delay tracking
- CLI watchdog `php bin/console cockpit:health`
- API endpoints `GET /health`, `GET /api/health/summary`, `GET /api/health/details`

## [0.8.0] - 2026-08-26

### Added
- `DispatchEvent` entity and `dispatch_events` table for durable scanner2 ledger.
- `total_dispatched` and `available_stock` to `CockpitState` table.
- `Scanner2DispatchProcessor` to handle business logic for finished goods dispatch.
- Strict inventory protection: Dispatches are rejected if `Available Stock < Batch Size`.
- Safe idempotency checking for scanner2 device events.
- Concurrency protection via row-level locking on `CockpitState`.
- `cockpit:process-pending-dispatch` command to safely retry stranded scanner2 events.
- `cockpit:verify-inventory-state` command to reconcile mathematical integrity.
- `GET /api/production/summary` for aggregate stats.
- `GET /api/dispatch/recent` for historical dispatch tracking.

## [0.7.0] - 2026-08-26

### Added
- `ProductionQueue` entity and `production_queue` table to track lifecycle of shortages.
- FIFO ordering engine based on oldest unresolved shortage (`FifoQueueService`).
- Strict deterministic tie-breaking (`device_timestamp` -> `received_at` -> `device_event_id`).
- Shortage transition detection in `PlcRequestProcessor` (enters queue when balance transitions to > 0).
- Shortage resolution detection in `Scanner1ProductionProcessor` (exits queue when balance transitions to <= 0).
- Next-cockpit lock-in via `POST /api/production/start-next` with pessimistic transaction concurrency protection.
- Read-only API `GET /api/production/queue` calculating dynamic waiting time.
- `cockpit:verify-fifo` command to audit queue health against cockpit math.

## [0.6.0] - 2026-08-26

### Added
- `Scanner1ProductionProcessor` to convert raw scanner1 HTTP events into immutable `ProductionEvent` ledgers.
- `total_produced` tracking in `cockpit_state` with signed `current_balance` supporting mathematical surplus (negative balance).
- Fixed production batch size validation (default `10` via `.env`).
- Model-to-cockpit resolution leveraging `cockpit_model_mappings`.
- Pessimistic write locking on `cockpit_state` to safely process concurrent PLC requests and scanner scans.
- Strict 1-to-1 idempotency enforcement via `device_event_id` unique constraints.
- `cockpit:process-pending-production` recovery command to safely process stranded raw events.
- `cockpit:verify-production-state` command to mathematically reconcile state snapshot against request and production ledgers.
- Updated read-only API endpoint to return `total_produced`.

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
