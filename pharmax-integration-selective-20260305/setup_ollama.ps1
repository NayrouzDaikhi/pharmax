# ========================================
# Ollama Setup Script for Windows (PowerShell)
# ========================================

Write-Host ""
Write-Host "╔════════════════════════════════════════════════════════╗"
Write-Host "║         OLLAMA SETUP FOR PHARMAX CHATBOT               ║"
Write-Host "║                                                        ║"
Write-Host "║  This script will help you setup Ollama on Windows    ║"
Write-Host "╚════════════════════════════════════════════════════════╝"
Write-Host ""

# STEP 1: Check if installed
Write-Host "[STEP 1] Checking if Ollama is installed..."
$ollamaCheck = Try { ollama --version } Catch { $null }

if ($null -eq $ollamaCheck) {
    Write-Host ""
    Write-Host "❌ Ollama is NOT installed"
    Write-Host ""
    Write-Host "Please download and install Ollama from:"
    Write-Host "👉 https://ollama.com/download"
    Write-Host ""
    Write-Host "After installation, run this script again."
    Write-Host ""
    Read-Host "Press Enter to continue"
    exit
} else {
    Write-Host "✅ Ollama is installed"
    Write-Host $ollamaCheck
}

# STEP 2: Check if running
Write-Host ""
Write-Host "[STEP 2] Checking if Ollama service is running..."

$ollamaRunning = $false
Try {
    $response = Invoke-WebRequest -Uri "http://localhost:11434/api/tags" -ErrorAction Stop
    Write-Host "✅ Ollama is running on http://localhost:11434"
    $ollamaRunning = $true
} Catch {
    Write-Host "❌ Ollama is NOT running. Starting Ollama service..."
    Start-Process "ollama" -ArgumentList "serve" -WindowStyle Minimized
    Start-Sleep -Seconds 5
}

# STEP 3: Check for Mistral model
Write-Host ""
Write-Host "[STEP 3] Checking for Mistral model..."

Try {
    $models = ollama list 2>$null
    if ($models -match "mistral") {
        Write-Host "✅ Mistral model is installed"
    } else {
        Write-Host ""
        Write-Host "⏳ Mistral model not found. Downloading (5-10 minutes)..."
        Write-Host "This may take a while, please wait..."
        Write-Host ""
        ollama pull mistral
        if ($LASTEXITCODE -ne 0) {
            Write-Host ""
            Write-Host "❌ Failed to download. Try manually:"
            Write-Host "   ollama pull mistral"
            Read-Host "Press Enter to continue"
            exit 1
        }
    }
} Catch {
    Write-Host "⚠️  Could not check models. Continuing..."
}

# STEP 4: Start Ollama server
Write-Host ""
Write-Host "[STEP 4] Starting Ollama service..."
Write-Host ""
Write-Host "⚠️  Do NOT close this window! Ollama will run here."
Write-Host ""
Write-Host "To test chatbot, visit:"
Write-Host "  👉 http://127.0.0.1:8000/chatbot"
Write-Host ""
Write-Host "Press Ctrl+C to stop Ollama when done."
Write-Host ""

ollama serve
