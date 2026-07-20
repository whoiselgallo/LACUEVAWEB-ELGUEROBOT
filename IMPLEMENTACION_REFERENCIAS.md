# IMPLEMENTACIÓN DE REFERENCIAS - La Cueva del Güero

## Resumen de Cambios

Se han implementado las referencias de Dify AI y Base de Datos de forma centralizada y segura en toda la aplicación.

---

## 1. Estructura de Configuración

### Archivo: `/config/config.php`
Centro de configuración única que contiene:

```php
// ✅ Dify AI
define('DIFY_API_KEY', 'app-uAuHKtsI6l82PIqdF7e7yiVL');
define('DIFY_URL', 'https://api.dify.ai/v1/chat-messages');
define('DIFY_TIMEOUT', 30);

// ✅ Base de Datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'u115767692_el_guero_bot');
define('DB_USER', 'u115767692_lacueva');
define('DB_PASS', 'eldesmadredelGuero1');
define('DB_CHARSET', 'utf8mb4');

// ✅ Funciones de conexión
function db_connect() { ... }
function call_dify_api() { ... }
function log_conversation() { ... }
```

**Ventajas:**
- ✅ Configuración centralizada
- ✅ Fácil mantenimiento
- ✅ Reutilizable en todos los APIs
- ✅ Manejo de errores consistente

---

## 2. APIs Actualizadas

### `/api/api-el-guero-bot.php`

**Cambios realizados:**

1. **Conexión a BD actualizada**
   ```php
   // ANTES
   $db = new PDO("mysql:host=...", DB_USER, DB_PASS);
   
   // DESPUÉS (usando config)
   $db = db_connect();
   ```

2. **Llamada a Dify centralizada**
   ```php
   // ANTES (curl manual)
   $ch = curl_init(DIFY_URL);
   // ... 20+ líneas de código
   
   // DESPUÉS (función)
   $result = call_dify_api($promptFinal, $user, $visitType);
   if ($result['success']) {
       $answer = $result['answer'];
   }
   ```

3. **Guardado de conversación**
   ```php
   // ANTES
   $stmt = $db->prepare("INSERT INTO conversations ...");
   $stmt->execute([...]);
   
   // DESPUÉS (función reutilizable)
   log_conversation($db, $user, $visitType, $query, $answer);
   ```

4. **Respuestas JSON mejoradas**
   ```php
   // ANTES
   echo json_encode(['answer' => $answer]);
   exit();
   
   // DESPUÉS (función centralizada)
   json_response([
       'success' => true,
       'answer'  => $answer,
       'visitType' => $visitType,
       'timestamp' => date('Y-m-d H:i:s')
   ], 200);
   ```

---

## 3. Frontend Actualizado

### `/paw-agent/paw-api.js`

**Cambios:**
- ✅ Simplificado: usa API única (sin fallback manual)
- ✅ Reintentos inteligentes con espera (1 segundo entre intentos)
- ✅ Manejo de errores mejorado
- ✅ Timeout configurable (10 segundos)
- ✅ Sanitización de inputs

```javascript
// Endpoint único
const MAIN_API = PAW_AGENT_API; // /api/api-el-guero-bot.php

// Reintentos automáticos
while (intentos < RETRIES) {
    try {
        const response = await fetchWithTimeout(MAIN_API, { ... });
        // Éxito
    } catch (err) {
        intentos++;
        if (intentos < RETRIES) {
            await esperar(1000); // Espera 1s
        }
    }
}
```

---

## 4. Seguridad

### Archivo: `/.htaccess`
Protege archivos sensibles:
- ✅ Bloquea acceso directo a `/config/`
- ✅ Protege `config.php`
- ✅ Protege `.env`
- ✅ Headers de seguridad (X-Frame-Options, X-Content-Type-Options)

### Archivo: `/.gitignore`
Previene que se suban:
- ✅ `config/config.php`
- ✅ `.env` y variables de entorno
- ✅ Logs y backups
- ✅ Directorios sensibles

---

