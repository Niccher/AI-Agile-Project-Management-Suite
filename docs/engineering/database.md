# Database Management & Schema Governance

This document covers relational database schema migrations, seeders, single source of truth governance, and ML schema guard synchronization.

---

## 🏛 Schema Ownership & Governance

The platform enforces strict single source of truth governance:
1. **Exclusive Migration Owner:** **CodeIgniter 4 WebApp** owns all schema definitions and migrations (`services/web/app/Database/Migrations/`).
2. **Read-Only / Protected Write Consumer:** The Python ML service queries entities directly via SQLAlchemy but **never** runs DDL migrations.
3. **Startup Guard:** The ML service executes `SchemaGuard` (`services/ml/app/db/schema_guard.py`) on boot to verify required table columns exist before processing requests.

---

## 🗄 Migration Commands (CodeIgniter 4)

Execute migration commands inside the WebApp container:

```bash
# 1. Apply all pending migrations across all batches
docker compose exec chege-jira php spark migrate --all

# 2. Inspect current migration batch and status
docker compose exec chege-jira php spark migrate:status

# 3. Rollback the most recent migration batch
docker compose exec chege-jira php spark migrate:rollback
```

### Creating a New Migration
Migrations follow the timestamped format `YYYY-MM-DD-HHIISS_ClassName.php`:
```bash
docker compose exec chege-jira php spark make:migration AddTelemetryAlertsTable
```

Template structure:
```php
<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class AddTelemetryAlertsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'alert_type' => ['type' => 'VARCHAR', 'constraint' => 100],
            'details' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('telemetry_alerts');
    }

    public function down()
    {
        $this->forge->dropTable('telemetry_alerts');
    }
}
```

---

## 🌱 Database Seeding

Seeders reside in `services/web/app/Database/Seeds/`:
- **`DemoSeeder.php`**: Provisions default roles (`admin`, `manager`, `user`), default administrator (`admin` / `admin_password_123`), sample agile projects, sprints, tasks, and timelogs.

Run seeds:
```bash
docker compose exec chege-jira php spark db:seed DemoSeeder
```

---

## 💾 Native Database Backup Command

CodeIgniter includes a custom CLI command to generate compressed SQL snapshots:
```bash
docker compose exec chege-jira php spark db:backup
```
Snapshots are stored in `services/web/writable/backups/`.

---

## 🛡 Synchronizing with Python ML Backend

When adding new columns to agile entities (`tasks`, `projects`, `sprints`, `time_logs`):
1. Create and apply the CodeIgniter migration.
2. If the ML backend extracts this column, update the relevant reader in `services/ml/app/db/readers/`.
3. If the column is mandatory for inference, update `REQUIRED_COLUMNS` in `services/ml/app/db/schema_guard.py`.
