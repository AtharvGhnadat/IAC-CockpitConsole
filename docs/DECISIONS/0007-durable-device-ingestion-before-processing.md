# Architecture Decision Record (ADR)

## 0007-durable-device-ingestion-before-processing

**Status**: Accepted

**Context**: 
The system ingests industrial device events over HTTP. We need to decide how and when to acknowledge these HTTP requests, especially concerning the processing of complex business logic (like batch-of-10 production scaling).

**Decision**: 
HTTP receivers will acknowledge the request (return 201 Created) **only after** the raw event is durably committed to the `device_events` table. The receiver will **not** attempt to process the business logic (e.g. updating dashboard states) synchronously during the ingestion request.

**Consequences**:
- The ingestion endpoints remain lightning fast and deterministic.
- An HTTP 201 response strictly guarantees the data is stored on disk.
- Errors in dashboard business logic cannot cause the device to think a transmission failed.
- We must implement a secondary background mechanism (or queue) in future phases to process the persisted `device_events` into business state.
