# Configuration Reference

This guide details all environment variables operators and system administrators may configure in `.env`.

---

## ⚙️ Environment Variables Table

| Variable | Service | Required | Default Value | Purpose |
| :--- | :--- | :---: | :--- | :--- |
| `CI_ENVIRONMENT` | WebApp | Yes | `production` | CodeIgniter environment mode (`production` or `development`). In production, debug bars are disabled. |
| `APP_ENV` | ML Service | Yes | `production` | Python application environment mode (`production` or `development`). Controls reload behavior and verbose logs. |
| `WEB_PORT` | Compose / WebApp | Yes | `80` | Host port on which the Nginx / CodeIgniter WebApp is bound and exposed. |
| `APP_URL` | WebApp | Yes | `http://localhost` | Public base URL used for email notifications, asset linking, and redirects. |
| `ML_PORT` | Compose / ML | Yes | `8000` | Host port on which the FastAPI ML microservice is published. |
| `ML_SERVICE_URL` | WebApp | Yes | `http://ml-chege-jira:8000` | Internal Docker network URL used by CodeIgniter `LlmService` to call FastAPI. |
| `ML_API_KEY` | WebApp & ML | Yes | `chege_jira_ml_super_secret_key_2026` | Shared secret key for authenticating inter-service communication via `X-API-Key` or Bearer token. |
| `DEFAULT_MODEL` | ML Service | No | `phi3-mini` | Name or key of the default GGUF model loaded on boot from `/app/models/`. |
| `N_THREADS` | ML Service | No | `4` | Number of CPU threads allocated to `llama-cpp-python` for inference. |
| `N_CTX` | ML Service | No | `4096` | Context window size in tokens for local LLM inference. |
| `N_GPU_LAYERS` | ML Service | No | `0` | Number of layers to offload to GPU (`0` defaults to CPU-only OpenBLAS execution). |
| `DB_HOST` | WebApp & ML | Yes | `mysql` | Internal Docker hostname for MySQL 8.4 container. |
| `DB_PORT` | WebApp & ML | Yes | `3306` | MySQL port (internal to private Docker network). |
| `DB_NAME` | WebApp & ML | Yes | `db_chege_jira` | MySQL database name. |
| `DB_USER` | WebApp & ML | Yes | `root` | MySQL user name. |
| `DB_ROOT_PASSWORD` | Database / All | Yes | `root_password` | Root administrative password for MySQL database. |
| `REDIS_URL` | WebApp & ML | Yes | `redis://redis:6379/0` | Connection string for Redis 7 session storage and async task queue. |
| `ADMIN_EMAIL` | WebApp Seeder | No | `admin@example.com` | Email address for default administrative account provisioned by `DemoSeeder`. |
| `ADMIN_USERNAME` | WebApp Seeder | No | `admin` | Username for default administrative account. |
| `ADMIN_PASSWORD` | WebApp Seeder | No | `admin_password_123` | Initial password for default administrative account. |

---

## 🔒 Security Best Practices for Operators

1. **Change Default Credentials:**
   In production environments, always change `DB_ROOT_PASSWORD`, `ML_API_KEY`, and `ADMIN_PASSWORD` before running migrations or exposing endpoints.
2. **Never Commit Secrets:**
   Ensure `.env` remains in `.gitignore`. Use `.env.example` as a template only.
3. **Internal Port Hardening:**
   Never map `3306` (MySQL) or `6379` (Redis) to the host in `docker-compose.yml`. Keep them isolated to the private Docker bridge network `chege-shared-network`.
