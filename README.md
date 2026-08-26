# CockpitConsole

Client: **IAC**  
Version: **0.1.0**  
Environment: **Production-ready Foundation**

## Purpose
CockpitConsole is an industrial production system designed for extreme reliability. The software is architected so that dashboard or browser failures do not stop device-event ingestion or production data processing.

## Architecture Summary
The application follows a clean architecture built on top of Symfony 7.4 LTS, utilizing Domain-Driven Design principles. It heavily relies on an append-only durable event architecture for device messages and business history, ensuring determinism and recoverability. 
The system is entirely localized to operate on the IAC local LAN with no dependence on external internet or cloud services.

## Technology Stack
- **OS**: Windows
- **Server**: Apache (via XAMPP)
- **Language**: PHP 8.2
- **Framework**: Symfony 7.4 LTS
- **Database**: MariaDB (InnoDB) via Doctrine
- **Frontend**: Twig, Symfony UX, Stimulus, Vanilla JS, CSS Grid/Flexbox/Container Queries
- **Logging**: Monolog
- **Queueing**: Symfony Messenger (Doctrine/MariaDB transport)
- **Testing**: PHPUnit

## Project Path
`C:\xampp\htdocs\IAC\CockpitConsole`

## Prerequisites
- XAMPP installed on Windows
- PHP 8.2 or higher
- MariaDB
- Composer

## Local Setup
1. Clone the repository to `C:\xampp\htdocs\IAC\CockpitConsole`.
2. Configure your XAMPP installation to use PHP 8.2.
3. Install dependencies:
   ```bash
   composer install
   ```

## Environment Configuration
- `.env` contains the default schema configuration.
- Copy `.env` to `.env.local` to define your specific machine credentials.
- Do **NOT** commit passwords, API keys, or private keys to version control.

## Database Configuration
Configure the MariaDB credentials in your `.env.local` file:
```env
DATABASE_URL="mysql://username:password@127.0.0.1:3306/cockpit_console?serverVersion=10.4.32-MariaDB&charset=utf8mb4"
```
Create the database and run migrations:
```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

## Apache/XAMPP Setup
Ensure the Apache `DocumentRoot` points to `C:\xampp\htdocs\IAC\CockpitConsole\public` or configure a Virtual Host or Alias to route traffic through `public/index.php`. Do not expose `.env`, `config/`, `src/`, or `vendor/` to the public HTTP directory.

## Development Commands
- Run local server (if not using Apache directly): `symfony server:start`
- Clear cache: `php bin/console cache:clear`

## Testing Commands
Run PHPUnit smoke tests:
```bash
php bin/phpunit
```

## Migration Commands
- Generate migration: `php bin/console doctrine:migrations:diff`
- Execute migration: `php bin/console doctrine:migrations:migrate`

## Documentation Links
- [System Overview](docs/SYSTEM_OVERVIEW.md)
- [Architecture & ADRs](docs/ARCHITECTURE.md)
- [Database Structure](docs/DATABASE.md)
- [Device Integration](docs/DEVICE_INTEGRATION.md)
- [Business Rules](docs/BUSINESS_RULES.md)
- [Authentication](docs/AUTHENTICATION.md)
- [Recovery Procedures](docs/RECOVERY.md)

## Versioning Policy
This project uses Semantic Versioning (`MAJOR.MINOR.PATCH`).
See the `VERSION` file for the current release.

## Branch Policy
- `main`: Production-ready code only
- `develop`: Integration branch
- `feature/*`: New features
- `fix/*`, `hotfix/*`: Bug fixes
- `refactor/*`, `docs/*`: Technical debt and documentation

## Changelog Policy
Every change must be documented in `CHANGELOG.md` and `docs/TECHNICAL_CHANGE_HISTORY.md`. No task is complete without updating the changelog.