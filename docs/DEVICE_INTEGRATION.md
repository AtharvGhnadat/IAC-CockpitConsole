# Device Integration

CockpitConsole receives critical production events from 4 known sources on the local LAN.
All HTTP endpoints expect `POST` requests with `Content-Type: application/json`.

## 1. eSSL Fingerprint
- **Device Endpoint**: `POST /api/device/essl`
- **Source type**: `essl`
- **Required fields**: `machine_ip`, `user_name`, `privilege`, `punch_time`
- **Success Response**: `{"success":true,"event_id":10542,"event_uuid":"..."}`

## 2. PLC
- **Legacy Compatibility Endpoint**: `POST http://localhost/IAC/cockpitConsole/plcdata.php` (or `/api/device/plc`)
- **Source type**: `plc`
- **Required fields**: `cockpit`, `dateTime`
- **Success Response**: `{"success":true,"event_id":10542,"event_uuid":"..."}`

## 3. Scanner 1 - Production Trolley
- **Device Endpoint**: `POST /api/device/scanner1`
- **Source type**: `scanner1`
- **Required fields**: `scanner`, `model`, `quantity`, `scandatetime`
- **Success Response**: `{"success":true,"event_id":10542,"event_uuid":"..."}`

## 4. Scanner 2 - Dispatch
- **Device Endpoint**: `POST /api/device/scanner2`
- **Source type**: `scanner2`
- **Required fields**: `scanner`, `model`, `quantity`, `scandatetime`
- **Success Response**: `{"success":true,"event_id":10542,"event_uuid":"..."}`

## Failure Responses
- `400 Bad Request`: Validation failure (e.g., missing field, invalid datetime format).
- `413 Payload Too Large`: Request body exceeds 16KB.
- `415 Unsupported Media Type`: Non-JSON content type.
- `500 Internal Server Error`: Persistence failure.

In case of validation failures, the detailed reason is logged in `var/log/device_ingestion_prod.log`.

## PLC HTTP Endpoint (Phase 5 Business Logic)
- **Endpoint**: \http://localhost/IAC/cockpitConsole/plcdata.php\ (POST)
- **Business Interpretation**: One valid event mathematically equates to exactly **+1 request** for the specified cockpit. The processor does not infer batching from the timestamp or other elements. Duplicate ingestion IDs are safely ignored to prevent ledger duplication.
