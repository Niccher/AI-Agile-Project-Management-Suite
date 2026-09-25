# WebApp Service (CodeIgniter 4)

The **WebApp** is the primary user-facing application built with PHP 8.3 and CodeIgniter 4, served via Nginx and PHP-FPM.

---

## 📁 Source Layout (`services/web/`)

```text
services/web/
├── app/
│   ├── Config/              # App, Database, Routes, Session, Security configs
│   ├── Controllers/         # Admin, Manager, User, Health, and Auth controllers
│   ├── Database/
│   │   ├── Migrations/      # Schema migrations
│   │   └── Seeds/           # DemoSeeder & initial admin user setup
│   ├── Models/              # TaskModel, ProjectModel, SprintModel, TimelogModel
│   ├── Services/            # LlmService (Client communicating with FastAPI ML)
│   ├── Session/Handlers/    # ResilientSessionHandler (Redis with DB fallback)
│   └── Views/               # Blade-style PHP views for Kanban, Sprints, Admin
├── entrypoint.sh            # Container bootstrapper (waits for MySQL, runs seeders)
├── nginx.conf               # Web server configuration & static asset caching
└── composer.json            # PHP dependencies (CodeIgniter 4, Shield, etc.)
```

---

## ⚡ Core Components

### 1. `LlmService.php` (`app/Services/LlmService.php`)
Acts as the HTTP gateway to the FastAPI ML backend:
- Encapsulates requests to `/api/v1/health`, `/api/v1/resources/*`, `/api/v1/admin/telemetry`.
- Automatically attaches the `X-API-Key` configured in `.env` or settings.
- Handles synchronous and asynchronous polling modes.

### 2. `ResilientSessionHandler.php` (`app/Session/Handlers/ResilientSessionHandler.php`)
Ensures seamless session resilience:
- Auto-detects `REDIS_URL`.
- Attempts to connect to Redis. If Redis is temporarily down or restarts, it transparently falls back to MySQL database session storage without kicking logged-in users out.

### 3. Database Migrations & Seeders
- Run automatically via `entrypoint.sh` on container boot.
- Manual execution:
  ```bash
  docker compose exec chege-jira php spark migrate --all
  docker compose exec chege-jira php spark db:seed DemoSeeder
  ```
