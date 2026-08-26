# Recovery Commands

This document catalogs the administrative commands available for recovering and verifying the state of CockpitConsole.

## 1. Health Watchdog (`cockpit:health`)

**Purpose**: Verifies the current active state of the factory floor connections.
**Safe to run while active?**: YES.
**Mutating?**: NO (Read-only).
**Expected Output**: A color-coded terminal table showing DB connection, processing backlog, and device status.
**When to use**: When the dashboard shows a WARNING or CRITICAL banner, or to quickly verify if the PLCs are talking to the server without opening a browser.

## 2. Replay Failed Events (`cockpit:replay-failed-events`) *(Hypothetical/Planned)*

**Purpose**: Attempts to re-process raw `device_events` that are currently marked as `failed`.
**Safe to run while active?**: YES. Doctrine pessimistic locking ensures no race conditions.
**Mutating?**: YES.
**Requires `--dry-run`?**: No, but recommended to inspect logs first.
**Expected Output**: "Processed X events. Y succeeded, Z failed."
**When to use**: If an unknown cockpit caused a batch of failures, and the master data for that cockpit was subsequently added to the database.

## 3. Reconcile Ledgers (`cockpit:reconcile`) *(Hypothetical/Planned)*

**Purpose**: Recalculates `cockpit_state` based purely on the historical `device_events` table to detect any mathematical mismatch.
**Safe to run while active?**: YES.
**Mutating?**: NO. (Unless run with `--fix` flag in the future).
**Expected Output**: "All balances match" OR "Mismatch found in Cockpit AX7 H (Expected 10, Actual 8)".
**When to use**: During weekly maintenance, or if an operator reports that available stock numbers look suspicious.

*Note: Commands marked (Hypothetical/Planned) represent procedures that should be implemented before or during Phase 12 Go-Live.*
