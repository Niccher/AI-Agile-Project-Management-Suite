# Threat Model & Security Architecture

This document defines trust boundaries, sensitive data assets, STRIDE threat analyses, and defensive mitigations for the **AI-Agile-Project-Management-Suite**.

---

## 🛡 Trust Boundaries & Network Zones

```text
[ UNTRUSTED ZONE: Public Internet / LAN ]
                   │
                   │ (HTTP / HTTPS Port 80)
                   ▼
┌────────────────────────────────────────────────────────┐
│ PUBLIC EDGE: Nginx & WebApp (services/web)             │
│ - CSRF Token Verification on POST/PUT requests         │
│ - Secure Session Cookie Flags (HttpOnly, SameSite=Lax) │
│ - Input Sanitization & Parameterized Query Binding     │
└──────────────────────────┬─────────────────────────────┘
                           │
                           │ (Internal Bridge Network: chege-shared-network)
                           ▼
┌────────────────────────────────────────────────────────┐
│ PRIVILEGED APPLICATION ZONE                            │
│                                                        │
│  FastAPI ML Microservice (Port 8000)                   │
│  - X-API-Key & Bearer Token Authentication             │
│  - Local Sandboxed CPU/GPU Execution                   │
│                                                        │
│  MySQL Database (Port 3306 - No Host Binding)          │
│  Redis In-Memory Engine (Port 6379 - No Host Binding)  │
└────────────────────────────────────────────────────────┘
```

---

## 📊 STRIDE Threat Analysis & Mitigations

| Threat Category | Potential Attack Vector | Impact | Mitigations Implemented in Code |
| :--- | :--- | :--- | :--- |
| **Spoofing** | Forging inter-service HTTP requests from untrusted network clients to the ML backend. | Unauthorized triggering of heavy LLM inference jobs. | `app/core/security.py` enforces `X-API-Key` or `Authorization: Bearer` on every protected endpoint. Unauthenticated calls receive HTTP 403 Forbidden. |
| **Tampering** | SQL injection attempts via task filters or Kanban search terms. | Data corruption, unauthorized data modification. | Strict CodeIgniter 4 Query Builder parameterization and SQLAlchemy prepared statements. Direct raw string interpolation is prohibited. |
| **Repudiation** | Users altering or deleting logged timelogs, sprint completions, or tasks without attribution. | Disputed engineering hours and unverified sprint changes. | System writes changes to `audit_logs` migration table (`2026-03-13-010000_CreateAuditLogsTable.php`) tracking timestamp, user ID, IP address, and changed fields. |
| **Information Disclosure** | Sniffing internal database traffic or leaking proprietary prompts. | Exposure of trade secrets and user credentials. | MySQL (3306) and Redis (6379) ports are unexposed on the host interface. All LLM inference executes locally via GGUF weights; zero prompts leave the container. |
| **Denial of Service** | Flooding ML endpoints with unbounded token generation requests, starving host CPU cores. | CPU exhaustion, system unresponsiveness. | `N_CTX=4096` token ceilings, `N_THREADS=4` core caps, and asynchronous task offloading via Redis `TaskManager`. In-flight jobs are bounded by thread locks. |
| **Elevation of Privilege** | Insecure Direct Object References (IDOR) where a standard user edits another team's tasks or views private wikis. | Unauthorized administrative access or project leakage. | Role-Based Access Control (RBAC) enforced via Shield filters. Controllers check `$project->manager_id` and `$task->assignee_id` before mutations. |

---

## 🔒 Role-Based Access Control (RBAC) Hierarchy

The platform implements three strict privilege tiers:

1. **Admin (`admin`):**
   - Full control over system settings, user account provisioning, audit logs, and AI telemetry dashboards (`/admin/*`).
   - Ability to configure AI models and adjust runtime inference parameters.
2. **Manager (`manager`):**
   - Project lifecycle governance, sprint planning, backlog grooming, and team assignment.
   - Review and approval authority for completed tasks and timelog reports.
3. **User (`user`):**
   - Standard team contributor with permissions to move assigned Kanban cards, submit work logs, and request AI task enhancement.
   - Restricted from altering project configurations or viewing other tenants' private wikis.

---

## 🔑 Credential & Secret Management Guidelines

- **Zero Hardcoded Secrets in Source:** All keys, passwords, and tokens are read dynamically from environment variables (`.env`).
- **Secret Rotation Protocol:** In the event of an API key compromise, rotate `ML_API_KEY` in `.env` and execute:
  ```bash
  docker compose restart ml-chege-jira chege-jira
  ```
- **Session Protection:** Session identifiers in `ci_sessions` and Redis are hashed, cryptographically random 128-character strings with automatic TTL expiry.
