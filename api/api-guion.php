<?php
/**
 * API - Generador de Guión Técnico Broadcast (Gemini 100% Nativo)
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

require_once __DIR__ . '/../config/config.php';

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'No se recibió información del invitado']);
    exit();
}

// Normalizar estructura
$inv = isset($input["nombre"]) ? $input : (isset($input[0]) ? $input[0] : null);

if (!$inv) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Formato inválido de invitado']);
    exit();
}

$ficha = isset($inv["ficha"]) ? $inv["ficha"] : $inv;

$nombre      = $inv["nombre"]        ?? "Invitado Especial";
$ocupacion   = $ficha["ocupacion"]   ?? "Ocupación no registrada";
$barrio      = $ficha["barrio"]      ?? "Barrio no registrado";
$herida      = $ficha["herida"]      ?? "Herida no registrada";
$momento     = $ficha["momento"]     ?? "Momento decisivo no registrado";
$trayectoria = $ficha["trayectoria"] ?? "Trayectoria no registrada";
$incomodo    = $ficha["incomodo"]    ?? "Temas incómodos no registrados";
$gustos      = $ficha["gustos"]      ?? "Gustos no registrados";
$logros      = $ficha["logros"]      ?? "Logros no registrados";
$fecha       = $inv["fecha"]         ?? "";

$prompt = "# ROL: GUIONISTA PRINCIPAL Y DIRECTOR DE TRANSMISIÓN - LA CUEVA DEL GÜERO PODCAST\n\n" .
          "Tu labor es redactar el GUIÓN TÉCNICO CINEMATOGRÁFICO Y BROADCAST completo para el episodio grabado en vivo en Mexicali, conducido por 'El Güero' y 'El Junior' con el siguiente invitado:\n\n" .
          "DATOS DEL INVITADO:\n" .
          "- Nombre: {$nombre}\n" .
          "- Ocupación: {$ocupacion}\n" .
          "- Barrio / Origen: {$barrio}\n" .
          "- Trayectoria: {$trayectoria}\n" .
          "- Herida / Momento Difícil: {$herida}\n" .
          "- Momento Decisivo: {$momento}\n" .
          "- Temas Incómodos: {$incomodo}\n" .
          "- Gustos / Personalidad: {$gustos}\n" .
          "- Logros Clave: {$logros}\n\n" .
          "REGLAS OBLIGATORIAS:\n" .
          "1. Tono norteño urbano, directo, sin censura, con alta dosis de tensión humana y comedia natural.\n" .
          "2. Estructura el guión con marcas de tiempo (TIMECODES) y acotaciones escénicas para cámaras y conductores.\n" .
          "3. Divide el episodio en 6 bloques claros:\n" .
          "   - BLOQUE 1 [00:00 - 02:00]: TEASER EXPLOSIVO (Frío de entrada con la confesión más tensa).\n" .
          "   - BLOQUE 2 [02:00 - 08:00]: PRESENTACIÓN, RAÍCES Y EL BARRIO (Conexión y anécdotas de origen).\n" .
          "   - BLOQUE 3 [08:00 - 20:00]: EL ASCENSO Y LA TRAYECTORIA (Los primeros chingazos y aprendizajes).\n" .
          "   - BLOQUE 4 [20:00 - 35:00]: LA HERIDA / EL MOMENTO DECISIVO (El corazón del episodio, momento crudo y emotivo).\n" .
          "   - BLOQUE 5 [35:00 - 45:00]: EL DESMADRE Y LAS PREGUNTAS INCÓMODAS (La ruleta de preguntas filosas de La Cueva).\n" .
          "   - BLOQUE 6 [45:00 - 50:00]: REMATE, LECCIÓN DE VIDA Y CIERRE (Llamado a la suscripción en YouTube).\n\n" .
          "Escribe el guión técnico completo con diálogos verosímiles y acotaciones para El Güero y El Junior.";

$payload = [
    "contents" => [
        [
            "role" => "user",
            "parts" => [
                ["text" => $prompt]
            ]
        ]
    ],
    "generationConfig" => [
        "temperature" => 0.75,
        "maxOutputTokens" => 4000
    ]
];

$geminiRes = call_gemini_generate($payload);

if ($geminiRes['success']) {
    echo json_encode([
        'status' => 'success',
        'guion'  => trim($geminiRes['text']),
        'model'  => $geminiRes['model']
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
} else {
    http_response_code(500);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Error al generar guión con Gemini: ' . $geminiRes['error']
    ], JSON_UNESCAPED_UNICODE);
}
exit();
