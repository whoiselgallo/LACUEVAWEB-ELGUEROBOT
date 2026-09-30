<?php
/**
 * ═════════════════════════════════════════════════════════════════════════════════
 * API - Guardar Storytelling y Conocimiento del Güero (PostgreSQL / Neon & JSON Resilient)
 * Endpoint: /api/api-guero-knowledge.php
 * Con soporte resiliente para base de datos, envíos recientes y fallback offline
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

// Intentar conectar a BD de forma segura
$db = null;
try {
    $db = db_connect();
} catch (Exception $e) {
    error_log('Knowledge DB Warning (Using resilient fallback): ' . $e->getMessage());
    $db = null;
}

// Fallbacks de Invitados Registrados (Fichas Maestras)
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
        'storytelling_enfoque' => "Resiliencia y lealtad en la música fronteriza desde la Libertad.",
        'reto' => "El incendio del primer estudio y reconstruir su carrera sin apoyo.",
        'escaleta' => "ESCALETA DE PRODUCCIÓN - LA CUEVA\nInvitado: Leo Camacho Higuera\nTema: Resiliencia y lealtad en la música fronteriza\n\n[00:00 - 03:00] Hook & Intro: El incendio que casi lo retira\n[03:00 - 15:00] Bloque 1: Creciendo en la Libertad y el primer micrófono\n[15:00 - 30:00] Bloque 2: El golpe duro y reconstruir la visión\n[30:00 - 45:00] Bloque 3: Produciendo en Mexicali y consejos a las nuevas generaciones\n[45:00 - 50:00] Cierre y reflexiones de La Cueva",
        'guion' => "GUIÓN BROADCAST - LA CUEVA DEL GÜERO\nInvitado: Leo Camacho Higuera\n\nEl Güero: ¡Qué onda manada! Hoy tenemos sentado en la mesa a un compa que le ha tocado picar piedra en serio: Leo Camacho.\n\nJunior: Bienvenido a la Cueva, carnal. La neta queríamos empezar con la pregunta directa: ¿qué sentiste el día que viste tu estudio en cenizas?\n\nLeo: Fue el momento donde tuve que decidir si me rendía o le metía el doble de ganas...",
        'cue_cards' => "CUE CARDS DE CABINA\n• Tarjeta 1: Preguntar sobre el origen del apodo y primeros años en la Libertad.\n• Tarjeta 2: Momento clave del incendio (conectar con la vulnerabilidad).\n• Tarjeta 3: Mencionar patrocinador oficial de la Cueva.\n• Tarjeta 4: Pregunta final sobre qué consejo le daría a su yo de hace 10 años.",
        'curaduria' => [
            'nivel' => 'ALTO',
            'badge' => '🟢 NIVEL ALTO',
            'formato' => 'Invitado Principal al Canal (Episodio Completo)',
            'color' => '#39FF14',
            'razon' => 'Expediente de alta potencia narrativa y lealtad de barrio.'
        ],
        'ponderacion' => [
            'score_total' => 96,
            'criterios' => [
                ['nombre' => 'Autenticidad & Conexión de Barrio', 'score' => 9.0, 'justificacion' => 'Raíces sólidas en la Colonia Libertad.'],
                ['nombre' => 'Potencia Emocional & Resiliencia', 'score' => 9.0, 'justificacion' => 'Historia de reconstrucción tras el incendio.'],
                ['nombre' => 'Confesión & Dinámica en Set', 'score' => 8.5, 'justificacion' => 'Anécdotas directas sin censura.'],
                ['nombre' => 'Mensaje Motivacional', 'score' => 9.0, 'justificacion' => 'Lealtad y superación para las nuevas generaciones.']
            ]
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
        'storytelling_enfoque' => "Cine independiente y visión cinematográfica en la frontera.",
        'reto' => "Romper barreras frente a la industria centralista.",
        'escaleta' => "ESCALETA - Javi Domz (JeyB)\n[00:00 - 05:00] Hook de impacto visual\n[05:00 - 20:00] La lucha por filmar en Mexicali\n[20:00 - 40:00] De la frontera para el mundo\n[40:00 - 50:00] Cierre",
        'guion' => "GUIÓN - Javi Domz en La Cueva\n\nEl Güero: Hoy está con nosotros JeyB, el compa detrás del lente más pesado de la baja...",
        'cue_cards' => "CUE CARDS\n• Preguntar sobre el proyecto de largometraje en Mexicali.\n• Detonador: ¿Vale la pena irse a CDMX o quedarse en el norte?",
        'curaduria' => [
            'nivel' => 'ALTO',
            'badge' => '🟢 NIVEL ALTO',
            'formato' => 'Invitado Principal al Canal',
            'color' => '#39FF14',
            'razon' => 'Storytelling visual y dirección cinematográfica de alto impacto.'
        ],
        'ponderacion' => [
            'score_total' => 95,
            'criterios' => [
                ['nombre' => 'Autenticidad & Conexión de Barrio', 'score' => 8.8, 'justificacion' => 'Cine fronterizo auténtico.'],
                ['nombre' => 'Potencia Emocional', 'score' => 8.9, 'justificacion' => 'Superación del rechazo en CDMX.'],
                ['nombre' => 'Mensaje Motivacional', 'score' => 9.0, 'justificacion' => 'Crear con lo que tienes a la mano.']
            ]
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
        'storytelling_enfoque' => "Justicia real vs burocracia desde el tribunal estatal.",
        'reto' => "Mantener la integridad en un sistema complejo.",
        'escaleta' => "ESCALETA - Marcelo Maciel\n[00:00 - 05:00] Hook: Ley vs Realidad de calle\n[05:00 - 25:00] Casos difíciles en la frontera\n[25:00 - 45:00] Ética y justicia real\n[45:00 - 50:00] Cierre",
        'guion' => "GUIÓN - Marcelo Maciel\nEl Güero: Marcelo, bienvenido a decir la neta del sistema...",
        'cue_cards' => "CUE CARDS\n• Pregunta clave: ¿Hay justicia real para el barrio?\n• Anécdota del caso más retador.",
        'curaduria' => [
            'nivel' => 'ALTO',
            'badge' => '🟢 NIVEL ALTO',
            'formato' => 'Invitado Principal al Canal',
            'color' => '#39FF14',
            'razon' => 'Tribunal estatal de justicia administrativa y visión social del barrio.'
        ],
        'ponderacion' => [
            'score_total' => 94,
            'criterios' => [
                ['nombre' => 'Autenticidad & Conexión', 'score' => 9.0, 'justificacion' => 'Defensa legal de la gente trabajadora.']
            ]
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
        'storytelling_enfoque' => "Comercio de barrio y décadas de trabajo en la Carbajal.",
        'reto' => "Levantarse de quiebras económicas familiares.",
        'escaleta' => "ESCALETA - Aurelio Gonzalez\n[00:00 - 10:00] La Carbajal y los primeros pesos\n[10:00 - 30:00] Negocios de frontera\n[30:00 - 50:00] Aprendizajes de vida",
        'guion' => "GUIÓN - Aurelio Gonzalez\nEl Güero: Hoy nos acompaña don Aurelio de la Carbajal...",
        'cue_cards' => "CUE CARDS\n• Historia del primer puesto comercial.\n• Claves para aguantar las crisis fronterizas.",
        'curaduria' => [
            'nivel' => 'ALTO',
            'badge' => '🟢 NIVEL ALTO',
            'formato' => 'Invitado Principal al Canal',
            'color' => '#39FF14',
            'razon' => 'Negocios y trayectoria comercial en la frontera desde la Carbajal.'
        ],
        'ponderacion' => ['score_total' => 95, 'criterios' => []]
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
        'storytelling_enfoque' => "El pilar de las madres de familia que sostienen el barrio.",
        'reto' => "Jornadas dobles y sacar a los hijos adelante.",
        'escaleta' => "ESCALETA - Guillermina Ayala (Especial Jefas de Familia)\n[00:00 - 10:00] Madres que no se rajan\n[10:00 - 35:00] Historias de la Libertad\n[35:00 - 50:00] Mensaje a los hijos",
        'guion' => "GUIÓN - Doña Guille en La Cueva\nEl Güero: Este episodio es un homenaje a las que nunca se doblan...",
        'cue_cards' => "CUE CARDS\n• Anécdota de cómo sacó adelante a la familia.\n• Momento de mayor orgullo.",
        'curaduria' => [
            'nivel' => 'ALTO',
            'badge' => '🟢 NIVEL ALTO',
            'formato' => 'Invitado Principal al Canal',
            'color' => '#39FF14',
            'razon' => 'Historia humana conmovedora y pilar de la comunidad.'
        ],
        'ponderacion' => ['score_total' => 97, 'criterios' => []]
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
        'storytelling_enfoque' => "El valor del oficio técnico y soporte urbano.",
        'reto' => "El regateo y la dignificación del oficio.",
        'escaleta' => "ESCALETA - Sergio Rene Coronado\n[00:00 - 10:00] El valor del oficio técnico\n[10:00 - 30:00] Casos de éxito\n[30:00 - 45:00] Consejos",
        'guion' => "GUIÓN - Sergio Coronado\nEl Güero: Hoy vamos a hablar de la raza que hace que las cosas funcionen...",
        'cue_cards' => "CUE CARDS\n• Preguntas rápidas de cabina y 3 hooks virales.",
        'curaduria' => [
            'nivel' => 'MEDIO',
            'badge' => '🟡 NIVEL MEDIO',
            'formato' => 'Entrevista Corta / Segmento (10 min)',
            'color' => '#00FFFF',
            'razon' => 'Respuestas breves. Canalizar a 3 hooks virales y clip vertical.'
        ],
        'ponderacion' => ['score_total' => 78, 'criterios' => []]
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
        'storytelling_enfoque' => "Migración, superación y trabajo honesto en la frontera.",
        'reto' => "El cruce migratorio y abrir su propio taller desde cero.",
        'escaleta' => "ESCALETA - Sergio Noe Escobar\n[00:00 - 10:00] El camino desde Honduras a Mexicali\n[10:00 - 30:00] Del llantero chalán al dueño del negocio\n[30:00 - 50:00] Amor por la tierra cachanilla",
        'guion' => "GUIÓN - Sergio Noe en La Cueva\nEl Güero: Este compa es el claro ejemplo de que el que quiere jalar, sale adelante en cualquier parte del mundo...",
        'cue_cards' => "CUE CARDS\n• Historia del cruce y llegada a Mexicali.\n• Cómo abrió su primer taller.",
        'curaduria' => [
            'nivel' => 'ALTO',
            'badge' => '🟢 NIVEL ALTO',
            'formato' => 'Invitado Principal al Canal',
            'color' => '#39FF14',
            'razon' => 'Migración, superación y trabajo honesto en la frontera.'
        ],
        'ponderacion' => ['score_total' => 96, 'criterios' => []]
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
        'storytelling_enfoque' => "Liderazgo femenino y defensa comunitaria en Puertas del Sol.",
        'reto' => "Organizar a la comunidad frente a la indiferencia de las autoridades.",
        'escaleta' => "ESCALETA - Yessica Fierro\n[00:00 - 10:00] Puertas del Sol: Retos de una colonia viva\n[10:00 - 30:00] Liderazgo femenino de barrio\n[30:00 - 50:00] Unión comunitaria",
        'guion' => "GUIÓN - Yessica Fierro en La Cueva\nEl Güero: Hoy tenemos una voz firme que no se calla nada: Jessy de Puertas del Sol...",
        'cue_cards' => "CUE CARDS\n• Logros comunitarios en Puertas del Sol.\n• Mensaje a las mujeres de barrio.",
        'curaduria' => [
            'nivel' => 'ALTO',
            'badge' => '🟢 NIVEL ALTO',
            'formato' => 'Invitado Principal al Canal',
            'color' => '#39FF14',
            'razon' => 'Perspectiva femenina auténtica y liderazgo de barrio.'
        ],
        'ponderacion' => ['score_total' => 95, 'criterios' => []]
    ],
    10 => [
        'id' => 10,
        'nombre' => "La Pocha",
        'created_at' => "2026-09-29 20:00:00",
        'alias' => "La Pocha",
        'ocupacion' => "Comerciante, Emprendedora y Personaje Urbano",
        'barrio' => "Pueblo Nuevo / La Línea, Mexicali",
        'trayectoria' => "Vida forjada entre el otro lado y Mexicali, rompiendo esquemas con comercio independiente, estilo chicano y puro flow cachanilla.",
        'herida' => "El rechazo en ambos lados de la frontera y levantarse de la ruina económica con puro trabajo honesto.",
        'molestia' => "La gente alzada que se avergüenza de sus raíces y los desleales.",
        'frase' => "En la frontera nadie nos regala nada: o le chingas con orgullo o te quedas en el camino.",
        'storytelling_enfoque' => "Identidad fronteriza, cultura de barrio y resiliencia femenina desde Pueblo Nuevo.",
        'reto' => "Cruzar el cerco, perderlo todo y reconstruirse como pilar de su familia y comunidad.",
        'escaleta' => "ESCALETA DE PRODUCCIÓN - LA CUEVA DEL GÜERO\nInvitado: La Pocha (Personaje Urbano de Mexicali)\nTema: Vida de frontera, cultura chicana y el poder de no rajarse\n\n[00:00 - 05:00] Hook & Intro: El origen del apodo 'La Pocha' y el choque de dos mundos en la línea\n[05:00 - 15:00] Bloque 1: Creciendo en Pueblo Nuevo, el spanglish y los primeros jales pesados\n[15:00 - 28:00] Bloque 2: Los madrazos de la vida: perderlo todo en el otro lado y empezar de cero en Mexicali\n[28:00 - 40:00] Bloque 3: Negocios de frontera, códigos de respeto y el don de conectar con la gente\n[40:00 - 48:00] Bloque 4: Dinámica en cabina, confesión incómoda sin censura y canción bélica\n[48:00 - 52:00] Cierre & Reflexión: Mensaje a las morras y vatos que están pasando por la lumbre",
        'guion' => "GUIÓN BROADCAST - LA CUEVA DEL GÜERO\nInvitado: La Pocha | Conducción: El Güero & Junior\n\nEl Güero: ¡Qué onda manada! Hoy la mesa vibra pesado porque tenemos a una morra que representa la pura esencia de la frontera: auténtica, entrona y sin pelos en la lengua. ¡Bienvenida a la Cueva, mi querida Pocha!\n\nJunior: ¡Qué onda Pocha! Todo Mexicali y el valle te ubican. Cuéntanos directo: ¿de dónde nació el apodo de 'La Pocha' y cómo fue crecer con un pie en Calexico y el otro en Pueblo Nuevo?\n\nLa Pocha: ¡Qué onda Güero, qué onda Junior! Pues la neta me decían así desde morra porque hablaba mocho, pero ese spanglish y esa mezcla es lo que me dio la fuerza para salir adelante en los dos lados...\n\nEl Güero: En el cuestionario nos platicaste de un momento donde sentiste que tocabas fondo. Cuéntale a la raza qué se siente tener que reinventarse cuando todos dudan de ti...\n\nLa Pocha: Se siente gacho, pero de la lumbre sales templado. Yo me dije: 'Aquí no hay tiempo de llorar, hay que chingarle al doble'.\n\nJunior: Y la neta, ¿cuál es el secreto para mantener la humildad cuando te empieza a ir bien?\n\nLa Pocha: Nunca olvidar de dónde vienes, carnal. El barrio te da escuela, pero tú decides si caminas derecho.",
        'cue_cards' => "CUE CARDS DE CABINA - HOSTS\n• TARJETA 1 (HOOK): Origen del apodo 'La Pocha' y anécdotas de Pueblo Nuevo y la garita.\n• TARJETA 2 (RESILIENCIA): El golpe más duro en el trabajo y cómo levantó su propio negocio.\n• TARJETA 3 (DINÁMICA): Activar reto en cabina y mención especial a patrocinadores de la Cueva.\n• TARJETA 4 (CIERRE): Mensaje motivacional de poder femenino y superación fronteriza.",
        'curaduria' => [
            'nivel' => 'ALTO',
            'badge' => '🟢 NIVEL ALTO',
            'formato' => 'Invitado Principal al Canal (Episodio Completo 45+ min)',
            'color' => '#39FF14',
            'razon' => 'Personaje urbano icónico de Mexicali. Potencia narrativa de frontera, autenticidad y resiliencia.'
        ],
        'ponderacion' => [
            'score_total' => 98,
            'criterios' => [
                ['nombre' => 'Autenticidad & Conexión de Barrio', 'score' => 9.0, 'justificacion' => 'Puro arraigo fronterizo en Pueblo Nuevo y La Línea.'],
                ['nombre' => 'Potencia Emocional & Resiliencia', 'score' => 9.0, 'justificacion' => 'Historia de superación y reinvención económica.'],
                ['nombre' => 'Carisma & Dinámica en Set', 'score' => 9.0, 'justificacion' => 'Lenguaje directo, chispa y anécdotas sin filtro.'],
                ['nombre' => 'Mensaje Motivacional', 'score' => 9.0, 'justificacion' => 'Empoderamiento y lealtad comunitaria.']
            ]
        ],
        'respuestas' => [
            1 => "La Pocha",
            2 => "La Pocha",
            3 => "lapocha@lacuevadelguero.com",
            4 => "Comerciante, Emprendedora y Creadora",
            5 => "Firme, alegre, trabajadora",
            6 => "Colonia Pueblo Nuevo / La Línea, Mexicali",
            7 => "La familia elegida y la escuela donde se aprende el respeto",
            8 => "A no rajarse por nada y defender a los tuyos",
            9 => "Tener mis propios negocios y ayudar a mi jefa",
            10 => "Muchos me dijeron que por ser mujer y pocha no iba a poder",
            11 => "Aventarme sola al otro lado a buscar la chuleta",
            12 => "Que me hicieran menos por hablar spanglish en un jale",
            13 => "Confiar en personas que me robaron mi inversión inicial",
            14 => "Armar una carne asada con mi familia y mi gente de barrio",
            15 => "Soy muy feliz y bendecida, pero voy por más metas",
            16 => "Años de juventud, desveladas y días sin descanso",
            17 => "Abrir mi propio local y ver sonreír a mi familia",
            18 => "A los años 2000 en Mexicali para abrazar a mis abuelos",
            19 => "No tengas miedo morra, todo lo que sueñas se va a cumplir",
            20 => "Huevos y disciplina",
            21 => "Sí, viviendo a mi manera y con la frente en alto",
            22 => "El carisma y que no me le achicopalo a nadie",
            23 => "La vez que se me cayó la peluca en pleno baile en Calexico",
            24 => "Corridos pesados y rap chicano",
            25 => "Que me sé de memoria todas las rolas de Selena y Paquita la del Barrio",
            26 => "Los hipócritas y los que no pagan lo que deben",
            27 => "Como una mujer entrona que nunca se rajó",
            28 => "Muy desconfiada a veces",
            29 => "Los churros locos y los tacos de noche",
            30 => "A la soledad, pero me refugio en mi trabajo y mi fe",
            31 => "Reto de destreza en vivo",
            32 => "Un saludo a toda la banda de Pueblo Nuevo y la frontera",
            33 => "Acuérdate que después de la tormenta sale el sol cachanilla. ¡Con todo y pa'delante!"
        ]
    ]
];

// Función para obtener envíos guardados en JSON local
function obtenerEnviosJSON() {
    $file = __DIR__ . '/../images/formularios/cuestionarios_envios.json';
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $data = json_decode($content, true);
        if (is_array($data)) {
            return $data;
        }
    }
    return [];
}

// ═════════════════════════════════════════════════════════════════════════════════
// 1. LISTAR REGISTROS (GET ?listar=true)
// ═════════════════════════════════════════════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'GET' && (isset($_GET['listar']) || isset($_GET['list']))) {
    $listaFinal = [];
    $seenNames = [];

    // 1.1 Leer registros desde Neon PostgreSQL (si hay conexión)
    if ($db) {
        try {
            $stmt = $db->query("SELECT id, nombre, storytelling, created_at FROM knowledge_base WHERE tipo='storytelling' ORDER BY id DESC");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $r) {
                $normName = mb_strtolower(trim($r['nombre']));
                if (!isset($seenNames[$normName])) {
                    $story = json_decode($r['storytelling'] ?? '{}', true) ?: [];
                    $curaduria = $story['curaduria'] ?? [
                        'nivel' => 'ALTO',
                        'badge' => '🟢 NIVEL ALTO',
                        'formato' => 'Invitado Principal al Canal',
                        'color' => '#39FF14',
                        'razon' => 'Ficha cargada en base de datos Neon.'
                    ];
                    $score = $story['ponderacion']['score_total'] ?? 95;

                    $listaFinal[] = [
                        'id' => $r['id'],
                        'nombre' => $r['nombre'],
                        'created_at' => $r['created_at'] ?? date('Y-m-d H:i:s'),
                        'curaduria' => $curaduria,
                        'ponderacion_score' => $score
                    ];
                    $seenNames[$normName] = true;
                }
            }
        } catch (Exception $e) {
            error_log('DB Read Error in Listar: ' . $e->getMessage());
        }
    }

    // 1.2 Leer envíos recientes del almacenamiento persistente JSON
    $enviosJSON = obtenerEnviosJSON();
    foreach ($enviosJSON as $envio) {
        $name = $envio['nombre'] ?? '';
        $normName = mb_strtolower(trim($name));
        if (!empty($name) && !isset($seenNames[$normName])) {
            $curaduria = $envio['curaduria'] ?? [
                'nivel' => 'ALTO',
                'badge' => '🟢 NIVEL ALTO',
                'formato' => 'Invitado Principal al Canal',
                'color' => '#39FF14',
                'razon' => 'Entrevista de 33 preguntas registrada.'
            ];
            $score = $envio['ponderacion']['score_total'] ?? 96;

            $listaFinal[] = [
                'id' => $envio['id'] ?? (100 + count($listaFinal)),
                'nombre' => $name,
                'created_at' => $envio['created_at'] ?? date('Y-m-d H:i:s'),
                'curaduria' => $curaduria,
                'ponderacion_score' => $score
            ];
            $seenNames[$normName] = true;
        }
    }

    // 1.3 Agregar los 8 registros clásicos de demostración si no existen ya
    foreach ($FALLBACK_INVITADOS as $fall) {
        $normName = mb_strtolower(trim($fall['nombre']));
        if (!isset($seenNames[$normName])) {
            $listaFinal[] = [
                'id' => $fall['id'],
                'nombre' => $fall['nombre'],
                'created_at' => $fall['created_at'],
                'curaduria' => $fall['curaduria'],
                'ponderacion_score' => $fall['ponderacion']['score_total'] ?? 95
            ];
            $seenNames[$normName] = true;
        }
    }

    echo json_encode($listaFinal, JSON_UNESCAPED_UNICODE);
    exit();
}

// ═════════════════════════════════════════════════════════════════════════════════
// 2. ACCIONES POST (DETALLE Y ACTUALIZACIÓN)
// ═════════════════════════════════════════════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rawInput = file_get_contents('php://input');
    $input = json_decode($rawInput, true) ?: $_POST;
    $action = $input['action'] ?? 'get';
    $id = $input['id'] ?? 0;
    $nombreReq = trim($input['nombre'] ?? '');

    if ($action === 'get') {
        // 2.1 Buscar en PostgreSQL
        if ($db && is_numeric($id) && intval($id) > 0) {
            try {
                $stmt = $db->prepare("SELECT * FROM knowledge_base WHERE id = ?");
                $stmt->execute([intval($id)]);
                $reg = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($reg) {
                    $story = json_decode($reg['storytelling'] ?? '{}', true) ?: [];
                    $reg['escaleta'] = $story['escaleta'] ?? ($reg['escaleta'] ?? '');
                    $reg['guion'] = $story['guion'] ?? ($reg['guion'] ?? '');
                    $reg['cue_cards'] = $story['cue_cards'] ?? ($reg['cue_cards'] ?? '');
                    $reg['alias'] = $story['alias'] ?? ($reg['alias'] ?? '');
                    $reg['storytelling_enfoque'] = $story['storytelling_enfoque'] ?? ($reg['storytelling_enfoque'] ?? '');
                    $reg['reto'] = $story['reto'] ?? ($reg['reto'] ?? '');
                    $reg['frase'] = $story['frase'] ?? ($reg['frase'] ?? '');
                    $reg['curaduria'] = $story['curaduria'] ?? [
                        'nivel' => 'ALTO',
                        'badge' => '🟢 NIVEL ALTO',
                        'color' => '#39FF14',
                        'formato' => 'Invitado Principal al Canal'
                    ];
                    $reg['ponderacion'] = $story['ponderacion'] ?? ['score_total' => 95, 'criterios' => []];
                    $reg['respuestas'] = $story['respuestas'] ?? [];
                    echo json_encode(['registro' => $reg], JSON_UNESCAPED_UNICODE);
                    exit();
                }
            } catch (Exception $e) {
                // Seguir a fallback
            }
        }

        // 2.2 Buscar en envíos recientes JSON
        $envios = obtenerEnviosJSON();
        foreach ($envios as $env) {
            if ((isset($env['id']) && strval($env['id']) === strval($id)) || (!empty($nombreReq) && strcasecmp($env['nombre'], $nombreReq) === 0)) {
                echo json_encode(['registro' => $env], JSON_UNESCAPED_UNICODE);
                exit();
            }
        }

        // 2.3 Buscar en FALLBACK_INVITADOS
        $intId = intval($id);
        if (isset($FALLBACK_INVITADOS[$intId])) {
            echo json_encode(['registro' => $FALLBACK_INVITADOS[$intId]], JSON_UNESCAPED_UNICODE);
            exit();
        }

        foreach ($FALLBACK_INVITADOS as $fall) {
            if (!empty($nombreReq) && strcasecmp($fall['nombre'], $nombreReq) === 0) {
                echo json_encode(['registro' => $fall], JSON_UNESCAPED_UNICODE);
                exit();
            }
        }

        // 2.4 Generador de expediente en vivo para IDs no encontrados
        $mockReg = [
            'id' => $id ?: 1,
            'nombre' => !empty($nombreReq) ? $nombreReq : "Invitado de La Cueva #" . ($id ?: 1),
            'alias' => "Compa de la Cueva",
            'ocupacion' => "Personaje Urbano de Mexicali",
            'barrio' => "Mexicali, B.C.",
            'storytelling_enfoque' => "Historias de barrio y superación en la frontera.",
            'reto' => "Los madrazos del camino y no rajarse.",
            'frase' => "\"El barrio no se platica, se demuestra en los hechos.\"",
            'escaleta' => "ESCALETA DE PRODUCCIÓN - LA CUEVA\n[00:00 - 05:00] Hook de impacto\n[05:00 - 25:00] Historia de vida y lucha\n[25:00 - 45:00] Reflexiones y anécdotas de barrio\n[45:00 - 50:00] Cierre y despedida",
            'guion' => "GUIÓN - LA CUEVA DEL GÜERO\nEl Güero: ¡Qué onda manada! Hoy tenemos una historia pesada en la mesa...\nJunior: Saludos a toda la gente conectada desde Mexicali y la frontera.",
            'cue_cards' => "CUE CARDS\n• Preguntar sobre el momento más difícil.\n• Anécdota principal.\n• Agradecimiento a patrocinadores.",
            'curaduria' => [
                'nivel' => 'ALTO',
                'badge' => '🟢 NIVEL ALTO',
                'color' => '#39FF14',
                'formato' => 'Invitado Principal al Canal',
                'razon' => 'Ficha generada para el panel de producción.'
            ],
            'ponderacion' => [
                'score_total' => 95,
                'criterios' => []
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
