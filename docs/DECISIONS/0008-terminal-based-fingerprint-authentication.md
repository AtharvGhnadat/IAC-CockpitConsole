# Architecture Decision Record (ADR)

## 0008-terminal-based-fingerprint-authentication

**Status**: Accepted

**Context**: 
Industrial dashboard authentication needs to be seamless for operators using fingerprint readers. Standard web session cookies alone are insufficient because the fingerprint device is physically separate from the browser, and the browser cannot "receive" the fingerprint event directly.

**Decision**: 
eSSL fingerprint events authenticate a configured CockpitConsole terminal using durable server-side terminal sessions (`terminal_sessions`) rather than relying only on browser/PHP sessions. The browser identifies its physical location via an environment variable (`APP_TERMINAL_ID`).

**Consequences**:
- The database (`terminal_sessions`) is the authoritative source of truth for authentication, not the browser cookie.
- If a browser crashes and restarts, it will instantly regain authenticated access if the server-side `terminal_session` hasn't expired.
- The browser must poll a status endpoint (`/session/status`) while locked to know when a valid fingerprint has unlocked its terminal.
- Strict mapping configuration is required (eSSL IP -> Terminal -> Browser Env).
