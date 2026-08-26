# Device Integration

CockpitConsole receives critical production events from 4 known sources on the local LAN.

## 1. eSSL Fingerprint
- **Source type**: `essl`
- **Future purpose**: Authentication/session start.
- **Current implementation status**: Persistent journal only. Endpoint/business processing: Pending implementation.
- **Timestamp field**: `punch_time`
- **JSON example**:
```json
{
    "machine_ip": "192.168.1.205",
    "user_name": "Sachinadmin",
    "privilege": "User",
    "punch_time": "2026-08-25 18:25:22"
}
```

## 2. Scanner 1 - Production Trolley
- **Source type**: `scanner1`
- **Future purpose**: Completed production trolley.
- **Current implementation status**: Persistent journal only. Endpoint/business processing: Pending implementation.
- **Identifier field**: `model`
- **Quantity field**: `quantity`
- **JSON example**:
```json
{
    "scanner": "scanner1",
    "model": "AX7 H - 2301FW608171N",
    "quantity": "10",
    "scandatetime": "2026-08-25 18:25:22"
}
```

## 3. PLC
- **Source type**: `plc`
- **Future purpose**: Cockpit production request.
- **Current implementation status**: Persistent journal only. Endpoint/business processing: Pending implementation.
- **Identifier field**: `cockpit`
- **JSON example**:
```json
{
    "cockpit": "2301AZ106071N",
    "dateTime": "2026-08-25 18:25:22"
}
```

## 4. Scanner 2 - Dispatch
- **Source type**: `scanner2`
- **Future purpose**: Dispatch event.
- **Current implementation status**: Persistent journal only. Endpoint/business processing: Pending implementation.
- **Identifier field**: `model`
- **Quantity field**: `quantity`
- **JSON example**:
```json
{
    "scanner": "scanner2",
    "model": "AX7 H - 2301FW608171N",
    "quantity": "10",
    "scandatetime": "2026-08-25 18:25:22"
}
```
