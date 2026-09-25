# AI-Agile-Project-Management-Suite

An enterprise-grade, role-based agile project management system and issue tracker integrated with a high-performance local LLM copilot backend.

The platform orchestrates agile sprints, Kanban boards, timelogs, Wiki documentation, management approvals, and automated AI assistance (task enhancement, priority estimation, sprint summarization, timelog insights, and technical Q&A) powered by on-premise quantized GGUF models.

---

## 🏛 Architecture Overview

| Service | Technology | Role | Local URL / Port |
| :--- | :--- | :--- | :--- |
| **WebApp** | PHP 8.3 / CodeIgniter 4 / Nginx | Core agile UI, role access, and business logic | `http://localhost:9001` |
| **ML Backend** | Python 3.12 / FastAPI / Llama.cpp | Local LLM inference & async AI copilot | `http://localhost:8000` (`/docs`) |
| **phpMyAdmin** | phpMyAdmin 5+ | Visual database management | `http://localhost:9000` |
| **MySQL** | MySQL 8.4 Server | Relational storage for sprints, tasks, and users | `localhost:9306` (internal 3306) |
| **Redis** | Redis 7 Alpine | Fast session storage, cache, and async task queue | `localhost:6379` |

---

## 🚀 Quick Start (Docker Compose)

### Prerequisites
- [Git](https://git-scm.com/)
- [Docker Engine 24+](https://docs.docker.com/engine/) & Docker Compose v2

### Setup & Run
1. **Clone the repository:**
   ```bash
   git clone <repo-url> AI-Agile-Project-Management-Suite
   cd AI-Agile-Project-Management-Suite
   ```

2. **Configure environment:**
   ```bash
   cp .env.example .env
   ```

3. **Start all services:**
   ```bash
   docker compose up --build -d
   ```
   *(Or use `make build`)*

4. **Verify running containers:**
   ```bash
   docker compose ps
   ```

5. **Access the application:**
   - **Web Application:** [http://localhost:9001](http://localhost:9001)
   - **FastAPI Interactive Docs (Swagger):** [http://localhost:8000/docs](http://localhost:8000/docs)
   - **phpMyAdmin:** [http://localhost:9000](http://localhost:9000)

---

## 🤖 Local AI Model Setup (Optional for LLM features)

To execute local LLM inference in the ML service:

1. Download a lightweight quantized GGUF model (e.g. `phi3-mini` or `mistral-7b`) into `services/ml/models/`:
   ```bash
   bash services/ml/scripts/download_model.sh phi3-mini
   ```
2. The ML service automatically detects and hot-loads models placed in `services/ml/models/`.

---

## 🛠 Developer Commands

A root `Makefile` is provided for common operations:

| Command | Action |
| :--- | :--- |
| `make up` | Start all services in the background |
| `make down` | Stop all containers |
| `make build` | Rebuild images and start the stack |
| `make logs` | Tail logs across all services |
| `make ps` | Check health and status of containers |
| `make migrate` | Run CodeIgniter database migrations |
| `make seed` | Seed database with demo projects and accounts |
| `make bash-web` | Open interactive shell inside WebApp container |
| `make bash-ml` | Open interactive shell inside ML backend container |

---

## 🔒 Security & Roles

- **Admin:** System provisioning, user management, audit logs, and AI telemetry.
- **Manager:** Sprint planning, task assignments, work review/approvals, and exportable reports.
- **User:** Execution, Kanban card movement, timelogging, and AI assistance prompts.
