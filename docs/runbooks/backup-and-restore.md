# Runbook: Backup & Restore

This runbook covers backing up and restoring relational and volatile state.

---

## 💾 MySQL Database Backup

### 1. Create a Snapshot Dump:
```bash
docker compose exec -T mysql mysqldump -u root -proot_password db_chege_jira > backup_$(date +%Y%m%d_%H%M%S).sql
```

### 2. Restore from Snapshot:
```bash
cat backup_file.sql | docker compose exec -T mysql mysql -u root -proot_password db_chege_jira
```

---

## ⚡ Redis Snapshot Backup

### 1. Trigger Background Save:
```bash
docker compose exec redis redis-cli bgsave
```

### 2. Copy Dump File:
```bash
docker compose cp shared-redis:/data/dump.rdb ./redis_dump.rdb
```
