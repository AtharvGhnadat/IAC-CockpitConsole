# Business Rules

## Quantities
CockpitConsole business quantities will ultimately be integer-only (positive, zero, or negative). No decimal production quantities are allowed.
**Note**: The raw event journal (`device_events`) must preserve the quantity strictly as it was received (e.g., string `"10"`), and normalization to integer occurs only during business processing. 

## Calculations
*Pending implementation*
