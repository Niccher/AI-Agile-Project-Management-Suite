# Release Management & Compatibility Matrix

This document defines versioning policies, compatibility requirements between WebApp, ML service, and database migrations, and the step-by-step release process.

---

## 🏷 Ecosystem Version Matrix

The suite enforces synchronization across multiple layers:

| Monorepo Release | WebApp (`services/web`) | ML Backend (`services/ml`) | API Endpoint Version | Migration Batch Floor | Compatibility Notes |
| :---: | :---: | :---: | :---: | :---: | :--- |
| **`v1.0.0`** | `1.0.0` | `0.1.0` | `/api/v1` | Batch 1 – 33 | Initial unified monorepo release with dual-engine session resilience and local quantized GGUF inference. |
| **`v1.1.0`** | `1.1.0` | `0.2.0` | `/api/v1` | Batch 1 – 35 | Sprint snapshot burndown enhancements; backwards compatible. |

---

## 📜 Semantic Versioning & Deprecation Policy

The suite adheres to **Semantic Versioning** (`MAJOR.MINOR.PATCH`):
- **MAJOR (`x.0.0`):** Incompatible API changes, breaking database schema refactors requiring manual migration steps, or replacement of core infrastructure engines.
- **MINOR (`1.x.0`):** New backward-compatible agile features, new AI prompt templates, or performance improvements.
- **PATCH (`1.0.x`):** Bug fixes, security patches, or documentation refinements.

### Deprecation Protocol
When an API endpoint or database column is scheduled for deprecation:
1. Announce deprecation in the minor release notes and mark endpoint with `deprecated: true` in `docs/api/openapi.yaml`.
2. Maintain the legacy endpoint for at least one minor release cycle before removing it in the next major version.

---

## 🚀 Release Train Checklist

Follow this checklist when preparing a production release:

1. **Pre-Flight Test Verification:**
   ```bash
   # Run WebApp test suite
   docker compose exec chege-jira ./vendor/bin/phpunit

   # Run ML test suite
   docker compose exec ml-chege-jira pytest tests/ -v

   # Run documentation quality linter
   python3 scripts/lint-docs.py .
   ```
2. **Database Migration Verification:**
   - Confirm migrations apply cleanly on a blank database:
     ```bash
     docker compose exec chege-jira php spark migrate --all
     ```
3. **Version Number Updates:**
   - Bump version in `services/ml/pyproject.toml`
   - Bump version in `docs/api/openapi.yaml`
4. **Tag Release:**
   ```bash
   git tag -a v1.0.0 -m "Release v1.0.0: Unified Monorepo"
   git push origin v1.0.0
   ```
5. **Publish Container Images (If GCP deployment):**
   - Push updated images to GCP Artifact Registry using the procedure in [Deployment Architecture](../architecture/deployment.md).
