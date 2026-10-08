<?php
/**
 * ═════════════════════════════════════════════════════════════════════════════════
 * API: GENERADOR DE RETOS Y DINÁMICAS DE CABINA CON IA CONTEXTUAL
 * La Cueva del Güero - Mexicali, B.C.
 * Endpoint: /api/api-retos-ai.php
 * Métodos: POST
 * ═════════════════════════════════════════════════════════════════════════════════
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
    echo json_encode(['success' => false, 'error' => 'Método no permitido.']);
    exit();
}

require_once __DIR__ . '/../config/config.php';

$inputData = json_decode(file_get_contents('php://input'), true) ?: $_POST;

$categoria = sanitize_input($inputData['categoria'] ?? 'aleatorio');
$nombre = sanitize_input($inputData['nombre'] ?? 'Invitado Especial');
$alias = sanitize_input($inputData['alias'] ?? '');
$ocupacion = sanitize_input($inputData['ocupacion'] ?? '');
$barrio = sanitize_input($inputData['barrio'] ?? 'Mexicali, B.C.');
$herida = sanitize_input($inputData['herida'] ?? '');
$reto = sanitize_input($inputData['reto'] ?? '');
$gustos = sanitize_input($inputData['gustos'] ?? '');
$dinamicaPreferida = sanitize_input($inputData['dinamica'] ?? '');

// ═════════════════════════════════════════════════════════════════════════════════
// CATÁLOGO DE RESPALDO (OFFLINE / FALLBACK INMEDIATO)
// ═════════════════════════════════════════════════════════════════════════════════
$catalogoFallback = [
    'destreza' => [
        [
            'titulo' => 'Torre Caguamera en 45 Segundos',
            'categoria' => 'Destreza',
            'reglas' => 'Hacer equilibrio apilando 5 corcholatas o vasos sobre una botella de cerveza cerrada en menos de 45 segundos usando solo una mano.',
            'tiempo_segundos' => 45,
            'materiales' => '1 botella de vidrio y 5 tapas/vasos',
            'castigo' => 'Darle un trago a la salsa más picosa del set sin hacer muecas.',
            'por_que_este_invitado' => 'Pone a prueba el pulso bajo presión y los nervios frente a cámara.',
            'angulo_tiktok' => 'Tensión visual máxima en cámara cerrada con conteo regresivo dramático.'
        ],
        [
            'titulo' => 'Malabares con Limones del Taco',
            'categoria' => 'Destreza',
            'reglas' => 'Mantener 3 limones en el aire durante al menos 20 segundos continuos mientras responde una pregunta rápida de El Junior.',
            'tiempo_segundos' => 30,
            'materiales' => '3 limones de la taquería',
            'castigo' => 'Morder medio limón con sal y chile habanero sin cerrar los ojos.',
            'por_que_este_invitado' => 'Demuestra reflejos y coordinación en vivo con toque cómico.',
            'angulo_tiktok' => 'Corte rápido con música de circo o corrido alterado cuando se le caiga el primer limón.'
        ],
        [
            'titulo' => 'Equilibrio de Taco en la Frente',
            'categoria' => 'Destreza',
            'reglas' => 'Sostener una lata vacía en la frente inclinado hacia atrás durante 30 segundos mientras El Güero le cuenta un chiste.',
            'tiempo_segundos' => 30,
            'materiales' => '1 lata vacía',
            'castigo' => 'El Junior le da un zape amistoso o imita el ladrido de Alan el Perro.',
            'por_que_este_invitado' => 'Rompe el hielo y provoca risas espontáneas en la mesa.',
            'angulo_tiktok' => 'El momento exacto donde se le cae la lata en cámara lenta.'
        ]
    ],
    'fisico' => [
        [
            'titulo' => 'La Sentadilla de los 90 Grados Mexicali',
            'categoria' => 'Reto Físico',
            'reglas' => 'Aguantar exactamente 60 segundos en posición de sentadilla isométrica a 90 grados contra la pared de ladrillo de La Cueva sin meter las manos a las rodillas.',
            'tiempo_segundos' => 60,
            'materiales' => 'Pared de ladrillo del set',
            'castigo' => 'Hacer 15 lagartijas con El Junior sentado en su espalda.',
            'por_que_este_invitado' => 'Reto de resistencia pura que muestra la tenacidad y si de verdad "aguanta la lumbre".',
            'angulo_tiktok' => 'Primer plano al temblor de las piernas en los últimos 15 segundos.'
        ],
        [
            'titulo' => 'Plancha de Barrio con Caguama',
            'categoria' => 'Reto Físico',
            'reglas' => 'Mantener la posición de plancha abdominal durante 45 segundos con una botella de vidrio sobre la espalda baja sin que se caiga.',
            'tiempo_segundos' => 45,
            'materiales' => 'Botella de vidrio cerrada y cronómetro',
            'castigo' => 'Pagar la cuenta de los tacos de todo el staff de producción.',
            'por_que_este_invitado' => 'Pone a prueba el abdomen y el orgullo callejero.',
            'angulo_tiktok' => 'Sonido de suspenso mientras la botella tambalea milimétricamente.'
        ],
        [
            'titulo' => 'Aguante de Cubeta con Hielos',
            'categoria' => 'Reto Físico',
            'reglas' => 'Sumergir ambas manos en la hielera del set a 0°C durante 45 segundos continuos sin retirarlas.',
            'tiempo_segundos' => 45,
            'materiales' => 'Hielera con agua y hielos',
            'castigo' => 'Ponerse un hielo en la nuca durante 1 minuto completo.',
            'por_que_este_invitado' => 'Contraste perfecto con los 45°C del clima caliente de Mexicali.',
            'angulo_tiktok' => 'Gesto de congelamiento y grito de desahogo al terminar.'
        ]
    ],
    'artistico' => [
        [
            'titulo' => 'Cantar el Corrido a Capela con Sentimiento',
            'categoria' => 'Reto Artístico',
            'reglas' => 'Cantar a capela durante 40 segundos su canción o corrido favorito, pero imitando el estilo de un mariachi dolido o cantante urbano con toda la pasión.',
            'tiempo_segundos' => 40,
            'materiales' => 'Micrófono principal de cabina',
            'castigo' => 'Cantar una canción infantil como si fuera corrido bélico.',
            'por_que_este_invitado' => 'Conecta con la fibra musical de la frontera y saca su lado más desinhibido.',
            'angulo_tiktok' => 'Clip vertical con subtítulos de karaoke neón resaltando las notas altas.'
        ],
        [
            'titulo' => 'Dibujo a Ciegas del Güero y El Junior',
            'categoria' => 'Reto Artístico',
            'reglas' => 'Con los ojos vendados y un plumón negro, tiene 60 segundos en una pizarra para dibujar los rostros de El Güero y El Junior. La producción evalúa si se parece.',
            'tiempo_segundos' => 60,
            'materiales' => 'Pizarrón blanco, plumón y antifaz/trapo',
            'castigo' => 'Dejar que El Junior le pinte un bigote con plumón lavable para el resto del episodio.',
            'por_que_este_invitado' => 'Humor visual garantizado y dinámica interactiva de set.',
            'angulo_tiktok' => 'Revelación del dibujo con la reacción de shock y carcajadas de los hosts.'
        ],
        [
            'titulo' => 'Freestyle Callejero de 30 Segundos',
            'categoria' => 'Reto Artístico',
            'reglas' => 'Rimar durante 30 segundos sobre una base de beatbox improvisada por El Güero, usando obligatoriamente 3 palabras al azar: "Mexicali", "Garita" y "Fayuca".',
            'tiempo_segundos' => 30,
            'materiales' => 'Beat rítmico de cabina',
            'castigo' => 'Hacer un poema cursi dedicado a Alan el Perro.',
            'por_que_este_invitado' => 'Rapidez mental, ingenio de barrio y flow.',
            'angulo_tiktok' => 'Rótulos grandes en pantalla con las 3 palabras iluminándose cuando las suelta.'
        ]
    ],
    'callejero' => [
        [
            'titulo' => 'La Prueba del Chile Habanero Bravo',
            'categoria' => 'Reto Callejero',
            'reglas' => 'Probar una tostada con salsa brava de la casa y aguantar 60 segundos hablando del tema más serio de su vida sin tomar agua ni cerveza.',
            'tiempo_segundos' => 60,
            'materiales' => 'Salsa brava artesanal de Mexicali y 1 tostada',
            'castigo' => 'Otro medio trago de salsa brava.',
            'por_que_este_invitado' => 'Tradición norteña de honor: ver si aguanta el chile de verdad.',
            'angulo_tiktok' => 'Ojos llorosos y voz entrecortada mientras intenta responder seriamente.'
        ],
        [
            'titulo' => 'Llamada de Broma al Compa de Confianza',
            'categoria' => 'Reto Callejero',
            'reglas' => 'Marcarle en altavoz a un amigo o colega y decirle en 45 segundos: "Güey, me atoraron aquí en la línea con una maleta y ocupo 5 mil bolas ya", sin reírse.',
            'tiempo_segundos' => 60,
            'materiales' => 'Teléfono celular en altavoz',
            'castigo' => 'Subir una historia a su Instagram diciendo: "El Güero me acaba de ganar una apuesta".',
            'por_que_este_invitado' => 'Pone a prueba las lealtades reales del barrio y genera suspenso telefónico.',
            'angulo_tiktok' => 'La reacción espontánea del amigo en altavoz cuando se entera que está en vivo.'
        ],
        [
            'titulo' => 'La Confesión Incómoda de Garita',
            'categoria' => 'Reto Callejero',
            'reglas' => 'Contar una anécdota real de algo ilegal, vergonzoso o absurdo que haya hecho en la frontera que jamás le haya dicho a su familia.',
            'tiempo_segundos' => 90,
            'materiales' => 'Plano cerrado a cámara 3',
            'castigo' => 'Enseñar la última foto guardada en su galería sin censura.',
            'por_que_este_invitado' => 'Contenido oro puro para el podcast, confesión sin filtros.',
            'angulo_tiktok' => 'Título gancho: "Confesó lo que nunca le dijo a su madre en La Cueva".'
        ]
    ]
];

// Si la categoría es aleatoria, elegir una al azar
$categoriasDisponibles = ['destreza', 'fisico', 'artistico', 'callejero'];
$catNormalizada = strtolower(trim($categoria));
if (!in_array($catNormalizada, $categoriasDisponibles)) {
    $catNormalizada = $categoriasDisponibles[array_rand($categoriasDisponibles)];
}

// ═════════════════════════════════════════════════════════════════════════════════
// GENERACIÓN INTELIGENTE CON GEMINI IA (PERSONALIZADO AL CONTEXTO DEL INVITADO)
// ═════════════════════════════════════════════════════════════════════════════════
$geminiApiKey = get_gemini_api_key();

if (!empty($geminiApiKey)) {
    $prompt = "Actúa como el Director Creativo y Productor Ejecutivo Javier Gallardo 'El Gallo', diseñando dinámicas y retos de cabina para el podcast 'La Cueva del Güero' en Mexicali, B.C.\n\n" .
              "EQUIPO OFICIAL:\n" .
              "- Personaje Principal e Inspiración: 'El Güero' el perro (mascota e imagen en logo/marca/set).\n" .
              "- CEO y Host Conductor: Ariel Higuera 'El Junior' (dirige el show frente a micrófonos y reta al invitado).\n" .
              "- Director Creativo y Productor Ejecutivo: Javier Gallardo 'El Gallo' (diseña los retos y lleva el ritmo).\n" .
              "- Socia Ángel y Finanzas: Maria Elena Anguiano 'La Mary' (administra finanzas y patrocinios).\n\n" .
              "Tu labor es diseñar UN RETO DE CABINA DIVERTIDO, MEMORABLE Y ADAPTADO AL CONTEXTO ÚNICO DEL INVITADO.\n\n" .
              "DATOS DEL INVITADO:\n" .
              "- Nombre: {$nombre}\n" .
              "- Alias / Apodo: {$alias}\n" .
              "- Ocupación / Oficio: {$ocupacion}\n" .
              "- Barrio / Colonia: {$barrio}\n" .
              "- Historia / Herida / Madrazo: {$herida}\n" .
              "- Gustos / Aficiones: {$gustos}\n" .
              "- Reto previo mencionado: {$reto}\n" .
              "- Dinámica preferida: {$dinamicaPreferida}\n" .
              "- CATEGORÍA DE RETO SOLICITADA: " . strtoupper($catNormalizada) . "\n\n" .
              "GUÍA DE CATEGORÍAS:\n" .
              "1. DESTREZA: Malabares con latas, equilibrio, armar algo en segundos, puntería, destreza manual según su oficio.\n" .
              "2. FÍSICO: Sentadillas 90° de 1 min, planchas con botella, vencidas, fuerza o resistencia física con humor.\n" .
              "3. ARTÍSTICO: Cantar un corrido o tema a capela, dibujo a ciegas del Güero/Junior, freestyle o imitación cómica.\n" .
              "4. CALLEJERO: Salsa habanera brava sin agua, llamadas de broma en altavoz, confesar secretos de barrio o mitos de la frontera.\n\n" .
              "REGLAS OBLIGATORIAS:\n" .
              "- Conecta el reto con la personalidad u oficio de {$nombre} (ej. si es llantero algo de llantas/fuerza, si es comerciante o de garita algo de regateo/fayuca, si es de barrio algo con picardía local de Mexicali).\n" .
              "- Tono: Urbano, pícaro, de sobremesa, divertido pero seguro para set de grabación.\n" .
              "- Duración sugerida: entre 30 y 90 segundos.\n\n" .
              "RESPONDE ÚNICAMENTE CON UN OBJETO JSON VÁLIDO CON ESTA ESTRUCTURA EXACTA (sin markdown adicional):\n" .
              "{\n" .
              "  \"titulo\": \"Nombre llamativo del reto\",\n" .
              "  \"categoria\": \"" . ucfirst($catNormalizada) . "\",\n" .
              "  \"reglas\": \"Explicación clara y detallada de cómo se ejecuta el reto paso a paso.\",\n" .
              "  \"tiempo_segundos\": 60,\n" .
              "  \"materiales\": \"Objetos necesarios en la mesa (ej: 1 caguama, salsa brava, cronómetro)\",\n" .
              "  \"por_que_este_invitado\": \"Explicación de por qué este reto encaja con su historia, oficio o barrio.\",\n" .
              "  \"castigo\": \"Penitencia divertida si no lo cumple o se rinde.\",\n" .
              "  \"angulo_tiktok\": \"Sugerencia de cómo grabar o editar este momento para volverlo viral en Shorts/TikTok.\"\n" .
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
            "temperature" => 0.8,
            "maxOutputTokens" => 1200
        ]
    ];

    $geminiRes = call_gemini_generate($payload);

    if ($geminiRes['success']) {
        $rawText = $geminiRes['text'];
        if (preg_match('/```(?:json)?\s*([\s\S]*?)\s*```/', $rawText, $matches)) {
            $rawText = $matches[1];
        }
        $parsed = json_decode(trim($rawText), true);
        if ($parsed && isset($parsed['titulo']) && isset($parsed['reglas'])) {
            echo json_encode([
                'success' => true,
                'origen' => 'gemini_ia',
                'modelo' => $geminiRes['model'] ?? 'gemini-flash',
                'reto' => $parsed
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }
    }
}

// ═════════════════════════════════════════════════════════════════════════════════
// FALLBACK ELEGANTE SI NO HAY IA O FALLÓ LA RESPUESTA
// ═════════════════════════════════════════════════════════════════════════════════
$listaOpciones = $catalogoFallback[$catNormalizada] ?? $catalogoFallback['destreza'];
$retoSeleccionado = $listaOpciones[array_rand($listaOpciones)];

// Inyectar toque personalizado con el nombre
$retoSeleccionado['por_que_este_invitado'] = "Dinámica curada para " . $nombre . " de " . $barrio . " para romper el hielo y encender el show.";

echo json_encode([
    'success' => true,
    'origen' => 'catalogo_curado',
    'reto' => $retoSeleccionado
], JSON_UNESCAPED_UNICODE);
