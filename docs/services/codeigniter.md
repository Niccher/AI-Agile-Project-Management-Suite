# CodeIgniter 4 WebApp Service Guide

The **WebApp** service provides the primary user-facing web dashboard, Kanban boards, sprint tracking, time tracking, Wiki documentation, client portal, and administration controls. Built with PHP 8.3 on **CodeIgniter 4**, it is served via Nginx and PHP-FPM.

---

## 📁 Source Layout (`services/web/`)

```text
services/web/
├── app/
│   ├── Commands/            # CLI spark commands (db:backup, session:gc, seed:user)
│   ├── Config/              # Routes.php, Database.php, Session.php, Security.php
│   ├── Controllers/         # MVC Controllers:
│   │   ├── Admin/           # System settings, AI telemetry, audit logs, users
│   │   ├── Api/             # AJAX API endpoints (Task drag-drop, Timers, Notes)
│   │   ├── User/            # Projects, Kanban, Sprints, Wiki, Tasks, Time, Notes
│   │   ├── HealthController.php # Health liveness probe (/health)
│   │   └── PortalController.php # Token-gated public client portal (/portal/{token})
│   ├── Database/
│   │   ├── Migrations/      # Relational schema migrations (33 versioned files)
│   │   └── Seeds/           # DemoSeeder.php (default accounts, projects, tasks)
│   ├── Entities/            # Strongly-typed entity representations
│   ├── Filters/             # Session auth, Shield security, and CSRF filters
│   ├── Models/              # TaskModel, ProjectModel, SprintModel, TimelogModel, etc.
│   ├── Services/            # LlmService.php (HTTP client calling FastAPI ML backend)
│   ├── Session/Handlers/    # ResilientSessionHandler.php (dual-engine Redis-to-MySQL)
│   └── Views/               # PHP UI views (Layouts, Modals, Kanban, Dashboard)
├── public/                  # Document root: index.php, assets (CSS, JS, images)
├── writable/                # Runtime data: cache, sessions, uploads, backups, logs
├── entrypoint.sh            # Container bootstrap script (waits for MySQL, migrates, seeds)
├── nginx.conf               # Web server virtual host and asset caching configuration
├── phpunit.xml.dist         # PHPUnit test configuration
└── composer.json            # PHP dependencies (CodeIgniter 4, Shield, DomPDF, League CSV)
```

---

## ⚙️ Service Configuration & Environment

The WebApp is configured via environment variables mapped in `docker-compose.yml` or `.env`:

| Key | Example Value | Description |
| :--- | :--- | :--- |
| `CI_ENVIRONMENT` | `production` | Set to `development` for verbose error pages and debug tools. |
| `app.baseURL` | `http://localhost/` | Base URL for the web application. |
| `database.default.hostname` | `mysql` | MySQL container hostname. |
| `database.default.database` | `db_chege_jira` | Active database name. |
| `database.default.username` | `root` | Database credentials. |
| `database.default.password` | `root_password` | Database root password. |
| `database.default.DBDriver` | `MySQLi` | Database driver. |
| `REDIS_URL` | `redis://redis:6379` | Redis connection URL for session handling and caching. |
| `ML_SERVICE_URL` | `http://ml-chege-jira:8000` | Target URL for inter-service communication with ML backend. |
| `ML_API_KEY` | `chege_jira_ml_super_secret_key_2026` | Secret API key for authenticating with the ML backend. |

---

## ⚡ Core Architecture & Components

### 1. Inter-Service Communication: `LlmService.php`
`app/Services/LlmService.php` encapsulates all HTTP requests to the FastAPI ML backend:
- Uses CodeIgniter's `CURLRequest` client.
- Automatically attaches the `X-API-Key` authentication header.
- Provides high-level methods:
  - `healthCheck(): array`
  - `getTelemetry(): array`
  - `enhanceTask(int $taskId, array $data): array`
  - `suggestPriority(int $taskId): array`
  - `summariseSprint(int $sprintId, bool $async = false): array`
  - `analyseTimeReports(array $filters, bool $async = false): array`
  - `askQuestion(string $question, int $projectId): array`
  - `getBackgroundTaskStatus(string $taskId): array`

### 2. High-Availability Sessions: `ResilientSessionHandler.php`
`app/Session/Handlers/ResilientSessionHandler.php` provides dual-engine session storage:
- Probes Redis socket availability via `@fsockopen` within a strict 50ms ceiling.
- Memoizes the probe result per HTTP request.
- Falls back seamlessly to MySQL table `ci_sessions` if Redis is offline.

### 3. Built-in CLI Spark Commands
The application includes custom CLI commands in `app/Commands/`:

```bash
# 1. Run all pending migrations
docker compose exec chege-jira php spark migrate --all

# 2. Seed initial demo dataset (admin account, sample agile projects)
docker compose exec chege-jira php spark db:seed DemoSeeder

# 3. Create a compressed SQL backup in writable/backups/
docker compose exec chege-jira php spark db:backup

# 4. Prune expired fallback sessions from MySQL ci_sessions
docker compose exec chege-jira php spark session:gc
```

---

## 🛠 Developer Recipes

### Adding a New Controller & Route
1. Create the controller in `app/Controllers/User/FeatureController.php`:
   ```php
   <?php
   namespace App\Controllers\User;
   use App\Controllers\BaseController;

   class FeatureController extends BaseController
   {
       public function index()
       {
           return view('user/feature/index', ['title' => 'New Feature']);
       }
   }
   ```
2. Register the route in `app/Config/Routes.php`:
   ```php
   $routes->group('', ['filter' => 'session'], function($routes) {
       $routes->get('/features', 'User\FeatureController::index');
   });
   ```
3. Add the view template in `app/Views/user/feature/index.php`.

### Adding a New Database Migration
1. Create a migration file in `app/Database/Migrations/`:
   ```php
   <?php
   namespace App\Database\Migrations;
   use CodeIgniter\Database\Migration;

   class AddFeatureTable extends Migration
   {
       public function up()
       {
           $this->forge->addField([
               'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
               'name' => ['type' => 'VARCHAR', 'constraint' => 255],
               'created_at' => ['type' => 'DATETIME', 'null' => true],
           ]);
           $this->forge->addKey('id', true);
           $this->forge->createTable('features');
       }

       public function down()
       {
           $this->forge->dropTable('features');
       }
   }
   ```
2. Run migrations:
   ```bash
   docker compose exec chege-jira php spark migrate --all
   ```

---

## 🧪 Testing

Execute the PHPUnit test suite:
```bash
# Run all tests
docker compose exec chege-jira ./vendor/bin/phpunit

# Run specific test suite
docker compose exec chege-jira ./vendor/bin/phpunit tests/app/Controllers/HealthTest.php
```
