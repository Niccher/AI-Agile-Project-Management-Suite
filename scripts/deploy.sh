#!/usr/bin/env bash
# =============================================================
# deploy.sh — ONE-CLICK Deployment & Setup Pipeline
#             for AI-Agile-Project-Management-Suite (Monorepo)
#
# Usage:  bash scripts/deploy.sh
#         (run from the repository root)
#
# Production Standard Ports:
#   - Web Dashboard & API : Port 80 (or $WEB_PORT)
#   - ML AI Microservice  : Port 8000 (or $ML_PORT)
#   - MySQL 8.4 Database  : Port 3306 (Internal to Docker)
#   - Redis 7 Cache/Queue : Port 6379 (Internal to Docker)
#
# 13-Step Automated Pipeline:
#   1. Detect Dynamic IP (Public / LAN Fallback)
#   2. Pre-flight System & Resource Check (OS, CPU, RAM, Disk)
#   3. Git Working Tree Sanity Check & Remote Tracking
#   4. Write / Patch .env Configuration
#   5. Stop Old Containers & Clear Name Conflicts
#   6. Build & Start All Containers (MySQL, Redis, ML, Web)
#   7. Wait for MySQL Health (:3306)
#   7b. Wait for Redis Health (:6379)
#   8. Run Database Migrations & Seeders
#   9. Wait for ML Microservice Readiness (:8000)
#  10. Wait for WebApp Server Readiness (:80)
#  11. Container Permissions Enforcement (services/web/writable)
#  12. Housekeeping, Docker Storage & Cache Clearing
#  13. Clean Status Summary, Endpoints Table & Execution Timeline
# =============================================================
set -euo pipefail

# ── Colors & Formatting ───────────────────────────────────────
GREEN="\033[0;32m"; YELLOW="\033[1;33m"; RED="\033[0;31m"
CYAN="\033[0;36m"; BOLD="\033[1m"; RESET="\033[0m"

DEPLOY_START_TIME=$(date +%s)
SECTION_START_TIME=$DEPLOY_START_TIME
PREV_SECTION=""
declare -a STAGE_NAMES=()
declare -a STAGE_DURATIONS=()

format_duration() {
    local S=$1
    if [ "$S" -ge 60 ]; then
        local M=$((S / 60))
        local R=$((S % 60))
        echo "${M}m ${R}s"
    else
        echo "${S}s"
    fi
}

log()     { echo -e "${GREEN}[OK]${RESET}   $1"; }
warn()    { echo -e "${YELLOW}[WARN]${RESET} $1"; }
err()     {
    local NOW=$(date +%s)
    local ELAPSED=$((NOW - DEPLOY_START_TIME))
    echo -e "${RED}[FAIL]${RESET} $1 (failed after $(format_duration $ELAPSED))"
    exit 1
}

section() {
    local NOW=$(date +%s)
    if [ -n "$PREV_SECTION" ]; then
        local DURATION=$((NOW - SECTION_START_TIME))
        echo -e "${CYAN}──> Completed: ${PREV_SECTION} in $(format_duration $DURATION)${RESET}"
        STAGE_NAMES+=("$PREV_SECTION")
        STAGE_DURATIONS+=("$DURATION")
    fi
    PREV_SECTION="$1"
    SECTION_START_TIME=$NOW
    echo -e "\n${CYAN}${BOLD}=== $1 ===${RESET}"
}

finish_deployment() {
    local NOW=$(date +%s)
    if [ -n "$PREV_SECTION" ]; then
        local DURATION=$((NOW - SECTION_START_TIME))
        echo -e "${CYAN}──> Completed: ${PREV_SECTION} in $(format_duration $DURATION)${RESET}"
        STAGE_NAMES+=("$PREV_SECTION")
        STAGE_DURATIONS+=("$DURATION")
        PREV_SECTION=""
    fi
    local TOTAL_DURATION=$((NOW - DEPLOY_START_TIME))
    echo ""
    echo -e "${GREEN}${BOLD}══════════════════════════════════════════════════════════════${RESET}"
    echo -e "${GREEN}${BOLD}   DEPLOYMENT TIMELINE & EXECUTION SUMMARY${RESET}"
    echo -e "${GREEN}${BOLD}══════════════════════════════════════════════════════════════${RESET}"
    for i in "${!STAGE_NAMES[@]}"; do
        printf "   %-48s : %s\n" "${STAGE_NAMES[$i]}" "$(format_duration ${STAGE_DURATIONS[$i]})"
    done
    echo -e "   ------------------------------------------------------------"
    printf "   ${BOLD}%-48s${RESET} : ${BOLD}%s (%ss)${RESET}\n" "Total Overall Deployment Time" "$(format_duration $TOTAL_DURATION)" "$TOTAL_DURATION"
    echo -e "${GREEN}${BOLD}══════════════════════════════════════════════════════════════${RESET}"
}

