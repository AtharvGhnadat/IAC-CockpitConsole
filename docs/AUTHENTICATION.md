# Terminal Based Fingerprint Authentication

## Overview
CockpitConsole uses industrial fingerprint readers (eSSL devices) to authenticate operators. However, the authentication mechanism differs from standard web applications. Instead of relying solely on browser cookies, CockpitConsole maps fingerprint events to **physical terminals**.

## Process Flow
1. **Fingerprint Scanned**: The operator scans their finger on an eSSL device.
2. **Device Event Logged**: The eSSL device sends an HTTP payload to CockpitConsole, which is instantly and durably logged in `device_events`.
3. **Event Processed**: The `FingerprintEventProcessor` intercepts the `essl` event.
4. **Mapping & Validation**:
   - Matches the eSSL `machine_ip` to a registered `Terminal`.
   - Matches the eSSL `user_name` + `machine_ip` to a CockpitConsole `User`.
   - Checks if the user is active.
5. **Session Created**: A 1-hour `TerminalSession` is created for that specific Terminal.
6. **Auto Unlock**: The browser running on the terminal (identified via `APP_TERMINAL_ID` in `.env.local`) constantly polls `/session/status`. Upon detecting the new session, it immediately redirects to the `/dashboard`.

## Session Duration
- Sessions are strictly valid for **1 hour**.
- Expiration is determined by **server time** (`expires_at`), not the fingerprint device's potentially inaccurate clock.
- If the session expires, the browser will be redirected to `/lock`.

## Session Replacement & Re-scans
- **One session per terminal**: If User A is logged in and User B scans their fingerprint, User A's session is terminated (`replaced`) and User B immediately takes over the terminal.
- **Re-scans**: If User A scans again while already logged in, a *new* 1-hour session is created, replacing the old one.

## Biometric Policy
- CockpitConsole **DOES NOT** store any biometric templates, minutiae, or fingerprint images.
- eSSL devices handle the actual biometric matching and send only identity metadata (Username, IP, Timestamp) to CockpitConsole.
