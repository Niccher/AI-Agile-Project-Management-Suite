# Runbook: Service Restart Procedures

This runbook covers graceful and emergency restart protocols.

---

## 🔄 Graceful Rolling Restart

To apply environment changes without data loss:
```bash
# Restart WebApp
docker compose restart chege-jira

# Restart ML Microservice
docker compose restart ml-chege-jira
```

---

## ⚡ Full Clean Stack Restart

To completely stop and re-create all containers while preserving data in volumes:
```bash
docker compose down
docker compose up -d
```

---

## 🧹 Hard Reset (Clearing Containers & Ephemeral Caches)
```bash
docker compose down -v  # WARNING: Deletes local volumes if specified
docker compose up --build -d
docker compose exec chege-jira php spark migrate --all
docker compose exec chege-jira php spark db:seed DemoSeeder
```
