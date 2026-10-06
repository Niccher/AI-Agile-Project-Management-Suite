# Local LLM Inference & Model Card

The **ML Backend** service hosts quantized Large Language Models (LLMs) locally using `llama-cpp-python` and OpenBLAS C++ optimizations, guaranteeing complete privacy and zero data egress.

For API endpoints and FastAPI code architecture, see the canonical [FastAPI Service Guide](fastapi.md).

---

## 🤖 Supported Model Profiles

The service supports any modern model converted to the GGUF binary format. Pre-configured download scripts and prompt templates are optimized for:

| Model Identifier | Parameter Count | Quantization | Disk Size | Context Window | Recommended RAM | Ideal Use Case |
| :--- | :---: | :---: | :---: | :---: | :---: | :--- |
| **`phi3-mini`** (Default) | 3.8B | `Q4_K_M` | ~2.2 GB | 4,096 tokens | 4 GB – 8 GB | Fast ticket enhancement, priority prediction, and short summaries. Low latency on CPU. |
| **`mistral-7b`** | 7.3B | `Q4_K_M` | ~4.1 GB | 8,192 tokens | 8 GB – 16 GB | Deep sprint retrospectives, complex technical wiki generation, and multi-document Q&A. |

---

## ⚙️ Quantization Architecture (`Q4_K_M`)

The system utilizes the **`Q4_K_M`** (4-bit Medium K-quant) quantization scheme:
- **Perplexity Retention:** Retains $>99\%$ of FP16 reasoning and structured output accuracy while reducing memory footprint by $\sim 75\%$.
- **CPU Vectorization:** Leverages OpenBLAS SIMD vector instructions (AVX2 / AVX-512 / ARM NEON) for low-latency matrix multiplication without requiring specialized GPU hardware.

---

## ⚡ Inference Tuning Parameters

Model execution is tuned via environment variables in `docker-compose.yml`:

```env
# Path where *.gguf models reside
MODELS_DIR=/app/models

# Default model loaded on container boot
DEFAULT_MODEL=phi3-mini

# OpenBLAS thread allocation (set to match physical CPU cores)
N_THREADS=4

# Maximum context window size in tokens
N_CTX=4096

# Number of model layers offloaded to GPU (0 = pure CPU execution)
N_GPU_LAYERS=0
```

---

## 📥 Managing & Downloading Models

### 1. Automated CLI Download
Use the pre-configured script inside `services/ml/`:
```bash
# Download Phi-3 Mini (3.8B)
bash services/ml/scripts/download_model.sh phi3-mini

# Download Mistral-7B Instruct
bash services/ml/scripts/download_model.sh mistral-7b
```

### 2. Hot-Loading Models at Runtime
You can dynamically switch the active in-memory model via HTTP without restarting the container:

```bash
curl -X POST http://localhost:8000/api/v1/models/load \
  -H "X-API-Key: chege_jira_ml_super_secret_key_2026" \
  -H "Content-Type: application/json" \
  -d '{"model_name": "mistral-7b"}'
```

### 3. Inspecting Active Model Vitals
```bash
curl -s http://localhost:8000/api/v1/models/active \
  -H "X-API-Key: chege_jira_ml_super_secret_key_2026" | jq .
```

---

## 📝 Prompt Compilation via Jinja2

Prompt templates reside in `services/ml/app/prompts/` and are compiled using Jinja2:
- `enhance_task.j2`: Structures raw bug descriptions into Gherkin acceptance criteria.
- `suggest_priority.j2`: Evaluates task severity and estimates agile priority.
- `summarise_sprint.j2`: Consolidates burndown velocity, completed points, and blocker notes into executive retrospectives.
- `analyse_timelogs.j2`: Audits logged intervals against estimated story points to surface bottlenecks.
- `ask.j2`: Injects project wiki articles and task context into retrieval-augmented prompts.
