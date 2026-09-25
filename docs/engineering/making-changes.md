# Making Changes & Feature Development

Follow this standardized workflow when adding new features or modifying existing behavior.

---

## 🔄 End-to-End Feature Development Workflow

### 1. Adding a New AI-Assisted Feature (e.g. "Auto-Generate Task Acceptance Criteria")
1. **Define Prompt Template in ML:**
   - Add template in `services/ml/app/prompts/generate_criteria.j2`.
2. **Add Endpoint in FastAPI Router:**
   - In `services/ml/app/api/v1/resources/tasks.py`, implement endpoint `POST /api/v1/resources/tasks/{id}/criteria`.
3. **Register Route in OpenAPI & Contract:**
   - Update `docs/api/contract.md` and `docs/api/openapi.yaml`.
4. **Implement Client Method in WebApp:**
   - Add method in `services/web/app/Services/LlmService.php`.
5. **Add UI Button & Action:**
   - In `services/web/app/Views/user/tasks/show.php`, add an "Auto-Generate Criteria" button calling an AJAX endpoint in `services/web/app/Controllers/User/TaskController.php`.
6. **Verify with Tests:**
   - Run `make test-ml` and PHPUnit.
