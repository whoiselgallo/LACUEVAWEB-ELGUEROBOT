<?php
/**
 * API - Generador de Guion (El Güero Bot)
 * Endpoint: /api/api-guion.php
 * Método: POST
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido. Usa POST.']);
    exit();
}

// Cargar configuración central
require_once __DIR__ . '/../config/config.php';

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'No se recibió información del invitado']);
    exit();
}

// Normalizar estructura (por si viene como array)
$inv = isset($input["nombre"]) ? $input : (isset($input[0]) ? $input[0] : null);

if (!$inv) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Formato inválido de invitado']);
    exit();
}

// Extraer ficha
$ficha = isset($inv["ficha"]) ? $inv["ficha"] : $inv;

// Variables seguras
$nombre        = $inv["nombre"]        ?? "Invitado";
$ocupacion     = $ficha["ocupacion"]   ?? "Ocupación no registrada";
$barrio        = $ficha["barrio"]      ?? "Barrio no registrado";
$herida        = $ficha["herida"]      ?? "Herida no registrada";
$momento       = $ficha["momento"]     ?? "Momento decisivo no registrado";
$trayectoria   = $ficha["trayectoria"] ?? "Trayectoria no registrada";
$incomodo      = $ficha["incomodo"]    ?? "Temas incómodos no registrados";
$gustos        = $ficha["gustos"]      ?? "Gustos no registrados";
$logros        = $ficha["logros"]      ?? "Logros no registrados";

// PROMPT PARA DIFY AI
$prompt = "
Eres Güero Bot, el asistente creativo de La Cueva del Güero. Tu tarea es generar el GUION de conducción completo para un episodio del podcast con el siguiente invitado.

### PERSONALIDAD Y ESTILO DEL PODCAST:
- Tono callejero, directo, norteño, sin poses, como platicando con un compa de confianza.
- Enfoque en la neta, historias crudas, superación, barrio y humor natural.
- El entrevistador es 'Junior' o 'El Güero'.

### DATOS DEL INVITADO:
- Nombre: $nombre
- Ocupación: $ocupacion
- Barrio: $barrio
- Herida emocional / Obstáculo fuerte: $herida
- Momento más decisivo: $momento
- Trayectoria: $trayectoria
- Temas incómodos / Límites: $incomodo
- Gustos / Pasiones: $gustos
- Logros clave: $logros

### ESTRUCTURA REQUERIDA PARA EL GUION:
1. 🎬 INTRO (0–5 min) - Presentación con estilo y flow.
2. 🔥 BLOQUE 1 – INFANCIA Y ORIGEN (5–15 min) - Preguntas del barrio y niñez.
3. 💥 BLOQUE 2 – HERIDA / MOMENTO DURO (15–30 min) - Abordar la herida con empatía pero de frente.
4. ⚡ BLOQUE 3 – MOMENTO DECISIVO (30–45 min) - El punto de quiebre.
5. 🎯 BLOQUE 4 – TRAYECTORIA (45–60 min) - Logros y camino.
6. 🔥 BLOQUE 5 – TEMAS INCÓMODOS (60–70 min) - Preguntas difíciles o cómo tratarlas.
7. ❤️ BLOQUE 6 – PASIONES Y FUTURO (70–80 min) - Gustos e intereses.
8. 🎤 CIERRE (80–90 min) - Mensaje final y reflexión.

Genera diálogos sugeridos para Junior e indicaciones de tono (ej. [Risas], [Tono serio]) que hagan que la conversación fluya con mucho estilo urbano. No agregues etiquetas HTML ni bloques Markdown de código, solo formato de texto plano estructurado.
";

// Llamada a la API centralizada de Dify
$difyResult = call_dify_api($prompt, 'guero-bot-guion', 'team');

if (!$difyResult['success']) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Error al llamar a Dify AI: ' . $difyResult['error']
    ], JSON_UNESCAPED_UNICODE);
    exit();
}

$guion = $difyResult['answer'];

// Respuesta final
echo json_encode([
    'status' => 'success',
    'guion' => trim($guion)
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
exit();
