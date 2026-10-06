# Engineering Documentation Portal

Welcome to the internal engineering documentation for the **AI-Agile-Project-Management-Suite** monorepo. This documentation is written for software engineers, site reliability engineers, and technical contributors who maintain, develop, or deploy the system.

If you are an operator or user looking for setup and execution instructions, please see the root [README.md](../README.md) or the [Operator Setup Guide](user/setup-and-run.md).

---

## 🧭 Navigation Index

| I want to… | Go here | Description |
| :--- | :--- | :--- |
| **Run the system without coding** | [../README.md](../README.md) | Root operator quick-start guide and one-click runbook. |
| **Follow full operator setup** | [user/setup-and-run.md](user/setup-and-run.md) | Detailed pre-flight checks, Docker deployment, and verification. |
| **Configure environment variables** | [user/configuration.md](user/configuration.md) | Comprehensive table of all user-configurable parameters. |
| **Resolve operator startup errors** | [user/troubleshooting.md](user/troubleshooting.md) | Port conflicts, daemon failures, and MySQL startup issues. |
| **Understand system topology** | [architecture/overview.md](architecture/overview.md) | C4 Context and Container diagrams with exact port mappings. |
| **Trace service-to-service calls** | [architecture/communication.md](architecture/communication.md) | Networking matrix and Mermaid interaction sequences. |
| **Explore database schemas & ERD** | [architecture/data-and-storage.md](architecture/data-and-storage.md) | MySQL relational ERD from migrations and Redis queues. |
| **Inspect dual-engine session HA** | [architecture/resilience-and-failover.md](architecture/resilience-and-failover.md) | 50ms pre-flight probe and Redis-to-MySQL session failover. |
| **Deploy to cloud or containers** | [architecture/deployment.md](architecture/deployment.md) | Docker Compose and Google Cloud Platform (GCP) setup. |
| **Review security & threat model** | [architecture/threat-model.md](architecture/threat-model.md) | STRIDE analysis, network isolation, and RBAC tiers. |
| **Work on CodeIgniter 4 WebApp** | [services/codeigniter.md](services/codeigniter.md) | PHP 8.3 MVC layout, CLI spark commands, and recipes. |
| **Work on FastAPI ML Backend** | [services/fastapi.md](services/fastapi.md) | Python 3.12 routers, Pydantic schemas, and task manager. |
| **Review local AI model card** | [services/ml.md](services/ml.md) | Quantized GGUF models, context windows, and Jinja2 prompts. |
| **Inspect REST API endpoints** | [api/contract.md](api/contract.md) | Schema reference for all `/api/v1` endpoints. |
| **View OpenAPI 3.0 specification** | [api/openapi.yaml](api/openapi.yaml) | Interactive OpenAPI contract for Swagger and tools. |
| **Develop locally on host machine** | [engineering/local-development.md](engineering/local-development.md) | Native PHP/Python setup vs Docker Compose hot-reloading. |
| **Safely make changes to code** | [engineering/making-changes.md](engineering/making-changes.md) | Feature development matrix and Definition of Done (DoD). |
| **Run migrations and seeders** | [engineering/database.md](engineering/database.md) | CodeIgniter migrations and ML schema guard governance. |
| **Execute automated test suites** | [engineering/testing.md](engineering/testing.md) | PHPUnit, Pytest, coverage collection, and docs linter. |
| **Inspect CI quality gates** | [engineering/ci.md](engineering/ci.md) | Automated GitHub Actions workflows and validation gates. |
| **Read contribution guidelines** | [engineering/contributing.md](engineering/contributing.md) | Branch conventions, coding standards, and PR checklists. |
| **Review security guidelines** | [engineering/security.md](engineering/security.md) | Defensive engineering, credential governance, and injection guards. |
| **Check version compatibility** | [engineering/release.md](engineering/release.md) | Monorepo version matrix and release train checklist. |
| **Debug complex runtime faults** | [engineering/troubleshooting.md](engineering/troubleshooting.md) | Diagnostics matrix, inter-service curls, and Redis checks. |
| **Perform rolling service restarts** | [runbooks/restart.md](runbooks/restart.md) | Standard Operating Procedures for safe restarts and recovery. |
| **Backup and restore state** | [runbooks/backup-and-restore.md](runbooks/backup-and-restore.md) | MySQL database dumps, Redis snapshots, and media restoration. |
| **Review architectural decisions** | [adr/0001-on-premise-quantized-llm-inference.md](adr/0001-on-premise-quantized-llm-inference.md) | Architecture Decision Records (ADRs 0001, 0002, 0003). |
