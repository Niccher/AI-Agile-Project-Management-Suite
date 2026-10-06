# Security & Defensive Engineering Guidelines

This document outlines security protocols, defensive coding standards, and vulnerability mitigation guidelines for engineers maintaining the repository.

---

## 🔒 1. Secrets & Credential Governance

- **Never Commit Secrets:** `.env` and production certificates must never be added to Git. Keep them in `.gitignore`.
- **Placeholder Values in Repositories:** Use `.env.example` with harmless default placeholders (`root_password`, `admin_password_123`).
- **Rotation Procedure:** Rotate `ML_API_KEY` periodically. When changing, update `.env` and restart both application containers:
  ```bash
  docker compose restart ml-chege-jira chege-jira
  ```

---

## 🛡 2. Port Binding & Network Hardening

- **Private Network Isolation:** Relational database (`mysql`) and cache (`redis`) containers must **never** specify `ports:` in `docker-compose.yml`. Use `expose:` to restrict them to the internal bridge network `chege-shared-network`.
- **Public Edge:** Only WebApp (`80`) and ML Swagger (`8000`) should bind to host interfaces. In production, place Nginx with TLS termination (Port 443) in front of Port 80 and restrict Port 8000 to trusted internal IP ranges.

---

## 🔐 3. Authentication & Session Defense

### WebApp (CodeIgniter 4)
- **CSRF Tokens:** All state-changing requests (`POST`, `PUT`, `DELETE`) require a valid CSRF token verified by CodeIgniter's Security filter.
- **Session Security:** Cookies must use `HttpOnly`, `SameSite=Lax`, and `Secure` (in HTTPS production).
- **Password Storage:** Managed via CodeIgniter Shield using adaptive `PASSWORD_BCRYPT` hashing with high work factors.

### ML Microservice (FastAPI)
- **Dependency Guard:** Every protected endpoint requires `Depends(verify_api_key)`.
- **Timing-Attack Resistance:** The API key validation compares secrets using constant-time string comparison (`secrets.compare_digest`).

---

## 🛡 4. Input Sanitization & Injection Prevention

- **SQL Injection:** Raw SQL string concatenation is strictly prohibited. All queries must use CodeIgniter 4 Query Builder or SQLAlchemy parameterized statements.
- **Cross-Site Scripting (XSS):** All dynamic variables rendered in PHP views must be escaped with `esc($variable, 'html')`.
- **Prompt Injection Defense:** Jinja2 prompt templates (`services/ml/app/prompts/`) sanitize user input and wrap untrusted content in clearly demarcated XML/Markdown tags (e.g. `<task_content>{{ description }}</task_content>`) to prevent prompt hijacking.
