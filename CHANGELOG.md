# Changelog

All notable changes to CockpitConsole will be documented here.

## [Unreleased]

### Added

### Changed

### Fixed

### Security

### Deprecated

### Removed

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
