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

## Phase 6: Production Processing Audit
- \production_events\: Business history mapping one scanner1 \device_event\ to one cockpit production batch (+10).
- \udit_events\: Stores \TROLLEY_PRODUCTION_ACCEPTED\ or \TROLLEY_PRODUCTION_REJECTED\ with current mathematical context.

## Phase 7: FIFO Queue Audit
- \FIFO_ENTERED\: Shortage began.
- \FIFO_RESOLVED\: Shortage satisfied.
- \PRODUCTION_STARTED\: Cockpit selected from queue.

## Phase 8: Dispatch Audit
- \DISPATCH_ACCEPTED\: Goods successfully left inventory.
- \DISPATCH_REJECTED\: Business validation failed (e.g. \INSUFFICIENT_AVAILABLE_STOCK\, \INVALID_DISPATCH_QUANTITY\).
- \DISPATCH_PROCESSING_FAILED\: Technical error.
- \INVENTORY_STATE_UPDATED\: Manual reconciliation adjustments (if applicable).
