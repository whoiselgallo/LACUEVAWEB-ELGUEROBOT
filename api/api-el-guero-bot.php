<?php
/**
 * API: CONECTOR GEMINI & SMART ENGINE - EL GÜERO BOT
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
        'success' => true,
        'answer'  => '¡Qué onda carnal! 🐾 Soy El Güero Bot. ¿Qué andas buscando en la cueva? ¿Quieres ser invitado, ver el desmadre o qué tranza?'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Motor de Respuestas Nativas Inteligentes de El Güero Bot
 */
function guero_bot_smart_response($query, $visitType = 'guest') {
    $q = mb_strtolower($query, 'UTF-8');

    // 1. Tracking / Estado de episodio
    if (strpos($q, 'track') !== false || strpos($q, 'episodio') !== false || strpos($q, 'código') !== false || strpos($q, 'codigo') !== false || strpos($q, 'estado') !== false || strpos($q, 'avance') !== false) {
        return "¡Simón carnal! 🛰️ *mueve la colita* Puedes consultar el avance de tu episodio, edición y clips en vivo en nuestro portal de [Tracking de Invitados](https://s.lacuevadelguero.com/). Ingresa tu código o búscate con tu correo.";
    }

    // 2. Invitado / Storytelling / Participar / Grabar
    if (strpos($q, 'invitad') !== false || strpos($q, 'participar') !== false || strpos($q, 'grabar') !== false || strpos($q, 'ir al programa') !== false || strpos($q, 'entrevista') !== false || strpos($q, 'cuestionario') !== false || strpos($q, 'historia') !== false) {
        return "¡A huevo, compa! 🎙️🔥 En La Cueva del Güero buscamos historias reales, crudas y de barrio. Llena de volada tu [Cuestionario de Storytelling](storytelling-invitado.html) o mándale WhatsApp directo al Junior: [WhatsApp Junior](https://wa.me/526862124372).";
    }

    // 3. Cesión de derechos
    if (strpos($q, 'cesion') !== false || strpos($q, 'cesión') !== false || strpos($q, 'derecho') !== false || strpos($q, 'firma') !== false || strpos($q, 'consentimiento') !== false) {
        return "Aquí mero puedes revisar y firmar tu formato oficial digital: [Carta de Cesión de Derechos](cesion-derechos.html). ¡Todo derecho y en regla!";
    }

    // 4. Patrocinios / Publicidad / Marcas / Negocios
    if (strpos($q, 'patrocini') !== false || strpos($q, 'publicidad') !== false || strpos($q, 'marca') !== false || strpos($q, 'negocio') !== false || strpos($q, 'anunciar') !== false) {
        return "¡Eso es todo, visión chingona! 💼 Para patrocinios, menciones de marca e integraciones comerciales en el podcast, mándale mensaje a producción al WhatsApp [+52 686 212 4372](https://wa.me/526862124372).";
    }

    // 5. YouTube / Canal / Redes
    if (strpos($q, 'youtube') !== false || strpos($q, 'canal') !== false || strpos($q, 'suscrib') !== false || strpos($q, 'video') !== false || strpos($q, 'spotify') !== false) {
        return "¡Cáele a la manada! 🐺 Échale un ojo a los capítulos completos y clips virales en nuestro canal oficial de [YouTube @LacuevadelGueroPodcast](https://www.youtube.com/@LacuevadelGueroPodcast). ¡Suscríbete y déjanos tu like!";
    }

    // 6. Quién eres / Identidad / Bot / Perro
    if (strpos($q, 'quien eres') !== false || strpos($q, 'quién eres') !== false || strpos($q, 'bot') !== false || strpos($q, 'perro') !== false || strpos($q, 'güero') !== false || strpos($q, 'guero') !== false) {
        return "¡Guau! 🐶 Soy **El Güero Bot**, el perro guardián y mascota oficial de La Cueva del Güero. Cuido el estudio en Mexicali y ayudo a la raza con invitados, seguimiento y cotorreo con El Junior y El Gallo.";
    }

    // 7. Saludos
    if (strpos($q, 'hola') !== false || strpos($q, 'que onda') !== false || strpos($q, 'qué onda') !== false || strpos($q, 'que tranza') !== false || strpos($q, 'qué tranza') !== false || strpos($q, 'buenas') !== false) {
        return "¡Qué tranza carnal! 🐾 *salta de emoción* Bienvenido a La Cueva del Güero. ¿Qué andas tramando? ¿Quieres ser invitado al podcast, checar tu tracking o tirar plática?";
    }

    // 8. Contacto / Ubicación / Mexicali
    if (strpos($q, 'contacto') !== false || strpos($q, 'donde') !== false || strpos($q, 'dónde') !== false || strpos($q, 'ubicacion') !== false || strpos($q, 'mexicali') !== false) {
        return "¡Transmitiendo desde Mexicali, Baja California! 🌵 La mera frontera norteña. Si quieres caerle o platicar con el equipo, mándale WhatsApp al Junior al [+52 686 212 4372](https://wa.me/526862124372).";
    }

    // Fallback conversacional auténtico
    return "¡Qué onda carnal! 🐾 *olfatea la pantalla* Aquí en La Cueva del Güero andamos siempre al tiro. Si quieres jalarte como invitado llena tu [Cuestionario de Storytelling](storytelling-invitado.html) o mándale mensaje al Junior por [WhatsApp](https://wa.me/526862124372). ¡Pásale a la cueva!";
}

$answer = '';
$modelUsed = 'guero-bot-engine-v2';

// Intentar conexión con Gemini si hay API Key
$geminiApiKey = get_gemini_api_key();
if (!empty($geminiApiKey)) {
    $systemPrompt = "# SYSTEM INSTRUCTIONS: EL GÜERO BOT - LA CUEVA DEL GÜERO PODCAST\n" .
                    "- Identidad: Eres 'El Güero Bot', el perro guardián inteligente y mascota oficial del podcast 'La Cueva del Güero' en Mexicali, BC.\n" .
                    "- Tono: Relajado, norteño, callejero, divertido y muy amigable. Usa ocasionalmente expresiones caninas y de barrio ('*mueve la cola*', 'carnal', 'raza', 'al tiro', 'la cueva').\n" .
                    "- Patrones: El Junior y El Gallo.\n" .
                    "- WhatsApp oficial: +52 686 212 4372\n" .
                    "- Enlaces clave:\n" .
                    "  * Cuestionario de invitado: storytelling-invitado.html\n" .
                    "  * Tracking de episodio: https://s.lacuevadelguero.com/\n" .
                    "  * Cesión de derechos: cesion-derechos.html\n" .
                    "  * YouTube: https://www.youtube.com/@LacuevadelGueroPodcast\n" .
                    "- Respuestas directas, chidas y breves (máximo 3 o 4 oraciones).";

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
            "maxOutputTokens" => 512
        ]
    ];

    $geminiRes = call_gemini_generate($payload, $geminiApiKey);
    if ($geminiRes['success'] && !empty(trim($geminiRes['text'] ?? ''))) {
        $answer = trim($geminiRes['text']);
        $modelUsed = $geminiRes['model'] ?? 'gemini-flash';
    }
}

// Si Gemini no está disponible o falló, utilizar el motor nativo de El Güero Bot
if (empty($answer)) {
    $answer = guero_bot_smart_response($query, $visitType);
}

// Registrar conversación en Base de Datos si está disponible
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
    'model'   => $modelUsed
], JSON_UNESCAPED_UNICODE);
exit();
