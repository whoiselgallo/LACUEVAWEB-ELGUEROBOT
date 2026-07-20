<?php
/**
 * ═════════════════════════════════════════════════════════════════════════════════
 * CONFIGURACIÓN GENERAL - LA CUEVA DEL GÜERO
 * ═════════════════════════════════════════════════════════════════════════════════
 * 
 * Archivo centralizado de configuración para:
 * - Credenciales de Dify AI (desde variables de entorno)
 * - Configuración de Base de Datos (desde variables de entorno)
 * - Funciones de conexión
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

// ═════════════════════════════════════════════════════════════════════════════════
// DIFY AI - Configuración
// ═════════════════════════════════════════════════════════════════════════════════
define('DIFY_API_KEY', getEnvVar('DIFY_API_KEY', 'app-uAuHKtsI6l82PIqdF7e7yiVL'));
define('DIFY_URL', 'https://api.dify.ai/v1/chat-messages');
define('DIFY_TIMEOUT', 30);

// ═════════════════════════════════════════════════════════════════════════════════
// BASE DE DATOS - MySQL/MariaDB
// ═════════════════════════════════════════════════════════════════════════════════
define('DB_HOST', getEnvVar('DB_HOST', 'localhost'));
define('DB_NAME', getEnvVar('DB_NAME', 'u115767692_el_guero_bot'));
define('DB_USER', getEnvVar('DB_USER', 'u115767692_lacueva'));
define('DB_PASS', getEnvVar('DB_PASS', 'eldesmadredelGuero1'));
define('DB_CHARSET', 'utf8mb4');
define('DB_PORT', 3306);

// ═════════════════════════════════════════════════════════════════════════════════
// APLICACIÓN - Configuración General
// ═════════════════════════════════════════════════════════════════════════════════
define('APP_NAME', 'La Cueva del Güero');
define('APP_VERSION', '2.0.1');
define('APP_ENV', 'production');  // development, staging, production

// ═════════════════════════════════════════════════════════════════════════════════
// FUNCIÓN: Conexión a Base de Datos
// ═════════════════════════════════════════════════════════════════════════════════
function db_connect() {
    try {
        $dsn = 'mysql:host=' . DB_HOST . 
               ';port=' . DB_PORT . 
               ';dbname=' . DB_NAME . 
               ';charset=' . DB_CHARSET;
        
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
        throw new Exception('Error de conexión a la base de datos');
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
