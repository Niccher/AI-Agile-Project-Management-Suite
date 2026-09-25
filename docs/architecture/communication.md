# Communication & Networking

This document outlines all internal and external communication protocols, payload formats, and service discovery mechanisms within the suite.

---

## 🌐 Network Topography

All four containers participate in the private Docker bridge network `chege-shared-network`:

| Source Service | Target Service | Protocol / Port | Address | Purpose |
| :--- | :--- | :--- | :--- | :--- |
| **External Client** | `chege-jira` (WebApp) | HTTP / 80 | `http://<host-ip>:80` | Browser interface & web APIs |
| **External Client** | `ml-chege-jira` (ML) | HTTP / 8000 | `http://<host-ip>:8000/docs` | Swagger API & external testing |
| **WebApp** | `ml-chege-jira` (ML) | HTTP / 8000 | `http://ml-chege-jira:8000` | Synchronous & async AI requests |
| **WebApp** | `mysql` | TCP / 3306 | `mysql:3306` | CodeIgniter relational queries |
| **WebApp** | `redis` | TCP / 6379 | `redis:6379` | Session storage & cache |
| **ML Service** | `mysql` | TCP / 3306 | `mysql:3306` | Async SQLAlchemy data extraction |
| **ML Service** | `redis` | TCP / 6379 | `redis://redis:6379/0` | Async task status & queue |

---

## 🔐 WebApp ↔ ML Service Communication

The WebApp communicates with the ML Service using the `LlmService` class (`services/web/app/Services/LlmService.php`).

### 1. Authentication
All requests from WebApp to ML must include the shared API key header:
```http
X-API-Key: chege_jira_ml_super_secret_key_2026
```
Or as a Bearer token:
```http
Authorization: Bearer chege_jira_ml_super_secret_key_2026
```

### 2. Request / Response Lifecycle

```mermaid
sequenceDiagram
    autonumber
    actor User as User Browser
    participant Web as CodeIgniter WebApp
    participant ML as FastAPI ML Backend
    participant Redis as Redis Cache/Queue
    participant DB as MySQL Database

    User->>Web: Click "Generate Sprint Summary"
    Web->>ML: POST /api/v1/resources/sprints/{id}/summarise (sync=false)
    ML->>Redis: Enqueue background task (task_id: UUID)
    ML-->>Web: HTTP 202 Accepted {task_id, status: "pending"}
    Web-->>User: Display loading spinner with poll URL
    
    par Async Processing
        ML->>DB: Query sprint tasks & timelogs
        ML->>ML: Build Jinja2 prompt & execute GGUF inference
        ML->>Redis: Update task status {status: "completed", result: "..."}
    end
    
    loop Every 2 seconds
        User->>Web: GET /admin/ai/task-status/{task_id}
        Web->>ML: GET /api/v1/resources/background-tasks/{task_id}
        ML->>Redis: Fetch task payload
        ML-->>Web: HTTP 200 {status: "completed", result: "..."}
        Web-->>User: Render formatted Markdown summary
    end
```
