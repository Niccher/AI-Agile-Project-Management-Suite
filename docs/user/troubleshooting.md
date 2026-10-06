# Operator Troubleshooting Guide

This guide provides immediate solutions for runtime, networking, and containerization issues encountered when starting or operating the stack.

---

## 🚨 Common Symptoms & Solutions

### 1. Port 80 or 8000 is Already in Use on Host

- **Symptom:** Docker Compose throws `Error starting userland proxy: listen tcp4 0.0.0.0:80: bind: address already in use`.
- **Diagnosis:**
  ```bash
  sudo lsof -i :80
  sudo lsof -i :8000
  ```
- **Solution:** Edit `.env` to map alternate host ports:
  ```env
  WEB_PORT=8080
  ML_PORT=8001
  ```
  Then reload containers:
  ```bash
  docker compose up -d
  ```
  Access the web dashboard at `http://localhost:8080` and Swagger at `http://localhost:8001/docs`.

---

### 2. Docker Daemon is Not Running

- **Symptom:** `Cannot connect to the Docker daemon at unix:///var/run/docker.sock. Is the docker daemon running?`
- **Solution:**
  - On Linux:
    ```bash
    sudo systemctl start docker
    sudo systemctl enable docker
    ```
  - On macOS / Windows: Start the Docker Desktop application and wait for the engine icon to turn green.

---

### 3. Database Container Exits or Health Probe Times Out

- **Symptom:** `deploy.sh` waits indefinitely for MySQL health probe or aborts after timeout.
- **Diagnosis:**
  ```bash
  docker compose logs mysql
  ```
- **Solution:**
  - Verify free disk space: `df -h`. MySQL fails to initialize if host disk space is $< 5\%$.
  - Clean stale database container if corruption occurred:
    ```bash
    docker compose down
    docker volume rm ai-agile-project-management-suite_mysql-data
    docker compose up -d mysql
    ```
    *(Note: removing the volume deletes previous database records).*

---

### 4. WebApp Displays `500 Internal Server Error` or Database Exception

- **Symptom:** Accessing `http://localhost` displays a blank white screen or standard CodeIgniter error page.
- **Diagnosis:**
  ```bash
  docker compose logs chege-jira --tail=50
  docker compose exec chege-jira ls -la writable/logs/
  ```
- **Solution:** Migrations or seeders were likely skipped. Execute them manually:
  ```bash
  docker compose exec chege-jira php spark migrate --all
  docker compose exec chege-jira php spark db:seed DemoSeeder
  ```

---

### 5. Permission Denied on `writable/` Directory

- **Symptom:** CodeIgniter logs `Unable to write file: /var/www/html/writable/cache/...`
- **Solution:** Enforce container file permissions:
  ```bash
  sudo chown -R 33:33 services/web/writable
  sudo chmod -R 775 services/web/writable
  ```
  *(Or execute `bash scripts/deploy.sh` which enforces this automatically).*

---

### 6. ML Service Reports Model Not Loaded (`HTTP 503`)

- **Symptom:** AI endpoints return `Model not loaded. Please download or select an active GGUF model.`
- **Diagnosis:** Check if a model file exists in `services/ml/models/`:
  ```bash
  ls -lh services/ml/models/
  ```
- **Solution:** Download a recommended quantized model:
  ```bash
  bash services/ml/scripts/download_model.sh phi3-mini
  ```
  The ML backend hot-detects new models placed in `services/ml/models/`.

---

### 7. Dual-Engine Alert: `Storage Degraded: MySQL Fallback Active`

- **Symptom:** WebApp admin navigation bar displays a warning badge indicating fallback is active.
- **Diagnosis:** The 50ms pre-flight socket probe detected that Redis is temporarily offline or unreachable:
  ```bash
  docker compose ps redis
  curl -s http://localhost/health | grep resilience
  ```
- **Solution:** Restart the Redis container:
  ```bash
  docker compose restart redis
  ```
  The application self-heals and immediately re-engages Redis in-memory acceleration on the very next HTTP request without dropping active user sessions.
