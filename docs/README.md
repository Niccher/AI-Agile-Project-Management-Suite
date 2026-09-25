# AI-Agile-Project-Management-Suite: Engineering Documentation

Welcome to the internal engineering documentation for the **AI-Agile-Project-Management-Suite** monorepo. This documentation is written for software engineers, site reliability engineers, and technical contributors who maintain, develop, or deploy the system.

If you are an operator or user looking for setup and execution instructions, please see the root [README.md](../README.md).

---

## 📚 Documentation Index

### 1. Architecture & Design
- [System Architecture Overview](architecture/overview.md) — Monorepo structure, high-level components, and topology.
- [Communication & Networking](architecture/communication.md) — Service-to-service protocols, REST endpoints, and Docker DNS.
- [Data & Storage](architecture/data-and-storage.md) — MySQL schemas, Redis session/queue isolation, and model storage.
- [Deployment](architecture/deployment.md) — Docker Compose orchestration, Railway deployment, and production hardening.
- [Threat Model & Security](architecture/threat-model.md) — Trust boundaries, RBAC roles, API key protection, and private networks.

### 2. Services
- [WebApp Service (CodeIgniter 4)](services/webapp.md) — PHP 8.3 MVC architecture, controllers, views, and session handling.
- [ML Inference Backend (FastAPI)](services/ml.md) — Python 3.12, `llama-cpp-python`, local GGUF models, and background task queues.

### 3. Engineering Workflows
- [Local Development](engineering/local-development.md) — Running services locally with or without Docker.
- [Database Management](engineering/database.md) — Migrations, seeding, and MySQL schema governance.
- [Testing Guide](engineering/testing.md) — Unit testing with PHPUnit and integration testing with Pytest.
- [Making Changes](engineering/making-changes.md) — Step-by-step workflow for modifying models, routes, or prompts.
- [Release Process](engineering/release.md) — Semantic versioning and container tagging.
- [Security Guidelines](engineering/security.md) — Credential management, vulnerability scanning, and best practices.
- [Troubleshooting & Diagnostics](engineering/troubleshooting.md) — Investigating 500 errors, latency, and container failures.

### 4. Runbooks
- [Service Restart Runbook](runbooks/restart.md) — Safe rolling restarts and clearing hung workers.
- [Backup & Restore Runbook](runbooks/backup-and-restore.md) — Dumping and restoring MySQL and Redis data.

### 5. API Reference
- [API Contract](api/contract.md) — REST schemas, payload structures, and error codes for WebApp ↔ ML communication.
- [OpenAPI Specification](api/openapi.yaml) — OpenAPI 3.0 specification for the FastAPI ML backend.
