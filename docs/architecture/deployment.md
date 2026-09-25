# Deployment Architecture

This document describes how to deploy the **AI-Agile-Project-Management-Suite** across bare-metal VPS, on-premise servers, and Google Cloud Platform (GCP) containers.

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
1. Detects host IP and sets `APP_URL`.
2. Checks system RAM, CPU, Swap, and Disk.
3. Verifies Git status and remotes.
4. Generates or patches `.env` with production keys.
5. Removes stale containers to eliminate name conflicts.
6. Builds Docker images using BuildKit.
7. Waits for MySQL health on internal port 3306.
8. Waits for Redis health on internal port 6379.
9. Runs CodeIgniter database migrations and seeders (`DemoSeeder`).
10. Validates ML microservice health (`/api/v1/health`).
11. Validates WebApp server responsiveness.
12. Enforces file permissions on `services/web/writable/` and prunes build cache.
13. Generates the deployment summary table and execution timeline.

---

## ☁ Target 2: Google Cloud Platform (GCP Container Deployment)

Deploying the monorepo to Google Cloud Platform using **GCP Cloud Run**, **Google Kubernetes Engine (GKE)**, or **GCP Compute Engine Container-Optimized OS**:

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
# Configure Docker with GCP
gcloud auth configure-docker us-central1-docker.pkg.dev

# Build & Push WebApp Image
docker build -t us-central1-docker.pkg.dev/$PROJECT_ID/chege-repo/webapp:latest -f services/web/Dockerfile services/web
docker push us-central1-docker.pkg.dev/$PROJECT_ID/chege-repo/webapp:latest

# Build & Push ML Backend Image
docker build -t us-central1-docker.pkg.dev/$PROJECT_ID/chege-repo/ml-backend:latest -f services/ml/Dockerfile services/ml
docker push us-central1-docker.pkg.dev/$PROJECT_ID/chege-repo/ml-backend:latest
```

### 2. Deploy Services via Cloud Run with Serverless VPC Access

Both Cloud Run services connect to Cloud SQL and Memorystore via a **Serverless VPC Access Connector**:

1. **WebApp Container Deployment:**
   ```bash
   gcloud run deploy chege-webapp \
     --image=us-central1-docker.pkg.dev/$PROJECT_ID/chege-repo/webapp:latest \
     --region=us-central1 \
     --vpc-connector=chege-vpc-connector \
     --set-env-vars="database.default.hostname=10.x.x.x,database.default.database=db_chege_jira,REDIS_URL=redis://10.y.y.y:6379,ML_SERVICE_URL=https://chege-ml-xxxx.run.app" \
     --allow-unauthenticated
   ```

2. **ML Backend Container Deployment:**
   ```bash
   gcloud run deploy chege-ml \
     --image=us-central1-docker.pkg.dev/$PROJECT_ID/chege-repo/ml-backend:latest \
     --region=us-central1 \
     --vpc-connector=chege-vpc-connector \
     --memory=8Gi \
     --cpu=4 \
     --set-env-vars="DB_HOST=10.x.x.x,DB_NAME=db_chege_jira,REDIS_URL=redis://10.y.y.y:6379/0,API_KEY=your_production_secret"
   ```
