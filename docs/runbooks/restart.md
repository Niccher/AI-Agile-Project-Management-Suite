# Operational Runbook: Service Restart & Container Recovery

This runbook provides Standard Operating Procedures (SOPs) for site reliability engineers and operators performing maintenance, container restarts, and deadlock recovery.

---

## 🔄 Scenario 1: Graceful Rolling Service Restart

Use this procedure when updating `.env` configuration or restarting a single service without affecting the rest of the stack.

```bash
# Restart only the WebApp container
docker compose restart chege-jira

# Restart only the Python ML microservice
docker compose restart ml-chege-jira

# Restart only the Redis cache container (WebApp degrades gracefully to MySQL)
docker compose restart redis
```

### Verification:
```bash
curl -s http://localhost/health | jq .
curl -s http://localhost:8000/api/v1/health | jq .
```

---

## ⚡ Scenario 2: Full Clean Restart (Preserving Volumes)

Use this procedure when restarting the host server or restarting all containers after configuration updates.

```bash
# 1. Stop all containers gracefully
docker compose down

# 2. Start containers in detached mode
docker compose up -d

# 3. Verify health status of all containers
docker compose ps
```
*(All MySQL and Redis volumes remain intact).*

---

## 🧹 Scenario 3: Clean Container Rebuild (Preserving Volumes)

Use this procedure when modifying Dockerfiles or installing new packages in `composer.json` or `pyproject.toml`:

```bash
# Rebuild images without cache while preserving database state
docker compose down
docker compose build --no-cache
docker compose up -d
```

---

## 🚨 Scenario 4: Deadlock & Hung Container Recovery

### MySQL Lock Recovery
If transactions hang or queries stall:
```bash
# Inspect running MySQL processlist
docker compose exec mysql mysql -u root -proot_password -e "SHOW FULL PROCESSLIST;"

# If threads are locked, restart the MySQL container safely
docker compose restart mysql
```

### Clearing Hung Redis Background Jobs
If an asynchronous inference job hangs in Redis:
```bash
# Inspect active task keys
docker compose exec redis redis-cli keys "task:*"

# Flush stale background task keys
docker compose exec redis redis-cli eval "return redis.call('del', unpack(redis.call('keys', 'task:*')))" 0
```

---

## ⚠️ Scenario 5: Nuclear Reset (Fresh Environment Re-provisioning)

> [!CAUTION]
> This command completely wipes all database volumes, uploaded attachments, and local model caches. Use only in testing or staging environments.

```bash
# 1. Tear down containers and delete named volumes
docker compose down -v

# 2. Rebuild and launch fresh stack
docker compose up --build -d

# 3. Re-run migrations and seed initial demo dataset
docker compose exec chege-jira php spark migrate --all
docker compose exec chege-jira php spark db:seed DemoSeeder
```
