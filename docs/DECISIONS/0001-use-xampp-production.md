# Architecture Decision Record: Use XAMPP for Production

**Status:** Accepted  
**Date:** 2026-08-26

## Context
CockpitConsole must run in an industrial environment without dependencies on external SaaS, cloud databases, or containerization platforms like Docker, which introduce unnecessary infrastructure complexity for this specific use case. The client, IAC, requires the system to run on Windows within their local LAN.

## Decision
We will use XAMPP on Windows as the production environment to host Apache, PHP, and MariaDB.

## Consequences
- **Positive:** Simplifies infrastructure requirements; conforms directly to client constraints; keeps everything on a single manageable local machine.
- **Negative:** Requires manual configuration of XAMPP services (PHP 8.2 upgrade, MariaDB tuning, Apache virtual hosts) and lacks containerized isolation.
