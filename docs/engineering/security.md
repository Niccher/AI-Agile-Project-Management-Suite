# Security Guidelines & Best Practices

This document outlines key security protocols for engineers maintaining the suite.

---

## 🔒 1. Secrets Management
- Never commit `.env`, private keys, or passwords.
- Always verify that `.gitignore` ignores `.env` and `*.pem`.
- Production credentials should be rotated every 90 days.

## 🛡 2. Port Binding Principle
- Do NOT expose `mysql` or `redis` ports in `docker-compose.yml` (`ports:`). Use `expose:` to restrict them to the internal Docker bridge network.

## 🔑 3. Authentication & API Key Rotation
- The shared `ML_API_KEY` authenticates WebApp to ML. When updating `ML_API_KEY`, restart both containers simultaneously:
  ```bash
  docker compose restart ml-chege-jira chege-jira
  ```
