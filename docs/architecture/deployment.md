# Deployment Architecture

This document describes how to deploy the **AI-Agile-Project-Management-Suite** across bare-metal VPS, on-premise servers, and cloud providers such as Railway.

---

## 🚀 Target 1: Self-Hosted Docker Compose (Recommended)

The platform is optimized for single-node deployment via Docker Compose.

### Automated One-Click Deployment
Run the automated deployment script from the repository root:
```bash
bash scripts/deploy.sh
```
Or via Makefile:
```bash
make deploy
```

The script executes 13 automated stages:
1. Detects host IP and sets `APP_URL`.
2. Checks system RAM, CPU, Swap, and Disk.
3. Verifies Git status and remotes.
4. Generates or patches `.env` with production keys.
5. Removes stale containers to eliminate name conflicts.
6. Builds Docker images using BuildKit.
7. Waits for MySQL health on internal port 3306.
8. Waits for Redis health on internal port 6379.
9. Runs CodeIgniter database migrations and seeders (`DemoSeeder`).
10. Validates ML microservice health (`/api/v1/health`).
11. Validates WebApp server responsiveness.
12. Enforces file permissions on `services/web/writable/` and prunes build cache.
13. Generates the deployment summary table and execution timeline.

---

## ☁ Target 2: Cloud Monorepo Deployment (e.g. Railway)

Deploying a multi-service monorepo to Railway:

```text
Railway Project
├── Service 1: MySQL (Add-on)
├── Service 2: Redis (Add-on)
├── Service 3: WebApp (Repo Root Directory: /services/web)
└── Service 4: ML Service (Repo Root Directory: /services/ml)
```

### Configuration Steps:
1. **WebApp Service:**
   - Link repo `https://github.com/Niccher/AI-Agile-Project-Management-Suite`
   - Set **Root Directory** to `/services/web`
   - Set Environment Variables:
     - `database.default.hostname` = `${{MySQL.MYSQLHOST}}`
     - `database.default.database` = `${{MySQL.MYSQLDATABASE}}`
     - `database.default.username` = `${{MySQL.MYSQLUSER}}`
     - `database.default.password` = `${{MySQL.MYSQLPASSWORD}}`
     - `REDIS_URL` = `${{Redis.REDIS_URL}}`
     - `ML_SERVICE_URL` = `http://${{ML-Service.RAILWAY_PRIVATE_DOMAIN}}:8000`
2. **ML Service:**
   - Link same repo
   - Set **Root Directory** to `/services/ml`
   - Add volume mount `/app/models` to persist downloaded GGUF weights.
   - `config.py` contains auto-resolution for Railway MySQL environment variables (`MYSQLHOST`, `MYSQL_URL`, etc.).
