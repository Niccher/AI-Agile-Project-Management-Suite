# Security Policy

The **AI-Agile-Project-Management-Suite** team is committed to ensuring the safety, privacy, and integrity of enterprise agile workflows, project telemetry, and local AI model inference.

---

## 🔒 Zero-Data-Leak Commitment

1. **Local-Only AI Execution:**
   - All AI copilot capabilities (sprint summaries, task enhancement, timelog analytics, and Q&A) execute strictly on local CPU/GPU hardware using quantized GGUF weights.
   - No project titles, descriptions, timelogs, user profiles, or codebase tokens are transmitted to external third-party APIs (such as OpenAI, Anthropic, or external telemetric services).
2. **Network Isolation:**
   - Database (MySQL 8.4) and caching (Redis 7) ports are strictly non-public (`expose` only) and never bound to public host interfaces by default.
3. **Hardened Credentials:**
   - API endpoints enforce header-based token verification (`X-API-Key` or `Authorization: Bearer`).

---

## 🛡 Supported Versions

| Version | Supported | Security Patch SLA |
| :--- | :--- | :--- |
| **1.0.x** | ✅ Yes | Within 48 hours |
| **< 1.0.0** | ❌ No | Please upgrade to v1.0.0+ |

---

## 🚨 Reporting a Vulnerability

If you discover a security vulnerability within this repository, **do not open a public GitHub issue.** Public disclosure puts production deployments at risk.

Instead, report vulnerabilities privately by emailing:
📧 **domi777nicch@gmail.com**

Please include:
1. Description of the vulnerability and attack vector.
2. Steps to reproduce or proof-of-concept (PoC) code.
3. Potential impact on WebApp, ML Microservice, or Database instances.
4. Any suggested fixes or mitigations.

### Our Commitment:
- **Acknowledgement:** We will acknowledge receipt of your vulnerability report within **24 hours**.
- **Assessment & Fix:** We will assess severity and prepare a patched release within **48–72 hours**.
- **Credit:** We will publicly credit you in the release notes and advisory (unless you prefer anonymity).
