# Logging and Audit

The CockpitConsole implements distinct separation of concerns regarding logging and auditing.

## 1. device_events
The raw source-of-truth device journal. This is **not** a log file; it is a critical database table containing unaltered incoming JSON payloads, timestamps, and origin metadata.

## 2. audit_events
The application/business audit trail. This tracks actions like `SESSION_STARTED` or `MANUAL_CORRECTION` with actor and entity associations.

## 3. processing_failures
Persistent business/event processing failures. Detailed failures relating to the handling of a specific `device_event` are persisted here for operator/developer inspection.

## 4. Monolog (Application Logs)
Technical/application logs located in `var/log/`. Used for stack traces, system-level errors, HTTP request logs, and infrastructure health metrics.
