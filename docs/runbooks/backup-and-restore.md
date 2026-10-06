# Operational Runbook: Backup & Restore Procedures

This runbook specifies procedures for backing up and restoring relational databases, Redis volatile state, and user media attachments.

---

## 💾 1. Relational Database Backup (MySQL 8.4)

### Method A: Native MySQL Dump (Recommended for Hot Backups)
```bash
# Create timestamped SQL dump
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
docker compose exec -T mysql mysqldump -u root -proot_password \
  --single-transaction \
  --quick \
  --databases db_chege_jira > backup_mysql_${TIMESTAMP}.sql

echo "Backup written to backup_mysql_${TIMESTAMP}.sql"
```

### Method B: CodeIgniter CLI Backup Command
```bash
# Triggers built-in database backup command
docker compose exec chege-jira php spark db:backup
```
The compressed SQL archive is written to `services/web/writable/backups/`.

---

## ⚡ 2. Redis State Backup

Redis persists data in an Append-Only File (`AOF`) and point-in-time RDB snapshots:

```bash
# 1. Trigger background snapshot save
docker compose exec redis redis-cli bgsave

# 2. Confirm background save completed
docker compose exec redis redis-cli lastsave

# 3. Copy dump file out of container
docker compose cp shared-redis:/data/dump.rdb ./redis_snapshot_$(date +%Y%m%d).rdb
```

---

## 📁 3. User Uploads & Media Attachments Backup

User avatars and task attachments reside in `services/web/writable/uploads/`:

```bash
# Archive all uploads into a compressed tarball
tar -czvf uploads_backup_$(date +%Y%m%d).tar.gz services/web/writable/uploads/
```

---

## 🔄 4. Complete Restoration Procedures

### Restoring MySQL from SQL Dump:
```bash
# 1. Ensure MySQL container is running and healthy
docker compose up -d mysql

# 2. Pipe backup file into MySQL
cat backup_mysql_20261006_120000.sql | docker compose exec -T mysql mysql -u root -proot_password db_chege_jira

# 3. Verify record counts
docker compose exec -T mysql mysql -u root -proot_password db_chege_jira -e "SELECT count(*) AS total_tasks FROM tasks; SELECT count(*) AS total_projects FROM projects;"
```

### Restoring Uploaded Media Files:
```bash
# Extract tarball into workspace
tar -xzvf uploads_backup_20261006.tar.gz

# Re-enforce permissions
sudo chown -R 33:33 services/web/writable/uploads
sudo chmod -R 775 services/web/writable/uploads
```
