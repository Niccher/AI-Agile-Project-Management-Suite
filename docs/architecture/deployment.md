# Deployment Architecture

This document describes deployment topologies across self-hosted Docker environments, on-premise VPS instances, and Google Cloud Platform (GCP) container services.

---

## 🚀 Target 1: Self-Hosted Docker Compose (Recommended)

The platform is optimized for single-node deployment via Docker Compose.

### Automated One-Click Deployment
Run the automated deployment script from the repository root:
```bash
bash scripts/deploy.sh
```
Or via Makefile:
```bash
make deploy
```

The script executes 13 automated stages:
1. **IP Detection:** Discovers local LAN/host IP and sets `APP_URL`.
2. **Resource Pre-flight:** Checks system RAM, CPU architecture, Swap, and Disk.
3. **Git Sanity Check:** Verifies working tree cleanliness and remote tracking.
4. **Environment Configuration:** Generates or patches `.env` with production keys.
5. **Conflict Resolution:** Stops stale containers to eliminate naming conflicts.
6. **Container Build:** Compiles Docker images using BuildKit cache.
7. **Database Readiness:** Waits for internal MySQL health on port 3306.
8. **Cache Readiness:** Waits for internal Redis health on port 6379.
9. **Database Migrations:** Runs CodeIgniter database migrations and `DemoSeeder`.
10. **ML Microservice Readiness:** Validates ML microservice health (`/api/v1/health`).
11. **WebApp Readiness:** Validates WebApp server responsiveness.
12. **Container Permissions:** Enforces file ownership on `services/web/writable/` and prunes cache.
13. **Deployment Summary:** Renders endpoints table and execution duration metrics.

---

## ☁ Target 2: Google Cloud Platform (GCP Containers)

When deploying to Google Cloud Platform, services map to serverless container runtimes with private VPC networking:

```text
Google Cloud Project (GCP)
├── Container Registry: GCP Artifact Registry (pkg.dev/<project-id>/chege-repo)
├── Relational DB:      GCP Cloud SQL for MySQL 8.4 (Private VPC IP)
├── Cache & Queue:      GCP Memorystore for Redis 7.0 (Private VPC IP)
├── Web Service:        GCP Cloud Run (Container: services/web)
└── AI Copilot Service: GCP Cloud Run / GKE (Container: services/ml with persistent disk for GGUF models)
```

### 1. Build & Push to GCP Artifact Registry

```bash
# Configure Docker credential helper with GCP
gcloud auth configure-docker us-central1-docker.pkg.dev

# Build & Push WebApp Container
docker build -t us-central1-docker.pkg.dev/$PROJECT_ID/chege-repo/webapp:latest -f services/web/Dockerfile services/web
docker push us-central1-docker.pkg.dev/$PROJECT_ID/chege-repo/webapp:latest

# Build & Push ML Backend Container
docker build -t us-central1-docker.pkg.dev/$PROJECT_ID/chege-repo/ml-backend:latest -f services/ml/Dockerfile services/ml
docker push us-central1-docker.pkg.dev/$PROJECT_ID/chege-repo/ml-backend:latest
```

### 2. Deploy Services via Cloud Run with Serverless VPC Access

Both Cloud Run services connect to Cloud SQL and Memorystore via a **Serverless VPC Access Connector**:

1. **Deploy WebApp Service:**
   ```bash
   gcloud run deploy chege-webapp \
     --image=us-central1-docker.pkg.dev/$PROJECT_ID/chege-repo/webapp:latest \
     --region=us-central1 \
     --vpc-connector=chege-vpc-connector \
     --set-env-vars="database.default.hostname=10.x.x.x,database.default.database=db_chege_jira,REDIS_URL=redis://10.y.y.y:6379,ML_SERVICE_URL=https://chege-ml-xxxx.run.app" \
     --allow-unauthenticated
   ```

2. **Deploy ML Copilot Service:**
   ```bash
   gcloud run deploy chege-ml \
     --image=us-central1-docker.pkg.dev/$PROJECT_ID/chege-repo/ml-backend:latest \
     --region=us-central1 \
     --vpc-connector=chege-vpc-connector \
     --memory=8Gi \
     --cpu=4 \
     --set-env-vars="DB_HOST=10.x.x.x,DB_NAME=db_chege_jira,REDIS_URL=redis://10.y.y.y:6379/0,API_KEY=your_production_secret"
   ```

---

## 🔒 Production Hardening Checklist

- [ ] **SSL/TLS Termination:** Terminate HTTPS via Nginx reverse proxy with automated Let's Encrypt certificates (`certbot`).
- [ ] **Firewall Isolation:** Enforce `ufw` or cloud security groups allowing external traffic only on ports 80 and 443.
- [ ] **Secret Management:** Remove all default passwords from `.env` prior to launch.
- [ ] **Log Rotation:** Configure Docker log rotation daemon (`max-size: 50m`, `max-file: 3`) to prevent disk exhaustion.
