<?php
/**
 * ═════════════════════════════════════════════════════════════════════════════════
 * CONFIGURACIÓN GENERAL - LA CUEVA DEL GÜERO (POSTGRESQL / GOOGLE CLOUD SQL)
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
 * Obtener variable de entorno de forma segura.
 * No se usan secretos visibles por defecto en producción.
 */
function getEnvVar($name, $default = null) {
    $value = getenv($name);
    if ($value !== false && $value !== '') {
        return $value;
    }

    if (array_key_exists($name, $_ENV) && $_ENV[$name] !== '') {
        return $_ENV[$name];
    }

    return $default;
}

function getRequiredEnvVar($name) {
    $value = getEnvVar($name);
    if ($value === null || trim((string)$value) === '') {
        throw new RuntimeException("Falta la variable de entorno requerida: {$name}");
    }
    return $value;
}

function getBoolEnvVar($name, $default = false) {
    $value = strtolower((string)getEnvVar($name, $default ? 'true' : 'false'));
    return in_array($value, ['1', 'true', 'yes', 'on'], true);
}

function assert_runtime_config() {
    $required = [
        'DIFY_CHATBOT_API_KEY',
        'DIFY_WORKFLOW_API_KEY',
        'DB_HOST',
        'DB_NAME',
        'DB_USER',
        'DB_PASS'
    ];

    $missing = [];
    foreach ($required as $name) {
        if (trim((string)getEnvVar($name, '')) === '') {
            $missing[] = $name;
        }
    }

    if (!empty($missing)) {
        throw new RuntimeException('Faltan variables de entorno requeridas: ' . implode(', ', $missing));
    }
}

