# Data & Storage Architecture

This document specifies data persistence, relational schemas, in-memory caching strategies, and file storage for the unified platform.

---

## 🗄 Relational Storage: MySQL 8.4

The central relational database is named `db_chege_jira` and managed by MySQL 8.4 LTS.

### 1. Volume Persistence
Data is preserved inside the named Docker volume `mysql-data`:
- **Container Path:** `/var/lib/mysql`
- **Engine:** InnoDB with `utf8mb4_unicode_ci` collation
- **SQL Mode:** Strict transactional tables (`STRICT_TRANS_TABLES,NO_ZERO_IN_DATE...`)

---

## 📊 Entity-Relationship Diagram (ERD)

The diagram below reflects the relational database schema defined by the 33 versioned migrations in `services/web/app/Database/Migrations/`:

```mermaid
erDiagram
    USERS ||--o{ PROJECTS : manages
    USERS ||--o{ TASKS : assigned_to
    USERS ||--o{ TIME_LOGS : logs
    USERS ||--o{ AUTH_IDENTITIES : authenticates
    PROJECTS ||--o{ SPRINTS : contains
    PROJECTS ||--o{ TASKS : tracks
    PROJECTS ||--o{ PROJECT_WIKI_PAGES : documents
    SPRINTS ||--o{ TASKS : includes
    SPRINTS ||--o{ SPRINT_SNAPSHOTS : records_velocity
    SPRINTS ||--o{ AI_SPRINT_SUMMARIES : summarised_by
    TASKS ||--o{ TASK_COMMENTS : discussed_in
    TASKS ||--o{ TASK_ATTACHMENTS : attaches
    TASKS ||--o{ TIME_LOGS : measures
    TASKS ||--o{ AI_TASK_ENHANCEMENTS : refined_by
    PROJECT_WIKI_PAGES ||--o{ PROJECT_WIKI_VERSIONS : versioned_in
    PROJECTS ||--o{ AI_QA_LOG : referenced_in

    USERS {
        int id PK
        string username
        string email
        string status
        datetime created_at
    }

    PROJECTS {
        int id PK
        string name
        string key
        int manager_id FK
        string status
        datetime created_at
    }

    SPRINTS {
        int id PK
        int project_id FK
        string name
        date start_date
        date end_date
        string status
        int total_points
    }

    TASKS {
        int id PK
        int project_id FK
        int sprint_id FK
        int assignee_id FK
        string title
        text description
        string status
        string priority
        int story_points
        datetime created_at
    }

    TIME_LOGS {
        int id PK
        int task_id FK
        int user_id FK
        int duration_seconds
        datetime started_at
        text description
    }

    PROJECT_WIKI_PAGES {
        int id PK
        int project_id FK
        string slug
        string title
        text content
        int version
    }

    AI_SPRINT_SUMMARIES {
        int id PK
        int sprint_id FK
        text executive_summary
        json velocity_metrics
        text blockers
        datetime generated_at
    }

    AI_TASK_ENHANCEMENTS {
        int id PK
        int task_id FK
        text enhanced_description
        json acceptance_criteria
        datetime generated_at
    }

    AI_QA_LOG {
        int id PK
        int project_id FK
        int user_id FK
        text question
        text answer
        json source_references
        datetime created_at
    }

    CI_SESSIONS {
        string id PK
        string ip_address
        int timestamp
        blob data
    }
```

---

## ⚡ In-Memory Storage: Redis 7

Redis handles high-throughput volatile operations and asynchronous coordination:

1. **User Sessions:**
   - Keys: `chege_jira_session*` / `ci_session*`
   - Primary driver for CodeIgniter 4. Falls back to MySQL `ci_sessions` table if Redis is temporarily offline.
2. **ML Background Tasks:**
   - Keys: `task:<uuid>` (TTL: 86,400s)
   - Stores task payload, status (`pending`, `processing`, `completed`, `failed`), progress percentage, and output text.
3. **Application Cache:**
   - Application telemetry vitals, active model catalog, and pre-computed dashboard metrics.
4. **Volume Persistence:**
   - Bound to `redis-data:/data` with Append-Only File (`AOF`) logging enabled (`--appendonly yes`).

---

## 📁 File System & Artifact Storage

1. **GGUF Models Directory:**
   - Path: `services/ml/models/` mounted to `/app/models:rw`.
   - Models are binary weight files (`*.gguf`) and are explicitly ignored by Git.
   - Pre-loaded models like `phi3-mini` (2.2 GB) or `mistral-7b` (4.1 GB) reside here.
2. **Web Uploads:**
   - Path: `services/web/writable/uploads/`
   - Preserves user avatars and project attachments.
3. **Database Backups:**
   - Path: `services/web/writable/backups/`
   - Stores compressed database archives created by `php spark db:backup`.
