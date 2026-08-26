# ADR 0013: Operational Health and Stale Data Detection

## Status
Accepted

## Context
CockpitConsole acts as the central production brain for the IAC factory floor. It consumes events from physical hardware (PLCs, Scanners, Fingerprint Readers). If physical hardware stops sending data, the web dashboard could silently continue to display the last known state, creating an illusion that production is healthy and no new units are being made.

This violates the core reliability principle: CockpitConsole must never silently appear healthy while device data has stopped. 

## Decision
CockpitConsole explicitly monitors:
1. **Device Activity**: We track `last_seen_at`, `last_valid_event_at`, and `last_processed_at` for every known device. We distinguish between "no recent events" (DELAYED) and "no communication" (OFFLINE).
2. **Database Availability**: A lightweight connection check ensures we can persist data.
3. **Event-Processing Lag**: We monitor the `device_events` table for unresolved backlog (events in "received" state) and age of the oldest event.
4. **Failed Events**: We track `processing_failures` and device-specific `consecutive_failures` counters.

## Consequences
- Operators are clearly warned when dashboard data may not be trustworthy (e.g. "PLC DATA DELAYED").
- The system uses configurable thresholds (`DEVICE_HEALTH_DELAY_SECONDS`, etc.) since different devices have different event frequencies.
- The `DeviceHealth` table was introduced to durably track these signals without scanning millions of rows of historical events on every health poll.
- The `/health` endpoint is restricted to minimal public info, while `/api/health/summary` and `/api/health/details` provide deep authenticated insights.
