<?php
/**
 * ═════════════════════════════════════════════════════════════════════════════════
 * CONFIGURACIÓN GENERAL - LA CUEVA DEL GÜERO (POSTGRESQL / NEON.TECH)
 * ═════════════════════════════════════════════════════════════════════════════════
 * 
 * Archivo centralizado de configuración para:
 * - Credenciales de Dify AI (desde variables de entorno)
 * - Configuración de Base de Datos PostgreSQL/Neon (desde variables de entorno)
 * - Funciones de conexión a BD
 * - Funciones auxiliares globales
 * 
 * ⚠️ SECURITY: Este archivo está protegido por .htaccess
 * - No es accesible desde web directamente
 * - Credenciales se cargan desde variables de entorno
 * - Nunca commitear datos sensibles
 */

// ═════════════════════════════════════════════════════════════════════════════════
// CARGAR CONFIGURACIÓN DESDE VARIABLES DE ENTORNO O FALLBACK SEGURO
// ═════════════════════════════════════════════════════════════════════════════════

/**
 * Obtener variable de entorno de forma segura
 */
function getEnvVar($name, $default = null) {
    return getenv($name) ?: $_ENV[$name] ?? $default;
}

// Cargar variables desde el archivo .env si existe localmente
$env_path = __DIR__ . '/../.env';
if (file_exists($env_path)) {
    $lines = file($env_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0) {
            continue;
        }
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim(trim($value), '"\'');
            if (!getenv($name) && !isset($_ENV[$name])) {
                putenv("{$name}={$value}");
                $_ENV[$name] = $value;
            }
        }
    }
}

// ═════════════════════════════════════════════════════════════════════════════════
// DIFY AI - Configuración
// ═════════════════════════════════════════════════════════════════════════════════
define('DIFY_API_KEY', getEnvVar('DIFY_API_KEY', 'app-HFon5L07VUDoe00fMeKudFjT'));
define('DIFY_URL', 'https://api.dify.ai/v1/chat-messages');
define('DIFY_TIMEOUT', 30);

// ═════════════════════════════════════════════════════════════════════════════════
// BASE DE DATOS - PostgreSQL / Neon.tech
// ═════════════════════════════════════════════════════════════════════════════════
define('DB_HOST', getEnvVar('DB_HOST', 'ep-winter-queen-af6tc66y-pooler.c-2.us-west-2.aws.neon.tech'));
define('DB_NAME', getEnvVar('DB_NAME', 'neondb'));
define('DB_USER', getEnvVar('DB_USER', 'neondb_owner'));
define('DB_PASS', getEnvVar('DB_PASS', 'npg_eOUvM7qXj0SZ'));
define('DB_PORT', getEnvVar('DB_PORT', '5432'));

// ═════════════════════════════════════════════════════════════════════════════════
// APLICACIÓN - Configuración General
// ═════════════════════════════════════════════════════════════════════════════════
define('APP_NAME', 'La Cueva del Güero');
define('APP_VERSION', '2.0.2');
define('APP_ENV', 'production');  // development, staging, production
define('ADMIN_USER', getEnvVar('ADMIN_USER', 'admin'));
define('ADMIN_PASS', getEnvVar('ADMIN_PASS', 'eldesmadredelGuero1'));

// ═════════════════════════════════════════════════════════════════════════════════
// FUNCIÓN: Conexión a Base de Datos
// ═════════════════════════════════════════════════════════════════════════════════
function db_connect() {
    try {
        $dsn = 'pgsql:host=' . DB_HOST . 
               ';port=' . DB_PORT . 
               ';dbname=' . DB_NAME . 
               ';sslmode=require';
        
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_PERSISTENT         => false
        ];
        
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
    } catch (PDOException $e) {
        error_log('Database Connection Error: ' . $e->getMessage());
        throw new Exception('Error de conexión a la base de datos: ' . $e->getMessage());
    }
}

// ═════════════════════════════════════════════════════════════════════════════════
// FUNCIÓN: Sanitizar entrada
// ═════════════════════════════════════════════════════════════════════════════════
function sanitize_input($input) {
    if (is_array($input)) {
        return array_map('sanitize_input', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// ═════════════════════════════════════════════════════════════════════════════════
// FUNCIÓN: Respuesta JSON segura
// ═════════════════════════════════════════════════════════════════════════════════
function json_response($data, $status = 200) {
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit();
}

// ═════════════════════════════════════════════════════════════════════════════════
// FUNCIÓN: Llamada a Dify API
// ═════════════════════════════════════════════════════════════════════════════════
function call_dify_api($prompt, $user_id = 'guest', $visit_type = 'guest') {
    $payload = [
        'inputs'         => new stdClass(),
        'query'          => $prompt,
        'response_mode'  => 'blocking',
        'user'           => $user_id,
        'metadata'       => [
            'visit_type' => $visit_type,
            'timestamp'  => date('Y-m-d H:i:s')
        ]
    ];

    $ch = curl_init(DIFY_URL);
    curl_setopt_array($ch, [
        CURLOPT_HTTPHEADER       => [
            'Authorization: Bearer ' . DIFY_API_KEY,
            'Content-Type: application/json'
        ],
        CURLOPT_POST             => true,
        CURLOPT_POSTFIELDS       => json_encode($payload),
        CURLOPT_RETURNTRANSFER   => true,
        CURLOPT_TIMEOUT          => DIFY_TIMEOUT,
        CURLOPT_SSL_VERIFYPEER   => true,
        CURLOPT_SSL_VERIFYHOST   => 2
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);

    curl_close($ch);

    if ($curl_error) {
        error_log('Dify cURL Error: ' . $curl_error);
        return [
            'success' => false,
            'error'   => 'Error de conexión con Dify',
            'code'    => 'CURL_ERROR'
        ];
    }

    if ($http_code !== 200) {
        error_log('Dify API Error: ' . $response);
        return [
            'success' => false,
            'error'   => 'Error de Dify AI',
            'code'    => 'DIFY_ERROR',
            'http_code' => $http_code
        ];
    }

    $data = json_decode($response, true);
    
    if (!isset($data['answer'])) {
        error_log('Invalid Dify response: ' . $response);
        return [
            'success' => false,
            'error'   => 'Respuesta inválida de Dify',
            'code'    => 'INVALID_RESPONSE'
        ];
    }

    return [
        'success' => true,
        'answer'  => $data['answer'],
        'raw'     => $data
    ];
}

// ═════════════════════════════════════════════════════════════════════════════════
// FUNCIÓN: Log de conversación
// ═════════════════════════════════════════════════════════════════════════════════
function log_conversation($db, $user_id, $visit_type, $user_message, $bot_answer) {
    try {
        $stmt = $db->prepare("
            INSERT INTO conversations 
            (user_id, visit_type, user_message, bot_answer, created_at) 
            VALUES (?, ?, ?, ?, NOW())
        ");
        return $stmt->execute([$user_id, $visit_type, $user_message, $bot_answer]);
    } catch (Exception $e) {
        error_log('Log Error: ' . $e->getMessage());
        return false;
    }
}

// ═════════════════════════════════════════════════════════════════════════════════
// CONFIGURACIÓN DE ERROR HANDLING
// ═════════════════════════════════════════════════════════════════════════════════
if (APP_ENV === 'production') {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

ini_set('error_log', __DIR__ . '/../logs/error.log');

// ═════════════════════════════════════════════════════════════════════════════════
// Crear directorio de logs si no existe
// ═════════════════════════════════════════════════════════════════════════════════
$logs_dir = __DIR__ . '/../logs';
if (!is_dir($logs_dir)) {
    @mkdir($logs_dir, 0755, true);
}
?>
