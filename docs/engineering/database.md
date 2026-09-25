# Database Management Guide

This document covers MySQL migrations, seeders, and data consistency checks.

---

## 🗄 Migrations (CodeIgniter 4)

Migrations live in `services/web/app/Database/Migrations/`.

### Commands:
```bash
# Run all pending migrations
docker compose exec chege-jira php spark migrate --all

# View migration status
docker compose exec chege-jira php spark migrate:status

# Rollback last batch of migrations
docker compose exec chege-jira php spark migrate:rollback
```

### Creating a New Migration:
```bash
docker compose exec chege-jira php spark make:migration CreateAuditLogsTable
```

---

## 🌱 Seeders

Seeders reside in `services/web/app/Database/Seeds/`:
- `DemoSeeder.php`: Provisions initial roles, admin user (`admin` / `admin_password_123`), sample agile projects, sprints, and tasks.

Run seeders:
```bash
docker compose exec chege-jira php spark db:seed DemoSeeder
```
