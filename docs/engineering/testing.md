# Testing Guide

This document explains automated test execution across both services.

---

## 🧪 WebApp Testing (PHPUnit)

PHPUnit test cases reside in `services/web/tests/`.

### Run tests inside container:
```bash
docker compose exec chege-jira ./vendor/bin/phpunit
```

### Run specific test suite:
```bash
docker compose exec chege-jira ./vendor/bin/phpunit tests/app/Controllers/HealthTest.php
```

---

## 🐍 ML Service Testing (Pytest)

Pytest suites live in `services/ml/tests/`:
- `tests/unit/`: Tests for `prompt_builder`, `llm_engine`, `security`, and `telemetry`.
- `tests/integration/`: Tests for API endpoints and Redis background tasks.

### Run tests inside container:
```bash
make test-ml
# or: docker compose exec ml-chege-jira pytest tests/
```

### Run with coverage report:
```bash
docker compose exec ml-chege-jira pytest --cov=app --cov-report=term-missing tests/
```
