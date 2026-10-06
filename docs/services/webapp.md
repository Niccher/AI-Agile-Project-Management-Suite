# WebApp Service Overview (CodeIgniter 4)

The **WebApp** service is the primary user-facing component of the suite, built with **PHP 8.3** on the **CodeIgniter 4** framework and served via Nginx and PHP-FPM.

For the comprehensive engineering handbook, CLI commands, and development recipes, see the canonical [CodeIgniter 4 Service Guide](codeigniter.md).

---

## 🏗 High-Level Architecture

The service follows MVC design patterns with strict domain separation:

```mermaid
flowchart TD
    Client([Browser Client]) -->|HTTP Port 80| Nginx[Nginx Reverse Proxy]
    Nginx -->|FastCGI UNIX Socket| PHP[PHP 8.3-FPM]
    
    subgraph "CodeIgniter 4 Application (services/web/app)"
        PHP --> Routes[Config/Routes.php]
        Routes --> Filters[Filters: Session / Shield Auth]
        Filters --> Controllers[Controllers: Admin / User / Api]
        Controllers --> Models[Models: Task / Project / Sprint]
        Controllers --> Views[Views: Kanban / Sprints / Wiki]
        Controllers --> LlmService[Services/LlmService.php]
        Controllers --> Resilient[Session/Handlers/ResilientSessionHandler.php]
    end

    LlmService -->|HTTP REST JSON| MLService[(FastAPI ML Service :8000)]
    Resilient -->|Sub-millisecond RAM| Redis[(Redis 7 :6379)]
    Resilient -.->|50ms Fallback| MySQL[(MySQL 8.4 :3306)]
    Models -->|MySQL Queries| MySQL
```

---

## 🔑 Key Features
- **Agile Sprint & Kanban Engine:** Interactive task cards with drag-and-drop state transitions, backlog grooming, and sprint burndown tracking.
- **Time Tracking & Billing:** Stopwatch timer and manual entry logging with CSV/PDF reporting.
- **Project Wiki:** Markdown-based documentation system with version history and rollback capabilities.
- **Public Client Portal:** Token-gated view allowing external stakeholders to inspect sprint velocity and roadmaps without an account.
- **AI Copilot Integration:** Seamless UI hooks for task enhancement, sprint retrospective generation, and natural-language project Q&A.

---

## 📚 Service Reference Links
- [CodeIgniter 4 Engineering Guide](codeigniter.md)
- [API Contract Specification](../api/contract.md)
- [Dual-Engine Resilience & Failover Architecture](../architecture/resilience-and-failover.md)
- [Local Development Workflow](../engineering/local-development.md)
