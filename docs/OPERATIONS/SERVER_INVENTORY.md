# Server Inventory

Document the known production components below. (Do NOT store passwords here).

| Component | Detail |
|-----------|--------|
| **OS** | Windows 10/11 or Windows Server (Specify) |
| **XAMPP Version** | 8.2.x |
| **Apache Version** | 2.4.x |
| **PHP Version** | 8.2 |
| **MariaDB Version** | 10.4+ |
| **CockpitConsole Path** | `C:\xampp\htdocs\IAC\CockpitConsole` |
| **Backup Path** | `C:\CockpitConsoleBackups\Database` |
| **Worker Mechanism** | Windows Task Scheduler (`messenger:consume`) |

## Device Endpoints

| Device | Type | IP Address | Contract | Endpoint | Mapping / Health |
|--------|------|------------|----------|----------|------------------|
| **ESSL-01** | Biometric | (Fill during Go-Live) | JSON Fingerprint | Direct TCP Integration | `system_health` mapped |
| **PLC-01** | Machine Controller | (Fill during Go-Live) | Custom XML/JSON | `POST /plcdata.php` | Configured warning limit 120s |
| **SCANNER-01**| Prod Scanner | (Fill during Go-Live) | Barcode JSON | `/api/scanner/trolley` | `system_health` mapped |
| **SCANNER-02**| Disp Scanner | (Fill during Go-Live) | Barcode JSON | `/api/scanner/dispatch`| `system_health` mapped |
