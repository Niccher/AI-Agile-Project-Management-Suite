FROM python:3.12-slim

# System dependencies for building llama-cpp-python and native extensions
RUN apt-get update && apt-get install -y --no-install-recommends \
    build-essential \
    cmake \
    libopenblas-dev \
    curl \
    git \
    && rm -rf /var/lib/apt/lists/*

ENV PYTHONDONTWRITEBYTECODE=1
ENV PYTHONUNBUFFERED=1

WORKDIR /app

# Install uv for fast Python package management
RUN pip install --no-cache-dir uv

# Pre-install pre-built llama-cpp-python CPU binary wheel (eliminates 15-minute C++ source build)
RUN uv pip install --system --no-cache-dir \
    --extra-index-url https://abetlen.github.io/llama-cpp-python/whl/cpu \
    "llama-cpp-python>=0.2.79"

# Copy project metadata and source code required by build backend (hatchling)
COPY pyproject.toml README.md ./
COPY app/ ./app/
COPY scripts/ ./scripts/

# Install dependencies and package
RUN uv pip install --system --no-cache-dir .

# Make scripts executable
RUN chmod +x /app/scripts/*.sh 2>/dev/null || true

EXPOSE 8000

# GCP Cloud Run passes $PORT dynamically; fallback to 8000
CMD ["sh", "-c", "uvicorn app.main:app --host 0.0.0.0 --port ${PORT:-8000}"]
