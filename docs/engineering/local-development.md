# Local Development Workflow

This guide explains how to develop, test, and debug services locally.

---

## 🛠 Approach A: Docker Compose (Recommended)

1. **Deploy the full stack:**
   ```bash
   bash scripts/deploy.sh
   # or: make up
   ```

2. **Code Hot-Reloading:**
   - **WebApp:** The `./services/web` directory is bind-mounted to `/var/www/html`. Any PHP, HTML, or CSS changes are reflected immediately on reload.
   - **ML Backend:** The `./services/ml/app` directory is mounted to `/app/app:ro`. Uvicorn runs in auto-reload mode when `APP_ENV=development`.

3. **Shell Access:**
   ```bash
   make bash-web   # Enters CodeIgniter container
   make bash-ml    # Enters FastAPI container
   ```

---

## 💻 Approach B: Native Execution Without Docker

If developing without Docker on Linux / macOS:

### Prerequisites:
- PHP 8.2+ with `intl`, `mysqli`, `pdo_mysql`, `curl`
- Composer 2
- Python 3.11 or 3.12 with `uv` or `pip`
- Local MySQL 8.0+ running on `localhost:3306`
- Local Redis 7+ running on `localhost:6379`

### 1. WebApp Setup:
```bash
cd services/web
composer install
cp ../../.env.example .env
php spark serve --port 8080
```

### 2. ML Service Setup:
```bash
cd services/ml
pip install -e "."
uvicorn app.main:app --host 0.0.0.0 --port 8000 --reload
```
