# Industrial Validation Matrix

This matrix documents the critical production scenarios tested during Phase 11.

| Test ID | Scenario | Precondition | Input | Expected Result | Actual Result | Pass/Fail | Recovery Required | Notes |
|---------|----------|--------------|-------|-----------------|---------------|-----------|-------------------|-------|
| VAL-001 | Baseline End-to-End Flow | Clean DB | PLC Request -> Scanner1 -> Scanner2 | State transitions through pending, production, dispatch; Dashboard updates. | As expected. | PASS | None | Validated via `PlcIngestionTest` & `FifoResolutionTest` |
| VAL-002 | Request 64 | Clean DB | 64 PLC -> 7 Scanner1 -> 2 Scanner2 | Req=64, Prod=70, Disp=20. Bal=-6, Avail=50 | As expected. | PASS | None | Math invariants upheld transactionally. |
| VAL-003 | Browser Crash Test | Active Dashboard | Close browser during event stream | Backend continues; no event lost. Dashboard reloads cleanly. | As expected. | PASS | None | Backend completely decoupled from frontend polling. |
| VAL-004 | Browser Refresh Test | Active Dashboard | F5 Refresh during operations | No duplicate actions; session maintained. | As expected. | PASS | None | Read-only polling `GET /api/dashboard/snapshot`. |
| VAL-005 | Apache Restart Test | Active System | Restart Apache (`httpd`) | System reconnects; no duplicate business events. | As expected. | PASS | None | REST APIs are stateless per-request. |
| VAL-006 | MariaDB Restart Test | Active System | Restart MariaDB service | DB offline = CRITICAL health. Recovery = System resumes seamlessly. | As expected. | PASS | None | Doctrine `ConnectionException` caught by health monitor. |
| VAL-007 | Processor Crash | Pre-commit | Simulate Exception in Processor | Raw event survives; Business state rolls back. | As expected. | PASS | Retry Worker | Enforced by `EntityManager::transactional()`. |
| VAL-008 | Idempotency Test | Same `device_event_id` | Send same ID twice | Processed exactly once. | As expected. | PASS | None | Handled by `status` flag in `device_events`. |
| VAL-009 | Identical Payload Test | Same JSON, Different ID | Send same payload twice | Processed as two separate valid events. | As expected. | PASS | None | Supported intentionally for repetitive identical trolley scans. |
| VAL-010 | Simultaneous PLC Concurrency | Req=100 | 20 concurrent PLC threads | Req=120 exactly. No lost updates. | As expected. | PASS | None | Enforced by Doctrine Pessimistic Locking. |
| VAL-011 | Simultaneous Scanner1 Concurrency | Prod=100 | 10 concurrent Scanner1 threads | Prod=200 exactly. | As expected. | PASS | None | Enforced by Doctrine Pessimistic Locking. |
| VAL-012 | Limited Stock Dispatch | Avail=10 | 2 concurrent dispatch requests | 1 succeeds, 1 fails. Avail=0. | As expected. | PASS | None | Enforced by `Available >= 0` invariant check in Processor. |
| VAL-013 | FIFO Same-Timestamp Resolution | Two requests identical timestamp | Send two identical timestamps | Resolved deterministically via `device_event_id`. | As expected. | PASS | None | Query strictly orders by `created_at ASC, id ASC`. |
| VAL-014 | Dashboard Polling Failure | Network Drop | `fetch()` fails | Dashboard shows "Connection Lost" and retains old data. | As expected. | PASS | None | Handled in `dashboard_controller.js`. |
| VAL-015 | Unknown Cockpit Data | Invalid payload | Send `"cockpit": "UNKNOWN"` | Event logged as failed. No state mutated. | As expected. | PASS | Data Correction | Handled by `DeviceIngestionService`. |
| VAL-016 | Stale Device Data | PLC Delay | Silence PLC for 2 minutes | Dashboard shows "DATA DELAYED". | As expected. | PASS | PLC Reconnect | Handled by `SystemHealthService` and UI. |
