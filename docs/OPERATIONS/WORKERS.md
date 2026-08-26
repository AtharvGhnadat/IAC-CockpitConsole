# Worker Documentation

Symfony Messenger workers process the asynchronous queue (the `device_events` table) translating raw IoT data into business state (`cockpit_state`, `production_events`, etc.).

## 1. Worker Architecture
CockpitConsole uses the `doctrine` transport. The worker uses pessimistic write locking (InnoDB `FOR UPDATE`) to ensure it is completely thread-safe against concurrent HTTP requests and scanner hits.

## 2. Command
The production command is:
```powershell
php bin/console messenger:consume doctrine --time-limit=3600 --memory-limit=128M -e prod
```

## 3. Auto-Start & Supervision
The worker is configured to start automatically on Windows boot via the **Windows Task Scheduler**. If it crashes, or if it reaches its 1-hour time limit, the task scheduler will instantly relaunch it. See [WINDOWS_TASK_SCHEDULER.md](WINDOWS_TASK_SCHEDULER.md).

## 4. Health Detection
The `SystemHealthService` actively monitors the backlog. If the worker crashes and stays dead, the `device_events` table will accumulate `pending` events. If the oldest pending event is older than the configured threshold (e.g., 2 minutes), the Dashboard UI will display a **CRITICAL WARNING** alerting operators to a processing halt.

## 5. Failed Transport
If a message throws an unhandled exception (e.g., database constraint failure due to an unknown cockpit), it is marked as `failed` in the Messenger table. 
- List failures: `php bin/console messenger:failed:show`
- Retry failures: `php bin/console messenger:failed:retry`
- *Note*: Never delete failed messages without developer review.
