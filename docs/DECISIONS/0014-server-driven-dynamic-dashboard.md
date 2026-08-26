# ADR 0014: Server-Driven Dynamic Dashboard

## Status
Accepted

## Context
Phase 10 requires a responsive, dynamic production dashboard capable of rendering live operational state without hardcoded frontend queries. We need a way for admins to configure rows and columns for specific metrics, while preventing unsafe or arbitrary SQL execution.

## Decision
1. **Database Configuration**: We introduced `dashboard_rows` and `dashboard_columns` to store the layout. These tables only store metadata (like `metric_key = OVERALL_PRODUCED`) and not the actual production numbers, preventing duplicate stale data.
2. **Server-Driven Snapshot**: The backend `DashboardSnapshotService` aggregates all critical data (Production, FIFO, Health) into a single unified JSON payload. The browser polls this `GET /api/dashboard/snapshot` endpoint every 1 second.
3. **Metric Catalog**: Only predefined `metric_key` values are supported by the frontend.
4. **Vanilla/Stimulus Frontend**: The UI dynamically builds the CSS Grid based on the configuration API and updates the DOM using `data-metric` attributes matching the snapshot keys.

## Consequences
- Single source of truth is maintained (the backend).
- Dashboard is completely isolated from underlying business entities, meaning dropping a dashboard row will never cascade-delete events.
- Low performance overhead due to a single unified payload instead of N+1 component requests.
- True responsive design achieved through CSS Grid `auto-fit` and `minmax()` without hardcoded sizes.
