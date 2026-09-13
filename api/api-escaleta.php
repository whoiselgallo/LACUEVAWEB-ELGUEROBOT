<?php
/**
 * API - Generador de Escaleta Técnica y Producción Ejecutiva (Gemini 100% Nativo)
 * Endpoint: /api/api-escaleta.php
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
if (!is_array($input)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'JSON inválido']);
    exit();
}

$campos = [
    'nombre','ocupacion','signo','fecha','barrio',
    'trayectoria','herida','incomodo','gustos'
];

$errores = [];
foreach ($campos as $c) {
    if (empty(trim($input[$c] ?? ''))) $errores[] = $c;
}

if ($errores) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Faltan campos obligatorios: ' . implode(', ', $errores)]);
    exit();
}

$datos = [];
foreach ($campos as $c) {
    $datos[$c] = htmlspecialchars(trim($input[$c]), ENT_QUOTES, 'UTF-8');
}

$momento = htmlspecialchars(trim($input['momento'] ?? 'Superación y resiliencia'), ENT_QUOTES, 'UTF-8');
$logros  = htmlspecialchars(trim($input['logros'] ?? 'Éxito y consolidación'), ENT_QUOTES, 'UTF-8');

$prompt = "# ROL: PRODUCTOR EJECUTIVO Y JEFE DE PISO - LA CUEVA DEL GÜERO PODCAST\n\n" .
          "Genera la ESCALETA TÉCNICA DE PRODUCCIÓN, un RESUMEN DEL GUIÓN y las CUE CARDS para el set de grabación en Mexicali con el siguiente invitado:\n\n" .
          "- Invitado: {$datos['nombre']}\n" .
          "- Ocupación: {$datos['ocupacion']}\n" .
          "- Barrio: {$datos['barrio']}\n" .
          "- Trayectoria: {$datos['trayectoria']}\n" .
          "- Herida / Conflicto: {$datos['herida']}\n" .
          "- Momento Decisivo: {$momento}\n" .
          "- Temas Incómodos: {$datos['incomodo']}\n" .
          "- Gustos: {$datos['gustos']}\n" .
          "- Logros: {$logros}\n\n" .
          "FORMATO DE SALIDA REQUERIDO:\n" .
          "Debes responder ESTRICTAMENTE un JSON válido (sin texto antes ni después) con las siguientes 3 claves:\n" .
          "{\n" .
          "  \"escaleta\": \"(Texto detallado de la escaleta técnica con parrilla de tiempos por bloques, timecodes, dinámicas, menciones de patrocinadores y cortes)\",\n" .
          "  \"guion\": \"(Estructura base del guión conversacional para El Güero y El Junior con ganchos y remates)\",\n" .
          "  \"cue_cards\": \"(Lista de viñetas claras con las 5 preguntas más detonantes e incómodas para que El Güero las lea en cabina)\"\n" .
          "}";

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
        "temperature" => 0.7,
        "maxOutputTokens" => 4000
    ]
];

$geminiRes = call_gemini_generate($payload);

if ($geminiRes['success']) {
    $text = $geminiRes['text'];
    if (preg_match('/```(?:json)?\s*([\s\S]*?)\s*```/', $text, $matches)) {
        $text = $matches[1];
    }
    $parsed = json_decode($text, true);

    $escaletaOut = $parsed['escaleta'] ?? $text;
    $guionOut    = $parsed['guion'] ?? "Guión en desarrollo para {$datos['nombre']}.";
    $cueCardsOut = $parsed['cue_cards'] ?? "Cue cards en cabina para {$datos['nombre']}.";

    echo json_encode([
        'status'    => 'success',
        'escaleta'  => $escaletaOut,
        'guion'     => $guionOut,
        'cue_cards' => $cueCardsOut,
        'model'     => $geminiRes['model']
    ], JSON_UNESCAPED_UNICODE);
} else {
    http_response_code(500);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Error al generar escaleta con Gemini: ' . $geminiRes['error']
    ], JSON_UNESCAPED_UNICODE);
}
exit();
