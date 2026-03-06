@echo off
REM ========================================
REM Ollama Setup Script for Windows
REM ========================================
REM This script helps install and configure Ollama

echo.
echo ╔════════════════════════════════════════════════════════╗
echo ║         OLLAMA SETUP FOR PHARMAX CHATBOT               ║
echo ║                                                        ║
echo ║  This script will help you setup Ollama on Windows    ║
echo ╚════════════════════════════════════════════════════════╝
echo.

echo [STEP 1] Checking if Ollama is installed...
ollama --version >nul 2>&1
if errorlevel 1 (
    echo.
    echo ❌ Ollama is NOT installed
    echo.
    echo Please download and install Ollama from:
    echo 👉 https://ollama.com/download
    echo.
    echo After installation, restart this script.
    echo.
    pause
    exit /b 1
) else (
    echo ✅ Ollama is installed
    ollama --version
)

echo.
echo [STEP 2] Checking if Ollama service is running...
timeout /t 2 /nobreak >nul

REM Try to connect to Ollama
powershell -Command "try { $response = Invoke-WebRequest -Uri 'http://localhost:11434/api/tags' -ErrorAction Stop; Write-Host '✅ Ollama is running on http://localhost:11434' } catch { Write-Host '❌ Ollama is NOT running. Starting Ollama...'; Start-Process 'ollama' -ArgumentList 'serve'; timeout /t 5 }" >nul 2>&1

echo.
echo [STEP 3] Checking for Mistral model...
ollama list 2>nul | findstr "mistral" >nul
if errorlevel 1 (
    echo.
    echo ⏳ Mistral model not found. Downloading (this may take 5-10 minutes)...
    echo.
    ollama pull mistral
    if errorlevel 1 (
        echo.
        echo ❌ Failed to download Mistral. Please try manually:
        echo    ollama pull mistral
        pause
        exit /b 1
    )
) else (
    echo ✅ Mistral model is installed
)

echo.
echo [STEP 4] Starting Ollama service...
echo.
echo ⚠️  Do NOT close this window! Ollama service will run here.
echo.
echo To test chatbot, visit: http://127.0.0.1:8000/chatbot
echo.
ollama serve
