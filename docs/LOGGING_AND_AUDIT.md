# Logging and Audit

## Monolog Channels
- `device_ingestion`: A dedicated channel specifically for logging the activity of the `DeviceIngestionService`. It records successful ingestions, structural validation failures, malformed JSON bodies, and persistence errors.

## Rejected Requests / Malformed JSON
Malformed JSON and structurally invalid requests are **not** persisted to the `device_events` database table. Instead, the raw payload and diagnostic evidence are logged to the `device_ingestion` Monolog channel to preserve evidence without bloating or breaking the database schema.

## Phase 5: PLC Processing Audit
- \device_events\: Pure raw incoming string exactly as received from PLC.
- \equest_events\: Business history mapping one \device_event\ to one cockpit request (+1).
- \udit_events\: Stores logical outcomes like \PLC_REQUEST_ACCEPTED\ or \PLC_REQUEST_REJECTED\ with current balance context.
- \Monolog\: Logs technical details of the transaction, such as skipped duplicate events or transaction rollbacks.
