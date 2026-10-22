# Communication & Networking Protocols

This document specifies inter-service communication protocols, payload formats, authentication handshakes, and network flows across the suite.

---

## 🌐 Communication Matrix

| Source Service | Target Service | Transport | Auth Mechanism | Dev Base URL | Notes |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **Browser Client** | `chege-jira-webapp` | HTTP/HTTPS | Cookie Session + CSRF Token | `http://localhost:80` | Interactive web dashboard and AJAX APIs |
| **External Integrator** | `ml-chege-jira` | HTTP REST | `X-API-Key` or Bearer Token | `http://localhost:8000` | Direct Swagger API testing and ML telemetry |
| **WebApp** | `ml-chege-jira` | HTTP REST (JSON) | `X-API-Key` Header | `http://ml-chege-jira:8000` | Inter-service calls from `LlmService.php` |
| **WebApp** | `shared-redis` | Redis TCP Socket | None (Internal Network) | `redis:6379` | 50ms pre-flight socket probe; session & cache |
| **WebApp** | `shared-mysql` | MySQL Protocol | Password Credentials | `mysql:3306` | CodeIgniter relational queries |
| **WebApp** | `shared-minio` | HTTP S3 REST (AWSv4) | AWS4-HMAC-SHA256 | `http://minio:9000` | Pure-PHP client; uploads attachments, avatars, backups |
| **Browser Client** | `shared-minio` | HTTP/HTTPS | Pre-signed URL HMAC | `http://localhost:9000` | Direct binary downloads (when `S3_DIRECT_PRESIGNED=true`) |
| **ML Backend** | `shared-mysql` | Async MySQL Protocol | Password Credentials | `mysql:3306` | Async SQLAlchemy queries for entity extraction |
| **ML Backend** | `shared-redis` | Redis TCP Socket | None (Internal Network) | `redis://redis:6379/0` | Async task state & job tracking |
| **ML Backend** | `shared-minio` | HTTP S3 REST (boto3) | S3 Credentials | `http://minio:9000` | Reads task attachments for AI summarization & QA tests |
| **Browser / Operator** | `shared-dozzle` | HTTP / SSE | Anonymous / Simple Auth | `http://localhost:8888` | Real-time container log streaming and live resource metrics |
| **Dozzle** | **Docker Engine** | UNIX Domain Socket | Host File Permissions (RO) | `/var/run/docker.sock` | Read-only container discovery and stdout/stderr stream attach |

---

## 🔄 Interaction Sequences

### 1. User Authentication & Resilient Session Establishment

```mermaid
sequenceDiagram
    autonumber
    actor User as Web Browser
    participant Apache as Apache Web Server
    participant CI as CodeIgniter 4 WebApp
    participant Resilient as ResilientSessionHandler
    participant Redis as Redis 7 (In-Memory)
    participant MySQL as MySQL 8.4 (ci_sessions)

    User->>Apache: POST /login {username, password, csrf_token}
    Apache->>CI: Handle Request (mod_php / MPM)
    CI->>CI: Verify CSRF & Validate Password Hash
    CI->>Resilient: Initialize Session (write session id)
    Resilient->>Redis: 50ms Socket Probe (@fsockopen)
    
    alt Redis is Online (<= 50ms)
        Redis-->>Resilient: Socket Connected
        Resilient->>Redis: SETEX chege_jira_session:...
        Resilient-->>CI: Session Stored in Redis RAM (< 1ms)
    else Redis Offline or Degraded
        Resilient-->>Resilient: Fallback Activated
        Resilient->>MySQL: INSERT INTO ci_sessions (id, data, timestamp)
        Resilient-->>CI: Session Stored in MySQL (2-5ms)
    end

    CI-->>Apache: Set-Cookie: ci_session=...; HttpOnly; SameSite=Lax
    Apache-->>User: HTTP 302 Redirect to /dashboard
```

---

### 2. Asynchronous Sprint Retrospective Generation

