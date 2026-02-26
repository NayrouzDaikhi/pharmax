# 🚀 OLLAMA MIGRATION GUIDE - COMPLETE

## ✅ What Has Been Changed

The entire chatbot system has been migrated from **Google Gemini API** to **Ollama** (local LLM service).

### Files Modified:
- ✅ `src/Service/ChatBotService.php` - Now uses OllamaService
- ✅ `src/Service/OllamaService.php` - **NEW** Service to handle Ollama API
- ✅ `src/Command/CheckExpirationCommand.php` - Uses OllamaService instead of GeminiService
- ✅ `config/services.yaml` - OllamaService registered, GeminiService removed
- ✅ `.env` - Updated with Ollama configuration
- ✅ `src/Controller/ChatBotApiController.php` - Updated error messages

### What's **NOT Changed**:
- ✅ Chat interface (`templates/chatbot/` and `templates/blog/show.html.twig`) - **Unchanged**
- ✅ Database schema - **No changes**
- ✅ Comment moderation (HuggingFace) - **Still working**
- ✅ API endpoints - **Same URLs**

---

## 📋 Installation Guide

### Step 1: Install Ollama

#### **Windows:**
1. Download from: https://ollama.ai/download/windows
2. Run the installer (OllamaSetup.exe)
3. Follow the installation wizard
4. Ollama will start automatically

#### **macOS:**
```bash
brew install ollama
ollama serve
```

#### **Linux:**
```bash
curl https://ollama.ai/install.sh | sh
ollama serve
```

#### **Docker (Any OS):**
```bash
docker run -d -p 11434:11434 --name ollama ollama/ollama
docker exec ollama ollama pull mistral
```

---

### Step 2: Pull the Mistral Model

Once Ollama is installed and running, open a terminal and download the model:

```bash
ollama pull mistral
```

This downloads ~4GB of data. Alternative models:

```bash
# Options:
ollama pull mistral           # (4GB) - RECOMMENDED - Fast & good quality
ollama pull neural-chat       # (4GB) - Optimized for conversation
ollama pull orca-mini         # (2GB) - Lightweight, faster
ollama pull llama2            # (7GB) - More powerful
ollama pull dolphin-mixtral   # (26GB) - Very powerful, needs GPU
```

---

### Step 3: Verify Ollama is Running

```bash
# Should return available models
curl http://localhost:11434/api/tags

# Should return status
curl http://localhost:11434/api/tags | jq .

# Expected response:
{
  "models": [
    {
      "name": "mistral:latest",
      "modified_at": "2024-02-26T10:00:00Z",
      "size": 4109159040,
      "digest": "..."
    }
  ]
}
```

---

### Step 4: Test Ollama Locally

```bash
# Test basic generation
curl http://localhost:11434/api/generate \
  -d '{
    "model": "mistral",
    "prompt": "Why is the sky blue?",
    "stream": false
  }' | jq .

# Should return something like:
{
  "model": "mistral",
  "response": "The sky appears blue because...",
  "done": true
}
```

---

### Step 5: Test Pharmax Chatbot with Ollama

```bash
# Health check endpoint
curl http://127.0.0.1:8000/api/chatbot/health

# Should return:
{
  "status":"ok",
  "ollama_configured":true,
  "message":"ChatBot API is running with Ollama"
}

# Test chatbot
curl -X POST http://127.0.0.1:8000/api/chatbot/ask \
  -H "Content-Type: application/json" \
  -d '{
    "question": "What products do you have?",
    "article_id": 1
  }'

# Should return:
{
  "success": true,
  "answer": "Based on our articles, we offer...",
  "sources": [...]
}
```

---

## 🔧 Configuration

### Environment Variables (`.env`)

```dotenv
# Ollama API endpoint
OLLAMA_API_URL=http://localhost:11434

# Model to use
OLLAMA_MODEL=mistral

# Optional: Different models for different purposes
# OLLAMA_CHATBOT_MODEL=mistral
# OLLAMA_MODERATION_MODEL=neural-chat
```

### Located in: `config/services.yaml`

```yaml
App\Service\OllamaService: ~
```

---

## 📊 Performance Characteristics

| Metric | Value |
|--------|-------|
| **Latency (first token)** | 0.5-2 seconds |
| **Latency (full response)** | 1-5 seconds |
| **Memory Usage** | 2-6GB depending on model |
| **CPU Load** | Low on GPU, High on CPU-only |
| **Cost** | Free (hardware only) |
| **Availability** | Always (local) |

---

## 🎯 Model Selection Guide

### For Chatbot (Recommended: Mistral):

```
┌─ mistral        (4GB, fast, good quality) ✅ RECOMMENDED
├─ neural-chat    (4GB, conversation-optimized)
├─ orca-mini      (2GB, lightweight)
├─ llama2         (7GB, more powerful)
└─ dolphin-mixtral (26GB, very powerful, needs GPU)
```

### For Moderation:
- Keep using HuggingFace `unitary/toxic-bert`
- No changes needed

---

## 🚨 Troubleshooting

### Problem: "Ollama API unavailable"

**Cause**: Ollama not running

**Solution**:
```bash
# Start Ollama service
ollama serve

# OR if using Docker:
docker start ollama
```

---

### Problem: "Connection refused on localhost:11434"

**Cause**: Ollama not installed or not on correct port

**Solution**:
```bash
# Check if Ollama is running
curl http://localhost:11434/api/tags

# Check port in use
netstat -tlnp | grep 11434  # Linux/Mac
netstat -aon | grep 11434   # Windows
```

