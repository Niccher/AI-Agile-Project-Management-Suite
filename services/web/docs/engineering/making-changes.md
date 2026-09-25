# Making Changes

## General Workflow

| If you want to… | Touch |
|-----------------|--------|
| Add a Manager page | Route group `manage` + Controller in `App/Controllers/Manager` + View |
| Add an Admin page | Route group `admin` + Controller in `App/Controllers/Admin` + View |
| Change a column | Migration in `App/Database/Migrations` |
| Add a Notification | Call `\App\Services\NotificationService::notify(...)` |
| Log an Audit Event | Call `\App\Services\AuditService::log(...)` |

## Definition of Done

- [ ] Code follows existing CI4 conventions
- [ ] Database migrations are created for any schema changes
- [ ] Role-based Access Control (RBAC) is applied to new routes
- [ ] Significant logic changes are logged using the `AuditService`
