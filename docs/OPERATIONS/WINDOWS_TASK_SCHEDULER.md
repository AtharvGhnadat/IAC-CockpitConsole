# Windows Task Scheduler Configuration

To ensure background processes run continuously and backups happen automatically without a human needing to open a terminal, configure the following tasks in the Windows Task Scheduler.

## 1. Symfony Messenger Worker
This process processes the asynchronous `device_events` into business state.

- **Task Name**: `CockpitConsole Worker Supervisor`
- **Purpose**: Ensures the Messenger consumer is always running.
- **Action**: Start a Program
- **Program/script**: `C:\xampp\php\php-win.exe` *(Note: use `php-win.exe` to avoid popping open a visible CMD window)*
- **Add arguments**: `bin/console messenger:consume doctrine --time-limit=3600 --memory-limit=128M -e prod`
- **Start in (Working Directory)**: `C:\xampp\htdocs\IAC\CockpitConsole`
- **Trigger**: At log on, OR Daily at 12:00 AM repeating every 1 minute.
- **Run-As Account**: Provide the local Administrator or Service account. Select "Run whether user is logged on or not".
- **Restart Policy**: Because `--time-limit=3600` is set, the PHP script will safely exit every hour to prevent memory leaks. Task Scheduler repeating every 1 minute will instantly relaunch it.

## 2. Automated Database Backup
- **Task Name**: `CockpitConsole Daily Backup`
- **Purpose**: Creates a `.sql` dump of the MariaDB database.
- **Action**: Start a Program
- **Program/script**: `powershell.exe`
- **Add arguments**: `-ExecutionPolicy Bypass -File C:\xampp\htdocs\IAC\CockpitConsole\scripts\backup_database.ps1`
- **Start in (Working Directory)**: `C:\xampp\htdocs\IAC\CockpitConsole`
- **Trigger**: Daily at 02:00 AM (or whenever production is quietest).
- **Run-As Account**: Local Administrator or Service account.
- **Log Location**: Output is logged to `C:\CockpitConsoleBackups\Database\backup_log.txt`.
