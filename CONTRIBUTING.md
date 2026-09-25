# Contributing to AI-Agile-Project-Management-Suite

Thank you for your interest in contributing to **AI-Agile-Project-Management-Suite**! We welcome bug fixes, documentation improvements, new agile features, and prompt optimizations from the open-source community.

---

## 🧭 Code of Conduct

All contributors are expected to uphold our standards of conduct. Please review our [Code of Conduct](CODE_OF_CONDUCT.md) before participating.

---

## 🛠 Getting Started

### 1. Fork & Clone
```bash
git clone https://github.com/<your-username>/AI-Agile-Project-Management-Suite.git
cd AI-Agile-Project-Management-Suite
```

### 2. Branching Model
Create a dedicated feature branch from `master`:
- `feat/kanban-swimlanes` (for new features)
- `fix/timelog-export-500` (for bug fixes)
- `docs/gcp-cloud-sql-guide` (for documentation)
- `perf/llama-context-caching` (for performance improvements)

```bash
git checkout -b feat/your-feature-name
```

---

## 🧪 Development & Coding Standards

### 1. WebApp (PHP 8.3 / CodeIgniter 4)
- **Style:** Follow **PSR-12** formatting and CodeIgniter 4 conventions.
- **Linting:** Run `php -l` on modified files to ensure zero syntax errors.
- **Testing:** Add PHPUnit tests in `services/web/tests/`:
  ```bash
  docker compose exec chege-jira ./vendor/bin/phpunit
  ```

### 2. ML Inference Backend (Python 3.12 / FastAPI)
- **Style:** Adhere to **PEP 8** and modern type hinting.
- **Linting & Formatting:** Formatted via **Ruff**:
  ```bash
  ruff check services/ml/app
  ruff format services/ml/app
  ```
- **Testing:** Add unit and integration tests in `services/ml/tests/`:
  ```bash
  make test-ml
  # or: docker compose exec ml-chege-jira pytest tests/
  ```

### 3. Docker Compose & Environment
- Validate Docker syntax whenever changing `docker-compose.yml`:
  ```bash
  docker compose config
  ```
- Never commit `.env` or service account credentials.

---

## 🔒 Privacy & Synthetic Test Data

To preserve privacy and prevent leakage of proprietary project descriptions or personal data:
- **Never commit real company tasks, customer names, or API keys.**
- Use our built-in synthetic agile data generator for development and testing:
  ```bash
  python3 scripts/generate_synthetic_agile_data.py --count 100
  ```

---

## 🚀 Submitting a Pull Request (PR)

1. **Keep PRs Focused:** Limit PRs to a single feature or bug fix.
2. **Fill Out the PR Template:** Complete all items in `.github/PULL_REQUEST_TEMPLATE.md`.
3. **Verify CI Passes:** Ensure GitHub Actions automated tests and linters pass without errors.