```mermaid
sequenceDiagram
    autonumber
    actor Mgr as Scrum Master / Browser
    participant Web as CodeIgniter WebApp
    participant ML as FastAPI Backend
    participant Redis as Redis Task Queue
    participant DB as MySQL Database

    Mgr->>Web: Click "Generate AI Sprint Summary"
    Web->>ML: POST /api/v1/resources/sprints/12/summarise?sync=false
    Note over Web,ML: Header: X-API-Key: chege_jira_ml_super_secret_key_2026
    
    ML->>Redis: Enqueue Job (UUID: "task:8f9a...") with status="pending"
    ML-->>Web: HTTP 202 Accepted {"task_id": "8f9a...", "status": "pending"}
    Web-->>Mgr: Render Progress Bar with Polling URL
    
    par Asynchronous ML Inference Worker
        ML->>DB: Query sprint tasks, burndown snapshots & timelogs
        DB-->>ML: Aggregate sprint dataset
        ML->>ML: Compile Jinja2 prompt (summarise_sprint.j2)
        ML->>ML: Execute llama-cpp-python inference
        ML->>Redis: Update job state: status="completed", result="..."
        ML->>DB: Persist summary in ai_sprint_summaries
    end

    loop Every 2 Seconds Polling
        Mgr->>Web: GET /admin/ai/task-status/8f9a...
        Web->>ML: GET /api/v1/resources/background-tasks/8f9a...
        ML->>Redis: Fetch job payload
        ML-->>Web: HTTP 200 {"status": "completed", "result": "Markdown text..."}
        Web-->>Mgr: Display Formatted Sprint Retrospective
    end
```

---

### 3. Synchronous Task Enhancement

```mermaid
sequenceDiagram
    autonumber
    actor Dev as Developer / Browser
    participant Web as CodeIgniter WebApp
    participant ML as FastAPI Backend
    participant Llama as LlamaEngine (In-Memory GGUF)

    Dev->>Web: Click "AI Enhance Description" on Task #42
    Web->>ML: POST /api/v1/resources/tasks/42/enhance (sync=true)
    ML->>ML: Compile prompt (enhance_task.j2)
    ML->>Llama: Run inference with temperature=0.2
    Llama-->>ML: Generated acceptance criteria & refined description
    ML-->>Web: HTTP 200 OK {"task_id": 42, "enhanced_description": "...", "criteria": [...]}
    Web-->>Dev: Populate Task Form with Generated Criteria
```

---

### 4. S3 Object Storage: Upload & Dual-Delivery Download Sequence

```mermaid
sequenceDiagram
    autonumber
    actor User as Web Browser / Client
    participant Controller as TaskAttachmentApiController
    participant Storage as StorageService (Pure-PHP AWSv4)
    participant MinIO as MinIO S3 (:9000/:9001)
    participant Disk as Local Disk (writable/uploads/)

    Note over User,MinIO: File Upload Flow
    User->>Controller: POST /api/tasks/10/attachments (multipart/form-data)
    Controller->>Disk: Store temporary file (move_uploaded_file)
    Controller->>Storage: uploadFile(bucket, s3Key, localPath, mime)
    Storage->>MinIO: PUT /agile-attachments/s3Key (AWS4-HMAC-SHA256)
    MinIO-->>Storage: HTTP 200 OK (ETag header)
    Controller->>Controller: Save s3_key, file_size in database
    Controller-->>User: HTTP 201 Created {"success": true, "attachment_id": 99}

    Note over User,MinIO: File Download Flow (Dual-Delivery Mode)
    User->>Controller: GET /api/tasks/attachments/99/download
    Controller->>Controller: Validate task view permission

    alt S3_DIRECT_PRESIGNED=true (Offload Bandwidth)
        Controller->>Storage: getPresignedUrl(bucket, s3Key, expires=3600)
        Storage-->>Controller: Presigned S3 GET URL with signature query params
        Controller-->>User: HTTP 307 Temporary Redirect -> http://localhost:9000/agile-attachments/...
        User->>MinIO: GET /agile-attachments/s3Key?X-Amz-Signature=...
        MinIO-->>User: HTTP 200 OK (File bytes delivered directly)
    else S3_DIRECT_PRESIGNED=false (Streaming Proxy Mode)
        Controller->>Storage: getObject(bucket, s3Key)
        Storage->>MinIO: GET /agile-attachments/s3Key (AWS4-HMAC-SHA256)
        MinIO-->>Storage: HTTP 200 OK (Binary stream)
        Storage-->>Controller: Binary data stream
        Controller-->>User: HTTP 200 OK (Content-Disposition: attachment)
    end
```

---

## 📐 API Versioning & Error Convention

### Versioning
All REST endpoints exposed by the ML microservice are prefixed with `/api/v1`. Breaking changes require a distinct version namespace (`/api/v2`).

### Error Response Schema
All error responses return standard JSON bodies:
```json
{
  "detail": {
    "error_code": "MODEL_NOT_LOADED",
    "message": "No active GGUF model is currently loaded in memory.",
    "resolution": "Execute POST /api/v1/models/load with an available model name."
  }
}
```
