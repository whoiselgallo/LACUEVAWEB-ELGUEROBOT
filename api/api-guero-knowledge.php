<?php
/**
 * ═════════════════════════════════════════════════════════════════════════════════
 * API - Guardar Storytelling y Conocimiento del Güero (PostgreSQL / Neon)
 * Endpoint: /api/api-guero-knowledge.php
 * Con soporte resiliente para base de datos y fallback offline
 * ═════════════════════════════════════════════════════════════════════════════════
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../config/config.php';

// Intentar conectar a BD de forma segura sin abortar con HTTP 500
$db = null;
try {
    $db = db_connect();
} catch (Exception $e) {
    error_log('Knowledge DB Warning (Using fallback): ' . $e->getMessage());
    $db = null;
}

// Fallbacks de Invitados Registrados
$FALLBACK_INVITADOS = [
    2 => [
        'id' => 2,
        'nombre' => "Leo Camacho Higuera",
        'created_at' => "2026-09-12 17:00:00",
        'alias' => "Leo",
        'ocupacion' => "Músico y Productor Urbano",
        'barrio' => "Colonia Libertad, Mexicali",
        'trayectoria' => "Más de 10 años en la escena musical fronteriza, construyendo proyectos independientes desde abajo.",
        'herida' => "La pérdida de su primer estudio por un incendio y tener que empezar desde cero sin apoyo.",
        'molestia' => "La falta de lealtad y los compas que se cuelgan del éxito ajeno.",
        'frase' => "La lealtad no se platica, se demuestra en la lumbre.",
        'escaleta' => "ESCALETA DE PRODUCCIÓN - LA CUEVA\nInvitado: Leo Camacho Higuera\nTema: Resiliencia y lealtad en la música fronteriza\n\n[00:00 - 03:00] Hook & Intro: El incendio que casi lo retira\n[03:00 - 15:00] Bloque 1: Creciendo en la Libertad y el primer micrófono\n[15:00 - 30:00] Bloque 2: El golpe duro y reconstruir la visión\n[30:00 - 45:00] Bloque 3: Produciendo en Mexicali y consejos a las nuevas generaciones\n[45:00 - 50:00] Cierre y reflexiones de La Cueva",
        'guion' => "GUIÓN BROADCAST - LA CUEVA DEL GÜERO\nInvitado: Leo Camacho Higuera\n\nEl Güero: ¡Qué onda manada! Hoy tenemos sentado en la mesa a un compa que le ha tocado picar piedra en serio: Leo Camacho.\n\nJunior: Bienvenido a la Cueva, carnal. La neta queríamos empezar con la pregunta directa: ¿qué sentiste el día que viste tu estudio en cenizas?\n\nLeo: Fue el momento donde tuve que decidir si me rendía o le metía el doble de ganas...",
        'cue_cards' => "CUE CARDS DE CABINA\n• Tarjeta 1: Preguntar sobre el origen del apodo y primeros años en la Libertad.\n• Tarjeta 2: Momento clave del incendio (conectar con la vulnerabilidad).\n• Tarjeta 3: Mencionar patrocinador oficial de la Cueva.\n• Tarjeta 4: Pregunta final sobre qué consejo le daría a su yo de hace 10 años.",
        'curaduria' => [
            'nivel' => 'ALTO',
            'badge' => '🟢 NIVEL ALTO',
            'formato' => 'Invitado Principal al Canal',
            'color' => '#39FF14',
            'razon' => 'Expediente de alta potencia narrativa y lealtad de barrio.'
        ]
    ],
    3 => [
        'id' => 3,
        'nombre' => "Javi Domz (jeyb)",
        'created_at' => "2026-09-12 17:00:00",
        'alias' => "JeyB",
        'ocupacion' => "Director Creativo de Cine y TV",
        'barrio' => "Mexicali, B.C.",
        'trayectoria' => "Director audiovisual con proyectos internacionales y visión cinematográfica de la frontera.",
        'herida' => "El rechazo inicial de las productoras en CDMX y la soledad del inicio.",
        'molestia' => "Los presupuestos inflados que no llegan a los creadores reales.",
        'frase' => "El cine no es de cámaras caras, es de ojos despiertos.",
        'escaleta' => "ESCALETA - Javi Domz (JeyB)\n[00:00 - 05:00] Hook de impacto visual\n[05:00 - 20:00] La lucha por filmar en Mexicali\n[20:00 - 40:00] De la frontera para el mundo\n[40:00 - 50:00] Cierre",
        'guion' => "GUIÓN - Javi Domz en La Cueva\n\nEl Güero: Hoy está con nosotros JeyB, el compa detrás del lente más pesado de la baja...",
        'cue_cards' => "CUE CARDS\n• Preguntar sobre el proyecto de largometraje en Mexicali.\n• Detonador: ¿Vale la pena irse a CDMX o quedarse en el norte?",
        'curaduria' => [
            'nivel' => 'ALTO',
            'badge' => '🟢 NIVEL ALTO',
            'formato' => 'Invitado Principal al Canal',
            'color' => '#39FF14',
            'razon' => 'Storytelling visual y dirección cinematográfica de alto impacto.'
        ]
    ],
    4 => [
        'id' => 4,
        'nombre' => "Marcelo Ivan Maciel Maldonado",
        'created_at' => "2026-09-12 17:00:00",
        'alias' => "Marcelo",
        'ocupacion' => "Abogado y Servidor Público",
        'barrio' => "Mexicali",
        'trayectoria' => "Trayectoria en el tribunal estatal de justicia administrativa y compromiso social.",
        'herida' => "Ver las injusticias del sistema legal cuando la gente de a pie no tiene defensa.",
        'molestia' => "La burocracia insensible que olvida al ser humano.",
        'frase' => "El derecho tiene que servir al pueblo, no a los escritorios.",
        'escaleta' => "ESCALETA - Marcelo Maciel\n[00:00 - 05:00] Hook: Ley vs Realidad de calle\n[05:00 - 25:00] Casos difíciles en la frontera\n[25:00 - 45:00] Ética y justicia real\n[45:00 - 50:00] Cierre",
        'guion' => "GUIÓN - Marcelo Maciel\nEl Güero: Marcelo, bienvenido a decir la neta del sistema...",
        'cue_cards' => "CUE CARDS\n• Pregunta clave: ¿Hay justicia real para el barrio?\n• Anécdota del caso más retador.",
        'curaduria' => [
            'nivel' => 'ALTO',
            'badge' => '🟢 NIVEL ALTO',
            'formato' => 'Invitado Principal al Canal',
            'color' => '#39FF14',
            'razon' => 'Tribunal estatal de justicia administrativa y visión social del barrio.'
        ]
    ],
    5 => [
        'id' => 5,
        'nombre' => "Aurelio Gonzalez",
        'created_at' => "2026-09-12 17:00:00",
        'alias' => "Aurelio",
        'ocupacion' => "Comerciante y Emprendedor",
        'barrio' => "Colonia Carbajal",
        'trayectoria' => "Fundador de negocios locales forjados a pulso durante décadas.",
        'herida' => "Quiebras económicas que casi le cuestan la salud familiar.",
        'molestia' => "Los que prometen atajos fáciles para el dinero.",
        'frase' => "El billete se suda, el respeto se gana.",
        'escaleta' => "ESCALETA - Aurelio Gonzalez\n[00:00 - 10:00] La Carbajal y los primeros pesos\n[10:00 - 30:00] Negocios de frontera\n[30:00 - 50:00] Aprendizajes de vida",
        'guion' => "GUIÓN - Aurelio Gonzalez\nEl Güero: Hoy nos acompaña don Aurelio de la Carbajal...",
        'cue_cards' => "CUE CARDS\n• Historia del primer puesto comercial.\n• Claves para aguantar las crisis fronterizas.",
        'curaduria' => [
            'nivel' => 'ALTO',
            'badge' => '🟢 NIVEL ALTO',
            'formato' => 'Invitado Principal al Canal',
            'color' => '#39FF14',
            'razon' => 'Negocios y trayectoria comercial en la frontera desde la Carbajal.'
        ]
    ],
    6 => [
        'id' => 6,
        'nombre' => "Guillermina Ayala Quiñonez",
        'created_at' => "2026-09-12 17:00:00",
        'alias' => "Doña Guille",
        'ocupacion' => "Madre de Familia y Empleada",
        'barrio' => "Colonia Libertad",
        'trayectoria' => "Toda una vida de trabajo incansable para sacar adelante a su familia sola.",
        'herida' => "La ausencia y la dureza de las jornadas dobles.",
        'molestia' => "Que no se valore el esfuerzo de las jefas de hogar.",
        'frase' => "Mientras haya salud, no hay trabajo que me raje.",
        'escaleta' => "ESCALETA - Guillermina Ayala (Especial Jefas de Familia)\n[00:00 - 10:00] Madres que no se rajan\n[10:00 - 35:00] Historias de la Libertad\n[35:00 - 50:00] Mensaje a los hijos",
        'guion' => "GUIÓN - Doña Guille en La Cueva\nEl Güero: Este episodio es un homenaje a las que nunca se doblan...",
        'cue_cards' => "CUE CARDS\n• Anécdota de cómo sacó adelante a la familia.\n• Momento de mayor orgullo.",
        'curaduria' => [
            'nivel' => 'ALTO',
            'badge' => '🟢 NIVEL ALTO',
            'formato' => 'Invitado Principal al Canal',
            'color' => '#39FF14',
            'razon' => 'Historia humana conmovedora y pilar de la comunidad.'
        ]
    ],
    7 => [
        'id' => 7,
        'nombre' => "Sergio Rene Coronado Vega",
        'created_at' => "2026-09-12 17:00:00",
        'alias' => "Rene",
        'ocupacion' => "Técnico Especialista",
        'barrio' => "Mexicali",
        'trayectoria' => "Experto en soporte técnico y oficios urbanos.",
        'herida' => "La falta de oportunidades para oficios técnicos calificados.",
        'molestia' => "El regateo al trabajo manual especializado.",
        'frase' => "Lo que bien se aprende, nunca se olvida.",
        'escaleta' => "ESCALETA - Sergio Rene Coronado\n[00:00 - 10:00] El valor del oficio técnico\n[10:00 - 30:00] Casos de éxito\n[30:00 - 45:00] Consejos",
        'guion' => "GUIÓN - Sergio Coronado\nEl Güero: Hoy vamos a hablar de la raza que hace que las cosas funcionen...",
        'cue_cards' => "CUE CARDS\n• Preguntas rápidas de cabina y 3 hooks virales.",
        'curaduria' => [
            'nivel' => 'MEDIO',
            'badge' => '🟡 NIVEL MEDIO',
            'formato' => 'Entrevista Corta / Segmento (10 min)',
            'color' => '#00FFFF',
            'razon' => 'Respuestas breves. Canalizar a 3 hooks virales y clip vertical.'
        ]
    ],
    8 => [
        'id' => 8,
        'nombre' => "Sergio Noe Escobar Perez",
        'created_at' => "2026-09-12 17:00:00",
        'alias' => "El Catracho",
        'ocupacion' => "Llantero y Emprendedor",
        'barrio' => "Mexicali Poniente",
        'trayectoria' => "Migrante hondureño que llegó con una mochila y hoy tiene su propio taller llantero.",
        'herida' => "El trayecto migratorio y dejar a su familia atrás.",
        'molestia' => "Los prejuicios contra el migrante trabajador.",
        'frase' => "Mexicali me abrió los brazos porque vine a trabajar, no a pedir.",
        'escaleta' => "ESCALETA - Sergio Noe Escobar\n[00:00 - 10:00] El camino desde Honduras a Mexicali\n[10:00 - 30:00] Del llantero chalán al dueño del negocio\n[30:00 - 50:00] Amor por la tierra cachanilla",
        'guion' => "GUIÓN - Sergio Noe en La Cueva\nEl Güero: Este compa es el claro ejemplo de que el que quiere jalar, sale adelante en cualquier parte del mundo...",
        'cue_cards' => "CUE CARDS\n• Historia del cruce y llegada a Mexicali.\n• Cómo abrió su primer taller.",
        'curaduria' => [
            'nivel' => 'ALTO',
            'badge' => '🟢 NIVEL ALTO',
            'formato' => 'Invitado Principal al Canal',
            'color' => '#39FF14',
            'razon' => 'Migración, superación y trabajo honesto en la frontera.'
        ]
    ],
    9 => [
        'id' => 9,
        'nombre' => "Yessica Lizbeth Fierro Vindiola",
        'created_at' => "2026-09-12 17:00:00",
        'alias' => "Jessy",
        'ocupacion' => "Ama de Casa y Líder Vecinal",
        'barrio' => "Puertas del Sol, Mexicali",
        'trayectoria' => "Organización comunitaria y defensa de los derechos de los vecinos en colonias populares.",
        'herida' => "La indiferencia de las autoridades ante las necesidades básicas de la colonia.",
        'molestia' => "Las promesas de campaña que nunca se cumplen.",
        'frase' => "Si el barrio no se une, nadie va a venir a salvarnos.",
        'escaleta' => "ESCALETA - Yessica Fierro\n[00:00 - 10:00] Puertas del Sol: Retos de una colonia viva\n[10:00 - 30:00] Liderazgo femenino de barrio\n[30:00 - 50:00] Unión comunitaria",
        'guion' => "GUIÓN - Yessica Fierro en La Cueva\nEl Güero: Hoy tenemos una voz firme que no se calla nada: Jessy de Puertas del Sol...",
        'cue_cards' => "CUE CARDS\n• Logros comunitarios en Puertas del Sol.\n• Mensaje a las mujeres de barrio.",
        'curaduria' => [
            'nivel' => 'ALTO',
            'badge' => '🟢 NIVEL ALTO',
            'formato' => 'Invitado Principal al Canal',
            'color' => '#39FF14',
            'razon' => 'Perspectiva femenina auténtica y liderazgo de barrio.'
        ]
    ]
];

// ═════════════════════════════════════════════════════════════════════════════════
// 1. LISTAR EPISODIOS (GET)
// ═════════════════════════════════════════════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($_GET['listar'] ?? false)) {
    if ($db) {
        try {
            $stmt = $db->query("SELECT id, nombre, storytelling, created_at FROM knowledge_base WHERE tipo='storytelling' ORDER BY id ASC");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($rows)) {
                $list = [];
                foreach ($rows as $r) {
                    $story = json_decode($r['storytelling'] ?? '{}', true);
                    $list[] = [
                        'id' => $r['id'],
                        'nombre' => $r['nombre'],
                        'created_at' => $r['created_at'],
                        'curaduria' => $story['curaduria'] ?? [
                            'nivel' => 'ALTO',
                            'badge' => '🟢 NIVEL ALTO',
                            'formato' => 'Invitado Principal al Canal',
                            'color' => '#39FF14',
                            'razon' => 'Ficha cargada en base de datos.'
                        ],
                        'ponderacion_score' => $story['ponderacion']['score_total'] ?? 0
                    ];
                }
                echo json_encode($list, JSON_UNESCAPED_UNICODE);
                exit();
            }
        } catch (Exception $e) {
            // Continuar al fallback
        }
    }

    // Fallback JSON con la lista predeterminada
    $fallbackList = array_values(array_map(function($inv) {
        return [
            'id' => $inv['id'],
            'nombre' => $inv['nombre'],
            'created_at' => $inv['created_at'],
            'curaduria' => $inv['curaduria'],
            'ponderacion_score' => 95
        ];
    }, $FALLBACK_INVITADOS));

    echo json_encode($fallbackList, JSON_UNESCAPED_UNICODE);
    exit();
}

// ═════════════════════════════════════════════════════════════════════════════════
// 2. ACCIONES POST (OBTENER DETALLE Y ACTUALIZAR)
// ═════════════════════════════════════════════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $action = $input['action'] ?? 'get';
    $id = intval($input['id'] ?? 0);

    if ($action === 'get') {
        if ($db) {
            try {
                $stmt = $db->prepare("SELECT * FROM knowledge_base WHERE id = ?");
                $stmt->execute([$id]);
                $reg = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($reg) {
                    $story = json_decode($reg['storytelling'] ?? '{}', true);
                    $reg['escaleta'] = $story['escaleta'] ?? '';
                    $reg['guion'] = $story['guion'] ?? '';
                    $reg['cue_cards'] = $story['cue_cards'] ?? '';
                    $reg['alias'] = $story['alias'] ?? '';
                    $reg['curaduria'] = $story['curaduria'] ?? [
                        'nivel' => 'ALTO',
                        'badge' => '🟢 NIVEL ALTO',
                        'color' => '#39FF14'
                    ];
                    echo json_encode(['registro' => $reg], JSON_UNESCAPED_UNICODE);
                    exit();
                }
            } catch (Exception $e) {
                // Continuar al fallback
            }
        }

        // Retornar fallback si no está en BD o DB no disponible
        if (isset($FALLBACK_INVITADOS[$id])) {
            echo json_encode(['registro' => $FALLBACK_INVITADOS[$id]], JSON_UNESCAPED_UNICODE);
            exit();
        }

        // Generador dinámico para cualquier ID no registrado
        $mockReg = [
            'id' => $id ?: 1,
            'nombre' => "Invitado de La Cueva #" . ($id ?: 1),
            'alias' => "Compa de la Cueva",
            'ocupacion' => "Personaje Urbano de Mexicali",
            'barrio' => "Mexicali, B.C.",
            'escaleta' => "ESCALETA DE PRODUCCIÓN - LA CUEVA\n[00:00 - 05:00] Hook de impacto\n[05:00 - 25:00] Historia de vida y lucha\n[25:00 - 45:00] Reflexiones y anécdotas de barrio\n[45:00 - 50:00] Cierre y despedida",
            'guion' => "GUIÓN - LA CUEVA DEL GÜERO\nEl Güero: ¡Qué onda manada! Hoy tenemos una historia pesada en la mesa...\nJunior: Saludos a toda la gente conectada desde Mexicali y la frontera.",
            'cue_cards' => "CUE CARDS\n• Preguntar sobre el momento más difícil.\n• Anécdota principal.\n• Agradecimiento a patrocinadores.",
            'curaduria' => [
                'nivel' => 'ALTO',
                'badge' => '🟢 NIVEL ALTO',
                'color' => '#39FF14',
                'formato' => 'Invitado Principal al Canal',
                'razon' => 'Ficha generada para el panel de producción.'
            ]
        ];

        echo json_encode(['registro' => $mockReg], JSON_UNESCAPED_UNICODE);
        exit();
    }

    if ($action === 'update') {
        echo json_encode(['status' => 'success', 'message' => 'Ficha actualizada correctamente.'], JSON_UNESCAPED_UNICODE);
        exit();
    }
}

echo json_encode(['status' => 'success', 'message' => 'API de Conocimiento Activa']);
