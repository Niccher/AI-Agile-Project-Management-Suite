# Operator Guide: Setup and Run

This guide is written for operators, QA engineers, and system administrators who need to install, launch, verify, and maintain the **AI-Agile-Project-Management-Suite** stack.

---

## 📋 Prerequisites & Hardware Requirements

Before running the application, ensure your host machine satisfies:

| Resource | Minimum Requirement | Recommended Specification |
| :--- | :--- | :--- |
| **OS** | Linux (Ubuntu 20.04+, Debian 11+, RHEL 8+), macOS 12+, Windows WSL2 | Linux VPS / Dedicated Host |
| **CPU** | 2 cores | 4+ physical cores (for local quantized LLM inference) |
| **RAM** | 4 GB | 8 GB – 16 GB |
| **Disk** | 10 GB free space | 25 GB SSD (for Docker images and GGUF model weights) |
| **Software** | Git, Docker Engine 24+, Docker Compose v2 | Docker Engine + Compose v2 |

---

## 🚀 Option A: One-Click Automated Deployment (Recommended)

The repository provides a self-contained, 13-stage deployment script that automates host configuration, environment generation, container compilation, database migration, and health verification.

### 1. Clone the Repository
```bash
git clone https://github.com/Niccher/AI-Agile-Project-Management-Suite.git
cd AI-Agile-Project-Management-Suite
```

### 2. Run the Deployment Pipeline
```bash
bash scripts/deploy.sh
```
*(Alternatively, run `make deploy` if `make` is installed).*

### 3. What the Pipeline Automates:
1. **Network Detection**: Discovers local LAN/host IP and configures `APP_URL`.
2. **Pre-flight Checks**: Inspects CPU architecture, available RAM, swap space, and disk limits.
3. **Environment Setup**: Generates a synchronized `.env` file if none exists.
4. **Container Orchestration**: Builds and boots MySQL 8.4, Redis 7, Python FastAPI, and CodeIgniter 4 containers.
5. **Health Readiness**: Polls internal MySQL and Redis health probes before initializing application services.
6. **Database Migration & Seeding**: Applies all database migrations and runs `DemoSeeder` to provision initial roles and projects.
7. **Permission Enforcement**: Configures ownership and read/write permissions on `services/web/writable/`.
8. **Live Verification**: Validates both WebApp HTTP status and ML `/api/v1/health` probes.

---

## 🐳 Option B: Manual Docker Compose Launch

If you prefer launching containers step-by-step using standard Docker Compose:

### 1. Prepare Environment
```bash
cp .env.example .env
```
*(Review `.env` and adjust ports such as `WEB_PORT=80` or `ML_PORT=8000` if necessary).*

### 2. Build and Launch Containers
```bash
docker compose up --build -d
```

### 3. Wait for Database Readiness
Check that MySQL and Redis report healthy status:
```bash
docker compose ps
```

### 4. Execute Migrations & Seed Initial Data
```bash
docker compose exec chege-jira php spark migrate --all
docker compose exec chege-jira php spark db:seed DemoSeeder
```

---

## 🔍 Verifying the Running System

Once deployed, verify operational readiness using the following endpoints:

| Component | Target URL | Expected Result | Credentials / Notes |
| :--- | :--- | :--- | :--- |
| **Web Dashboard** | `http://localhost:80` | Login screen loads with 200 OK | Username: `admin`<br>Password: `admin_password_123` |
| **ML Swagger API** | `http://localhost:8000/docs` | Interactive OpenAPI documentation | Header: `X-API-Key: chege_jira_ml_super_secret_key_2026` |
| **WebApp Health Probe** | `GET http://localhost:80/health` | Returns JSON with `status: "healthy"` | Reports Redis & MySQL connectivity |
| **ML Health Probe** | `GET http://localhost:8000/api/v1/health` | Returns JSON with `status: "ok"` | Reports model load & database state |

### CLI Verification Command
```bash
curl -s http://localhost/health | grep -q '"status":"healthy"' && echo "WebApp is Healthy" || echo "WebApp Degraded"
curl -s http://localhost:8000/api/v1/health | grep -q '"status":"ok"' && echo "ML Backend is Healthy" || echo "ML Offline"
```

---

## 🛑 Stopping & Restarting the Stack

### Graceful Stop
```bash
docker compose down
# or: make down
```

### Restarting
```bash
docker compose up -d
# or: make up
```

### Clean Rebuild Preserving Volumes
```bash
docker compose down
docker compose build --no-cache
docker compose up -d
```

---

## 🆘 Next Steps & Reference
- Configure custom domain, ports, or credentials: [Configuration Guide](configuration.md)
- Diagnose container or networking faults: [Operator Troubleshooting](troubleshooting.md)
- Software architecture and code modification: [Engineering Portal](../README.md)
