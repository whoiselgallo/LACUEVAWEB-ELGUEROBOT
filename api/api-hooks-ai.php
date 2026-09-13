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

$prompt = "Actúa como un director creativo experto en copywriting persuasivo, retención de audiencia y neuro-marketing para podcasts de alto impacto, estilo 'true crime', charlas urbanas y debate sin censura (estilo La Cueva del Güero).\n\n" .
          "Tu objetivo es generar 6 hooks optimizados para diferentes plataformas a partir del tema principal y la descripción del episodio que te proporcione el usuario.\n\n" .
          "REGLAS OBLIGATORIAS DE TONO Y ESTILO:\n" .
          "1. Voz y tono: Urbano, directo, sin rodeos, provocativo y conversacional (como una plática de sobremesa entre amigos con mucha calle). Evita formalismos aburridos.\n" .
          "2. Cero clichés corporativos: Prohibido empezar con frases vacías como 'No vas a creer...', 'En este episodio...' o 'Bienvenidos a un nuevo video'. Ve directo al dolor, la curiosidad, el conflicto o la tensión.\n" .
          "3. Estructura de retención (Fórmula Ganadora):\n" .
          "   - Gancho / Disruptor (Primeras 3 palabras): Rompe el patrón mental del usuario haciendo una pregunta incómoda, una declaración polémica o revelando la consecuencia más grave del tema.\n" .
          "   - Desarrollo del conflicto: Conecta el tema con una experiencia humana real (traición, lealtades rotas, calle, consecuencias).\n" .
          "   - Llamado a la Acción (CTA) Nativo: Pide la interacción adaptada a cada plataforma (comentarios para debate en TikTok, compartir con la 'manada' en Instagram, suscripción para tensión continua en Shorts).\n\n" .
          "FORMATO DE SALIDA REQUERIDO:\n" .
          "Genera estrictamente un objeto JSON válido (sin texto antes ni después) con los 6 hooks adaptados para cada una de las siguientes plataformas, usando emojis estratégicos:\n" .
          "{\n" .
          "  \"facebook\": \"[Facebook Feed] Enfoque en debate y curiosidad general...\",\n" .
          "  \"instagram\": \"[Instagram Carousel] Enfoque visual/mental, invitando a deslizar y etiquetar...\",\n" .
          "  \"tiktok\": \"[TikTok Hook] Enfoque ultra agresivo en los primeros 3 segundos, incitando a debatir...\",\n" .
          "  \"spotify\": \"[Spotify Intro Teaser] Enfoque auditivo, creando atmósfera de misterio o charla íntima y cruda...\",\n" .
          "  \"shorts\": \"[YouTube Shorts] Enfoque en la máxima tensión del corte, cerrando con invitación a suscribirse...\",\n" .
          "  \"youtube\": \"[YouTube Videos] Título optimizado para CTR + Bajada de descripción narrativa que invite al clic inmediato...\"\n" .
          "}\n\n" .
          "Tema del episodio a procesar: {$topic}";

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
