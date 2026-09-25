# Release Process

This document describes versioning, tagging, and deployment promotions.

---

## 🏷 Versioning Scheme

The platform follows **Semantic Versioning** (`MAJOR.MINOR.PATCH`):
- `MAJOR`: Incompatible API contract changes or database schema overhauls.
- `MINOR`: New features (e.g. new AI capabilities, new Kanban view options).
- `PATCH`: Bug fixes, security patches, prompt template refinements.

---

## 📦 Release Checklist
1. Ensure all tests pass: PHPUnit in WebApp and Pytest in ML.
2. Verify database migrations run cleanly on fresh instances.
3. Update version strings in `services/ml/pyproject.toml` and `services/web/composer.json`.
4. Tag release:
   ```bash
   git tag -a v1.0.0 -m "Release v1.0.0: Unified Monorepo"
   git push origin v1.0.0
   ```
