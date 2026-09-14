<?php
/**
 * API: REDACTOR DE ARTÍCULOS Y PROPUESTAS EDITORIALES DE BLOG CON GEMINI IA
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

$inputData = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$action = sanitize_input($inputData['action'] ?? 'redactar');
$nombreInvitado = sanitize_input($inputData['nombre_invitado'] ?? 'Invitado Especial');
$guion = $inputData['guion'] ?? '';
$escaleta = $inputData['escaleta'] ?? '';
$storytelling = $inputData['storytelling'] ?? '';
$temaAnterior = sanitize_input($inputData['tema_anterior'] ?? '');

// ═════════════════════════════════════════════════════════════════════════════════
// ACCIÓN 1: PROPONER TEMA EDITORIAL CON PODER Y PESO PARA BLOG
// ═════════════════════════════════════════════════════════════════════════════════
if ($action === 'proponer_tema') {
    $contexto = "INVITADO: " . $nombreInvitado . "\n\n";
    if (!empty($guion)) $contexto .= "GUIÓN:\n" . substr($guion, 0, 3000) . "\n\n";
    if (!empty($escaleta)) $contexto .= "ESCALETA:\n" . substr($escaleta, 0, 2000) . "\n\n";
    if (!empty($storytelling)) $contexto .= "STORYTELLING / ANÉCDOTAS:\n" . substr($storytelling, 0, 2000) . "\n\n";

    $promptTema = "Actúa como el Editor en Jefe y Estratega Narrativo del podcast 'La Cueva del Güero'. " .
        "Analiza el siguiente expediente completo del invitado '{$nombreInvitado}' y extrae un tema editorial con GRAN PODER, PESO EMOCIONAL Y GANCHO CALLEJERO para desarrollar un artículo de blog de alto impacto.\n\n" .
        ($temaAnterior ? "IMPORTANTE: Genera un tema y ángulo COMPLETAMENTE DIFERENTE al tema anterior: '{$temaAnterior}'. Explora otra faceta (ej. superación, traición, lealtad, contraste social, madrazos del trabajo, raíces de barrio o negocios).\n\n" : "") .
        "EXPEDIENTE DEL INVITADO:\n" . $contexto . "\n\n" .
        "RESPONDE ÚNICAMENTE CON UN OBJETO JSON VÁLIDO CON ESTA ESTRUCTURA EXACTA (sin markdown adicional):\n" .
        "{\n" .
        "  \"titulo\": \"Título magnético con gancho y peso editorial\",\n" .
        "  \"tesis\": \"Tesis central o ángulo de impacto: por qué este tema conecta profundamente\",\n" .
        "  \"puntos_clave\": [\n" .
        "    \"Eje 1: Raíz o situación de origen\",\n" .
        "    \"Eje 2: El momento de quiebre o lección de vida\",\n" .
        "    \"Eje 3: Conclusión o sabiduría para el lector\"\n" .
        "  ],\n" .
        "  \"frase_gancho\": \"Cita textual o frase detonadora del invitado\",\n" .
        "  \"categoria\": \"Storytelling\",\n" .
        "  \"potencia\": \"NIVEL ALTO\",\n" .
        "  \"resumen_semidesarrollado\": \"Borrador estructurado de 3 párrafos listos con antecedentes, desarrollo del conflicto y moraleja para expandir en el artículo final.\"\n" .
        "}";

    $payload = [
        "contents" => [
            [
                "role" => "user",
                "parts" => [
                    ["text" => $promptTema]
                ]
            ]
        ],
        "generationConfig" => [
            "temperature" => 0.85,
            "maxOutputTokens" => 2500
        ]
    ];

    $geminiRes = call_gemini_generate($payload);

    if ($geminiRes['success']) {
        $rawText = $geminiRes['text'];
        if (preg_match('/```(?:json)?\s*([\s\S]*?)\s*```/', $rawText, $matches)) {
            $rawText = $matches[1];
        }
        $jsonParsed = json_decode(trim($rawText), true);
        if ($jsonParsed && !empty($jsonParsed['titulo'])) {
            echo json_encode([
                'success' => true,
                'data' => $jsonParsed,
                'model' => $geminiRes['model'] ?? 'gemini'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    // Fallback inteligente si la IA está temporalmente inaccesible
    $fallbacks = [
        [
            'titulo' => "Los madrazos del camino: Cómo {$nombreInvitado} convirtió la adversidad de barrio en su mayor ventaja",
            'tesis' => "El verdadero carácter no se forja en la comodidad, sino en la capacidad de aguantar los golpes de la vida sin perder la lealtad ni el rumbo.",
            'puntos_clave' => [
                "Las lecciones invisibles que solo la calle y el trabajo duro enseñan.",
                "El punto de quiebre donde la mayoría se rinde y por qué no tirar la toalla.",
                "La regla de oro de La Cueva: la lealtad no se negocia ni se vende."
            ],
            'frase_gancho' => "El barrio no es la esquina, el barrio es la familia que te cuida la espalda.",
            'categoria' => "Storytelling",
            'potencia' => "NIVEL ALTO",
            'resumen_semidesarrollado' => "En este episodio de La Cueva del Güero, {$nombreInvitado} desglosa cómo los momentos más oscuros de su trayectoria se convirtieron en el cimiento de su crecimiento actual.\n\nAnalizamos la tensión entre el entorno difícil y la disciplina personal, destacando anécdotas inéditas de resistencia frente a la humillación y el desaliento.\n\nUna lectura obligada para cualquiera que esté atravesando un bache y necesite recordar por qué vale la pena seguir adelante sin bajar la cabeza."
        ],
        [
            'titulo' => "De abajo hacia arriba: La verdad sin filtro de {$nombreInvitado} sobre el éxito en la frontera",
            'tesis' => "Triunfar en un entorno fronterizo y competitivo exige astucia callejera combinada con una ética de trabajo intachable.",
            'puntos_clave' => [
                "Romper el molde sin olvidar de dónde vienes.",
                "El costo real de las decisiones difíciles en momentos de presión.",
                "Cómo ganarse el respeto en un mundo que no perdona errores."
            ],
            'frase_gancho' => "A veces hay que tocar fondo para saber exactamente sobre qué piso estás parado.",
            'categoria' => "Reflexión",
            'potencia' => "NIVEL ÉPICO",
            'resumen_semidesarrollado' => "La historia de {$nombreInvitado} en La Cueva del Güero revela los matices crudos de abrirse camino en Mexicali y la frontera.\n\nExaminamos la disciplina de levantarse cada mañana cuando las probabilidades están en contra, desmintiendo el mito del éxito de la noche a la mañana.\n\nUn artículo reflexivo que combina anécdotas de set con lecciones prácticas de supervivencia urbana y liderazgo de barrio."
        ]
    ];

    $picked = $fallbacks[array_rand($fallbacks)];
    echo json_encode([
        'success' => true,
        'data' => $picked,
        'model' => 'curaduria-interna-fallback'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// ═════════════════════════════════════════════════════════════════════════════════
// ACCIÓN 2: REDACTAR ARTÍCULO COMPLETO DE BLOG A PARTIR DEL GUIÓN / TEMA
// ═════════════════════════════════════════════════════════════════════════════════
if (empty($guion) && empty($inputData['tema'])) {
    echo json_encode(['success' => false, 'error' => 'El guión o tema para redactar el post está vacío.']);
    exit;
}

$prompt = "Actúa como el Redactor de Contenido y especialista SEO del podcast 'La Cueva del Güero'. " .
          "A partir del siguiente material del capítulo con el invitado '{$nombreInvitado}', redacta un artículo de blog completo, atrapante y con peso.\n\n" .
          "REGLAS DEL ARTÍCULO:\n" .
          "- Debe tener un título llamativo (con ganchos de curiosidad o intriga urbana).\n" .
          "- El tono debe ser directo, norteño, de barrio pero bien redactado y profesional para lectura digital.\n" .
          "- Estructura el cuerpo con subtítulos claros (H2 / H3).\n" .
          "- Cierra con una conclusión contundente y llamada a la acción para escuchar el episodio.\n\n" .
          "MATERIAL DISPONIBLE:\n" .
          ($guion ? "GUIÓN:\n" . substr($guion, 0, 4000) . "\n\n" : "") .
          (!empty($inputData['tema']) ? "TEMA PROPUESTO:\n" . json_encode($inputData['tema'], JSON_UNESCAPED_UNICODE) : "") . "\n\n" .
          "FORMATEA TU RESPUESTA EXACTAMENTE EN ESTE FORMATO JSON (No envíes texto fuera del JSON):\n" .
          "{\n" .
          "  \"titulo\": \"Escribe el título aquí\",\n" .
          "  \"articulo\": \"Escribe el cuerpo del artículo aquí con subtítulos y párrafos claros\"\n" .
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
    $jsonText = $geminiRes['text'];
    if (preg_match('/```(?:json)?\s*([\s\S]*?)\s*```/', $jsonText, $matches)) {
        $jsonText = $matches[1];
    }
    $parsedResult = json_decode(trim($jsonText), true);
    if ($parsedResult && !empty($parsedResult['titulo'])) {
        echo json_encode([
            'success' => true,
            'titulo' => $parsedResult['titulo'],
            'articulo' => $parsedResult['articulo'] ?? '',
            'model' => $geminiRes['model'] ?? 'gemini'
        ], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode([
            'success' => true,
            'titulo' => "Secretos revelados en La Cueva del Güero: {$nombreInvitado}",
            'articulo' => $jsonText,
            'model' => $geminiRes['model'] ?? 'gemini'
        ], JSON_UNESCAPED_UNICODE);
    }
} else {
    echo json_encode([
        'success' => false,
        'error' => $geminiRes['error']
    ]);
}
?>

