
## PLC Request Processor Tests (Phase 5)
- Located in \	ests/Integration/Processing/PlcRequestProcessorTest.php\.
- **Idempotency**: Verifies that duplicate processing of a single PLC event only increments \	otal_requested\ exactly once.
- **Unknown Cockpit**: Verifies that an unmapped cockpit code logs a failure instead of auto-creating master data.
- **Manual Verification**: Check API.

## Production Processor Tests (Phase 6)
- Located in \	ests/Integration/Processing/Scanner1ProductionProcessorTest.php\.
- **Tests**: +1 -> -9 progression, 64 -> -6 progression, Invalid batch quantity rejection, Unknown model rejection, Idempotency, Concurrency, and Invariant Verification.

## FIFO Testing (Phase 7)
- \	ests/Integration/Queue/FifoQueueServiceTest.php\ ensures deterministic tie-breaking and concurrency lock rejection.
