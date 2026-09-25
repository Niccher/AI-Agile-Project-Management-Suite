# Threat Model & Security Architecture

This document defines trust boundaries, sensitive data assets, threat mitigations, and access controls for the platform.

---

## 🛡 Trust Boundaries

```text
[ UNTRUSTED ZONE: Public Internet ]
           │
           │ (HTTPS / HTTP Port 80)
           ▼
┌──────────────────────────────────────────────┐
│ WebApp (Nginx / PHP-FPM)                     │
│ - CSRF Protection (CodeIgniter Security)     │
│ - Session Guard (Redis / Shield Auth)        │
│ - Input Sanitization & SQL Parameterization  │
└──────────────────────┬───────────────────────┘
                       │
                       │ (Internal Network: chege-shared-network)
                       ▼
┌──────────────────────────────────────────────┐
│ [ PRIVILEGED ZONE: Microservice & Storage ]  │
│                                              │
│  FastAPI ML Service (Port 8000)              │
│  - X-API-Key / Bearer Token Enforcement      │
│  - Local CPU/GPU Sandboxed Execution         │
│                                              │
│  MySQL Database (Port 3306 - No Host Access) │
│  Redis Engine   (Port 6379 - No Host Access) │
└──────────────────────────────────────────────┘
```

---

## 🔒 Key Mitigations

1. **Port Isolation:**
   - MySQL (3306) and Redis (6379) are unexposed on the host interface. Even if the host server runs an open firewall, database and cache ports cannot be probed from the outside.
2. **API Key Guard:**
   - Every administrative and ML endpoint in FastAPI (`/api/v1/resources/*`, `/api/v1/admin/*`) is validated by `app/core/security.py`. Requests lacking a matching `X-API-Key` or `Authorization: Bearer` are immediately rejected with HTTP 403 Forbidden.
3. **Role-Based Access Control (RBAC):**
   - Three strict tiers: `admin`, `manager`, and `user`.
   - Admin routes (`/admin/*`) are protected by CodeIgniter Shield filters and session checks.
   - Managers cannot alter system configurations or access telemetry.
   - Users are restricted to their assigned tasks and timelog submissions.
