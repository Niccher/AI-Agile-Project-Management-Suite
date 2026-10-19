# ADR 0004: MinIO S3-Compatible Object Storage for Unstructured Assets and Backups

- **Status:** Accepted
- **Date:** 2026-10-19
- **Deciders:** Chege Jira Architecture Team

---

## 📌 Context and Problem Statement

Initially, user uploads (task attachments, avatars) and automated database dumps were written directly to the WebApp's local disk under `services/web/writable/uploads/` and `writable/backups/`.

This presented several operational and architectural challenges:
1. **Container Ephemerality & Scaling:** Local disk storage binds file availability to a specific container volume. Scaling the WebApp horizontally across multiple application containers or migrating to Kubernetes requires complex shared network mounts (NFS, Ceph) that suffer from lock contention and latency.
2. **Bandwidth Saturation:** When users download multi-megabyte project attachments or database backups, PHP worker threads are tied up streaming bytes through the application runtime rather than servicing fast API requests.
3. **Cross-Service File Access:** The ML service (`ml-chege-jira`) needs to analyze attachments (e.g. document summarization, QA test generation from specs) and store generated artifacts without requiring shared POSIX volume mounts across containers.
4. **Cloud Portability:** Enterprises require the flexibility to run air-gapped on-premise with self-hosted storage or seamlessly switch to AWS S3, Google Cloud Storage, or Cloudflare R2 without code modifications.

---

## 💡 Decision Drivers

- **Industry Standard API:** S3 API compatibility ensures vendor-neutral storage that functions identically on-premise (MinIO) and in public cloud environments (AWS S3, GCP Cloud Storage, Cloudflare R2).
- **Stateless Web Application:** Decoupling binary assets from application containers enables truly stateless web nodes and simplified blue/green deployments.
- **Zero-Dependency Footprint:** Avoid 30MB+ of AWS SDK vendor bloat and Composer dependency conflicts in CodeIgniter 4 by utilizing a lightweight native AWS Signature Version 4 implementation.
- **Dual Delivery Strategy:** Support both direct browser downloads via secure pre-signed URLs (offloading application bandwidth) and server-proxied streaming (for strict firewall / private network compliance).
- **Zero-Downtime Data Migration:** Seamless transition path with local disk fallbacks and CLI migration tools to prevent data loss for existing installations.

---

## 🎯 Decision Outcome

**Chosen Option:** Deploy MinIO as a unified S3-compatible object storage service with a zero-dependency PHP AWSv4 client in CodeIgniter 4 and a `boto3` helper in FastAPI.

### Architectural Structure:

1. **Service Architecture (`docker-compose.yml`):**
   - MinIO container (`shared-minio`, image `minio/minio:RELEASE.2024-05-10T01-41-38Z`) exposes S3 API on port `9000` and Web Console on port `9001`.
   - Dedicated persistent Docker volume `minio-data` isolates all binary objects.
   - Standard Docker health check validates `/minio/health/live`.
2. **Dedicated Buckets:**
   - `agile-attachments`: Task file attachments, document uploads, and QA artifacts.
   - `agile-avatars`: User profile avatars and organization graphics.
   - `agile-backups`: Compressed database snapshots (`.sql.gz`).
3. **Storage Abstraction (`services/web/app/Services/StorageService.php`):**
   - Pure-PHP implementation of AWS Signature Version 4 (`AWS4-HMAC-SHA256`) using native `hash_hmac`, `curl`, and `parse_url`.
   - Handles public browser endpoints (`S3_PUBLIC_ENDPOINT`) vs internal Docker network endpoints (`S3_ENDPOINT`).
   - Supports bucket creation (`ensureBucket`), object upload (`uploadFile`, `uploadContents`), retrieval (`getObject`), deletion (`deleteObject`), and pre-signed URL generation (`getPresignedUrl`).
4. **Dual Delivery Controller Integration:**
   - Controllers (`TaskAttachmentApiController`, `AvatarController`) check `S3_DIRECT_PRESIGNED`. When enabled, downloads issue a temporary 307 redirect to a pre-signed S3 URL; otherwise, they stream data through the controller with local file fallback.
5. **ML Service Integration (`services/ml/app/core/storage.py`):**
   - Lightweight `boto3` wrapper for downloading user attachments for document processing and uploading AI-generated report artifacts.
6. **Automated Provisioning & Migration:**
   - One-click deployment script (`scripts/deploy.sh`) verifies MinIO health and automatically provisions buckets.
   - CLI migration command (`php spark storage:migrate-to-s3`) facilitates idempotent migration of legacy disk files to S3 with `--dry-run` inspection.

---

## ⚖️ Consequences

- **Positive:**
  - **Stateless Web Nodes:** WebApp containers can be scaled horizontally without requiring shared NFS mounts.
  - **Reduced Application Load:** Direct pre-signed URL downloads offload I/O bandwidth from PHP workers.
  - **Standardized Cloud Migration:** Switching from local MinIO to AWS S3 or Cloudflare R2 requires changing only environment variables (`S3_ENDPOINT`, `S3_ACCESS_KEY`, `S3_SECRET_KEY`).
  - **Zero Dependency Bloat:** CodeIgniter 4 vendor size remains lean with native AWSv4 implementation.
  - **Graceful Fallback:** Systems operate seamlessly with local disk if S3 is unavailable or during migration.
- **Negative:**
  - Additional container service (`shared-minio`) requiring ~100MB RAM and port `9000`/`9001` allocation.
  - Requires automated bucket provisioning during initial bootstrapping.
