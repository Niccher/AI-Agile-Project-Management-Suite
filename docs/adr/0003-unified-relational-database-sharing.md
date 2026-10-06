# ADR 0003: Unified Relational Database Sharing with Strict Service Ownership

- **Status:** Accepted
- **Date:** 2026-09-15
- **Deciders:** Chege Jira Architecture Team

---

## 📌 Context and Problem Statement

The platform comprises two backend services operating on the same business domain:
1. **WebApp (CodeIgniter 4 / PHP 8.3):** Handles user interactions, Kanban boards, timelogging, project settings, and role-based access control.
2. **ML Backend (FastAPI / Python 3.12):** Executes LLM prompt construction, retrieval-augmented generation (RAG), sprint metrics summarization, and timelog anomaly analysis.

When generating a sprint retrospective or analyzing 3 months of engineering timelogs, the ML service requires access to hundreds of related entities: task comments, status transitions, estimated vs logged seconds, and sprint burndown snapshots.

We evaluated two architectural patterns:
1. **Isolated Microservice Databases:** The WebApp serializes and transmits all entity models over internal HTTP REST APIs to the ML service.
2. **Shared Relational Database with Strict Ownership:** Both services connect to `mysql:3306`, with clear boundaries governing schema migrations, entity writes, and dedicated AI tables.

---

## 💡 Decision Drivers

- **Inference Speed & Context Assembly:** Assembling prompts for complex sprints requires scanning large volumes of relational records; HTTP serialization overhead between PHP and Python would introduce substantial latency.
- **Single Source of Truth:** Agile sprint state, velocity, and user permissions must never drift or desynchronize between separate databases.
- **Operational Simplicity:** A single MySQL 8.4 database instance simplifies backup, point-in-time recovery, and developer onboarding.

---

## 🎯 Decision Outcome

**Chosen Option:** Shared MySQL 8.4 database with strict domain ownership boundaries.

### Architectural Rules:
1. **Migration Authority:**
   - **CodeIgniter 4 WebApp** is the sole owner of database schema migrations (`services/web/app/Database/Migrations/`).
   - The ML service never executes schema migrations or alters existing tables.
2. **Entity Read Boundaries:**
   - The ML service uses an asynchronous SQLAlchemy engine (`services/ml/app/db/session.py`) and specialized read-only query modules (`services/ml/app/db/readers/`) to extract sprint, task, project, and timelog context.
3. **Entity Write Boundaries:**
   - Core agile entities (`users`, `projects`, `sprints`, `tasks`, `timelogs`, `wikis`) are exclusively written by WebApp controllers.
   - The ML service writes strictly to dedicated AI output tables (`ai_task_enhancements`, `ai_sprint_summaries`, `ai_time_reports`, `ai_qa_log`).
4. **Schema Protection:**
   - The ML service implements `SchemaGuard` (`services/ml/app/db/schema_guard.py`) during startup to verify that required table structures and columns exist before serving inference requests.

### Consequences

- **Positive:**
  - High performance: Prompt builders query relational views and aggregated datasets directly in sub-millisecond database queries.
  - Zero data duplication or synchronization drift.
  - Streamlined operational footprint: Single backup volume and snapshot procedure.
- **Negative:**
  - Schema changes in CodeIgniter 4 migrations require corresponding updates in ML reader queries if existing column names or types change.
