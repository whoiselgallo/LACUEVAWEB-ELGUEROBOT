<?php
/**
 * API: REDACTOR DE ARTÍCULOS DE BLOG DE LA CUEVA CON GEMINI IA
 * Endpoint: /api/api-blog-ai.php
 * Métodos: POST
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método no permitido.']);
    exit;
}

$inputData = json_decode(file_get_contents('php://input'), true);
$nombreInvitado = sanitize_input($inputData['nombre_invitado'] ?? 'Invitado Especial');
$guion = $inputData['guion'] ?? '';

if (empty($guion)) {
    echo json_encode(['success' => false, 'error' => 'El guión del capítulo está vacío.']);
    exit;
}

$geminiApiKey = get_gemini_api_key();
if (empty($geminiApiKey)) {
    echo json_encode(['success' => false, 'error' => 'No hay claves de API de Gemini configuradas.']);
    exit;
}

$prompt = "Actúa como el Redactor de Contenido y especialista SEO del podcast 'La Cueva del Güero'. " .
          "A partir del siguiente guión del capítulo con el invitado '{$nombreInvitado}', selecciona el tema más impactante y escribe un artículo de blog completo.\n\n" .
          "REGLAS DEL ARTÍCULO:\n" .
          "- Debe tener un título llamativo (con ganchos de curiosidad o intriga urbana).\n" .
          "- El tono debe ser directo, urbano y alineado con la cultura del norte de México, pero legible para SEO.\n" .
          "- Estructura el cuerpo con subtítulos claros.\n" .
          "- El artículo debe centrarse en la lección o anécdota clave del guión.\n\n" .
          "GUIÓN DEL CAPÍTULO:\n" .
          $guion . "\n\n" .
          "FORMATEA TU RESPUESTA EXACTAMENTE EN ESTE FORMATO JSON (No envíes texto fuera del JSON):\n" .
          "{\n" .
          "  \"titulo\": \"Escribe el título aquí\",\n" .
          "  \"articulo\": \"Escribe el cuerpo del artículo aquí con párrafos claros\"\n" .
          "}";

$ch = curl_init("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . $geminiApiKey);
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
        "maxOutputTokens" => 4000,
        "responseMimeType" => "application/json"
    ]
];

curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_TIMEOUT => 45
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    $resData = json_decode($response, true);
    $jsonText = $resData['candidates'][0]['content']['parts'][0]['text'] ?? '';
    
    $parsedResult = json_decode($jsonText, true);
    if (isset($parsedResult['titulo']) && isset($parsedResult['articulo'])) {
        echo json_encode([
            'success' => true,
            'titulo' => $parsedResult['titulo'],
            'articulo' => $parsedResult['articulo']
        ], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'La respuesta de la IA no contenía las claves esperadas.',
            'raw' => $jsonText
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'error' => "Error de API de Gemini (HTTP {$http_code}): " . $response
    ]);
}
?>
