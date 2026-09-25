# AI-Agile-Project-Management-Suite

<div align="center">

[![Release](https://img.shields.io/badge/Release-v1.0.0-blue.svg)](https://github.com/Niccher/AI-Agile-Project-Management-Suite/releases)
[![License](https://img.shields.io/badge/License-Apache_2.0-green.svg)](LICENSE)
[![PHP](https://img.shields.io/badge/PHP-8.3_%7C_CodeIgniter_4-777BB4.svg?logo=php&logoColor=white)](services/web)
[![Python](https://img.shields.io/badge/Python-3.12_%7C_FastAPI-3776AB.svg?logo=python&logoColor=white)](services/ml)
[![Database](https://img.shields.io/badge/MySQL-8.4-4479A1.svg?logo=mysql&logoColor=white)](docker-compose.yml)
[![Cache](https://img.shields.io/badge/Redis-7.0-DC382D.svg?logo=redis&logoColor=white)](docker-compose.yml)
[![Deployment](https://img.shields.io/badge/Deployment-Docker_%7C_GCP_Containers-4285F4.svg?logo=googlecloud&logoColor=white)](docs/architecture/deployment.md)
[![CI/CD](https://img.shields.io/badge/CI%2FCD-Quality_Gates_Passing-success.svg)](.github/workflows/ci.yml)

<p align="center">
  <strong>An enterprise-grade, role-based agile project management platform & issue tracker integrated with a high-performance local LLM copilot backend.</strong>
</p>

</div>

---

The platform orchestrates agile sprints, Kanban boards, timelogs, Wiki documentation, management approvals, and automated AI assistance (task enhancement, priority estimation, sprint summarization, timelog insights, and technical Q&A) powered by on-premise quantized GGUF models (`llama-cpp-python`).

**If you only need to run the system, this page is enough.**  
Software engineers: [docs/README.md](docs/README.md).

---

## 🏛 What “Running” Looks Like

| Piece | URL / How to Open | Dev Login (Default Seeder) |
| :--- | :--- | :--- |
| **Web Dashboard** | [http://localhost](http://localhost) (Port 80) | Username: `admin`<br>Password: `admin_password_123` |
| **ML API Swagger** | [http://localhost:8000/docs](http://localhost:8000/docs) (Port 8000) | Header: `X-API-Key: chege_jira_ml_super_secret_key_2026` |
| **MySQL Database** | `mysql:3306` | Docker internal network only (`db_chege_jira`) |
| **Redis Cache/Queue** | `redis:6379` | Docker internal network only |

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
   ```
   *(Or run `make deploy`)*

3. **What the pipeline does automatically:**
   - Detects host/LAN IP and patches `.env` with standard ports (`WEB_PORT=80`, `ML_PORT=8000`)
   - Performs hardware pre-flight checks (CPU, RAM, swap, disk)
   - Builds and launches the 4-container stack (MySQL, Redis, ML FastAPI, WebApp)
   - Waits for MySQL and Redis health probes
   - Applies database migrations and seeds initial admin accounts and demo agile projects
   - Enforces runtime permissions on `services/web/writable/`
   - Clears application caches and displays a live endpoint summary report

4. **To stop the system:**
   ```bash
   make down
   # or: docker compose down
   ```

---

## 🧪 Privacy-First Synthetic Agile Test Data

For testing and benchmarking local LLMs without exposing confidential company projects or personal data:

```bash
python3 scripts/generate_synthetic_agile_data.py --count 100 --output synthetic_seed.sql
```

---

## 🤖 Local AI Model Setup (Optional for LLM features)

To enable local LLM inference in the ML service:

1. Download a lightweight quantized GGUF model (e.g. `phi3-mini` or `mistral-7b`) into `services/ml/models/`:
   ```bash
   bash services/ml/scripts/download_model.sh phi3-mini
   ```
2. The ML service automatically detects and hot-loads models placed in `services/ml/models/`.

---

## 🔒 Roles and Access

The system enforces three primary roles:
- **Admin:** Has complete control over system settings, user provisioning, audit logs, and AI telemetry.
- **Manager:** Can assign tasks to team members, review/approve submitted work, manage sprints, and generate performance reports.
- **User:** General team member who can move Kanban cards, execute tasks, submit timelogs, and request AI assistance.

---

## 🛠 Something Went Wrong?

- **Port in use:** If port 80 or 8000 is taken on your host machine, edit `.env`:
  ```env
  WEB_PORT=8080
  ML_PORT=8001
  ```
  Then reload with: `docker compose up -d`.
- **Database issues:** If migrations didn't run automatically, execute them manually:
  ```bash
  make migrate
  make seed
  ```
- **Review logs:**
  ```bash
  make logs
  ```

---

## 👥 Community & Open-Source Governance

- [Apache 2.0 License](LICENSE) — Permissive license with explicit patent grant protection.
- [Security Policy](SECURITY.md) — Vulnerability reporting policy & zero-data-leak commitment.
- [Code of Conduct](CODE_OF_CONDUCT.md) — Contributor Covenant v2.1.
- [Contributing Guidelines](CONTRIBUTING.md) — Developer setup, coding standards, and PR checklists.

---

## 📚 Software Engineers

Detailed architectural specifications, API contracts, threat models, and developer guides are located in the [docs/](docs/README.md) directory:
- [System Architecture](docs/architecture/overview.md)
- [Communication & Protocols](docs/architecture/communication.md)
- [GCP Container Deployment](docs/architecture/deployment.md)
- [API Contract](docs/api/contract.md)
- [Local Development](docs/engineering/local-development.md)
- [Runbooks & Restarts](docs/runbooks/restart.md)
