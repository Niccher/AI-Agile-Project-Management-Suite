# Automated Testing Guide

This guide details test execution, coverage collection, and automated verification across WebApp, ML service, and documentation.

---

## 🧪 1. WebApp Testing (PHPUnit)

WebApp test suites reside in `services/web/tests/` and test CodeIgniter 4 controllers, filters, models, and session handlers.

### Execution Commands:
```bash
# Run the complete PHPUnit test suite
docker compose exec chege-jira ./vendor/bin/phpunit

# Run a specific controller test
docker compose exec chege-jira ./vendor/bin/phpunit tests/app/Controllers/HealthTest.php

# Run with test filter
docker compose exec chege-jira ./vendor/bin/phpunit --filter testHealthReturns200
```

---

## 🐍 2. ML Backend Testing (Pytest)

Pytest suites live in `services/ml/tests/`:
- **`tests/unit/`**: Validates isolated modules:
  - `test_prompt_builder.py`: Jinja2 template variable compilation
  - `test_llm_engine.py`: JSON extraction, token count estimation, context bounds
  - `test_security.py`: API key header and Bearer token parsing
  - `test_telemetry.py`: Metric collection resilience
- **`tests/integration/`**: Validates FastAPI endpoints and async task lifecycle with mock or active models.

### Execution Commands:
```bash
# Run all tests
make test-ml
# or: docker compose exec ml-chege-jira pytest tests/ -v

# Run only unit tests
docker compose exec ml-chege-jira pytest tests/unit/ -v

# Run with coverage report
docker compose exec ml-chege-jira pytest --cov=app --cov-report=term-missing tests/
```

---

## 📚 3. Documentation Quality Linting (`lint-docs.py`)

The repository includes a strict automated documentation linter (`scripts/lint-docs.py`) that enforces:
1. `README.md` length $\le 160$ lines.
2. Prevention of leaked code-editing instructions in the user `README.md`.
3. Resolution of all relative Markdown links across the entire repository.
4. Mermaid diagram block syntax sanity.
5. Prevention of committed credentials and local user path leaks (`/home/...`).

### Run Linter:
```bash
python3 scripts/lint-docs.py .
```
*(Runs automatically on pull requests via `ci/workflows/docs-verify.yml`).*
