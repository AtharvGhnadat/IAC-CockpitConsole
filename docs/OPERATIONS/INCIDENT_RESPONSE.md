# Incident Response

This runbook dictates the immediate actions to take during a production failure of CockpitConsole.

## Golden Rules
1. **Preserve Data**: Never delete raw `device_events`.
2. **No Direct DB Edits**: Do not manually `UPDATE` balances via SQL.
3. **Verify Ingestion**: Differentiate between "Devices can't reach the server" and "The server isn't processing the queue".

---

## Scenario A: Dashboard says "CONNECTION LOST"
**Symptoms**: The top right of the UI is red, saying connection lost.
1. **Immediate Operator Action**: Press F5. If it persists, call IT.
2. **Technical Checks**: 
   - Is Apache running in XAMPP?
   - Can you reach the server IP from another PC on the LAN?
   - Is the Windows Firewall blocking port 80?
3. **What NOT to do**: Do not reboot the whole server immediately. Restart Apache first.

## Scenario B: Dashboard says "CRITICAL: DATABASE OFFLINE"
**Symptoms**: Huge red banner across the screen.
1. **Immediate Operator Action**: Halt manual scans if possible. (Note: PLCs will likely buffer or retry, but verify PLC specs).
2. **Technical Checks**: 
   - Is MariaDB running in XAMPP?
   - Did the disk run out of space?
3. **Recovery Procedure**: Start MariaDB. The system will auto-recover.

## Scenario C: Dashboard says "DATA DELAYED" or "PROCESSING HALTED"
**Symptoms**: Yellow banner. Production balances aren't updating, even though trolleys are passing.
1. **Immediate Operator Action**: Production can physically continue, but the UI is lagging.
2. **Technical Checks**: 
   - Check the worker backlog: `php bin/console cockpit:health`
   - Is the Messenger worker running in Task Scheduler?
   - Did it crash due to a fatal PHP error? Check `var/log/prod.log`.
3. **Recovery Procedure**: Relaunch the worker via Task Scheduler. The backlog will instantly process and catch up. No data is lost.

## Scenario D: Device Data is Simply Missing
**Symptoms**: No warnings on the dashboard, but a specific scanner isn't updating numbers.
1. **Technical Checks**:
   - Check the raw `device_events` table for that scanner's IP.
   - If there are no recent rows, the scanner is failing to reach the network (Check LAN cables/WiFi).
   - If there ARE rows, but they are stuck as `failed`, check `var/log/prod.log`. The scanner might be sending an Unknown Cockpit ID or Malformed JSON.
2. **What NOT to do**: Do not alter the database balances to "catch up". Fix the scanner payload issue, then use the replay tools.
