# Data & Storage Architecture

This document covers data persistence, schemas, caching strategies, and file storage for the unified platform.

---

## 🗄 Relational Storage: MySQL 8.4

The central relational database is named `db_chege_jira` and managed by MySQL 8.4.

### 1. Volume Persistence
Data is preserved inside the named Docker volume `mysql-data`:
- Container Path: `/var/lib/mysql`
- Host Location: `/var/lib/docker/volumes/ai-agile-project-management-suite_mysql-data/_data`

### 2. Core Entities
- `users`: User profiles, roles (`admin`, `manager`, `user`), passwords, and avatars.
- `projects`: High-level initiatives with categories, statuses, and assigned managers.
- `sprints`: Agile iterations with start/end dates, goals, and point velocity.
- `tasks`: Individual issues (bugs, features, chores) linked to projects and sprints with priorities (`low`, `medium`, `high`, `critical`).
- `timelogs`: User-logged work intervals, duration in seconds, and descriptions.
- `wikis`: Project documentation markdown articles, versioning, and auto-generated AI docs.
- `qa_entries`: Automated question-answering records, prompt history, and AI evaluation metrics.

---

## ⚡ In-Memory Storage: Redis 7

Redis handles fast volatile operations and background coordination:

1. **Web Sessions:**
   - Keys: `chege_jira_session*` / `ci_session*`
   - Managed via `ResilientSessionHandler` in CodeIgniter 4. Falls back to MySQL `ci_sessions` table if Redis is temporarily unreachable.
2. **ML Background Tasks:**
   - Keys: `task:<uuid>` (TTL: 86400s)
   - Stores task payload, status (`pending`, `processing`, `completed`, `failed`), progress percentage, and output text.
3. **Application Cache:**
   - Telemetry vitals, active model catalog, and pre-computed dashboard statistics.
4. **Volume Persistence:**
   - Bound to `redis-data:/data` with Append-Only File (`AOF`) logging enabled (`--appendonly yes`).

---

## 🧠 Model Weights & File Storage

1. **GGUF Models Directory:**
   - Path: `services/ml/models/` mounted to `/app/models:rw`.
   - Models are heavy binary files (`*.gguf`) and are explicitly ignored by Git.
   - Pre-loaded models like `phi3-mini` (2.2 GB) or `mistral-7b` (4.1 GB) reside here.

2. **Web Uploads:**
   - Path: `services/web/writable/uploads/`
   - Stores user avatars and project attachments.
