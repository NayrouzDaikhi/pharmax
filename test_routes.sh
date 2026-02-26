#!/bin/bash

echo "Testing ChatBot API with curl..."
echo ""

echo "=== Test 1: Health Endpoint ==="
echo "GET /api/chatbot/health"
curl -i -X GET http://127.0.0.1:8000/api/chatbot/health 2>&1
echo -e "\n\n"

echo "=== Test 2: Debug Endpoint ===" 
echo "GET /api/chatbot/debug"
curl -i -X GET http://127.0.0.1:8000/api/chatbot/debug 2>&1
echo -e "\n\n"

echo "=== Test 3: Root Routes ==="
echo "GET /chatbot"
curl -i -X GET http://127.0.0.1:8000/chatbot 2>&1 | head -20
echo -e "\n\n"

echo "=== Test 4: Ask Endpoint ==="
echo "POST /api/chatbot/ask"
curl -i -X POST http://127.0.0.1:8000/api/chatbot/ask \
  -H "Content-Type: application/json" \
  -d '{"question": "test"}' 2>&1 | head -30
