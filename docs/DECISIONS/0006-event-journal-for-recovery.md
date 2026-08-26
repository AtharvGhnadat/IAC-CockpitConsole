# Architecture Decision Record: Event Journal for Recovery

**Status:** Accepted  
**Date:** 2026-08-26

## Context
Industrial systems require high determinism and auditability. If the system crashes, we must be able to recover the state exactly. Traditional CRUD overwrites history, which is unacceptable for production tracking.

## Decision
We will use an append-only event architecture for device messages and business history. 
Workflow:
1. Validate Device Event
2. Persist Raw Event
3. Commit
4. Process Business Logic
5. Update Current State
6. Mark Event Processed

## Consequences
- **Positive:** Current state is replaceable and reconstructible. Event history is authoritative. High auditability.
- **Negative:** Increased database storage requirements and slightly more complex business logic to handle event processing instead of direct state mutation.
