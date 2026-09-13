<?php
/**
 * API - Generador de Cue Cards Imprimibles (Gemini 100% Nativo)
 * Endpoint: /api/api-cuecards.php
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
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
    exit();
}

require_once __DIR__ . '/../config/config.php';

$input = json_decode(file_get_contents('php://input'), true);
$invitado = trim($input['invitado'] ?? 'Invitado Especial');
$tarjetas = $input['tarjetas'] ?? [];

if ($invitado === '') {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Falta el nombre del invitado']);
    exit();
}

if (!is_array($tarjetas)) {
    $tarjetas = [];
}

$prompt = "# ROL: DIRECTOR DE PISO Y CONTINUISTA - LA CUEVA DEL GÜERO PODCAST\n\n" .
          "Genera el HTML COMPLETO DE TARJETAS DE CONDUCCIÓN (CUE CARDS) para 'El Güero' durante la grabación con '{$invitado}'.\n\n" .
          "DATOS Y TEMAS DE LAS TARJETAS:\n" .
          json_encode($tarjetas, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n\n" .
          "REQUISITOS DEL HTML:\n" .
          "1. Debe incluir etiquetas <style> con diseño de tarjetas de media carta apaisadas (A5 horizontal).\n" .
          "2. Tipografía grande, de altísimo contraste, con viñetas claras y legibles a 2 metros de distancia.\n" .
          "3. Estilo Cyberpunk Neón con acentos magenta (#FF00FF) y cian (#00FFFF) sobre fondo oscuro, optimizado para impresión física (print CSS con fondos blancos y texto negro azabache al mandar a imprimir).\n" .
          "4. Incluye un botón interactivo '🖨️ IMPRIMIR CUE CARDS' con window.print().\n" .
          "5. Retorna ÚNICAMENTE el código HTML dentro de <div>.";

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
        "maxOutputTokens" => 3500
    ]
];

$geminiRes = call_gemini_generate($payload);

if ($geminiRes['success']) {
    $html = $geminiRes['text'];
    if (preg_match('/```(?:html)?\s*([\s\S]*?)\s*```/', $html, $matches)) {
        $html = $matches[1];
    }
    echo json_encode([
        'status' => 'success',
        'html'   => trim($html),
        'model'  => $geminiRes['model']
    ], JSON_UNESCAPED_UNICODE);
} else {
    http_response_code(500);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Error al generar cue cards con Gemini: ' . $geminiRes['error']
    ], JSON_UNESCAPED_UNICODE);
}
exit();
