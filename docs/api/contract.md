# REST API Contract Reference

The **ML Backend** (`ml-chege-jira`) exposes an asynchronous REST API under the `/api/v1` namespace for local LLM inference, background job execution, and telemetry.

---

## 🔐 Authentication

All endpoints (except `GET /api/v1/health`) require authentication using the shared `ML_API_KEY`. Requests can pass the key using either:

1. **Custom Header (Recommended):**
   ```http
   X-API-Key: chege_jira_ml_super_secret_key_2026
   ```
2. **Authorization Header:**
   ```http
   Authorization: Bearer chege_jira_ml_super_secret_key_2026
   ```

Requests failing validation receive:
```json
{
  "detail": "Invalid or missing API key"
}
```
*(HTTP 403 Forbidden)*

---

## 1. Health & Service Vitals

### `GET /api/v1/health`
Liveness probe checking database connectivity and loaded model state.

- **Security:** Public (No API key required)
- **Response `200 OK`:**
```json
{
  "status": "ok",
  "version": "0.1.0",
  "app_env": "production",
  "default_model": "phi3-mini",
  "model_loaded": true,
  "database_connected": true
}
```

---

## 2. Telemetry & Hardware

### `GET /api/v1/admin/telemetry`
Returns live system metrics from the host container via `psutil`.

- **Response `200 OK`:**
```json
{
  "status": "online",
  "timestamp": "2026-10-06T08:30:00Z",
  "cpu_percent": 14.2,
  "cpu_cores": 4,
  "ram_used_bytes": 1073741824,
  "ram_total_bytes": 8589934592,
  "ram_used_human": "1.00 GB",
  "ram_percent": 12.5,
  "disk_percent": 28.4,
  "db_connected": true,
  "db_threads": 3
}
```

---

## 3. Model Management

### `GET /api/v1/models`
Lists all supported model entries and their download status in `/app/models`.

### `GET /api/v1/models/active`
Returns runtime details of the currently loaded in-memory GGUF model.

### `POST /api/v1/models/load`
Dynamically loads or hot-swaps an in-memory model.
- **Request Body:**
```json
{
  "model_name": "mistral-7b"
}
```
- **Response `200 OK`:**
```json
{
  "success": true,
  "message": "Model mistral-7b loaded successfully",
  "active_model": "mistral-7b"
}
```

---

## 4. Agile AI Resources

### `POST /api/v1/tasks/{task_id}/enhancements`
Reads task specifications from MySQL, generates Gherkin acceptance criteria via LLM, and persists the record into `ai_task_enhancements`.

- **Request Body:**
```json
{
  "model": "phi3-mini",
  "temperature": 0.2,
  "max_tokens": 1024
}
```
- **Response `201 Created`:**
```json
{
  "success": true,
  "data": {
    "id": 14,
    "task_id": 42,
    "summary": "Implement automated sprint burndown calculations",
    "acceptance_criteria": [
      "Given an active sprint, when story points change, velocity updates within 500ms",
      "Burndown trendline charts ideal vs actual remaining points"
    ],
    "story_points": 5,
    "priority": "high"
  },
  "meta": {
    "model_used": "phi3-mini",
    "tokens_used": 348,
    "duration_ms": 1280.5
  }
}
```

### `POST /api/v1/tasks/{task_id}/priority-suggestions`
Predicts priority rating (`low`, `medium`, `high`, `critical`) based on project velocity and complexity.

### `POST /api/v1/sprints/{sprint_id}/summaries`
Consolidates all sprint tasks, burndown snapshots, and blockers into an executive retrospective report.

- **Request Body:**
```json
{
  "model": "mistral-7b",
  "custom_focus": "Identify bottlenecks in Code Review stage"
}
```
- **Response `201 Created`:**
```json
{
  "success": true,
  "data": {
    "id": 8,
    "sprint_id": 12,
    "executive_summary": "Sprint 14 completed 42 of 48 committed story points...",
    "key_achievements": ["Delivered user notification bell", "Upgraded MySQL to 8.4"],
    "blockers_identified": ["Redis socket timeout during load test"],
    "action_items": ["Implement 50ms pre-flight socket probe"]
  }
}
```

### `POST /api/v1/projects/{project_id}/wiki-pages`
Generates a markdown documentation page grounded in project tasks and architecture.

### `POST /api/v1/time-reports`
Analyzes team member timelog records across a date range and surfaces work discrepancies.

### `POST /api/v1/qa`
Executes retrieval-augmented questions and answers against project wiki articles.

---

## 5. Asynchronous Background Jobs

### `POST /api/v1/async-tasks/generate`
Offloads heavy prompt generations to a background thread tracked in Redis.

- **Response `202 Accepted`:**
```json
{
  "task_id": "9b1deb4d-3b7d-4bad-9bdd-2b0d7b3dcb6d",
  "status": "pending",
  "message": "Task queued successfully"
}
```

### `GET /api/v1/async-tasks/{task_id}`
Returns current job execution state and generated result.

- **Response `200 OK`:**
```json
{
  "task_id": "9b1deb4d-3b7d-4bad-9bdd-2b0d7b3dcb6d",
  "status": "completed",
  "result": {
    "summary": "Executive retrospective details...",
    "velocity_score": 88.5
  }
}
```