---

### Problem: "Model not found: mistral"

**Cause**: Ollama model not downloaded

**Solution**:
```bash
# Download the model
ollama pull mistral

# List available models
ollama list
```

---

### Problem: Slow responses (>10 seconds)

**Cause**: Running on CPU instead of GPU

**Solution**:
- Option A: Use lighter model: `ollama pull orca-mini`
- Option B: Install GPU support (NVIDIA CUDA, AMD ROCm)
- Option C: Your CPU might be overloaded

**For better performance with GPU:**
- NVIDIA GPU: Install CUDA from https://developer.nvidia.com/cuda-toolkit
- AMD GPU: Install ROCm from https://rocmdocs.amd.com/

---

### Problem: Out of memory

**Cause**: Model too large for available RAM

**Solution**:
```bash
# Use lighter model
ollama pull orca-mini   # 2GB instead of 4GB

# OR add swap space
# Windows: Virtual Memory settings
# Linux: sudo fallocate -l 8G /swapfile
# Mac: Automatic
```

---

## 🔄 API Reference

### Health Check
```bash
GET /api/chatbot/health
```

Response:
```json
{
  "status": "ok",
  "ollama_configured": true,
  "message": "ChatBot API is running"
}
```

### Ask Question
```bash
POST /api/chatbot/ask

{
  "question": "Your question here",
  "article_id": 123,        # Optional
  "article_title": "Title"  # Optional
}
```

Response:
```json
{
  "success": true,
  "answer": "AI generated response...",
  "sources": [
    {"title": "Article 1", "id": 1},
    {"title": "Article 2", "id": 2}
  ]
}
```

### Get Ollama Status
```bash
GET http://localhost:11434/api/tags
```

---

## 📈 Scaling & Performance Tips

### 1. **Use Lighter Models for Speed**
```bash
ollama pull orca-mini  # Fast (2GB)
ollama pull mistral    # Balanced (4GB) ✅
ollama pull llama2     # Powerful (7GB)
```

### 2. **Enable GPU Acceleration**
Ollama automatically uses GPU if available:
- NVIDIA: CUDA 11.0+
- AMD: ROCm 5.0+
- Intel: Check Ollama docs

### 3. **Optimize Context Window**
```php
// In OllamaService.php - reduce for speed
'top_p' => 0.9,          // Reduce to 0.7 for faster
'top_k' => 40,           // Reduce to 20 for faster
'temperature' => 0.7,    // Higher = slower but more creative
```

### 4. **Enable Response Caching**
```bash
# Proxy responses through Redis
# Implement in ChatBotService if needed
```

---

## 🔒 Security Notes

### Ollama Runs Locally
- ✅ No data sent to external servers
- ✅ Private by default
- ✅ Models stay on your machine

### Recommended Setup
```yaml
# Firewall rules
localhost:11434 - Only allow from your app
```

### Do NOT Expose Port 11434 Publicly
```bash
# BAD: Don't do this
ufw allow 11434  # ← Opens to internet

# GOOD: Only localhost
iptables -A INPUT -i localhost -p tcp --dport 11434 -j ACCEPT
```

---

## 📚 Useful Commands

```bash
# View running models
ollama ps

# View all installed models
ollama list

# Download a model
ollama pull mistral

# Remove a model
ollama rm mistral

# Run model directly
ollama run mistral

# View model details
ollama show mistral

# Copy model
ollama cp mistral my-mistral

# Create custom model from Modelfile
ollama create my-model -f ./Modelfile

# Push to registry
ollama push my-registry/my-model

# Get help
ollama --help
```

---

## 🔄 Docker Compose Setup (Recommended for Production)

Create `docker-compose.override.yaml`:

```yaml
version: '3.8'

services:
  ollama:
    image: ollama/ollama
    container_name: pharmax-ollama
    ports:
      - "11434:11434"
    volumes:
      - ollama_data:/root/.ollama
    environment:
      - OLLAMA_HOST=0.0.0.0:11434
    networks:
      - pharmax
    restart: unless-stopped
    # GPU support (uncomment for NVIDIA)
    # deploy:
    #   resources:
    #     reservations:
    #       devices:
    #         - driver: nvidia
    #           count: 1
    #           capabilities: [gpu]

  # Your Pharmax app
  pharmax-app:
    depends_on:
      - ollama
    environment:
      - OLLAMA_API_URL=http://ollama:11434
    networks:
      - pharmax

volumes:
  ollama_data:

networks:
  pharmax:
```

Run:
```bash
docker-compose up -d
docker-compose exec ollama ollama pull mistral
```

---

## 🎉 You're Done!

Your Pharmax chatbot is now using **Ollama** (local, free, private LLM) instead of Gemini!

### What Changed:
- ✅ Chatbot queries answered by local Mistral model
- ✅ Zero API costs
- ✅ No rate limits
- ✅ Private (data doesn't leave your server)
- ✅ Same chat interface for users

### What Stayed the Same:
- ✅ Comment moderation (HuggingFace)
- ✅ Database
- ✅ UI/Frontend
- ✅ All other features

---

## 📞 Support

If you encounter issues:

1. **Check Ollama is running**: `curl http://localhost:11434/api/tags`
2. **Check logs**: `docker logs pharmax-ollama` (if using Docker)
3. **Verify model downloaded**: `ollama list`
4. **Check firewall**: Ensure port 11434 is accessible
5. **Try different model**: `ollama pull orca-mini` (lighter)

---

**Happy coding! 🚀**

The Pharmax chatbot is now completely self-hosted and independent! 🎉
