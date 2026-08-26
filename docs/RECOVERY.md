# Recovery Procedures

This document outlines the recovery procedures verified during Phase 11 Industrial Validation.

## 1. Browser Failure / Refresh
**Scenario**: The operator accidentally closes the dashboard tab or hits F5.
**Recovery**: 
1. Re-open the browser to the CockpitConsole URL.
2. If the session has not expired, the dashboard will immediately reload the latest state.
3. If the session expired, re-scan the fingerprint.
**Note**: The backend completely detaches from the frontend. Closing the browser will *never* interrupt a PLC or Scanner ingestion process.

## 2. Apache Failure
**Scenario**: The Apache web server crashes or is restarted (`httpd.exe`).
**Recovery**:
1. Restart Apache via XAMPP Control Panel.
2. No manual data intervention is required. `device_events` that were mid-flight *before* hitting Apache will be retransmitted by the physical devices per standard TCP/HTTP retry policies.

## 3. MariaDB Failure
**Scenario**: The database crashes.
**Recovery**:
1. The dashboard will instantly turn red with a `SYSTEM CRITICAL` warning.
2. Restart MariaDB via XAMPP Control Panel.
3. Once the database is online, the `DashboardSnapshotService` will automatically recover, and the red banner will disappear. 

## 4. Pending or Failed Events
**Scenario**: An event is stuck as `failed` due to bad master data (e.g. unknown cockpit).
**Recovery**:
1. Correct the master data (e.g. add the cockpit).
2. Run `php bin/console cockpit:replay-failed-events` (Planned feature).
3. The event will re-process safely.