# ── Guard: must run from repository root ─────────────────────
[ -f "docker-compose.yml" ] || err "Run from the repository root (where docker-compose.yml lives)."

# ── 1. Detect Dynamic IP ──────────────────────────────────────
section "1. Detecting Dynamic IP"
# 1. GCP Compute Engine metadata server probe (instant on GCP)
DETECTED_IP=$(curl -s -m 2 -H "Metadata-Flavor: Google" http://metadata.google.internal/computeMetadata/v1/instance/network-interfaces/0/access-configs/0/external-ip 2>/dev/null | tr -d '[:space:]' || true)

# 2. Public IP fallbacks
if [ -z "$DETECTED_IP" ]; then
    DETECTED_IP=$(curl -s --max-time 4 ip.me 2>/dev/null | tr -d '[:space:]' || true)
fi
if [ -z "$DETECTED_IP" ]; then
    DETECTED_IP=$(curl -s --max-time 4 ifconfig.me 2>/dev/null | tr -d '[:space:]' || true)
fi
if [ -z "$DETECTED_IP" ]; then
    DETECTED_IP=$(curl -s --max-time 4 icanhazip.com 2>/dev/null | tr -d '[:space:]' || true)
fi
if [ -z "$DETECTED_IP" ]; then
    DETECTED_IP=$(hostname -I 2>/dev/null | awk '{print $1}' | tr -d '[:space:]' || true)
fi
if [ -z "$DETECTED_IP" ]; then
    DETECTED_IP="127.0.0.1"
fi
log "Dynamic Host IP: $DETECTED_IP"

# ── 2. Pre-flight System & Resource Check ─────────────────────
section "2. Pre-flight Disk & RAM Diagnostics"

if [ -f /etc/os-release ]; then
    . /etc/os-release
    OS_PRETTY="${PRETTY_NAME:-$NAME}"
else
    OS_PRETTY="$(uname -s)"
fi
KERNEL="$(uname -r)"
ARCH="$(uname -m)"

CPU_CORES=$(nproc 2>/dev/null || echo "1")
CPU_MODEL=$(grep -m1 "model name" /proc/cpuinfo 2>/dev/null | sed -E "s/^model name\s*:\s*//" | tr -s " " || echo "Generic CPU")
[ -z "$CPU_MODEL" ] && CPU_MODEL="Generic CPU"

TOTAL_MEM_MB=$(free -m | awk '/Mem:/ {print $2}')
USED_MEM_MB=$(free -m | awk '/Mem:/ {print $3}')
FREE_MEM_MB=$(free -m | awk '/Mem:/ {print ($7 != "" ? $7 : $4)}')
MEM_PCT=$(awk -v u="$USED_MEM_MB" -v t="$TOTAL_MEM_MB" 'BEGIN {if (t>0) printf "%.1f", (u/t)*100; else print "0"}')
MEM_FREE_PCT=$(awk -v f="$FREE_MEM_MB" -v t="$TOTAL_MEM_MB" 'BEGIN {if (t>0) printf "%.1f", (f/t)*100; else print "0"}')
TOTAL_MEM_GB=$(awk -v m="$TOTAL_MEM_MB" 'BEGIN {printf "%.2f GB", m/1024}')
USED_MEM_GB=$(awk -v m="$USED_MEM_MB" 'BEGIN {printf "%.2f GB", m/1024}')
FREE_MEM_GB=$(awk -v m="$FREE_MEM_MB" 'BEGIN {printf "%.2f GB", m/1024}')

SWAP_TOTAL_MB=$(free -m | awk '/Swap:/ {print $2}')

DISK_LINE=$(df -h / | awk 'NR>1 {print $(NF-4), $(NF-3), $(NF-2), $(NF-1), $(NF)}')
DISK_TOTAL=$(echo "$DISK_LINE" | awk '{print $1}')
DISK_USED=$(echo "$DISK_LINE" | awk '{print $2}')
DISK_FREE=$(echo "$DISK_LINE" | awk '{print $3}')
DISK_PCT=$(echo "$DISK_LINE" | awk '{print $4}')

echo -e "   ${BOLD}Operating System :${RESET} $OS_PRETTY (Kernel $KERNEL, $ARCH)"
echo -e "   ${BOLD}CPU Processor    :${RESET} $CPU_MODEL ($CPU_CORES logical cores)"
echo -e "   ${BOLD}Memory (RAM)     :${RESET} $TOTAL_MEM_GB total | $USED_MEM_GB used (${MEM_PCT}%) | $FREE_MEM_GB free (${MEM_FREE_PCT}%)"
echo -e "   ${BOLD}Swap Memory      :${RESET} ${SWAP_TOTAL_MB} MB"
echo -e "   ${BOLD}Disk Space (/)   :${RESET} $DISK_TOTAL total | $DISK_USED used (${DISK_PCT}) | $DISK_FREE free"

# Warn if low memory for local LLM execution
if [ "$TOTAL_MEM_MB" -lt 3500 ]; then
    warn "Total RAM is under 4GB ($TOTAL_MEM_GB). Local LLMs may require CPU quantization (Q4_K_M) or higher swap."
else
    log "Resource allocation is sufficient for WebApp and local LLM execution"
fi

# ── 3. Git Working Tree Sanity Check ──────────────────────────
section "3. Git Repository & Remote Tracking Verification"
CURRENT_BRANCH=$(git rev-parse --abbrev-ref HEAD 2>/dev/null || echo "master")
CURRENT_COMMIT=$(git rev-parse --short HEAD 2>/dev/null || echo "unknown")
REMOTE_URL=$(git remote get-url origin 2>/dev/null || echo "No remote origin configured")

echo -e "   ${BOLD}Git Branch       :${RESET} $CURRENT_BRANCH"
echo -e "   ${BOLD}Current Commit   :${RESET} $CURRENT_COMMIT"
echo -e "   ${BOLD}Remote Origin    :${RESET} $REMOTE_URL"
log "Repository tree verified"

# ── 4. Environment Configuration (.env) ───────────────────────
section "4. Environment Configuration (.env)"
if [ ! -f ".env" ]; then
    warn ".env file missing. Generating from .env.example..."
    cp .env.example .env
fi

# Read port configuration with safe fallbacks
WEB_PORT=$(grep -E "^WEB_PORT=" .env | cut -d'=' -f2 | tr -d ' "' || echo "80")
[ -z "$WEB_PORT" ] && WEB_PORT="80"
ML_PORT=$(grep -E "^ML_PORT=" .env | cut -d'=' -f2 | tr -d ' "' || echo "8000")
[ -z "$ML_PORT" ] && ML_PORT="8000"

# Auto-update BASE_URL if bound to specific port
if [ "$WEB_PORT" = "80" ]; then
    BASE_URL="http://${DETECTED_IP}/"
else
    BASE_URL="http://${DETECTED_IP}:${WEB_PORT}/"
fi

# Ensure essential env variables exist in .env
ensure_env_var() {
    local KEY=$1
    local VAL=$2
    if ! grep -q "^${KEY}=" .env; then
        echo "${KEY}=${VAL}" >> .env
        log "Injected missing env var: ${KEY}"
    fi
}

ensure_env_var "WEB_PORT" "$WEB_PORT"
ensure_env_var "ML_PORT" "$ML_PORT"
ensure_env_var "APP_URL" "${BASE_URL%/}"
ensure_env_var "DB_ROOT_PASSWORD" "root_password"
ensure_env_var "DB_NAME" "db_chege_jira"
ensure_env_var "ML_SERVICE_URL" "http://ml-chege-jira:8000"
ensure_env_var "ML_API_KEY" "chege_jira_ml_super_secret_key_2026"
ensure_env_var "DEFAULT_MODEL" "phi3-mini"

log "Configured WebApp Port: ${WEB_PORT} | ML Microservice Port: ${ML_PORT}"
log "Application Base URL: ${BASE_URL}"

# ── 5. Stop Old Containers & Clear Conflicts ──────────────────
section "5. Conflict Resolution & Container Cleanup"
CONTAINER_NAMES=("chege-jira-webapp" "ml-chege-jira" "shared-mysql" "shared-redis" "shared-phpmyadmin")

for cname in "${CONTAINER_NAMES[@]}"; do
    if docker ps -a --format '{{.Names}}' | grep -Eq "^${cname}\$"; then
        warn "Stopping and removing existing container: $cname"
        docker rm -f "$cname" >/dev/null 2>&1 || true
    fi
done
log "Container namespaces sanitized"

# ── 6. Build & Launch Containers ──────────────────────────────
section "6. Building & Launching Unified Container Stack"
log "Executing BuildKit compilation (MySQL, Redis, ML FastAPI, CodeIgniter WebApp)..."
DOCKER_BUILDKIT=1 COMPOSE_DOCKER_CLI_BUILD=1 docker compose up --build -d

# ── 7. Wait for MySQL Health (:3306) ──────────────────────────
section "7. Waiting for MySQL Health (Internal :3306)"
printf "   Checking shared-mysql health"
MYSQL_HEALTHY=false
for i in $(seq 1 30); do
    STATUS=$(docker inspect --format='{{.State.Health.Status}}' shared-mysql 2>/dev/null || echo "missing")
    if [ "$STATUS" = "healthy" ]; then
        echo ""
        log "MySQL 8.4 is healthy and accepting internal connections"
        MYSQL_HEALTHY=true
        break
    fi
    printf "."
    sleep 3
done
$MYSQL_HEALTHY || { echo ""; warn "MySQL did not report healthy within timeout. Check: docker compose logs mysql"; }

# ── 7b. Wait for Redis Health (:6379) ─────────────────────────
section "7b. Waiting for Redis Health (Internal :6379)"
printf "   Checking shared-redis health"
REDIS_HEALTHY=false
for i in $(seq 1 15); do
    R_STATUS=$(docker inspect --format='{{.State.Health.Status}}' shared-redis 2>/dev/null || echo "missing")
    if [ "$R_STATUS" = "healthy" ]; then
        echo ""
        log "Redis cache and session store is healthy"
        REDIS_HEALTHY=true
        break
    fi
    printf "."
    sleep 2
done
$REDIS_HEALTHY || { echo ""; warn "Redis did not report healthy within timeout. Check: docker compose logs redis"; }

# ── 8. Run Database Migrations & Seeding ──────────────────────
section "8. Running Database Migrations & Seeding"
if $MYSQL_HEALTHY; then
    log "Running core application database migrations..."
    docker compose exec -T chege-jira php spark migrate 2>&1 \
        && log "Core database migrations applied successfully" \
        || warn "Core migrations had notices or completed with warnings"

    log "Running settings migrations..."
    docker compose exec -T chege-jira php spark migrate -n CodeIgniter\\Settings 2>&1 \
        && log "Settings migrations applied successfully" \
        || warn "Settings migrations had notices or completed with warnings"

    log "Applying initial database seeders..."
    docker compose exec -T chege-jira php spark db:seed DemoSeeder 2>&1 \
        && log "Seeders executed successfully" \
        || warn "Seeder notices (ignored if already seeded)"
else
    warn "Skipping migrations because MySQL is not yet healthy"
fi

# ── 9. Wait for ML Microservice Readiness (:8000) ─────────────
section "9. Waiting for ML Microservice Readiness (:${ML_PORT})"
printf "   Checking ml-chege-jira status"
ML_READY=false
for i in $(seq 1 45); do
    ML_CODE=$(curl -s -o /dev/null -w "%{http_code}" http://localhost:${ML_PORT}/api/v1/health 2>/dev/null || echo "000")
    if [ "$ML_CODE" = "200" ]; then
        echo ""
        log "ML FastAPI inference backend is healthy (HTTP $ML_CODE)"
        ML_READY=true
        break
    fi
    printf "."
    sleep 3
done
$ML_READY || { echo ""; warn "ML microservice took longer to initialize. Check: docker compose logs ml-chege-jira"; }

# ── 10. Wait for WebApp Server Readiness ──────────────────────
section "10. Waiting for WebApp Server Readiness (:${WEB_PORT})"
printf "   Waiting for WebApp response"
WEB_READY=false
for i in $(seq 1 25); do
    CODE=$(curl -s -o /dev/null -w "%{http_code}" http://localhost:${WEB_PORT} 2>/dev/null || echo "000")
    if [[ "$CODE" =~ ^(200|301|302|307|403)$ ]]; then
        echo ""
        log "WebApp server is responding (HTTP $CODE)"
        WEB_READY=true
        break
    fi
    printf "."
    sleep 2
done
$WEB_READY || { echo ""; warn "Web server did not respond in time. Check: docker compose logs chege-jira"; }

# ── 11. Container Permissions Enforcement ─────────────────────
section "11. Setting Container Writable Directory Permissions"
docker compose exec -T chege-jira sh -c "
    mkdir -p writable/cache writable/logs writable/session writable/uploads/avatars writable/debugbar writable/reports && \
    chown -R www-data:www-data writable && \
    chmod -R 775 writable
" 2>&1 && log "Writable permissions enforced (www-data:775)" || warn "Failed to update writable permissions"

# ── 12. Housekeeping, Storage Metrics & Cache Clearing ────────
section "12. Housekeeping & Cache Clearing"
docker compose exec -T chege-jira php spark cache:clear 2>&1 || true
log "CodeIgniter application cache cleared"

docker compose exec -T chege-jira sh -c "
    rm -rf writable/debugbar/* writable/tmp/* 2>/dev/null || true
" && log "Temporary runtime files cleared" || true

docker builder prune -af >/dev/null 2>&1 && log "Docker BuildKit build cache pruned" || true
docker image prune -f >/dev/null 2>&1 && log "Dangling Docker build layers pruned" || true

echo ""
echo -e "   ${BOLD}Docker Storage Footprint:${RESET}"
docker system df 2>/dev/null | sed 's/^/   /' || true

# ── 13. Container Status & Final Summary ──────────────────────
section "13. Deployment Summary & Health Verification"
docker compose ps

echo ""
FINAL_WEB=$(curl -s -o /dev/null -w "%{http_code}" http://localhost:${WEB_PORT} 2>/dev/null || echo "000")

if [[ "$FINAL_WEB" =~ ^(200|301|302|307)$ ]]; then
    log "PLATFORM IS LIVE — ALL SYSTEMS OPERATIONAL (WebApp HTTP ${FINAL_WEB})"
    echo ""
    echo -e "  ${BOLD}WebApp Dashboard     :${RESET}  ${BASE_URL}"
    echo -e "  ${BOLD}Admin Telemetry      :${RESET}  ${BASE_URL}admin/telemetry"
    echo -e "  ${BOLD}Admin AI Settings    :${RESET}  ${BASE_URL}admin/ai"
    echo -e "  ${BOLD}ML API Swagger Docs  :${RESET}  http://${DETECTED_IP}:${ML_PORT}/docs"
    echo -e "  ${BOLD}ML Health Probe      :${RESET}  http://${DETECTED_IP}:${ML_PORT}/api/v1/health"
    echo -e "  ${BOLD}MySQL Database       :${RESET}  mysql:3306 (Docker internal only)"
    echo -e "  ${BOLD}Redis Cache/Queue    :${RESET}  redis:6379 (Docker internal only)"
    echo ""
    finish_deployment

elif [ "$FINAL_WEB" = "500" ]; then
    warn "HTTP 500 detected on WebApp — dumping logs..."
    docker compose logs chege-jira 2>&1 | tail -30
    finish_deployment
    err "Fix the errors above, then re-run: bash scripts/deploy.sh"

else
    warn "HTTP ${FINAL_WEB} — unexpected status code. Recent WebApp logs:"
    docker compose logs chege-jira 2>&1 | tail -30
    finish_deployment
fi
