# Making Changes & Feature Development

This guide establishes the engineering protocol for implementing features, modifying database schemas, and safely updating API contracts across the monorepo.

---

## 🎯 Task Matrix: Where to Make Changes

| If you want to… | Touch These Files | Verification Steps |
| :--- | :--- | :--- |
| **Add a WebApp Route / Page** | `services/web/app/Config/Routes.php`<br>`services/web/app/Controllers/`<br>`services/web/app/Views/` | Run PHP syntax check (`php -l`) and load page in browser. |
| **Add a WebApp Model / Entity** | `services/web/app/Models/`<br>`services/web/app/Entities/` | Write PHPUnit model test in `services/web/tests/`. |
| **Add an AI Feature or Prompt** | `services/ml/app/prompts/*.j2`<br>`services/ml/app/core/prompt_builder.py` | Run unit test in `services/ml/tests/unit/test_prompt_builder.py`. |
| **Add a New ML API Endpoint** | `services/ml/app/api/v1/resources/`<br>`services/ml/app/schemas/`<br>`docs/api/contract.md`<br>`docs/api/openapi.yaml` | Add integration test in `services/ml/tests/integration/`. |
| **Call ML from WebApp** | `services/web/app/Services/LlmService.php`<br>Target WebApp Controller | Test end-to-end with active or mock ML service. |
| **Modify Database Schema** | `services/web/app/Database/Migrations/`<br>`services/ml/app/db/readers/`<br>`services/ml/app/db/schema_guard.py` | Run `php spark migrate:refresh` on local test database. |
| **Change Inter-Service Auth** | `services/web/app/Services/LlmService.php`<br>`services/ml/app/core/security.py`<br>`docs/architecture/communication.md` | Verify health check and authenticated resource endpoints. |

---

## ✅ Definition of Done (DoD) Checklist

Before submitting a Pull Request or promoting code, ensure all items are fulfilled:

- [ ] **Implementation Complete:** Clean, documented code following PSR-12 (PHP) and Ruff/PEP8 (Python).
- [ ] **Contract Locking Tests:** Unit and integration tests verify both happy path and error handling.
- [ ] **Environment Synchronization:** Any new environment variables are documented in `.env.example` and `docs/user/configuration.md`.
- [ ] **Documentation Updated:** Changes reflected in `docs/api/contract.md`, `docs/api/openapi.yaml`, or relevant service guides.
- [ ] **Backward Compatibility:** Inter-service changes do not break in-flight WebApp requests or degrade active sessions.
- [ ] **Linter Quality Gate:** `python3 scripts/lint-docs.py .` passes with zero errors.
- [ ] **Changelog Recorded:** Bullet added to PR description or release notes.
