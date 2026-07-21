<?php
/**
 * ═════════════════════════════════════════════════════════════════════════════════
 * API - GÜERO BOT (El Güero Bot)
 * Endpoint: /api/api-el-guero-bot.php
 * Método: POST
 * ═════════════════════════════════════════════════════════════════════════════════
 */

// Headers CORS
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Solo POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    json_response(['error' => 'Método no permitido. Usa POST.'], 405);
}

// Cargar configuración
require_once __DIR__ . '/../config/config.php';

// Leer y validar entrada
$input = json_decode(file_get_contents('php://input'), true);
$query = sanitize_input($input['query'] ?? "");
$visitType = sanitize_input($input['visitType'] ?? 'guest');
$user = sanitize_input($input['user'] ?? 'anonimo');

// Validar query
if (empty($query)) {
    json_response(['error' => 'Query vacío. Por favor escribe algo.'], 400);
}

try {
    // Conexión BD usando función de config
    $db = db_connect();

    // CONTEXTO
    switch ($visitType) {
        case 'team':
            $contexto = "Hablas con alguien del EQUIPO de La Cueva del Güero. Sé directo, operativo, usa jerga interna.";
            break;
        case 'guest':
            $contexto = "Hablas con un INVITADO al podcast. Sé acogedor pero callejero, hazlo sentir en confianza.";
            break;
        case 'follower':
            $contexto = "Hablas con un SEGUIDOR/FAN. Sé agradecido, cabrón pero accesible, invita a participar.";
            break;
        default:
            $contexto = "Visitante desconocido.";
    }

    // MEMORIA
    $kb = $db->query("SELECT storytelling FROM knowledge_base WHERE storytelling IS NOT NULL ORDER BY id DESC LIMIT 10")
             ->fetchAll(PDO::FETCH_COLUMN);

    $conv = $db->query("SELECT user_message, bot_answer FROM conversations ORDER BY id DESC LIMIT 5")
               ->fetchAll(PDO::FETCH_ASSOC);

    $memoria = "";
    foreach ($kb as $item) $memoria .= "- $item\n";
    foreach ($conv as $c) {
        $memoria .= "Usuario: {$c['user_message']}\nBot: {$c['bot_answer']}\n\n";
    }

    // PROMPT FINAL
    $promptFinal = "
Eres Güero Bot, el asistente creativo oficial de La Cueva del Güero.

### PERSONALIDAD
- Tono callejero, directo, norteño, sin poses.
- Humor natural, ingenioso, ligero.
- Hablas como compa de confianza.
- Respondes con flow y autenticidad.

### CONTEXTO DEL VISITANTE
$contexto

### MEMORIA DEL SISTEMA
$memoria

### REGLAS
1. Usa la memoria si es útil.
2. Si falta información, pregunta.
3. No inventes datos personales.
4. Mantén coherencia con el estilo del podcast.

### FORMATOS
**ESCALETA**  
- Título  
- Tema  
- Bloques narrativos  
- Momentos clave  
- Cue cards  

**GUION**  
- Escenas  
- Diálogo Güero–Invitado  
- Indicaciones de tono  

### INSTRUCCIÓN FINAL
El usuario dice: \"$query\"

Responde con flow, máximo 3 líneas si es conversación normal.  
Si pide escaleta o guion, usa formato completo.
";

    // LLAMADA A DIFY usando función centralizada
    $difyResult = call_dify_api($promptFinal, $user, $visitType);

    if (!$difyResult['success']) {
        http_response_code(500);
        json_response([
            'error' => $difyResult['error'],
            'code'  => $difyResult['code']
        ], 500);
    }

    $answer = $difyResult['answer'];

    // GUARDAR CONVERSACIÓN usando función centralizada
    log_conversation($db, $user, $visitType, $query, $answer);

    // RESPUESTA exitosa
    json_response([
        'success' => true,
        'answer'  => $answer,
        'visitType' => $visitType,
        'timestamp' => date('Y-m-d H:i:s')
    ], 200);

} catch (PDOException $e) {
    error_log('Database Error: ' . $e->getMessage());
    http_response_code(500);
    json_response([
        'error' => 'Error de base de datos: ' . $e->getMessage(),
        'code'  => 'DB_ERROR'
    ], 500);

} catch (Exception $e) {
    error_log('API Error: ' . $e->getMessage());
    http_response_code(500);
    json_response([
        'error' => 'Error del servidor: ' . $e->getMessage(),
        'code'  => 'SERVER_ERROR'
    ], 500);
}
