# Engineering Contribution Guidelines

Thank you for contributing to the **AI-Agile-Project-Management-Suite**. This document outlines development standards, branch workflows, and PR expectations for all contributors.

---

## 🌿 Branching Strategy

Follow standard branch naming conventions:

| Prefix | Description | Example |
| :--- | :--- | :--- |
| `feature/` | New functionality or user-facing feature | `feature/burndown-enhancement` |
| `bugfix/` | Bug or issue resolution | `bugfix/session-failover-timeout` |
| `refactor/` | Code refactoring without changing behavior | `refactor/ml-reader-queries` |
| `docs/` | Documentation additions or updates | `docs/add-adr-storage` |

---

## 📝 Code Standards

### PHP (CodeIgniter 4 WebApp)
- Follow **PSR-12** formatting and CodeIgniter 4 framework conventions.
- Keep business logic in Services or Models; keep Controllers thin.
- Parameterize all database queries using the CodeIgniter Query Builder.
- Never hardcode URLs or environment credentials.

### Python (FastAPI ML Backend)
- Format code using **Ruff** (`ruff format` and `ruff check`).
- Ensure type hints are included for all route signatures and Pydantic schemas.
- Place all prompt templates in `services/ml/app/prompts/` as Jinja2 (`.j2`) files.
- Thread CPU-heavy inference tasks via `engine_manager` execution locks.

### Documentation (Markdown)
- Adhere to the [project-docs specification](../README.md).
- Keep operator instructions in `README.md` and user guides (`docs/user/`).
- Keep engineering architecture and code modification details in `docs/`.
- Validate with `python3 scripts/lint-docs.py .` before committing.

---

## 🚀 Pull Request Checklist

Before opening a PR, ensure:
1. All automated tests pass: PHPUnit, Pytest, and CI linter.
2. Code fulfills the [Definition of Done](making-changes.md).
3. PR template in [`.github/PULL_REQUEST_TEMPLATE.md`](../../.github/PULL_REQUEST_TEMPLATE.md) is filled out with a clear description and testing evidence.
4. No sensitive information or `.env` files are included in the git commit history.
