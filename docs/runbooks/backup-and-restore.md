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

### Method B: CodeIgniter CLI Backup Command (with S3 Cloud Mirroring)
```bash
# Triggers built-in database backup command
docker compose exec chege-jira php spark db:backup
```
The compressed SQL archive (`.sql.gz`) is written to `services/web/writable/backups/` and immediately mirrored to the MinIO S3 bucket `agile-backups`.

---

## 🪣 2. MinIO S3 Object Storage Backup & Mirroring

All task attachments, avatars, and database dumps are stored in MinIO buckets (`agile-attachments`, `agile-avatars`, `agile-backups`).

### Snapshot S3 Buckets using MinIO Client (`mc`) or AWS CLI:
```bash
# Option A: Using AWS CLI against local MinIO
aws --endpoint-url=http://localhost:9000 s3 sync s3://agile-attachments ./s3_backups/agile-attachments/
aws --endpoint-url=http://localhost:9000 s3 sync s3://agile-avatars ./s3_backups/agile-avatars/
aws --endpoint-url=http://localhost:9000 s3 sync s3://agile-backups ./s3_backups/agile-backups/

# Option B: Backup Docker volume directly
docker run --rm -v minio-data:/data -v $(pwd):/backup alpine \
  tar -czvf /backup/minio_data_$(date +%Y%m%d).tar.gz -C /data .
```

### Migrating Local Disk Uploads into MinIO S3:
If upgrading from a legacy installation with files in `services/web/writable/uploads/`:
```bash
# 1. Preview migration (dry run)
docker compose exec chege-jira php spark storage:migrate-to-s3 --dry-run

# 2. Execute migration
docker compose exec chege-jira php spark storage:migrate-to-s3
```

---

## ⚡ 3. Redis State Backup

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

## 📁 4. Legacy Local Uploads Backup (Fallback)

For installations retaining local disk storage in `services/web/writable/uploads/`:

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
