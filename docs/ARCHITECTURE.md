# Architecture

## Event Processing Pipeline

CockpitConsole decouples the ingestion of events from the processing of business logic.

```mermaid
flowchart TD
    LAN[LAN Device] --> HTTP[Future HTTP Receiver]
    HTTP --> REC[Raw Event Recorder]
    REC --> DB[(device_events)]
    DB --> PROC[Future Business Processor]
    PROC --> STATE[(Current State)]
```

**Key Principle**: `Event ingestion` != `Business processing`.
Events are safely committed to the database independently of dashboard state or batch logic.
