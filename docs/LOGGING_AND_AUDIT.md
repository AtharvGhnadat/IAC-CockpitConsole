# Logging and Audit

## Monolog Channels
- `device_ingestion`: A dedicated channel specifically for logging the activity of the `DeviceIngestionService`. It records successful ingestions, structural validation failures, malformed JSON bodies, and persistence errors.

## Rejected Requests / Malformed JSON
Malformed JSON and structurally invalid requests are **not** persisted to the `device_events` database table. Instead, the raw payload and diagnostic evidence are logged to the `device_ingestion` Monolog channel to preserve evidence without bloating or breaking the database schema.
