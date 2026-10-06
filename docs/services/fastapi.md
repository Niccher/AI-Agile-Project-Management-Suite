# FastAPI ML Backend Service Guide

The **ML Backend** service (`ml-chege-jira`) is an asynchronous microservice built with **Python 3.12** and **FastAPI**. It delivers local, high-throughput LLM inference via `llama-cpp-python`, background job execution via Redis, real-time hardware telemetry, and automated agile assistance.

---

## 📁 Source Layout (`services/ml/`)

```text
services/ml/
├── app/
│   ├── main.py              # Application entrypoint, lifespan startup/shutdown, CORS
│   ├── config.py            # Pydantic Settings (environment parsing & validation)
│   ├── api/
│   │   ├── deps.py          # Dependency injection (authentication, db session, llm engine)
│   │   └── v1/
│   │       ├── router.py    # Master router aggregating all v1 sub-routers
│   │       ├── health.py    # GET /api/v1/health probe
│   │       ├── models.py    # GET /api/v1/models, POST /api/v1/models/load
│   │       ├── admin/
│   │       │   ├── telemetry.py # GET /api/v1/admin/telemetry (CPU, RAM, Disk, DB vitals)
│   │       │   └── config.py    # Dynamic runtime configuration adjustments
│   │       └── resources/
│   │           ├── tasks.py         # POST /api/v1/resources/tasks/{id}/enhance
│   │           ├── sprints.py       # POST /api/v1/resources/sprints/{id}/summarise
│   │           ├── projects.py      # Project-level insights & recommendations
│   │           ├── time_reports.py  # POST /api/v1/resources/time-reports/analyse
│   │           ├── qa.py            # POST /api/v1/resources/qa/ask (RAG wiki Q&A)
│   │           └── background_tasks.py # GET /api/v1/resources/background-tasks/{id}
│   ├── core/
│   │   ├── llm_engine.py    # Llama.cpp engine abstraction & context manager
│   │   ├── prompt_builder.py# Jinja2 template rendering engine
│   │   ├── security.py      # X-API-Key and Bearer token validator
│   │   ├── task_manager.py  # Redis-backed async task queue and status tracker
│   │   └── telemetry.py     # System hardware metric collector (psutil)
│   ├── db/
│   │   ├── session.py       # Async SQLAlchemy session factory (aiomysql)
│   │   ├── schema_guard.py  # Startup validation of MySQL table columns
│   │   ├── readers/         # Direct async read queries (TaskReader, SprintReader, etc.)
│   │   └── writers/         # AI output persistence (SprintWriter, QaWriter, etc.)
│   ├── prompts/             # Jinja2 templates (enhance_task.j2, summarise_sprint.j2, etc.)
│   └── schemas/             # Pydantic models for request & response validation
├── models/                  # Mount point for downloaded *.gguf model files
├── scripts/
│   └── download_model.sh    # Pre-configured HuggingFace GGUF downloader
├── tests/                   # Pytest test suite (unit and integration)
├── Dockerfile               # Multi-stage Dockerfile (OpenBLAS + Python 3.12)
└── pyproject.toml           # Project metadata, Ruff rules, and dependencies
```

---

## ⚙️ Service Configuration & Environment

| Variable | Default Value | Description |
| :--- | :--- | :--- |
| `APP_ENV` | `production` | Set to `development` for auto-reloading and verbose tracebacks. |
| `APP_HOST` | `0.0.0.0` | Bind interface. |
| `APP_PORT` | `8000` | Microservice port. |
| `API_KEY` | `chege_jira_ml_super_secret_key_2026` | Secret API key required on all protected endpoints. |
| `DB_HOST` | `mysql` | MySQL database host. |
| `DB_PORT` | `3306` | MySQL port. |
| `DB_NAME` | `db_chege_jira` | MySQL database name. |
| `DB_USER` | `root` | Database user. |
| `DB_PASSWORD` | `root_password` | Database password. |
| `REDIS_URL` | `redis://redis:6379/0` | Redis instance for async job state. |
| `MODELS_DIR` | `/app/models` | Filesystem path storing `.gguf` binary weights. |
| `DEFAULT_MODEL` | `phi3-mini` | Model key loaded by default on startup. |
| `N_THREADS` | `4` | Number of CPU cores allocated for inference. |
| `N_CTX` | `4096` | Token context window capacity. |
| `N_GPU_LAYERS` | `0` | Number of model layers offloaded to GPU (0 = CPU only). |

---

## 🧠 Core Systems

### 1. `LlmEngine` (`app/core/llm_engine.py`)
Encapsulates `llama-cpp-python` interaction:
- Manages in-memory model instances.
- Handles model loading, unloading, and dynamic hot-swapping via `load_model(model_name)`.
- Applies temperature, top-p, and max token parameters.
- Wraps inference in a thread-safe execution lock to prevent concurrency contention on CPU.

### 2. `TaskManager` (`app/core/task_manager.py`)
Coordinates asynchronous background tasks using Redis:
- Enqueues jobs with a unique UUID (`task:<uuid>`).
- Manages lifecycle states: `pending` $\to$ `processing` $\to$ `completed` / `failed`.
- Stores progress percentage and generated Markdown content.
- Automatically sets an expiration TTL (86,400s / 24 hours).

### 3. `SchemaGuard` (`app/db/schema_guard.py`)
Executed during FastAPI lifespan startup:
- Connects to MySQL and validates that all tables and columns expected by readers exist.
- Prevents runtime SQL exceptions if the WebApp has not completed initial migrations.

---

## 🛠 Developer Recipes

### Adding a New Prompt Template & Endpoint
1. Create a Jinja2 template in `app/prompts/review_code.j2`:
   ```jinja2
   You are an expert software engineer reviewing a pull request.
   Project: {{ project_name }}
   Code Diff:
   {{ diff_text }}

   Provide constructive feedback, identify potential edge cases, and rate the code quality.
   ```
2. Define Pydantic request/response schemas in `app/schemas/code_review.py`:
   ```python
   from pydantic import BaseModel

   class CodeReviewRequest(BaseModel):
       project_name: str
       diff_text: str

   class CodeReviewResponse(BaseModel):
       review_markdown: str
       status: str = "completed"
   ```
3. Add the endpoint in `app/api/v1/resources/projects.py`:
   ```python
   @router.post("/code-review", response_model=CodeReviewResponse)
   async def review_code(payload: CodeReviewRequest, engine: LlmEngine = Depends(get_llm_engine)):
       prompt = prompt_builder.render("review_code.j2", **payload.model_dump())
       output = await engine.generate(prompt)
       return CodeReviewResponse(review_markdown=output)
   ```
4. Update the API contract: `docs/api/contract.md` and `docs/api/openapi.yaml`.

---

## 🧪 Testing

Execute the test suites inside the ML container:
```bash
# Run all unit and integration tests
docker compose exec ml-chege-jira pytest tests/

# Run with test coverage
docker compose exec ml-chege-jira pytest --cov=app --cov-report=term-missing tests/
```
