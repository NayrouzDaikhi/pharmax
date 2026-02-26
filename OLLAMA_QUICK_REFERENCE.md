# ⚡ OLLAMA QUICK REFERENCE

## 🚀 Start Using Ollama

```bash
# 1. Start Ollama service (keep running in background)
ollama serve

# 2. In another terminal - download model
ollama pull mistral

# 3. Your app is ready! Test it:
curl http://localhost:11434/api/tags
```

---

## 🎯 Most Common Commands

```bash
# Download a model
ollama pull mistral
ollama pull neural-chat
ollama pull orca-mini

# List installed models
ollama list

# See what's running
ollama ps

# Chat with a model directly
ollama run mistral

# Remove a model (free space)
ollama rm mistral

# Get help
ollama --help
```

---

## 🧪 Test Pharmax Chatbot

```bash
# Health check
curl http://127.0.0.1:8000/api/chatbot/health

# Ask a question
curl -X POST http://127.0.0.1:8000/api/chatbot/ask \
  -H "Content-Type: application/json" \
  -d '{"question": "What do you offer?"}'

# With article context
curl -X POST http://127.0.0.1:8000/api/chatbot/ask \
  -H "Content-Type: application/json" \
  -d '{
    "question": "Tell me more",
    "article_id": 1
  }'
```

---

## 🐳 Docker Commands

```bash
# Start Ollama in Docker
docker run -d -p 11434:11434 --name ollama ollama/ollama

# Download model in Docker
docker exec ollama ollama pull mistral

# Check if running
docker ps | grep ollama

# Stop Ollama
docker stop ollama

# Restart
docker restart ollama

# View logs
docker logs ollama

# Remove container
docker rm ollama
```

---

## 🔍 Check Service Status

```bash
# Check if Ollama is running
curl http://localhost:11434/api/tags

# Check specific model
curl -X POST http://localhost:11434/api/generate \
  -d '{"model": "mistral", "prompt": "Hi", "stream": false}'

# Look at running processes
ollama ps
```

---

## 🛠️ Troubleshoot

```bash
# Port in use? Try different port
OLLAMA_HOST=0.0.0.0:11434 ollama serve

# Out of memory? Use lighter model
ollama pull orca-mini

# Enable GPU support
# NVIDIA: Install CUDA
# AMD: Install ROCm
# Then Ollama auto-uses it

# Clear cache/free memory
ollama rm mistral
```

---

## 🌐 Change Model

Edit `.env`:
```dotenv
OLLAMA_MODEL=mistral           # Default
# Or switch to:
OLLAMA_MODEL=neural-chat
OLLAMA_MODEL=orca-mini
OLLAMA_MODEL=llama2
```

---

## 📱 Use from Phone/Network

```bash
# Allow other machines to connect
# Linux/Mac:
export OLLAMA_HOST=0.0.0.0:11434
ollama serve

# Or in Docker:
docker run -d -p 11434:11434 -e OLLAMA_HOST=0.0.0.0:11434 ollama/ollama

# Then access from: http://your-server-ip:11434
```

---

## ⚙️ Advanced Configuration

```bash
# Set context window (tokens)
export OLLAMA_NUM_THREAD=4
ollama serve

# Set max loaded model count
export OLLAMA_MAX_LOADED_MODELS=2
ollama serve

# Enable debugging
export OLLAMA_DEBUG=1
ollama serve
```

---

## 📊 Model Comparison

| Model | Size | Speed | Quality | Best For |
|-------|------|-------|---------|----------|
| orca-mini | 2GB | ⚡⚡⚡ | Good | Budget |
| mistral | 4GB | ⚡⚡ | Excellent | **Recommended** |
| neural-chat | 4GB | ⚡⚡ | Excellent | Conversation |
| llama2 | 7GB | ⚡ | Excellent | Power |
| dolphin-mixtral | 26GB | ⚠️ | Best | Needs GPU |

---

## 🔗 Useful Links

- 🌐 Ollama.ai: https://ollama.ai
- 📦 Models: https://ollama.ai/library
- 📚 Docs: https://github.com/ollama/ollama
- 🐳 Docker: https://hub.docker.com/r/ollama/ollama

---

**Keep this handy!** 📌
