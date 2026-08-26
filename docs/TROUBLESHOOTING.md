
## PLC Events are arriving but balance is not updating
- Check if the cockpit is mapped in \cockpits\. The processor strictly ignores unknown cockpits.
- Run \php bin/console cockpit:process-pending-plc\ manually to see if there are stranded events in \device_events\ that failed to process. The command will output exact failure reasons (e.g. \UNKNOWN_COCKPIT\).

## Scanner1 Trolley scanned but production did not increase
- Check if the scanner model is mapped in \cockpit_model_mappings\ and is active. The processor strictly ignores unknown models.
- Check if the scanner quantity is exactly 10. The system rejects any other number.
- Run \php bin/console cockpit:process-pending-production\ manually to see if there are stranded events in \device_events\ that failed to process. The command will output exact failure reasons (e.g. \UNKNOWN_MODEL\).
- Run \php bin/console cockpit:verify-production-state\ to ensure the database mathematical snapshot is synced with the production ledger.

## FIFO Queue Issues
- **Missing Cockpit from Queue**: Run \cockpit:verify-fifo\ to check if a cockpit has a positive balance but no queue entry. If so, manual intervention is needed to reconstruct its queue entry using the oldest PLC request.
- **Two active cockpits**: The system uses \LockMode::PESSIMISTIC_WRITE\. If two cockpits are ever in production, check MariaDB transaction logs. Run \cockpit:verify-fifo\ to identify them.
