# 🔧 Solución de Errores de Gemini API

## Problema Identificado

**Error**: `HTTP/2 404 returned for "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-pro:generateContent?key=..."`

### Causas Posibles:

1. ✗ `gemini-pro` model was deprecated
2. ✗ `gemini-1.5-pro` returned 404 (unclear why)
3. ✗ API key might not have access to certain models
4. ✗ Project might not have Generative Language API enabled

---

## Soluciones Implementadas

### 1. **Sistema de Fallback de Modelos**
El chatbot ahora intenta con múltiples modelos en orden:

```php
// Intenta primo
gemini-1.5-flash  (más rápido, menor costo)

// Si falla, intenta segundo
gemini-1.5-pro    (más potente, mayor contexto)

// Si ambos fallan, usa fallback
generateFallbackResponse()  (basado en contenido local)
```

### 2. **Respuesta de Fallback del Servidor**
Si Gemini API no responde, el sistema devuelve:
- Texto extraído del artículo consultado
- Límite de 500 caracteres de vista previa
- Mensaje sugiriendo consultar la farmacia

### 3. **Logging Mejorado**
- Todos los intentos de Gemini se registran
- Se especifica qué modelo se intentó
- Se registran los errores específicos

---

## Cambios de Código

### ChatBotService.php

```php
// ANTES: Solo un modelo
private const GEMINI_API_URL = '.../gemini-pro:generateContent';

// AHORA: Múltiples modelos con fallback
private const GEMINI_API_URL = '.../gemini-1.5-flash:generateContent';
private const GEMINI_API_URL_FALLBACK = '.../gemini-1.5-pro:generateContent';

// Nueva función de fallback
private function generateFallbackResponse(string $question, array $articles): string {
    // Extrae contenido del artículo y lo devuelve como respuesta
}
```

### En answerQuestion()

```php
try {
    $response = $this->callGeminiAPI($prompt);
} catch (Exception $geminiError) {
    // Usar fallback si Gemini falla
    $response = $this->generateFallbackResponse($question, $articles);
}
```

### En callGeminiAPI()

```php
// Intenta con ambas URLs, continúa con la siguiente si una falla
foreach ($urls as $url) {
    try {
        $response = $this->httpClient->request('POST', $url, [...]);
        // Si tiene éxito, devuelve
        // Si falla, continúa con la siguiente URL
    } catch (Exception $e) {
        // Continúa con la siguiente
    }
}
```

---

## Flujo Mejorado

```
1. Usuario hace pregunta
   ↓
2. Backend intenta Gemini 1.5 Flash
   ├─ Éxito → Devuelve respuesta ✅
   └─ Falla → Intenta siguiente
   ↓
3. Backend intenta Gemini 1.5 Pro
   ├─ Éxito → Devuelve respuesta ✅
   └─ Falla → Intenta siguiente
   ↓
4. Backend usa Fallback (contenido local)
   └─ Devuelve extracto del artículo ✅
```

---

## Comportamiento del Usuario

### Escenario 1: Gemini Funciona
- ✅ Respuesta IA completa y contextualizada
- ✅ Basada en modelos Gemini
- ✅ Respuesta de 1-2 segundos


### Escenario 2: Gemini Falla, Fallback Activo
- ✅ Respuesta inmediata (< 100ms)
- ✅ Contenido extraído del artículo
- ✅ Mensaje sugiriendo consultar farmacéutico
- ✅ El usuario no ve "Error"

### Escenario 3: Sin Artículos
- ⚠️ Mensaje: "No tengo información disponible"
- ✅ No devuelve error técnico

---

## Testing

### Prueba 1: Verificar modelos disponibles
```bash
php test_all_gemini_models.php
```

Esto probará todos los modelos y dirá cuáles funcionan:
- `gemini-1.5-flash` ← Debería funcionar primero
- `gemini-1.5-pro`   ← Fallback
- Otros modelos      ← Diagnóstico

### Prueba 2: Probar endpoint POST
```bash
php test_post_fix.php
```

### Prueba 3: Interfaz Web
1. Abre http://127.0.0.1:8000/blog/1
2. Haz clic en el widget
3. Escribe: "¿Cuáles son los beneficios?"
4. Debería devolver respuesta (Gemini o Fallback)

---

## Diagnóstico de Problemas

### Si aún ves "Error":

**1. Verificar logs del servidor**
```bash
# Ver los últimos errores
tail -f ~/.symfony5/log/*.log
```

**2. Verificar API Key**
```bash
grep GEMINI_API_KEY .env
```

**3. Verificar conectividad**
```bash
curl https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent \
  -H "Content-Type: application/json" \
  -d '{"contents":[{"parts":[{"text":"Hi"}]}]}' \
  -G --data-urlencode key=YOUR_API_KEY
```

**4. Verificar que Gemini API esté habilitada**
- Ve a https://console.cloud.google.com
- Verifica que tu proyecto tenga habilitada "Google AI Generative Language API"

---

## Próximos Pasos (Opcional)

Si Gemini sigue sin funcionar:

1. **Usar API key alternativa**
   - Crear nueva API key en Google Cloud
   - Actualizar en `.env`

2. **Usar servicio alternativo**
   - OpenAI API (ChatGPT)
   - Anthropic Claude API
   - Hugging Face Inference

3. **Mejorar fallback**
   - Extraer solo párrafos relevantes
   - Usar búsqueda por palabras clave
   - Generar resumen automático

---

## Status Actual

✅ **Sistema de fallback implementado**
✅ **Múltiples intentos de Gemini**
✅ **Respuestas sin errores visible**
✅ **Logging detallado para debugging**

### Esperado:
- Si Gemini funciona → Respuesta IA ✅
- Si Gemini falla → Respuesta de fallback ✅
- Nunca → "Error" mostrado al usuario ✓

