
## PLC Request Processor Tests (Phase 5)
- Located in \	ests/Integration/Processing/PlcRequestProcessorTest.php\.
- **Idempotency**: Verifies that duplicate processing of a single PLC event only increments \	otal_requested\ exactly once.
- **Unknown Cockpit**: Verifies that an unmapped cockpit code logs a failure instead of auto-creating master data.
- **Manual Verification**: Check API.
