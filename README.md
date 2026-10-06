# AI-Agile-Project-Management-Suite

<div align="center">

[![Release](https://img.shields.io/badge/Release-v1.0.0-blue.svg)](https://github.com/Niccher/AI-Agile-Project-Management-Suite/releases)
[![License](https://img.shields.io/badge/License-Apache_2.0-green.svg)](LICENSE)
[![PHP](https://img.shields.io/badge/PHP-8.3_%7C_CodeIgniter_4-777BB4.svg?logo=php&logoColor=white)](services/web)
[![Python](https://img.shields.io/badge/Python-3.12_%7C_FastAPI-3776AB.svg?logo=python&logoColor=white)](services/ml)
[![CI/CD](https://img.shields.io/badge/CI%2FCD-Quality_Gates_Passing-success.svg)](ci/workflows/ci.yml)
[![Docs](https://img.shields.io/badge/Docs-MkDocs_Material-blueviolet.svg)](docs/README.md)

**Enterprise-grade agile project management platform & issue tracker with an on-premise local LLM copilot.**

</div>

---

The platform orchestrates agile sprints, Kanban boards, timelogs, Wiki documentation, management approvals, and automated AI assistance powered by local quantized GGUF models (`llama-cpp-python`).

Stack: **CodeIgniter 4 (PHP 8.3)**, **FastAPI (Python 3.12)**, **MySQL 8.4**, **Redis 7**.

**If you only need to run the system, this page is enough.**  
Software engineers: [docs/README.md](docs/README.md).

---

## 🏛 What “Running” Looks Like

| Piece | URL / How to Open | Dev Login / Credentials |
| :--- | :--- | :--- |
| **Web Dashboard** | [http://localhost](http://localhost) (Port 80) | Username: `admin`<br>Password: `admin_password_123` |
| **ML API Swagger** | [http://localhost:8000/docs](http://localhost:8000/docs) (Port 8000) | Header: `X-API-Key: chege_jira_ml_super_secret_key_2026` |
| **Health Probe** | `GET http://localhost/health` | Returns HTTP 200 `{"status":"healthy"}` |
| **MySQL Database** | `mysql:3306` | Internal Docker network only (`db_chege_jira`) |
| **Redis Cache/Queue** | `redis:6379` | Internal Docker network only |

---

## ⚡ Prerequisites

- [Git](https://git-scm.com/)
- [Docker Engine 24+](https://docs.docker.com/engine/) & Docker Compose v2 (or Docker Desktop)
- Minimum 4 GB RAM recommended for local quantized LLM inference

---

## 🚀 Setup and Run (One-Click Automated Deployment)

1. **Clone the repository:**
   ```bash
   git clone https://github.com/Niccher/AI-Agile-Project-Management-Suite.git
   cd AI-Agile-Project-Management-Suite
   ```

2. **Run the one-click deployment pipeline:**
   ```bash
   bash scripts/deploy.sh
   # or: make deploy
   ```

3. **What the pipeline does automatically:**
   - Detects host/LAN IP and patches `.env` (`WEB_PORT=80`, `ML_PORT=8000`)
   - Pre-flight system checks (CPU, RAM, swap, disk)
   - Builds and boots the 4-container stack (MySQL, Redis, ML FastAPI, WebApp)
   - Waits for MySQL & Redis readiness, runs migrations and seeds demo agile projects
   - Enforces file permissions and verifies service health

4. **To stop the system:**
   ```bash
   make down
   # or: docker compose down
   ```

---

## ⚙️ Configuration Users May Change

| Variable | Service | Default | Purpose |
| :--- | :--- | :--- | :--- |
| `WEB_PORT` | WebApp | `80` | Host port for the Web Dashboard |
| `ML_PORT` | ML Service | `8000` | Host port for FastAPI & Swagger UI |
| `APP_URL` | WebApp | `http://localhost` | Public web address for links and redirects |
| `DB_NAME` | Database | `db_chege_jira` | MySQL database name |
| Full list | All | — | See [docs/user/configuration.md](docs/user/configuration.md) |

---

## 🛠 Something Went Wrong?

- **Port in use:** Edit `.env` (`WEB_PORT=8080`, `ML_PORT=8001`), then reload: `docker compose up -d`.
- **Database issues:** Run migrations manually: `make migrate && make seed`.
- **Review logs:** `make logs` or `docker compose logs chege-jira`.
- **Detailed guide:** See [docs/user/troubleshooting.md](docs/user/troubleshooting.md).

---

## 📚 Software Engineers

- **Architecture Overview:** [docs/architecture/overview.md](docs/architecture/overview.md)
- **Communication & Protocols:** [docs/architecture/communication.md](docs/architecture/communication.md)
- **Database ERD & Storage:** [docs/architecture/data-and-storage.md](docs/architecture/data-and-storage.md)
- **Dual-Engine HA Resilience:** [docs/architecture/resilience-and-failover.md](docs/architecture/resilience-and-failover.md)
- **API Contract Reference:** [docs/api/contract.md](docs/api/contract.md)
- **Making Changes Safely:** [docs/engineering/making-changes.md](docs/engineering/making-changes.md)
- **Engineering Portal Home:** [docs/README.md](docs/README.md)