function check_required_db_tables(array $tables) {
    $db = db_connect();
    $stmt = $db->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'");
    $existing = array_map('strval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    $missing = [];

    foreach ($tables as $table) {
        if (!in_array($table, $existing, true)) {
            $missing[] = $table;
        }
    }

    return [
        'connected' => true,
        'missing' => $missing,
        'present' => array_values(array_intersect($tables, $existing))
    ];
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
// DIFY AI - Configuración Centralizada
// ═════════════════════════════════════════════════════════════════════════════════
define('DIFY_CHATBOT_API_KEY', getRequiredEnvVar('DIFY_CHATBOT_API_KEY'));
define('DIFY_CHATBOT_URL', getEnvVar('DIFY_CHATBOT_URL', 'https://api.dify.ai/v1/chat-messages'));

define('DIFY_WORKFLOW_API_KEY', getRequiredEnvVar('DIFY_WORKFLOW_API_KEY'));
define('DIFY_WORKFLOW_URL', getEnvVar('DIFY_WORKFLOW_URL', 'https://api.dify.ai/v1/workflows/run'));

define('DIFY_TIMEOUT', (int)getEnvVar('DIFY_TIMEOUT', 60));

// ═════════════════════════════════════════════════════════════════════════════════
// BASE DE DATOS - PostgreSQL / Neon.tech (Render + Neon)
// ═════════════════════════════════════════════════════════════════════════════════
define('DB_HOST', getRequiredEnvVar('DB_HOST'));
define('DB_NAME', getRequiredEnvVar('DB_NAME'));
define('DB_USER', getRequiredEnvVar('DB_USER'));
define('DB_PASS', getRequiredEnvVar('DB_PASS'));
define('DB_PORT', getEnvVar('DB_PORT', '5432'));

// ═════════════════════════════════════════════════════════════════════════════════
// APLICACIÓN - Configuración General
// ═════════════════════════════════════════════════════════════════════════════════
define('APP_NAME', getEnvVar('APP_NAME', 'La Cueva del Güero'));
define('APP_VERSION', getEnvVar('APP_VERSION', '2.0.2'));
define('APP_ENV', strtolower(getEnvVar('APP_ENV', 'production')));
define('APP_DEBUG', getBoolEnvVar('APP_DEBUG', false));
define('ADMIN_USER', getEnvVar('ADMIN_USER', 'admin'));
define('ADMIN_PASS', getEnvVar('ADMIN_PASS', 'Cueva2026!'));

// ═════════════════════════════════════════════════════════════════════════════════
// FUNCIÓN: Conexión a Base de Datos
// ═════════════════════════════════════════════════════════════════════════════════
function db_connect() {
    try {
        $host = DB_HOST;
        $port = DB_PORT;
        $database = DB_NAME;
        $user = DB_USER;
        $pass = DB_PASS;

        if (preg_match('/^postgresql:\/\//i', $host)) {
            $parsed = parse_url($host);
            if (is_array($parsed) && isset($parsed['host'])) {
                $host = $parsed['host'];
                $port = $parsed['port'] ?? $port;
                $database = isset($parsed['path']) ? ltrim($parsed['path'], '/') : $database;
                $user = $parsed['user'] ?? $user;
                $pass = $parsed['pass'] ?? $pass;
            }
        }

        $isCloudSqlSocket = (strpos($host, '/cloudsql/') === 0 || substr_count($host, ':') === 2);
        $isPostgres = ($isCloudSqlSocket || $port == '5432' || strpos($host, 'neon.tech') !== false || strpos($host, 'supabase') !== false);
        
        if ($isCloudSqlSocket) {
            $socketPath = (strpos($host, '/cloudsql/') === 0) ? $host : '/cloudsql/' . $host;
            $dsn = "pgsql:host={$socketPath};port={$port};dbname={$database}";
        } elseif ($isPostgres) {
                 $dsn = 'pgsql:host=' . $host .
                     ';port=' . $port .
                     ';dbname=' . $database .
                   ';sslmode=require' .
                   ';connect_timeout=10';

            if (strpos($host, 'neon.tech') !== false) {
                $endpoint = preg_replace('/-pooler\./', '.', $host);
                $endpoint = preg_replace('/\..*$/', '', $endpoint);
                if ($endpoint !== '' && stripos($endpoint, 'ep-') === 0) {
                    $dsn .= ';options=endpoint=' . $endpoint;
                }
            }
        } else {
                 $dsn = 'mysql:host=' . $host .
                     ';port=' . $port .
                     ';dbname=' . $database .
                   ';charset=utf8mb4';
        }
        
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_PERSISTENT         => false
        ];
        
        $pdo = new PDO($dsn, $user, $pass, $options);
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
// FUNCIÓN: Llamada a Dify API (Chatbot)
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

    $ch = curl_init(DIFY_CHATBOT_URL);
    curl_setopt_array($ch, [
        CURLOPT_HTTPHEADER       => [
            'Authorization: Bearer ' . DIFY_CHATBOT_API_KEY,
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
            'error'   => 'Error de Dify AI (' . $http_code . '): ' . $response,
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
// FUNCIÓN: Llamada a Dify Workflow (Escaleta / Guiones / Cue Cards)
// ═════════════════════════════════════════════════════════════════════════════════
function call_dify_workflow($inputs, $user_id = 'la-cueva-web') {
    $payload = [
        'inputs'        => $inputs,
        'response_mode' => 'blocking',
        'user'          => $user_id
    ];

    $ch = curl_init(DIFY_WORKFLOW_URL);
    curl_setopt_array($ch, [
        CURLOPT_HTTPHEADER       => [
            'Authorization: Bearer ' . DIFY_WORKFLOW_API_KEY,
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
        error_log('Dify Workflow cURL Error: ' . $curl_error);
        return [
            'success' => false,
            'error'   => 'Error de conexión con Dify Workflow',
            'code'    => 'CURL_ERROR'
        ];
    }

    if ($http_code !== 200) {
        error_log('Dify Workflow API Error: ' . $response);
        return [
            'success' => false,
            'error'   => 'Error de Dify Workflow (' . $http_code . '): ' . $response,
            'code'    => 'DIFY_ERROR',
            'http_code' => $http_code
        ];
    }

    $data = json_decode($response, true);
    return [
        'success' => true,
        'data'    => $data['data'] ?? [],
        'outputs' => $data['data']['outputs'] ?? []
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
// FUNCIÓN: Rotación de Claves Gemini API
// ═════════════════════════════════════════════════════════════════════════════════
function get_gemini_api_key() {
    $keysStr = getEnvVar('GEMINI_API_KEYS') ?: getEnvVar('GEMINI_API_KEY');
    if (empty($keysStr)) {
        return '';
    }
    $keys = explode(',', $keysStr);
    $keys = array_filter(array_map('trim', $keys));
    if (empty($keys)) {
        return '';
    }
    $randomIndex = array_rand($keys);
    return $keys[$randomIndex];
}

/**
 * FUNCIÓN CENTRAL: Llamar a la API de Gemini con rotación de claves y fallback automático de modelos
 */
function call_gemini_generate($payload, $apiKey = null) {
    if (!$apiKey) {
        $apiKey = get_gemini_api_key();
    }
    if (empty($apiKey)) {
        return ['success' => false, 'error' => 'No hay claves de API de Gemini configuradas.'];
    }

    $models = ['gemini-3.6-flash', 'gemini-3-flash-preview'];
    $lastError = '';

    foreach ($models as $model) {
        $ch = curl_init("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key=" . $apiKey);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0
        ]);
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code === 200) {
            $data = json_decode($response, true);
            $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
            if (!empty($text)) {
                return ['success' => true, 'text' => $text, 'model' => $model, 'raw' => $data];
            }
        } else {
            $errData = json_decode($response, true);
            $lastError = $errData['error']['message'] ?? "Error HTTP {$http_code}: {$response}";
        }
    }

    return ['success' => false, 'error' => $lastError];
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
