# Engineering Troubleshooting & Diagnostics

This guide provides diagnostics procedures for software engineers debugging services, communication bridges, database queries, and test failures.

---

## 🔍 Diagnostic Matrix

| Issue Area | Diagnostic Tool / Command | Likely Root Cause & Remediation |
| :--- | :--- | :--- |
| **WebApp 500 Errors** | `docker compose logs chege-jira`<br>`cat services/web/writable/logs/log-$(date +%Y-%m-%d).log` | Missing migration, database connection failure, or unhandled PHP exception. Set `CI_ENVIRONMENT=development` in `.env` for full stack trace. |
| **Inter-Service Failures** | `docker compose exec chege-jira curl -v http://ml-chege-jira:8000/api/v1/health` | Network bridge issue or wrong container alias. Check `docker-compose.yml` network definitions. |
| **403 Forbidden on ML Calls** | Inspect header in `app/Services/LlmService.php` | `ML_API_KEY` mismatch between WebApp and ML service containers. Verify both containers use the identical key in `.env`. |
| **ML Inference Out of Memory** | `docker compose exec ml-chege-jira ps aux`<br>`docker stats` | Model weight too large for allocated RAM. Switch active model to `phi3-mini` (2.2 GB) or expand swap space. |
| **Slow Query Performance** | MySQL Slow Query Log inside `mysql` container | Missing index on `task_id`, `sprint_id`, or `project_id`. Add migration with indexed foreign key. |
| **Dual-Engine Degraded State** | `curl -s http://localhost/health \| jq .resilience` | Redis socket timed out ($>50\text{ ms}$) or crashed. Check Redis logs (`docker compose logs redis`) and restart. |

---

## 🛠 Deep-Dive Diagnostic Procedures

### 1. Testing Inter-Service HTTP Communication Inside Containers
To test whether the WebApp can reach the FastAPI ML backend over the Docker bridge network:
```bash
docker compose exec chege-jira curl -s -H "X-API-Key: chege_jira_ml_super_secret_key_2026" http://ml-chege-jira:8000/api/v1/models/active
```
If this times out or returns connection refused, check that both services are joined to `chege-shared-network`.

### 2. Inspecting CodeIgniter Runtime Log Files
CodeIgniter logs daily error files in `services/web/writable/logs/`:
```bash
docker compose exec chege-jira tail -n 50 writable/logs/log-$(date +%Y-%m-%d).log
```

### 3. Validating MySQL Schema Integrity from ML Container
If the ML service throws SQLAlchemy column errors, run the schema guard manually:
```bash
docker compose exec ml-chege-jira python3 -c "
import asyncio
from app.db.session import async_session_factory
from app.db.schema_guard import validate_schema

async def check():
    async with async_session_factory() as session:
        ok = await validate_schema(session)
        print('Schema check passed:', ok)

asyncio.run(check())
"
```

### 4. Inspecting Background Task State in Redis
To inspect pending or failed async inference jobs stored in Redis:
```bash
docker compose exec redis redis-cli keys "task:*"
docker compose exec redis redis-cli get "task:<uuid>"
```
