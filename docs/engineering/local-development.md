# Local Development Workflow

This guide explains how to set up, develop, and debug the monorepo services locally with or without Docker.

---

## 🛠 Approach A: Docker Compose Workflow (Recommended)

Docker Compose provides parity with production environments and eliminates manual dependency installation.

### 1. Launch the Stack
```bash
bash scripts/deploy.sh
# or: make up
```

### 2. Live Hot-Reloading Behavior
- **WebApp (PHP):** `./services/web` is bind-mounted directly to `/var/www/html`. Changes to controllers, views, models, and routes take effect immediately on browser refresh.
- **ML Backend (Python):** `./services/ml` is mounted into the container. When `APP_ENV=development`, Uvicorn automatically monitors Python files and reloads workers when changes are saved.

### 3. Interactive Container Shells
```bash
# Enter CodeIgniter WebApp container
make bash-web
# or: docker compose exec chege-jira bash

# Enter FastAPI ML microservice container
make bash-ml
# or: docker compose exec ml-chege-jira bash
```

### 4. Viewing Live Container Logs
```bash
# Stream all container logs
make logs

# Stream only WebApp logs
docker compose logs -f chege-jira

# Stream only ML backend logs
docker compose logs -f ml-chege-jira
```

---

## 💻 Approach B: Native Local Execution Without Docker

If developing natively on Linux or macOS without Docker:

### Prerequisites:
- PHP 8.3 with `intl`, `mysqli`, `pdo_mysql`, `curl`, `mbstring`, `zip`
- Composer 2.x
- Python 3.12 with `pip`
- Local MySQL 8.4 running on `localhost:3306` with database `db_chege_jira`
- Local Redis 7 running on `localhost:6379`

### 1. WebApp Setup (CodeIgniter 4):
```bash
cd services/web
composer install
cp ../../.env.example .env
# Edit .env to set database.default.hostname=localhost and database.default.port=3306
php spark migrate --all
php spark db:seed DemoSeeder
php spark serve --port 8080
```
Access at `http://localhost:8080`.

### 2. ML Backend Setup (FastAPI):
```bash
cd services/ml
python3 -m venv .venv
source .venv/bin/activate
pip install -e ".[dev]"
# Download a lightweight model
bash scripts/download_model.sh phi3-mini
# Run Uvicorn in auto-reload mode
uvicorn app.main:app --host 0.0.0.0 --port 8000 --reload
```
Access Swagger UI at `http://localhost:8000/docs`.