## 5. Documentación

### `/config/README.md`
- Descripción de configuración
- Funciones disponibles
- Ejemplos de uso
- Errores comunes
- Guía de seguridad

### `/.env.example`
- Plantilla de variables de entorno
- Notas sobre configuración en producción
- Instrucciones para diferentes plataformas

---

## 6. Flujo de Datos

```
┌─────────────────────┐
│   Frontend (JS)     │
│  (paw-agent.js)     │
└──────────┬──────────┘
           │ fetch() + sanitize
           ▼
┌─────────────────────────────────────────┐
│  /api/api-el-guero-bot.php              │
│                                         │
│  1. Valida entrada                      │
│  2. Conecta a BD (db_connect)           │
│  3. Obtiene contexto de visitante       │
│  4. Consulta memoria (conversations)    │
│  5. Llama a Dify (call_dify_api)        │
│  6. Guarda respuesta (log_conversation) │
│  7. Retorna JSON (json_response)        │
└──────────┬──────────────────────────────┘
           │ response
           ▼
┌────────────────────────┐
│  Frontend (JS)         │
│  Muestra respuesta     │
└────────────────────────┘
```

---

## 7. Checklist de Implementación

- [x] Crear `/config/config.php` con constantes
- [x] Actualizar `/api/api-el-guero-bot.php` para usar config
- [x] Implementar función `call_dify_api()`
- [x] Implementar función `log_conversation()`
- [x] Actualizar `/paw-agent/paw-api.js` con API única
- [x] Crear protección `.htaccess`
- [x] Crear `.gitignore`
- [x] Crear `.env.example`
- [x] Documentación en `/config/README.md`

---

## 8. Próximos Pasos

### Para usar en producción:

1. **Copiar configuración:**
   ```bash
   cp .env.example .env
   ```

2. **Editar valores:**
   ```php
   // .env o config/config.php
   DIFY_API_KEY=tu-key-real
   DB_PASS=tu-password-real
   ```

3. **Crear base de datos:**
   ```sql
   CREATE DATABASE u115767692_el_guero_bot;
   CREATE TABLE conversations (
       id INT AUTO_INCREMENT PRIMARY KEY,
       user_id VARCHAR(255),
       visit_type VARCHAR(50),
       user_message TEXT,
       bot_answer TEXT,
       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
   );
   CREATE TABLE knowledge_base (
       id INT AUTO_INCREMENT PRIMARY KEY,
       content TEXT,
       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
   );
   ```

4. **Probar API:**
   ```bash
   curl -X POST http://localhost/api/api-el-guero-bot.php \
     -H "Content-Type: application/json" \
     -d '{"query":"Hola","visitType":"guest"}'
   ```

---

## 9. Archivos Modificados/Creados

| Archivo | Estado | Descripción |
|---------|--------|-------------|
| `config/config.php` | ✅ CREADO | Configuración centralizada |
| `config/README.md` | ✅ CREADO | Documentación de config |
| `api/api-el-guero-bot.php` | ✅ ACTUALIZADO | Usa config centralizada |
| `paw-agent/paw-api.js` | ✅ ACTUALIZADO | API única y reintentos |
| `.htaccess` | ✅ ACTUALIZADO | Seguridad de archivos |
| `.gitignore` | ✅ ACTUALIZADO | Protege secretos |
| `.env.example` | ✅ CREADO | Plantilla variables |

---

## 10. Referencias Implementadas

### Constantes Dify
```php
define('DIFY_API_KEY', 'app-uAuHKtsI6l82PIqdF7e7yiVL');
define('DIFY_URL', 'https://api.dify.ai/v1/chat-messages');
```

### Constantes BD
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'u115767692_el_guero_bot');
define('DB_USER', 'u115767692_lacueva');
define('DB_PASS', 'eldesmadredelGuero1');
define('DB_CHARSET', 'utf8mb4');
```

---

**Última actualización:** 2026-06-16  
**Versión:** 2.0.1  
**Estatus:** ✅ IMPLEMENTADO
