# Business Rules

## Quantities
CockpitConsole business quantities will ultimately be integer-only (positive, zero, or negative). No decimal production quantities are allowed.
**Note**: The raw event journal (`device_events`) must preserve the quantity strictly as it was received (e.g., string `"10"`), and normalization to integer occurs only during business processing. 

## Calculations
*Pending implementation*

## PLC Processing Rules (Phase 5)
- **One valid PLC event = one request**. The request quantity is strictly 1.
- **total_requested += 1**: Cumulative total requests increment sequentially.
- **current_balance += 1**: Current balance increments upon a request.
- **Future Formula**: \current_balance = total_requested - completed_production\. (Scanner production processing is *Pending implementation* in a future phase).
- **Unknown Cockpits**: Events mapped to unknown cockpits are marked as failed; the system will NEVER auto-create master data.
