# Deployment Checklist

Execute these exact steps in order for production deployment.

1. [ ] **Announce Maintenance**: Inform operators of a brief ~1-minute UI outage (Device ingestion will buffer).
2. [ ] **Verify Backup**: Ensure `scripts\backup_database.ps1` ran recently.
3. [ ] **Pull Code**:
   ```powershell
   git fetch origin
   git checkout tags/v1.0.0 -b release
   ```
4. [ ] **Run Deployment Script**:
   ```powershell
   # Run as Administrator
   .\scripts\deploy_production.ps1
   ```
5. [ ] **Wait for Script Output**: Verify it says `DEPLOYMENT COMPLETED SUCCESSFULLY`.
6. [ ] **Proceed to Post-Deployment Checklist**: See [POST_DEPLOYMENT_CHECKLIST.md](./POST_DEPLOYMENT_CHECKLIST.md).
