<?php
/**
 * API: CONECTOR GEMINI - EL GÜERO BOT
 * Mascota virtual y asistente conversacional de La Cueva del Güero
 * Endpoint: /api/api-el-guero-bot.php
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../config/config.php';

$inputData = json_decode(file_get_contents('php://input'), true);
$query     = trim($inputData['query'] ?? '');
$userId    = trim($inputData['user'] ?? 'usuario_paw_web');
$visitType = trim($inputData['visitType'] ?? 'guest');

if (empty($query)) {
    echo json_encode([
        'answer' => '¡Qué onda carnal! 🐾 Soy El Güero Bot. ¿Qué andas buscando en la cueva? ¿Quieres ser invitado, ver el desmadre o qué tranza?'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$geminiApiKey = get_gemini_api_key();
if (empty($geminiApiKey)) {
    echo json_encode([
        'answer' => '¡Guau! 🐶 Algo anda fallando con mi olfato digital, carnal. Échale un WhatsApp al Junior (+52 686 212 4372) para platicar en directo.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$systemPrompt = "# SYSTEM INSTRUCTIONS: EL GÜERO BOT - LA CUEVA DEL GÜERO PODCAST\n\n" .
                "## 1. ROL Y PERSONALIDAD\n" .
                "- Identidad: Eres 'El Güero Bot', el perro guardián inteligente y la mascota virtual oficial del podcast 'La Cueva del Güero' en Mexicali, Baja California.\n" .
                "- Tono: Relajado, callejero, norteño, directo y muy amigable. Hablas con la raza de forma chida. Ocasionalmente usas expresiones caninas y de barrio (ej: '*mueve la cola*', '*salta de emoción*', '*olfatea*', 'carnal', 'raza', 'chido', 'al tiro', 'la cueva', 'desmadre').\n" .
                "- Conductores: Tus patrones son 'El Junior' y 'El Gallo', los meros meros de La Cueva.\n\n" .
                "## 2. OBJETIVOS CLAVE\n" .
                "1. Captación de Invitados / Patrocinios: Si alguien quiere salir en el podcast o patrocinar, pídele amablemente su nombre, WhatsApp y de qué le gustaría hablar.\n" .
                "2. Promoción del Canal de YouTube: Invita a la gente a suscribirse al canal oficial: https://www.youtube.com/@LacuevadelGueroPodcast\n" .
                "3. Contacto directo por WhatsApp: Para cosas urgentes, dales el contacto de Junior (+52 686 212 4372).\n\n" .
                "## 3. REGLAS\n" .
                "- Mantén tu personaje siempre. Nunca digas 'Soy un modelo de IA'. Eres El Güero Bot.\n" .
                "- Respuestas cortas, dinámicas y directas (máximo 3 a 4 oraciones).";

$payload = [
    "contents" => [
        [
            "role" => "user",
            "parts" => [
                ["text" => $query]
            ]
        ]
    ],
    "systemInstruction" => [
        "parts" => [
            ["text" => $systemPrompt]
        ]
    ],
    "generationConfig" => [
        "temperature" => 0.75,
        "maxOutputTokens" => 1024
    ]
];

$geminiRes = call_gemini_generate($payload);

if ($geminiRes['success']) {
    $answer = trim($geminiRes['text']);
    
    // Registrar si la base de datos está disponible
    try {
        $db = db_connect();
        if ($db) {
            log_conversation($db, $userId, $visitType, $query, $answer);
        }
    } catch (Exception $e) {
        error_log("Paw agent log error: " . $e->getMessage());
    }

    echo json_encode([
        'success' => true,
        'answer'  => $answer,
        'model'   => $geminiRes['model']
    ], JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode([
        'success' => false,
        'answer'  => "¡Guau carnal! 🐾 Tuve un pequeño tropezón de red: {$geminiRes['error']}. Pero no te agüites, pregúntame de nuevo o dale click al WhatsApp de Junior."
    ], JSON_UNESCAPED_UNICODE);
}
exit();
