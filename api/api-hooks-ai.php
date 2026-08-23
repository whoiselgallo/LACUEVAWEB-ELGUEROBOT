<?php
/**
 * API: GENERADOR DE HOOKS Y MARKETING COPY CON GEMINI IA
 * Endpoint: /api/api-hooks-ai.php
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
$topic = sanitize_input($inputData['topic'] ?? '');

if (empty($topic)) {
    echo json_encode(['success' => false, 'error' => 'El tema central está vacío.']);
    exit;
}

$geminiApiKey = get_gemini_api_key();
if (empty($geminiApiKey)) {
    echo json_encode(['success' => false, 'error' => 'No hay claves de API de Gemini configuradas.']);
    exit;
}

$prompt = "Actúa como el Director de Contenido y Copywriter Viral de 'La Cueva del Güero'. " .
          "A partir del siguiente tema central: '{$topic}', genera 6 ganchos (hooks) y copys personalizados para redes sociales. " .
          "Debes usar expresiones del norte de México (Mexicali) como 'carnal', 'raza', 'chido', 'guau', 'la cueva', 'desmadre' para que encaje con la vibra del podcast.\n\n" .
          "REQUISITOS POR PLATAFORMA:\n" .
          "1. facebook: Un copy interactivo para feed, con hashtags, invitando a comentar.\n" .
          "2. instagram: Estilo gancho para carrusel de 3 slides (Slide 1: Título intrigante, Slide 2: Contenido, Slide 3: Llamado a la acción).\n" .
          "3. tiktok: Gancho de 3 segundos de alto impacto para video corto.\n" .
          "4. spotify: Guion de introducción para el episodio de audio.\n" .
          "5. shorts: Gancho dinámico y loopable para YouTube Shorts.\n" .
          "6. youtube: Título clickbait y descripción llamativa para el video largo.\n\n" .
          "FORMATEA TU RESPUESTA EXACTAMENTE EN ESTE FORMATO JSON (No envíes texto fuera del JSON):\n" .
          "{\n" .
          "  \"facebook\": \"texto del gancho\",\n" .
          "  \"instagram\": \"texto del gancho\",\n" .
          "  \"tiktok\": \"texto del gancho\",\n" .
          "  \"spotify\": \"texto del gancho\",\n" .
          "  \"shorts\": \"texto del gancho\",\n" .
          "  \"youtube\": \"texto del gancho\"\n" .
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
        "maxOutputTokens" => 2000,
        "responseMimeType" => "application/json"
    ]
];

curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_TIMEOUT => 30
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    $resData = json_decode($response, true);
    $jsonText = $resData['candidates'][0]['content']['parts'][0]['text'] ?? '';
    
    $parsedResult = json_decode($jsonText, true);
    if ($parsedResult) {
        echo json_encode([
            'success' => true,
            'hooks' => $parsedResult
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
