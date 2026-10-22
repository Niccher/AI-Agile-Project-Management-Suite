# Operational Runbook: Log Inspection & Real-Time Debugging

This runbook provides Standard Operating Procedures (SOPs) for developers and site reliability engineers diagnosing container issues, inspecting live request lifecycles, and profiling resource utilization across the **AI-Agile-Project-Management-Suite**.

---

## 🖥️ Live Observability Dashboard (Dozzle)

The suite bundles **Dozzle** (`shared-dozzle`), an ultra-lightweight, zero-dependency real-time log viewer and container monitor accessible via web browser.

- **Web UI URL:** [http://localhost:8888](http://localhost:8888) (or `http://<HOST_IP>:8888`)
- **Default Authentication:** Anonymous in local development (`DOZZLE_AUTH_PROVIDER=none`). For credential gating in production, configure `DOZZLE_AUTH_PROVIDER=simple` in `.env`.
- **Docker Daemon Socket:** Attached strictly read-only (`/var/run/docker.sock:ro`) with no host container mutation capabilities.

---

## 🔍 Common Debugging Scenarios

### 1. Tracing Cross-Service AI Copilot Invocations

When a user initiates an AI task (e.g., Sprint Retrospective, Story Point Estimation, or Timelog Anomaly Analysis), the request crosses three container boundaries:
1. `chege-jira-webapp`: Formulates request payload in `LlmService.php`.
2. `ml-chege-jira`: Assembles Jinja2 prompts and streams tokens via `llama-cpp-python`.
3. `shared-redis`: Enqueues task state and emits progress updates.

**Dozzle Split-View Procedure:**
1. Open [http://localhost:8888](http://localhost:8888).
2. Click **`chege-jira-webapp`** from the left navigation panel.
3. Hold `Shift` (or click the split icon in the top right) and select **`ml-chege-jira`**.
4. Both log streams will synchronize side-by-side in real time.

---

### 2. Filtering for Application Errors & Exceptions

Use Dozzle's real-time regex search bar at the top of the stream to isolate error states:

| Search Pattern | Purpose / Error Type |
| :--- | :--- |
| `CRITICAL\|ERROR\|Exception` | Highlights unhandled PHP exceptions and Python tracebacks. |
| `HTTP\/1\.[01]\" 50[0-9]` | Filters Apache access logs for server-side HTTP 500/502/503 errors. |
| `SQLSTATE\|Deadlock` | Detects database query syntax errors or MySQL InnoDB transaction deadlocks. |
| `OOM\|Killed` | Identifies container out-of-memory terminations. |
| `X-API-Key` | Inspects unauthorized or rejected ML backend authentication handshakes. |

---

### 3. Monitoring Real-Time RAM & CPU Spikes

Dozzle displays continuous memory and CPU telemetry beside each container name:

- **`ml-chege-jira`:** Memory will rise to ~1.2 GB–2.5 GB when loading a GGUF model (`phi3-mini` or `llama-3.2-3b`). If container memory reaches the host limit, Dozzle flags the spike immediately.
- **`shared-redis`:** Memory should stay under 20 MB during ordinary session caching. Any continuous linear climb suggests un-garbage-collected keys.
- **`chege-jira-webapp`:** Apache MPM workers will scale CPU during peak report generations or database migrations.

---

### 4. Downloading Offline Diagnostic Log Bundles

If submitting an issue report or analyzing an incident post-mortem:
1. In Dozzle, open the desired container log view.
2. Click the **Download** icon (tray with down arrow) in the top-right toolbar.
3. Save the `.log` text file to your local machine for offline inspection.

---

## ⌨️ CLI Fallbacks (When Headless or Without Web Access)

If operating on a remote server over SSH without port forwarding:

```bash
# Stream all project container logs simultaneously
docker compose logs -f --tail=50

# Stream only WebApp logs with timestamps
docker compose logs -f --tail=100 -t chege-jira

# Stream only FastAPI ML microservice logs
docker compose logs -f --tail=100 -t ml-chege-jira

# Inspect recent MySQL startup and query errors
docker compose logs mysql 2>&1 | tail -50

# Inspect MinIO S3 object access logs
docker compose logs minio 2>&1 | tail -50
```
