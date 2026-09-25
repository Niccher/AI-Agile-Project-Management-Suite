# Troubleshooting & Diagnostics

This guide provides fast remedies for common deployment and runtime issues.

---

## 🚨 Common Issues & Resolutions

### 1. WebApp Returns HTTP 500 Error
- **Cause:** Database migration not applied or Redis session connection failure.
- **Diagnostics:**
  ```bash
  docker compose logs chege-jira 2>&1 | tail -50
  docker compose exec chege-jira cat writable/logs/log-$(date +%Y-%m-%d).log
  ```
- **Fix:** Run migrations manually: `docker compose exec chege-jira php spark migrate --all`.

### 2. ML Backend Returns HTTP 503 / Model Not Loaded
- **Cause:** No GGUF model file downloaded in `services/ml/models/`.
- **Diagnostics:**
  ```bash
  docker compose exec ml-chege-jira ls -lh /app/models
  curl http://localhost:8000/api/v1/models/active
  ```
- **Fix:** Download model: `bash services/ml/scripts/download_model.sh phi3-mini`.

### 3. Port 80 or 8000 Already in Use on Host
- **Diagnostics:**
  ```bash
  sudo lsof -i :80
  sudo lsof -i :8000
  ```
- **Fix:** Edit `.env` to set alternate host ports:
  ```env
  WEB_PORT=8080
  ML_PORT=8001
  ```
  Then reload: `docker compose up -d`.
