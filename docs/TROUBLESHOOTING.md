
## PLC Events are arriving but balance is not updating
- Check if the cockpit is mapped in \cockpits\. The processor strictly ignores unknown cockpits.
- Run \php bin/console cockpit:process-pending-plc\ manually to see if there are stranded events in \device_events\ that failed to process. The command will output exact failure reasons (e.g. \UNKNOWN_COCKPIT\).
