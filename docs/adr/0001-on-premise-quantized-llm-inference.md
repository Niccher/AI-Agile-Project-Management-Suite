# ADR 0001: On-Premise Quantized LLM Inference via Llama.cpp

- **Status:** Accepted
- **Date:** 2026-09-15
- **Deciders:** Chege Jira Architecture Team

---

## 📌 Context and Problem Statement

Enterprise agile project management systems handle sensitive business data: proprietary code snippets, security vulnerabilities in task tickets, commercial sprint roadmaps, and confidential team performance reviews. 

Relying on external commercial cloud LLM APIs (e.g., OpenAI, Anthropic, Google Cloud Vertex) introduces several critical challenges:
1. **Data Leakage & Compliance Violations:** Proprietary company information is transmitted over public networks to third-party inference providers, violating strict data residency, HIPAA, and GDPR regulations.
2. **Unpredictable OpEx Costs:** Token-based API billing escalates unpredictably as development teams generate numerous sprint summaries, ticket enhancements, and wiki documents daily.
3. **Network & Uptime Dependencies:** Internet connectivity drops or cloud API rate limits paralyze the internal agile workflow.

---

## 💡 Decision Drivers

- **Zero-Egress Data Confidentiality:** All code, tasks, and timelog data must remain entirely on-premise within the self-hosted network boundary.
- **Consumer Hardware Accessibility:** The solution must run reliably on standard developer workstations, VPS instances, or modest servers without requiring enterprise multi-GPU clusters.
- **Low-Latency Task Execution:** Asynchronous job processing for heavy tasks (sprint retrospectives, time reports) alongside fast synchronous endpoints for task enhancement.

---

## ⚖️ Considered Options

1. **Third-Party Cloud APIs (OpenAI / Anthropic):** High quality, but severe privacy risks and recurring per-token costs.
2. **Self-Hosted Full Precision Models (vLLM / HuggingFace Transformers with FP16 weights):** High VRAM requirements (> 16 GB - 32 GB GPU VRAM required), inaccessible on standard VPS environments.
3. **Quantized Local GGUF Inference with `llama-cpp-python`:** Highly optimized C++ inference supporting 4-bit and 5-bit quantized models (`Q4_K_M`), OpenBLAS CPU multi-threading, and optional GPU layer offloading.

---

## 🎯 Decision Outcome

**Chosen Option:** Option 3 — Quantized Local GGUF Inference with `llama-cpp-python`.

The platform bundles a dedicated Python 3.12 FastAPI microservice (`ml-chege-jira`) embedding `llama-cpp-python`. Pre-quantized GGUF models (e.g., `phi3-mini-4k-instruct.Q4_K_M.gguf`, `mistral-7b-instruct-v0.2.Q4_K_M.gguf`) are mounted from `services/ml/models/`.

### Consequences

- **Positive:**
  - Complete data privacy: Zero tokens or prompts leave the container network.
  - Predictable cost: Runs on existing compute resources without subscription or per-token fees.
  - Modularity: Models can be swapped dynamically via `/api/v1/models/load` without redeploying containers.
- **Negative:**
  - Inference throughput on CPU is bounded by CPU core count and thread allocation (`N_THREADS=4`).
  - Requires initial model download (~2.2 GB for Phi-3, ~4.1 GB for Mistral-7B).
