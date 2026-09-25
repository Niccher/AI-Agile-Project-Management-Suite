# ML Inference Backend (FastAPI)

The **ML Backend** is an asynchronous microservice powered by Python 3.12, FastAPI, and `llama-cpp-python` for high-throughput, local, quantized LLM inference.

---

## 📁 Source Layout (`services/ml/`)

```text
services/ml/
├── app/
│   ├── api/
│   │   ├── deps.py          # Fast authentication & dependency injection
│   │   └── v1/
│   │       ├── health.py    # Health probes & version info
│   │       ├── models.py    # Model catalog & dynamic loading
│   │       ├── admin/       # Real-time hardware telemetry
│   │       └── resources/   # Tasks, Sprints, Timelogs, Q&A, Background Jobs
│   ├── core/
│   │   ├── llm_engine.py    # Llama.cpp engine abstraction & context manager
│   │   ├── prompt_builder.py# Jinja2 prompt compilation
│   │   ├── security.py      # Header & bearer API key validator
│   │   └── task_manager.py  # Redis-backed async job tracker
│   ├── db/
│   │   ├── session.py       # Async SQLAlchemy connection engine
│   │   └── readers/         # Direct entity queries into MySQL db_chege_jira
│   └── prompts/             # Jinja2 templates (sprint_summary, task_enhance, etc.)
├── models/                  # Mount point for *.gguf model files
├── scripts/
│   └── download_model.sh    # Pre-configured GGUF model downloader
└── pyproject.toml           # Package metadata and dependencies
```

---

## 🤖 Supported Capabilities & Endpoints

| Endpoint | Method | Purpose |
| :--- | :--- | :--- |
| `/api/v1/health` | `GET` | Service vitals probe (HTTP 200) |
| `/api/v1/models/active` | `GET` | Active model metadata, loaded context, parameter size |
| `/api/v1/models/download` | `POST` | Trigger asynchronous model download from HuggingFace |
| `/api/v1/models/load` | `POST` | Dynamically hot-swap active GGUF model in memory |
| `/api/v1/resources/tasks/{id}/enhance` | `POST` | Rewrite task descriptions with user stories & test criteria |
| `/api/v1/resources/tasks/{id}/priority` | `POST` | Predict priority estimation based on project velocity |
| `/api/v1/resources/sprints/{id}/summarise` | `POST` | Generate executive sprint retro & metrics summary |
| `/api/v1/resources/time-reports/analyse` | `POST` | Identify bottlenecks and log discrepancies |
| `/api/v1/resources/qa/ask` | `POST` | Query project wikis and codebase context with RAG |
| `/api/v1/resources/background-tasks/{id}`| `GET` | Check async status and retrieve LLM generation result |
| `/api/v1/admin/telemetry` | `GET` | System CPU, GPU, RAM, Disk, MySQL, and Redis metrics |
