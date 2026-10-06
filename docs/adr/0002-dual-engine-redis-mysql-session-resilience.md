# ADR 0002: Dual-Engine Redis-to-MySQL Session Resilience

- **Status:** Accepted
- **Date:** 2026-09-15
- **Deciders:** Chege Jira Architecture Team

---

## 📌 Context and Problem Statement

Under normal operations, web user sessions and application caches require high throughput and low latency. Storing sessions in **Redis 7** provides sub-millisecond response times (< 1ms).

However, in containerized or cloud environments, volatile cache services periodically experience transient outages: container restarts, memory limits, network partitioning, or routine rolling updates. 

Standard framework implementations (e.g., native CodeIgniter `RedisHandler` or PHP session extensions) exhibit two critical flaws when Redis becomes unresponsive:
1. **Blocking Socket Timeouts:** PHP hangs for 1,000ms – 2,000ms waiting for TCP connection timeouts, degrading page load times for every active user.
2. **Fatal 500 Exceptions & Session Loss:** Requests abort with fatal connection errors, immediately logging out active users and corrupting form submissions.

---

## 💡 Decision Drivers

- **Zero-Downtime Availability:** The application must never throw HTTP 500 errors or log out active users during Redis maintenance or restarts.
- **Microsecond Probe Overhead:** Pre-flight health checks must not introduce latency when Redis is healthy.
- **Autonomous Self-Healing:** The system must automatically restore in-memory Redis acceleration the moment Redis recovers, without requiring application restarts or administrator intervention.

---

## ⚖️ Considered Options

1. **MySQL-Only Sessions (`DatabaseHandler`):** Stable and ACID-compliant, but imposes relational database I/O on every single HTTP request (2–5ms overhead), limiting concurrency under high load.
2. **Redis-Only Sessions with Restart Policies:** Fastest runtime latency, but completely brittle during Redis downtime or cache flushes.
3. **Dual-Engine Adaptive Failover (`ResilientSessionHandler`):** Redis 7 as the primary in-memory engine, paired with a strict 50ms pre-flight non-blocking socket probe and transparent fallback to MySQL (`ci_sessions`) and local file cache.

---

## 🎯 Decision Outcome

**Chosen Option:** Option 3 — Dual-Engine Adaptive Failover.

We engineered `ResilientSessionHandler` (`services/web/app/Session/Handlers/ResilientSessionHandler.php`):
- **50ms Socket Probe:** Uses non-blocking `@fsockopen($host, $port, $errno, $errstr, 0.05)`. If the connection is refused or takes $> 50\text{ ms}$, the handler instantly delegates session operations to `DatabaseHandler` (`ci_sessions` table).
- **Request-Lifecycle Static Memoization:** The probe result is cached in `private static ?bool $redisAlive` for the remainder of the HTTP request lifecycle, ensuring 0ms overhead for subsequent session reads and writes.
- **Autonomous Re-Acceleration:** On subsequent HTTP requests after Redis returns online, the probe verifies connectivity and immediately resumes in-memory Redis operation.

### Consequences

- **Positive:**
  - Zero session loss or 500 errors during Redis restarts.
  - Sub-millisecond latency preserved during normal operations.
  - Administrators are notified via telemetry banners and `/health` degraded status indicators.
- **Negative:**
  - Fallback sessions accumulate in MySQL during prolonged Redis outages, requiring periodic pruning via `php spark session:gc`.
