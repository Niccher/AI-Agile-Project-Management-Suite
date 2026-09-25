#!/usr/bin/env python3
"""
generate_synthetic_agile_data.py — Privacy-First Synthetic Agile Data Generator
Part of AI-Agile-Project-Management-Suite (Open-Source Maturity Pillar 5)

Generates realistic, anonymized agile projects, sprints, tasks, and timelogs
for benchmarking local LLMs and testing without leaking proprietary corporate data.

Usage:
    python3 scripts/generate_synthetic_agile_data.py --count 50 --output synthetic_seed.sql
"""

import argparse
import random
import sys
from datetime import datetime, timedelta

PROJECTS = [
    ("Distributed Payment Orchestrator", "Core high-throughput settlement engine with idempotency guards", "Fintech"),
    ("Autonomous Telemetry Platform", "Edge telemetry ingestion pipeline with Prometheus & OpenTelemetry", "Infrastructure"),
    ("Zero-Trust Identity Gateway", "OAuth2 / OIDC mutual TLS authentication gateway for microservices", "Security"),
    ("Neural Recommendation Engine", "Real-time vector search & collaborative filtering recommendation model", "Machine Learning"),
]

SPRINT_GOALS = [
    "Stabilize container failover and decrease cold start latency under 150ms",
    "Complete zero-trust service mesh authentication and mTLS certificate rotation",
    "Implement distributed rate-limiting and circuit breaker pattern on public endpoints",
    "Overhaul Redis caching layer with resilient database fallbacks and automated tests",
]

TASK_TEMPLATES = [
    ("Implement exponential backoff in Redis connection handler", "Task", "high", 5),
    ("Add Prometheus histogram metrics to FastAPI inference loop", "Feature", "medium", 3),
    ("Fix race condition in task status concurrency lock", "Bug", "critical", 8),
    ("Optimize GGUF model memory mapping using OpenBLAS threads", "Optimization", "high", 5),
    ("Configure Docker healthcheck probes for internal MySQL server", "DevOps", "medium", 2),
    ("Refactor CodeIgniter session driver to handle transient network drops", "Refactoring", "medium", 3),
    ("Add integration test verifying Bearer token authentication header", "Testing", "low", 2),
    ("Audit CORS origin headers on public WebApp endpoints", "Security", "high", 3),
    ("Generate automated PDF sprint velocity report from timelog history", "Feature", "medium", 5),
    ("Benchmark throughput for Phi-3 Mini vs Mistral 7B on 4 CPU threads", "Research", "low", 5),
]

MOCK_USERS = [
    ("alex.chen@example.org", "Alex Chen", "developer"),
    ("sarah.jenkins@example.org", "Sarah Jenkins", "developer"),
    ("david.kim@example.org", "David Kim", "manager"),
    ("elena.rostova@example.org", "Elena Rostova", "admin"),
]

def generate_sql(task_count: int) -> str:
    lines = [
        "-- ============================================================================",
        f"-- Synthetic Agile Test Data (Generated {datetime.now().isoformat()})",
        "-- Safe for open-source benchmarking, CI testing, and local LLM evaluation.",
        "-- ============================================================================\n",
        "SET FOREIGN_KEY_CHECKS = 0;\n",
    ]

    # Projects
    lines.append("-- 1. Projects")
    for idx, (name, desc, cat) in enumerate(PROJECTS, start=1):
        clean_desc = desc.replace("'", "''")
        lines.append(
            f"INSERT INTO `projects` (`id`, `name`, `description`, `category`, `status`, `created_at`, `updated_at`) "
            f"VALUES ({idx}, '{name}', '{clean_desc}', '{cat}', 'active', NOW(), NOW()) "
            f"ON DUPLICATE KEY UPDATE `name`=VALUES(`name`);"
        )
    lines.append("")

    # Sprints
    lines.append("-- 2. Sprints")
    now = datetime.now()
    for s_idx in range(1, 4):
        start = (now - timedelta(days=14 * (3 - s_idx))).strftime("%Y-%m-%d %H:%M:%S")
        end = (now - timedelta(days=14 * (2 - s_idx))).strftime("%Y-%m-%d %H:%M:%S")
        goal = random.choice(SPRINT_GOALS).replace("'", "''")
        lines.append(
            f"INSERT INTO `sprints` (`id`, `project_id`, `name`, `goal`, `start_date`, `end_date`, `status`, `created_at`) "
            f"VALUES ({s_idx}, {random.randint(1, len(PROJECTS))}, 'Sprint {s_idx}', '{goal}', '{start}', '{end}', 'active', NOW()) "
            f"ON DUPLICATE KEY UPDATE `goal`=VALUES(`goal`);"
        )
    lines.append("")

    # Tasks
    lines.append("-- 3. Tasks & Timelogs")
    for t_idx in range(1, task_count + 1):
        tmpl, issue_type, priority, points = random.choice(TASK_TEMPLATES)
        title = f"{tmpl} #{t_idx}".replace("'", "''")
        desc = (
            f"As a platform engineer, I need to {tmpl.lower()} to ensure enterprise stability. "
            f"Acceptance Criteria: 1. Unit tests pass with >90% coverage. 2. No regression in latency."
        ).replace("'", "''")
        status = random.choice(["todo", "in_progress", "review", "done"])
        proj_id = random.randint(1, len(PROJECTS))
        sprint_id = random.randint(1, 3)

        lines.append(
            f"INSERT INTO `tasks` (`id`, `project_id`, `sprint_id`, `title`, `description`, `type`, `priority`, `status`, `points`, `created_at`, `updated_at`) "
            f"VALUES ({t_idx}, {proj_id}, {sprint_id}, '{title}', '{desc}', '{issue_type}', '{priority}', '{status}', {points}, NOW(), NOW()) "
            f"ON DUPLICATE KEY UPDATE `status`=VALUES(`status`);"
        )

        # Timelogs
        if status in ("in_progress", "done"):
            duration = random.choice([3600, 7200, 10800, 14400])
            note = f"Investigated and worked on {title}".replace("'", "''")
            lines.append(
                f"INSERT INTO `timelogs` (`task_id`, `user_id`, `duration_seconds`, `description`, `logged_date`, `created_at`) "
                f"VALUES ({t_idx}, 1, {duration}, '{note}', CURDATE(), NOW());"
            )

    lines.append("\nSET FOREIGN_KEY_CHECKS = 1;\n")
    return "\n".join(lines)

def main():
    parser = argparse.ArgumentParser(description="Generate synthetic agile test data for AI-Agile-Project-Management-Suite.")
    parser.add_argument("--count", type=int, default=50, help="Number of synthetic tasks to generate (default: 50)")
    parser.add_argument("--output", type=str, default="", help="File path to write output SQL script (defaults to stdout)")

    args = parser.parse_args()
    sql = generate_sql(args.count)

    if args.output:
        with open(args.output, "w", encoding="utf-8") as f:
            f.write(sql)
        print(f"[OK] Generated {args.count} synthetic tasks and written to {args.output}", file=sys.stderr)
    else:
        print(sql)

if __name__ == "__main__":
    main()
