# Acceptance Testing Scenarios

This document outlines the testing scenarios that must be successfully executed during the final Customer Acceptance Testing phase.

## 1. Authentication
- [ ] **Valid Fingerprint Login**: Verify that a known user can scan and unlock the dashboard.
- [ ] **Invalid Fingerprint**: Verify an unknown scan is rejected and logged.
- [ ] **Session Expiry**: Wait 1 hour (or simulate) and verify the dashboard auto-locks while processing continues.
- [ ] **Operator Handoff**: Scan a new user during an active session and verify the session switches to the new user.

## 2. PLC Integration
- [ ] **Ingest Valid PLC Data**: Verify a standard production request updates the `Total Requested` and increases the queue.
- [ ] **Burst Test**: Fire 50 PLC requests in rapid succession; verify all 50 are recorded correctly without locking failures.
- [ ] **Missing Fields**: Send malformed JSON; verify a safe error and no crash.

## 3. Production (Scanner 1)
- [ ] **Trolley Scan**: Scan a trolley; verify `Total Produced` increments by 10 (batch size).
- [ ] **Queue Resolution**: Ensure that producing the item resolves the pending FIFO shortage in the queue.
- [ ] **Negative Balance Validation**: Produce more than requested; verify balance goes negative and dashboard turns orange for that metric.

## 4. Dispatch (Scanner 2)
- [ ] **Valid Dispatch**: Scan out a trolley; verify `Total Dispatched` increases and `Available Stock` decreases.
- [ ] **Insufficient Stock Rejection**: Attempt to dispatch when `Available Stock` is 0; verify the system rejects it safely.

## 5. Dashboard & UI
- [ ] **Auto-Refresh**: Perform backend actions and verify the dashboard updates within 1 second without hitting F5.
- [ ] **Layout Responsiveness**: Resize the browser window and ensure columns wrap elegantly.
- [ ] **Network Disconnect**: Disconnect the LAN cable briefly; verify the "Connection Lost" warning appears without destroying the screen layout.

## 6. Health & Recovery
- [ ] **PLC Delay Warning**: Power off the PLC simulator; wait 2 minutes and verify the yellow "DATA DELAYED" banner appears.
- [ ] **Database Offline Warning**: Stop the MariaDB service; verify the red "CRITICAL" banner appears.
- [ ] **Recovery Execution**: Run `php bin/console cockpit:reconcile` and verify no mismatch is reported under normal circumstances.
