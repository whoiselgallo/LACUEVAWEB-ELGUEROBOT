# Configuración - La Cueva del Güero

## Descripción

Este directorio contiene los archivos de configuración centralizada para La Cueva del Güero, incluyendo credenciales de Dify AI y configuración de base de datos.

## Archivos

### `config.php`
Archivo principal de configuración que contiene:
- **Credenciales de Dify AI**
  - `DIFY_API_KEY`: Token de autenticación para Dify
  - `DIFY_URL`: Endpoint de la API de Dify
  - `DIFY_TIMEOUT`: Timeout de conexión (segundos)

- **Configuración de Base de Datos MySQL**
  - `DB_HOST`: Host del servidor MySQL (localhost por defecto)
  - `DB_NAME`: Nombre de la base de datos
  - `DB_USER`: Usuario de MySQL
  - `DB_PASS`: Contraseña de MySQL
  - `DB_CHARSET`: Juego de caracteres (utf8mb4)
  - `DB_PORT`: Puerto MySQL (3306 por defecto)

- **Configuración de Aplicación**
  - `APP_NAME`: Nombre de la aplicación
  - `APP_VERSION`: Versión actual
  - `APP_ENV`: Entorno (production, staging, development)

## Funciones Disponibles

### `db_connect()`
Establece conexión PDO a la base de datos con manejo de errores.
```php
$db = db_connect();
```

### `call_dify_api($prompt, $user_id, $visit_type)`
Llama a Dify API con el prompt y retorna respuesta estructurada.
```php
$result = call_dify_api("Mi mensaje", "usuario123", "guest");
if ($result['success']) {
    echo $result['answer'];
}
```

### `log_conversation($db, $user_id, $visit_type, $user_message, $bot_answer)`
Registra conversaciones en la base de datos.
```php
log_conversation($db, "usuario123", "guest", "Hola", "¡Ey!");
```

### `sanitize_input($input)`
Sanitiza entrada de usuario.
```php
$safe_input = sanitize_input($_POST['message']);
```

### `json_response($data, $status)`
Retorna respuesta JSON estándar.
```php
json_response(['success' => true, 'data' => $result], 200);
```

## Uso en APIs

### Ejemplo: api-el-guero-bot.php

```php
require_once __DIR__ . '/../config/config.php';

// Conexión a BD
$db = db_connect();

// Obtener entrada
$query = sanitize_input($_POST['query']);

// Llamar a Dify
$result = call_dify_api($query, $user_id, $visit_type);

if ($result['success']) {
    // Guardar conversación
    log_conversation($db, $user_id, $visit_type, $query, $result['answer']);
    
    // Retornar respuesta
    json_response(['success' => true, 'answer' => $result['answer']]);
} else {
    json_response(['error' => $result['error']], 500);
}
```

## Seguridad

⚠️ **IMPORTANTE**:
- Este directorio está protegido por `.htaccess`
- Nunca confirmes `config.php` a control de versiones
- Usa variables de entorno en producción
- Mantén credenciales seguras

## Logs

Los errores se registran en `/logs/error.log`. El directorio se crea automáticamente si no existe.

## Variables de Entorno

Para producción, considera usar variables de entorno:

```php
define('DIFY_API_KEY', getenv('DIFY_API_KEY') ?: 'app-uAuHKtsI6l82PIqdF7e7yiVL');
define('DB_PASS', getenv('DB_PASS') ?: 'eldesmadredelGuero1');
```

## Errores Comunes

| Error | Causa | Solución |
|-------|-------|----------|
| `Error de conexión a BD` | Credenciales incorrectas | Verificar `DB_USER`, `DB_PASS`, `DB_HOST` |
| `Error de Dify` | Token inválido o expirado | Verificar `DIFY_API_KEY` |
| `CURL_ERROR` | Problemas de red | Verificar conectividad y firewall |
| `Database not found` | BD no existe | Crear base de datos con nombre correcto |

---

**Última actualización**: 2026-06-16  
**Versión**: 2.0.1
