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
        "maxOutputTokens" => 2000
    ]
];

$geminiRes = call_gemini_generate($payload);

if ($geminiRes['success']) {
    $jsonText = $geminiRes['text'];
    
    // Extraer bloque JSON si viene envuelto en markdown
    if (preg_match('/```(?:json)?\s*([\s\S]*?)\s*```/', $jsonText, $matches)) {
        $jsonText = $matches[1];
    }
    
    $parsedResult = json_decode($jsonText, true);
    if ($parsedResult && isset($parsedResult['facebook'])) {
        echo json_encode([
            'success' => true,
            'hooks' => $parsedResult,
            'model' => $geminiRes['model']
        ], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode([
            'success' => true,
            'hooks' => [
                'facebook' => "🚨 ¡No vas a creer lo que se habló en La Cueva sobre {$topic}! Mira el episodio completo.",
                'instagram' => "🔥 Lo más picante de la plática sobre {$topic}. Dale like y compártelo con tu manada.",
                'tiktok' => "😱 Confesiones incómodas sobre {$topic}. ¿Tú qué hubieras hecho? Comenta abajo.",
                'spotify' => "🎙️ Nuevo episodio: Charlamos a calzón quitado sobre {$topic}. Disponible ya.",
                'shorts' => "⚡ El momento más tenso sobre {$topic} en 30 segundos. ¡Suscríbete!",
                'youtube' => "🔥 EL DESMADRE DE {$topic} EN VIVO | La Cueva del Güero Podcast"
            ],
            'model' => $geminiRes['model']
        ], JSON_UNESCAPED_UNICODE);
    }
} else {
    echo json_encode([
        'success' => false,
        'error' => $geminiRes['error']
    ]);
}
?>
