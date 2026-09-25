# Chege Jira WebApp

An enterprise-grade, role-based project management system for tracking tasks, logging time, generating reports, and enforcing management approvals.

**If you only need to run the system, this page is enough.**  
Software engineers: [docs/README.md](docs/README.md).

## What “running” looks like

| Piece | URL / how to open | Dev login (if any) |
|-------|-------------------|--------------------|
| Web App | http://localhost:9001 | |
| phpMyAdmin | http://localhost:9000 | |

## Prerequisites

- Git
- Docker Engine + Compose v2 (or Docker Desktop)

## Setup and run

1. Clone the repository
2. Ensure Docker is running
3. Build and start the containers:
   ```bash
   docker compose up -d --build
   ```
4. The database migrations will run automatically on boot via the container's `entrypoint.sh`.
5. Seed your first system administrator account by running this command:
   ```bash
   docker compose exec web php spark user:seed
   ```
6. Open the Web App URL (http://localhost:9001) and log in with your new admin account.
7. To stop the system, run:
   ```bash
   docker compose down
   ```

## Roles and Access

The system enforces three primary roles:
- **Admin**: Has complete control over system settings, user provisioning, and audit logs.
- **Manager**: Can assign tasks to team members, review/approve submitted work, and generate PDF/CSV performance reports.
- **User**: General team member who can execute tasks and log time.

## Something went wrong?

- **Port in use:** If port 9001 or 9000 is taken, update the `docker-compose.yml` file to map to different host ports.
- **Database issues:** If migrations didn't run, execute them manually: `docker compose exec web php spark migrate`.

## Software engineers

- [docs/README.md](docs/README.md)
