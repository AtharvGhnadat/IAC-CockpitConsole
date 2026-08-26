# Production Setup Guide

This guide describes how to install CockpitConsole on a fresh, approved Windows + XAMPP server on the IAC LAN.

## 1. Prerequisites
- Windows 10/11 or Windows Server.
- XAMPP installed at `C:\xampp`.
- PHP 8.2 (included with XAMPP).
- MariaDB 10.4+ (included with XAMPP).
- Composer installed globally.
- Git installed.

## 2. Directory Structure
The application MUST be located at `C:\xampp\htdocs\IAC\CockpitConsole`.

```powershell
mkdir C:\xampp\htdocs\IAC
cd C:\xampp\htdocs\IAC
git clone <repository_url> CockpitConsole
```

## 3. Environment Configuration
Create a `.env.local` file based on `.env`:
```ini
APP_ENV=prod
APP_DEBUG=0
APP_SECRET=<generate_a_random_32_char_string>
DATABASE_URL="mysql://cockpit_app:YOUR_SECURE_PASSWORD@127.0.0.1:3306/cockpit_console?serverVersion=10.4.32-MariaDB"
MESSENGER_TRANSPORT_DSN=doctrine://default?auto_setup=0
```
> [!IMPORTANT]
> Never commit `.env.local`. Ensure `APP_DEBUG=0`.

## 4. MariaDB Hardening
1. Open XAMPP Control Panel -> MySQL Shell.
2. Create the application user:
   ```sql
   CREATE DATABASE cockpit_console CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   CREATE USER 'cockpit_app'@'localhost' IDENTIFIED BY 'YOUR_SECURE_PASSWORD';
   GRANT SELECT, INSERT, UPDATE, DELETE ON cockpit_console.* TO 'cockpit_app'@'localhost';
   GRANT CREATE, DROP, ALTER, INDEX, REFERENCES ON cockpit_console.* TO 'cockpit_app'@'localhost'; -- Needed for migrations
   FLUSH PRIVILEGES;
   ```

## 5. Apache Hardening
Edit `C:\xampp\apache\conf\httpd.conf` or the application's vhost. 
Ensure directory listing is disabled, and critical folders are blocked:
```apache
<Directory "C:/xampp/htdocs/IAC/CockpitConsole">
    Options -Indexes
    AllowOverride All
    Require all granted
</Directory>

# Block access to sensitive directories
<DirectoryMatch "^C:/xampp/htdocs/IAC/CockpitConsole/(src|config|var|vendor|docs|scripts)/">
    Require all denied
</DirectoryMatch>

<FilesMatch "^\.env">
    Require all denied
</FilesMatch>
```
Restart Apache.

## 6. PHP OPcache
Edit `C:\xampp\php\php.ini`:
```ini
[opcache]
zend_extension=opcache
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=10000
opcache.revalidate_freq=2
opcache.enable_cli=0
```
Restart Apache.

## 7. Windows Services
Ensure Apache and MariaDB are installed as Windows Services via the XAMPP Control Panel by checking the "Service" boxes. This ensures they start automatically when the server reboots.

## 8. Deployment Execution
Run the deployment script from PowerShell (Run as Administrator):
```powershell
cd C:\xampp\htdocs\IAC\CockpitConsole
.\scripts\deploy_production.ps1
```

## 9. Final Step: Windows Task Scheduler
You must configure the automated backups and the Messenger Worker. See [WINDOWS_TASK_SCHEDULER.md](./WINDOWS_TASK_SCHEDULER.md) for detailed instructions.
