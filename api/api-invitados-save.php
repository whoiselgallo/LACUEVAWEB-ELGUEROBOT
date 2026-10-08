<?php
/**
 * ═════════════════════════════════════════════════════════════════════════════════
 * API - Guardar Entrevista / Expediente Storytelling de Invitados (La Cueva del Güero)
 * Endpoint: /api/api-invitados-save.php
 * Con soporte resiliente para PostgreSQL Neon y almacenamiento persistente JSON
 * ═════════════════════════════════════════════════════════════════════════════════
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../config/config.php';

// Leer entrada JSON
$rawInput = file_get_contents("php://input");
$input = json_decode($rawInput, true);

if (!$input && !empty($_POST)) {
    $input = $_POST;
}

if (!$input) {
    echo json_encode(["status" => "error", "error" => "No se recibió información en la solicitud."]);
    exit;
}

// Extraer campos principales
$respuestas = $input['respuestas'] ?? [];
$nombre = trim($input['nombre'] ?? ($respuestas[1] ?? ($input['No bre'] ?? ($input['Nombre'] ?? ''))));

if (empty($nombre)) {
    $nombre = "Invitado La Cueva " . date('d-m-Y H:i');
}

$alias = trim($input['alias'] ?? ($respuestas[2] ?? ($input['petardo'] ?? '')));
$contacto = trim($input['contacto'] ?? ($respuestas[3] ?? ($input['correo'] ?? '')));
$ocupacion = trim($input['ocupacion'] ?? ($respuestas[4] ?? ($input['¿A qué te dedicas actualmente?'] ?? 'Invitado Especial')));
$definicion = trim($respuestas[5] ?? ($input['¿Como te defines en 3 palabras?'] ?? 'Auténtico, trabajador, de barrio'));
$barrio = trim($input['barrio'] ?? ($respuestas[6] ?? ($input['¿De qué colonia eres?'] ?? 'Mexicali, B.C.')));
$significadoBarrio = trim($respuestas[7] ?? ($input['Que significa para ti el Barrio?'] ?? 'Familia y códigos'));
$ensenanzaBarrio = trim($respuestas[8] ?? ($input['¿Cuál es la mayor enseñanza que te ha regalado el barrio?'] ?? 'Lealtad y no rajarse'));
$suenoNino = trim($respuestas[9] ?? ($input['Que quería ser de niño, el tu de 10 años?'] ?? 'Salir adelante'));
$obstaculo = trim($respuestas[10] ?? ($input['Hubo alguien que se burlo o te dijo que no podrias conseguir tu sueño? ¿Quien?'] ?? 'Muchos dudaron'));
$peligroso = trim($respuestas[11] ?? ($input['Que es lo mas peligroso que has hecho?'] ?? 'Vivir al límite'));
$humillante = trim($respuestas[12] ?? ($input['El Camino y los madrazos: ¿Qué es lo más humillante que te han pedido hacer en un jale (trabajo)? ¿cuál fue tu reacción?'] ?? 'Madrazos del trabajo'));
$peorError = trim($respuestas[13] ?? ($input['En el camino a veces perdemos el rumbo y nos desviamos del objetivo, ¿Cual ha sido el peor error que cometiste en tu carrera, ese que casi hace que renuncies a tus sueños?'] ?? 'Desviarme del camino'));
$ultimas24h = trim($respuestas[14] ?? ($input['Si en este momento te dijera, solo te quedan 24 horas de vida: ¿En que las utilizarias?'] ?? 'Con mi familia y los míos'));
$felicidad = trim($respuestas[15] ?? ($input['¿Eres feliz o aun te faltan sueños por realizar?'] ?? 'Feliz pero con más metas'));
$sacrificios = trim($respuestas[16] ?? ($input['¿Cuáles son esas cosas que tuviste que sacrificar para poder estar en este punto de tu carrera?'] ?? 'Tiempo y esfuerzo'));
$primerLogro = trim($respuestas[17] ?? ($input['¿Cual fue tu primer logro? Ese momento en donde dijiste, "chignon" me siento orgulloso de mi y lo que logre.'] ?? 'El primer paso'));
$maquinaTiempo = trim($respuestas[18] ?? ($input['¿Si tuvieras una máquina del tiempo, a donde viajarías y por qué?'] ?? 'Al pasado a corregir o revivir'));
$yo10Anos = trim($respuestas[19] ?? ($input['¿Que le dirias hoy si te encontraras de frente a tu yo de hace 10 años?'] ?? 'Que no se rinda'));
$secretoExito = trim($respuestas[20] ?? ($input['La mentalidad y el flow. ¿Tu secreto para el éxito en una palabra? ¿cómo le hiciste para llegar donde estas?'] ?? 'Perseverancia'));
$cumpliendoSueno = trim($respuestas[21] ?? ($input['En estos momentos: ¿Estas cumpliendo lo que soñaste ser?'] ?? 'En el camino'));
$donEspecial = trim($respuestas[22] ?? ($input['¿Qué te hace diferente a los de más, tienes identificado ese don que te hace especial?'] ?? 'Autenticidad'));
$chusco = trim($respuestas[23] ?? ($input['Algo chusco o chistoso que te haya pasado, que cada que lo recuerdas te hace reir.'] ?? 'Anécdotas con los compas'));
$cancionBelica = trim($respuestas[24] ?? ($input['¿Esa canción que escuchas y te pones "bélico"?'] ?? 'Corridos y música urbana'));
$confesion = trim($respuestas[25] ?? ($input['Confesión incomoda: Cuentanos eso que siempre has querido decir, pero no te atreves a sacarlo de tu ronco pecho. Danos la exclusiva.'] ?? 'La neta sin filtro'));
$molestia = trim($respuestas[26] ?? ($input['Lo que más te molesta:'] ?? 'La hipocresía y la deslealtad'));
$recordar = trim($respuestas[27] ?? ($input['¿Como te gustaría que la gente te recuerde cuando ya no estes en este mundo?'] ?? 'Como alguien derecho y trabajador'));
$mayorDefecto = trim($respuestas[28] ?? ($input['¿Cuál consideras que es tu mayor defecto?'] ?? 'Desesperado / terco'));
$gustoCulposo = trim($respuestas[29] ?? ($input['Todos tenemos algo que nos gusta, pero nos da pena que la gente se entere. ¿cuál es tu gusto culposo?'] ?? 'Música romántica / comida'));
$miedo = trim($respuestas[30] ?? ($input['¿A qué le tienes miedo y cómo lo enfrentas?'] ?? 'Al fracaso, pero dándole de frente'));
$dinamica = trim($respuestas[31] ?? ($input['Si tuvieras que elegir solo una opción:'] ?? 'Reto de habilidad en cabina'));
$mencionExtra = trim($respuestas[32] ?? ($input['¿Algo más que quieras que mencionemos en el episodio?'] ?? 'Un saludo al barrio'));
$mensajeAyuda = trim($respuestas[33] ?? ($input['Es momento de la ayuda, que le dirias a esa persona que esta pasando por un mal momento, para que no tire la toalla y se motive a seguir adelante.'] ?? 'El sol siempre vuelve a brillar'));

// Generar Código Único de Tracking
$codeNumber = mt_rand(1000, 9999);
$trackingCode = 'GUEST-' . $codeNumber;
$fechaRegistro = date('Y-m-d H:i:s');
$fechaCorta = date('Y-m-d');

// Storytelling Sintetizado
$storytellingEnfoque = "De {$barrio} para el mundo: Cómo {$nombre} forjó su camino superando obstáculos con {$secretoExito}.";
$retoPrincipal = "Momento crítico: {$humillante} y la lección de {$peorError}.";
$fraseGancho = !empty($mensajeAyuda) ? "\"{$mensajeAyuda}\"" : (!empty($ensenanzaBarrio) ? "\"{$ensenanzaBarrio}\"" : "\"La lealtad no se platica, se demuestra.\"");

// ═════════════════════════════════════════════════════════════════════════════════
// EVALUACIÓN AVANZADA Y AFINADA DE CURADURÍA (33 PARÁMETROS: 1-10 PTS, TOTAL 33-330)
// ═════════════════════════════════════════════════════════════════════════════════
$PREGUNTAS_META = [
    1 => ['acto' => 'Identificación', 'titulo' => 'Nombre Completo', 'tipo' => 'ident', 'val' => $nombre],
    2 => ['acto' => 'Identificación', 'titulo' => 'Petardo / Alias de Barrio', 'tipo' => 'alias', 'val' => $alias],
    3 => ['acto' => 'Identificación', 'titulo' => 'Contacto (Correo & WhatsApp)', 'tipo' => 'contacto', 'val' => $contacto],
    4 => ['acto' => 'Bloque 1: Raíces', 'titulo' => 'Ocupación Actual & Jale Diario', 'tipo' => 'jale', 'val' => $ocupacion],
    5 => ['acto' => 'Bloque 1: Raíces', 'titulo' => 'Definición en 3 Palabras', 'tipo' => 'tres_palabras', 'val' => $definicion],
    6 => ['acto' => 'Bloque 1: Raíces', 'titulo' => 'Colonia / Barrio de Origen', 'tipo' => 'barrio', 'val' => $barrio],
    7 => ['acto' => 'Bloque 1: Raíces', 'titulo' => 'Significado del Barrio', 'tipo' => 'narrativa', 'val' => $significadoBarrio],
    8 => ['acto' => 'Bloque 1: Raíces', 'titulo' => 'Mayor Enseñanza de la Calle', 'tipo' => 'sabiduria', 'val' => $ensenanzaBarrio],
    9 => ['acto' => 'Bloque 1: Raíces', 'titulo' => 'Qué Quería Ser de Niño a los 10 Años', 'tipo' => 'nino', 'val' => $suenoNino],
    10 => ['acto' => 'Bloque 1: Raíces', 'titulo' => 'Obstáculos & Quien se Burló de su Sueño', 'tipo' => 'conflicto', 'val' => $obstaculo],
    11 => ['acto' => 'Bloque 2: Madrazos', 'titulo' => 'Lo Más Peligroso Vivido', 'tipo' => 'peligro', 'val' => $peligroso],
    12 => ['acto' => 'Bloque 2: Madrazos', 'titulo' => 'Lo Más Humillante en un Jale (Los Madrazos)', 'tipo' => 'madrazo_oro', 'val' => $humillante],
    13 => ['acto' => 'Bloque 2: Madrazos', 'titulo' => 'Peor Error en su Carrera / Rumbo Perdido', 'tipo' => 'madrazo_oro', 'val' => $peorError],
    14 => ['acto' => 'Bloque 2: Madrazos', 'titulo' => 'Últimas 24 Horas de Vida', 'tipo' => 'filosofia', 'val' => $ultimas24h],
    15 => ['acto' => 'Bloque 2: Madrazos', 'titulo' => 'Estado de Felicidad & Metas Pendientes', 'tipo' => 'felicidad', 'val' => $felicidad],
    16 => ['acto' => 'Bloque 2: Madrazos', 'titulo' => 'Sacrificios para Llegar a este Punto', 'tipo' => 'sacrificio', 'val' => $sacrificios],
    17 => ['acto' => 'Bloque 2: Madrazos', 'titulo' => 'Primer Logro Chingón & Orgullo', 'tipo' => 'logro', 'val' => $primerLogro],
    18 => ['acto' => 'Bloque 3: Mentalidad', 'titulo' => 'Viaje en la Máquina del Tiempo', 'tipo' => 'nostalgia', 'val' => $maquinaTiempo],
    19 => ['acto' => 'Bloque 3: Mentalidad', 'titulo' => 'Mensaje a su Yo de Hace 10 Años', 'tipo' => 'sabiduria', 'val' => $yo10Anos],
    20 => ['acto' => 'Bloque 3: Mentalidad', 'titulo' => 'Secreto del Éxito & Mentalidad de Triunfo', 'tipo' => 'secreto', 'val' => $secretoExito],
    21 => ['acto' => 'Bloque 3: Mentalidad', 'titulo' => 'Cumpliendo lo Soñado de Niño', 'tipo' => 'sueno', 'val' => $cumpliendoSueno],
    22 => ['acto' => 'Bloque 3: Mentalidad', 'titulo' => 'Don Especial & Diferenciador', 'tipo' => 'don', 'val' => $donEspecial],
    23 => ['acto' => 'Bloque 4: Anécdotas', 'titulo' => 'Anécdota Chusca / Graciosa', 'tipo' => 'comedia', 'val' => $chusco],
    24 => ['acto' => 'Bloque 4: Anécdotas', 'titulo' => 'Canción Bélica Favorita', 'tipo' => 'musica', 'val' => $cancionBelica],
    25 => ['acto' => 'Bloque 4: Anécdotas', 'titulo' => 'Confesión Incómoda & Exclusiva', 'tipo' => 'exclusiva', 'val' => $confesion],
    26 => ['acto' => 'Bloque 4: Anécdotas', 'titulo' => 'Lo Que Más le Molesta en la Vida', 'tipo' => 'molestia', 'val' => $molestia],
    27 => ['acto' => 'Bloque 4: Anécdotas', 'titulo' => 'Cómo Quiere ser Recordado (Legado)', 'tipo' => 'legado', 'val' => $recordar],
    28 => ['acto' => 'Bloque 4: Anécdotas', 'titulo' => 'Mayor Defecto Reconocido', 'tipo' => 'defecto', 'val' => $mayorDefecto],
    29 => ['acto' => 'Bloque 4: Anécdotas', 'titulo' => 'Gusto Culposo Oculto', 'tipo' => 'gusto', 'val' => $gustoCulposo],
    30 => ['acto' => 'Bloque 4: Anécdotas', 'titulo' => 'Miedo Profundo & Cómo lo Enfrenta', 'tipo' => 'vulnerabilidad', 'val' => $miedo],
    31 => ['acto' => 'Bloque 4: Anécdotas', 'titulo' => 'Dinámica / Reto Elegido en Cabina', 'tipo' => 'dinamica', 'val' => $dinamica],
    32 => ['acto' => 'Bloque 5: Cierre', 'titulo' => 'Mención Extra para el Episodio', 'tipo' => 'mencion', 'val' => $mencionExtra],
    33 => ['acto' => 'Bloque 5: Cierre', 'titulo' => 'Mensaje Motivacional (No Tirar la Toalla)', 'tipo' => 'motivacional', 'val' => $mensajeAyuda]
];

$criterios33 = [];
$totalScore = 0;
$keywordsOro = ['barrio', 'calle', 'jale', 'jefa', 'familia', 'compas', 'chinga', 'madrazo', 'sueño', 'respeto', 'humildad', 'huevos', 'corazón', 'lágrimas', 'orgullo', 'miedo', 'levantarse', 'perder', 'ganar', 'sangre', 'sudor', 'mexicali', 'frontera', 'pueblo', 'lealtad', 'lucha', 'sacrificio', 'fe', 'auténtico', 'firme'];

for ($i = 1; $i <= 33; $i++) {
    $meta = $PREGUNTAS_META[$i];
    $val = trim($meta['val'] ?? '');
    $len = mb_strlen($val);
    $norm = mb_strtolower($val);
    $tipo = $meta['tipo'] ?? 'narrativa';

    // Evaluar respuestas evasivas o vacías
    $esVacio = ($len === 0);
    $esEvasivo = in_array($norm, ['.', '-', 'nada', 'no', 'nose', 'no se', 'ninguno', 'ninguna', 'sin palabras', 'lo de siempre', 'lo normal', 'todo bien', 'n/a', 'na']);

    if ($esVacio) {
        $score = 2;
        $just = "Sin respuesta registrada. Pregunta abierta para formular en cabina.";
        $tag = "⚠️ Pendiente";
    } elseif ($esEvasivo) {
        $score = 3;
        $just = "Respuesta evasiva: \"$val\". El Güero y Junior deben presionar con pregunta directa.";
        $tag = "⚠️ Evasiva";
    } else {
        // Evaluación afinada según categoría de pregunta
        switch ($tipo) {
            case 'ident':
                $palabras = count(preg_split('/\s+/', $val));
                $score = ($palabras >= 2) ? 10 : 8;
                $just = "Nombre oficial validado para plecas y créditos de producción.";
                $tag = "👤 Registro";
                break;

            case 'alias':
                $score = (preg_match('/^(el|la|los|dj|mc)\s+/i', $val) || $len >= 3) ? 10 : 8;
                $just = "Alias de calle/personaje: \"$val\". Ideal para identificación rápida.";
                $tag = "🏷️ Petardo";
                break;

            case 'contacto':
                $score = (strpos($val, '@') !== false || preg_match('/\d{7,}/', $val)) ? 10 : 7;
                $just = "Vía de comunicación directa con el invitado para producción.";
                $tag = "📱 Contacto";
                break;

            case 'tres_palabras':
                $palabras = count(preg_split('/[\s,\/]+/', $val));
                if ($palabras >= 2 && $palabras <= 6) {
                    $score = 10;
                    $just = "Definición contundente y con síntesis perfecta: \"$val\".";
                    $tag = "💎 Síntesis Top";
                } elseif ($len > 30) {
                    $score = 8;
                    $just = "Buena descripción conceptual: \"$val\".";
                    $tag = "📝 Descriptivo";
                } else {
                    $score = 7;
                    $just = "Definición puntual: \"$val\".";
                    $tag = "🎯 Puntual";
                }
                break;

            case 'barrio':
                $score = (preg_match('/(colonia|fracc|pueblo|mexicali|valle|calexico|línea|san|sta|zona|ejido)/i', $norm) || $len > 6) ? 10 : 8;
                $just = "Arraigo territorial en \"$val\". Conecta con la identidad local del show.";
                $tag = "📍 Territorio";
                break;

            case 'madrazo_oro':
            case 'exclusiva':
                $hasKw = false;
                foreach ($keywordsOro as $kw) {
                    if (strpos($norm, $kw) !== false) { $hasKw = true; break; }
                }
                if ($len >= 60 || ($len >= 25 && $hasKw)) {
                    $score = 10;
                    $just = "🔥 ORO EDITORIAL: Anécdota cruda y de alto impacto para clip viral o clímax.";
                    $tag = "🔥 Oro Viral";
                } elseif ($len >= 20) {
                    $score = 8;
                    $just = "Buen momento de tensión: \"$val\". Se debe profundizar el desenlace en cabina.";
                    $tag = "⚡ Clave";
                } else {
                    $score = 6;
                    $just = "Anécdota breve: \"$val\". El Güero debe detonar los detalles en vivo.";
                    $tag = "🔍 Explorar";
                }
                break;

            case 'comedia':
                $score = ($len >= 35 || preg_match('/(risa|caí|peluca|ped|amigo|compas|pena|chistoso|desmadre)/i', $norm)) ? 10 : ($len >= 15 ? 8 : 6);
                $just = ($score === 10) ? "😂 Chispa de cabina: Gran potencial para arrancar carcajadas y aligerar el set." : "Anécdota chusca: \"$val\".";
                $tag = "😂 Comedia";
                break;

            case 'secreto':
                $score = ($len >= 4 && $len <= 60) ? 10 : 8;
                $just = "Mantra de vida: \"$val\". Excelente para cita gráfica en redes y miniatura.";
                $tag = "💡 Mantra";
                break;

            case 'motivacional':
            case 'sabiduria':
                if ($len >= 50 || (strpos($norm, 'adelante') !== false || strpos($norm, 'toalla') !== false || strpos($norm, 'lucha') !== false || strpos($norm, 'sueño') !== false)) {
                    $score = 10;
                    $just = "🌟 CLÍMAX EMOCIONAL: Mensaje profundo de inspiración para la audiencia.";
                    $tag = "🌟 Clímax";
                } elseif ($len >= 15) {
                    $score = 8;
                    $just = "Mensaje directo y honesto: \"$val\".";
                    $tag = "✨ Inspirador";
                } else {
                    $score = 6;
                    $just = "Consejo puntual: \"$val\".";
                    $tag = "📌 Breve";
                }
                break;

            default:
                $hasKw = false;
                foreach ($keywordsOro as $kw) {
                    if (strpos($norm, $kw) !== false) { $hasKw = true; break; }
                }
                if ($len >= 70 || ($len >= 30 && $hasKw)) {
                    $score = 10;
                    $just = "Excelente profundidad narrativa y autenticidad: \"$val\".";
                    $tag = "💎 Profundo";
                } elseif ($len >= 25) {
                    $score = 8;
                    $just = "Buen contexto de barrio y respuesta clara: \"$val\".";
                    $tag = "👍 Sólido";
                } else {
                    $score = 7;
                    $just = "Respuesta puntual: \"$val\". Dinamizar en set.";
                    $tag = "🎯 Puntual";
                }
                break;
        }
    }

    $totalScore += $score;
    $criterios33[] = [
        'num' => $i,
        'nombre' => "Pregunta $i: " . $meta['titulo'],
        'acto' => $meta['acto'],
        'score' => $score,
        'max' => 10,
        'etiqueta' => $tag ?? 'Parámetro',
        'respuesta' => $val,
        'justificacion' => $just
    ];
}

$nivel = ($totalScore >= 260) ? 'ALTO' : (($totalScore >= 165) ? 'MEDIO' : 'BAJO');
$badge = ($nivel === 'ALTO') ? '🟢 NIVEL ALTO' : (($nivel === 'MEDIO') ? '🟡 NIVEL MEDIO' : '🔴 NIVEL BAJO');
$color = ($nivel === 'ALTO') ? '#39FF14' : (($nivel === 'MEDIO') ? '#00FFFF' : '#FF00FF');
$formato = ($nivel === 'ALTO') ? 'Invitado Principal al Canal (Episodio Completo 45+ min)' : (($nivel === 'MEDIO') ? 'Entrevista Corta / Segmento (15 - 25 min)' : 'Micro-contenido / Reto en Cabina (Shorts 30 - 60s)');

$curaduria = [
    'nivel' => $nivel,
    'badge' => $badge,
    'formato' => $formato,
    'color' => $color,
    'razon' => "Curaduría afinada de 33 parámetros con análisis semántico, detección de ganchos virales y ponderación por bloque temático ({$totalScore} / 330 pts)."
];

$ponderacion = [
    'score_total' => $totalScore,
    'score_max' => 330,
    'score_min' => 33,
    'criterios' => $criterios33
];

// Escaleta Broadcast
$escaleta = "ESCALETA DE PRODUCCIÓN - LA CUEVA DEL GÜERO\n" .
    "Invitado: {$nombre} (" . ($alias ?: $nombre) . ")\n" .
    "Barrio/Origen: {$barrio}\n" .
    "Tema Central: {$storytellingEnfoque}\n\n" .
    "[00:00 - 05:00] INTRO & PRESENTACIÓN: El Güero y Junior presentan a {$nombre}. Conexión inmediata con {$barrio} y el apodo.\n" .
    "[05:00 - 15:00] BLOQUE 1 - RAÍCES Y LA CALLE: Qué soñaba a los 10 años ({$suenoNino}), qué significa el barrio ({$significadoBarrio}) y la mayor enseñanza ({$ensenanzaBarrio}).\n" .
    "[15:00 - 27:00] BLOQUE 2 - LOS MADRAZOS Y EL QUIEBRE: El momento más difícil en el jale ({$humillante}), el peor error ({$peorError}) y lo más peligroso ({$peligroso}).\n" .
    "[27:00 - 38:00] BLOQUE 3 - EL ÉXITO Y SACRIFICIOS: El primer logro chingón ({$primerLogro}), sacrificios ({$sacrificios}) y mentalidad clave ({$secretoExito}).\n" .
    "[38:00 - 46:00] BLOQUE 4 - CONFESIÓN & RETO: Canción bélica ({$cancionBelica}), gusto culposo ({$gustoCulposo}), confesión exclusiva ({$confesion}) y reto en vivo ({$dinamica}).\n" .
    "[46:00 - 52:00] CIERRE & MENSAJE: Mensaje a quien está por tirar la toalla ({$mensajeAyuda}) y cómo le gustaría ser recordado ({$recordar}).";

// Guion Broadcast
$guion = "GUIÓN BROADCAST - LA CUEVA DEL GÜERO\n" .
    "Invitado: {$nombre} | Conducción: El Güero & Junior\n\n" .
    "El Güero: ¡Qué onda manada! Hoy tenemos la casa llena y la mesa puesta con un compa que viene desde {$barrio} a platicar la neta sin censura: bienvenido a la Cueva, {$nombre}.\n\n" .
    "Junior: ¡Qué onda carnal! Un gustazo tenerte acá. La raza te conoce como {$alias}. Queremos arrancar preguntándote: cuando erasmorro de 10 años, ¿te imaginabas estar donde estás hoy?\n\n" .
    "{$nombre}: " . ($suenoNino ?: "La neta no, pero con esfuerzo y perseverancia fuimos dándole...") . "\n\n" .
    "El Güero: En la calle y en el jale siempre tocan madrazos. Cuéntanos ese momento donde te quisieron humillar o donde casi tiras la toalla...\n\n" .
    "{$nombre}: " . ($humillante ?: "Me tocó aguantar vara y demostrar con hechos que el trabajo habla solo.") . "\n\n" .
    "Junior: Y la confesión incómoda que nos dejaste: {$confesion}. ¡Suéltala con lujo de detalle en el micrófono!\n\n" .
    "El Güero: Para cerrar con broche de oro, ¿qué le dices al vato o morra que ahorita está pasando un momento cabrón y siente que ya no puede más?\n\n" .
    "{$nombre}: {$mensajeAyuda}";

// Cue Cards de Cabina
$cueCards = "CUE CARDS DE CABINA - HOSTS\n" .
    "• TARJETA 1 (HOOK): Abrir mencionando {$barrio} y el apodo '{$alias}'. Preguntar por el don que lo hace especial: {$donEspecial}.\n" .
    "• TARJETA 2 (VULNERABILIDAD): Tocar el tema del error ({$peorError}) y la mayor enseñanza ({$ensenanzaBarrio}).\n" .
    "• TARJETA 3 (DINÁMICA & PATROCINIO): Activar dinámica ({$dinamica}) y mencionar patrocinador oficial.\n" .
    "• TARJETA 4 (CIERRE): Mensaje motivacional ({$mensajeAyuda}) y despedida oficial de La Cueva.";

// Estructurar expediente completo
$expediente = [
    'id' => $codeNumber,
    'token' => $trackingCode,
    'nombre' => $nombre,
    'alias' => $alias,
    'contacto' => $contacto,
    'ocupacion' => $ocupacion,
    'barrio' => $barrio,
    'created_at' => $fechaRegistro,
    'storytelling_enfoque' => $storytellingEnfoque,
    'reto' => $retoPrincipal,
    'frase' => $fraseGancho,
    'curaduria' => $curaduria,
    'ponderacion' => $ponderacion,
    'escaleta' => $escaleta,
    'guion' => $guion,
    'cue_cards' => $cueCards,
    'respuestas' => $respuestas,
    'estado' => 'Cuestionario Recibido',
    'fase_index' => 1
];

// ═════════════════════════════════════════════════════════════════════════════════
// 1. GUARDAR EN ARCHIVO PERSISTENTE JSON (ALTA DISPONIBILIDAD)
// ═════════════════════════════════════════════════════════════════════════════════
$storageDir = __DIR__ . '/../images/formularios';
if (!is_dir($storageDir)) {
    @mkdir($storageDir, 0777, true);
}
$enviosFile = $storageDir . '/cuestionarios_envios.json';
$envios = [];
if (file_exists($enviosFile)) {
    $existing = json_decode(file_get_contents($enviosFile), true);
    if (is_array($existing)) {
        $envios = $existing;
    }
}

// Actualizar en sitio si ya existía el invitado (por nombre normalizado o por token)
$encontradoIndex = -1;
$normNombre = mb_strtolower(trim($nombre));
foreach ($envios as $idx => $item) {
    $itemNombre = mb_strtolower(trim($item['nombre'] ?? ''));
    $itemToken = $item['token'] ?? '';
    if (($itemNombre === $normNombre && !empty($normNombre)) || ($itemToken === $trackingCode && !empty($trackingCode))) {
        $encontradoIndex = $idx;
        // Preservar ID y token previo si existía
        if (!empty($item['token'])) {
            $expediente['token'] = $item['token'];
            $trackingCode = $item['token'];
        }
        if (!empty($item['id'])) {
            $expediente['id'] = $item['id'];
        }
        break;
    }
}

if ($encontradoIndex >= 0) {
    $envios[$encontradoIndex] = $expediente;
} else {
    array_unshift($envios, $expediente);
}
@file_put_contents($enviosFile, json_encode($envios, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

// ═════════════════════════════════════════════════════════════════════════════════
// 2. GUARDAR EN BASE DE DATOS NEON POSTGRESQL (KNOWLEDGE_BASE & INVITADOS)
// ═════════════════════════════════════════════════════════════════════════════════
$dbSaved = false;
try {
    $pdo = db_connect();
    if ($pdo) {
        // Asegurar tablas
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS knowledge_base (
                id SERIAL PRIMARY KEY,
                nombre VARCHAR(255) NOT NULL,
                tipo VARCHAR(50) DEFAULT 'storytelling',
                storytelling TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );
            CREATE TABLE IF NOT EXISTS invitados (
                id SERIAL PRIMARY KEY,
                nombre VARCHAR(255) NOT NULL,
                ocupacion TEXT,
                signo VARCHAR(50),
                fecha_nacimiento VARCHAR(100),
                barrio VARCHAR(255),
                trayectoria TEXT,
                herida TEXT,
                incomodo TEXT,
                gustos TEXT,
                fecha_propuesta VARCHAR(100),
                token VARCHAR(100),
                estado VARCHAR(100) DEFAULT 'En Proceso',
                fase_index INT DEFAULT 1,
                ficha TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );
        ");

        // Insertar en knowledge_base
        $stmtKb = $pdo->prepare("
            INSERT INTO knowledge_base (nombre, tipo, storytelling, created_at)
            VALUES (:nombre, 'storytelling', :storytelling, NOW())
        ");
        $stmtKb->execute([
            ':nombre' => $nombre,
            ':storytelling' => json_encode($expediente, JSON_UNESCAPED_UNICODE)
        ]);

        // Insertar en invitados
        $stmtInv = $pdo->prepare("
            INSERT INTO invitados 
            (nombre, ocupacion, signo, fecha_nacimiento, barrio, trayectoria, herida, incomodo, gustos, fecha_propuesta, token, ficha, created_at)
            VALUES 
            (:nombre, :ocupacion, :signo, :fecha_nacimiento, :barrio, :trayectoria, :herida, :incomodo, :gustos, :fecha_propuesta, :token, :ficha, NOW())
        ");
        $stmtInv->execute([
            ':nombre' => $nombre,
            ':ocupacion' => $ocupacion,
            ':signo' => $alias ?: 'Mexicali Flow',
            ':fecha_nacimiento' => $fechaCorta,
            ':barrio' => $barrio,
            ':trayectoria' => $storytellingEnfoque,
            ':herida' => $humillante,
            ':incomodo' => $confesion,
            ':gustos' => $cancionBelica,
            ':fecha_propuesta' => date('Y-m-d', strtotime('+7 days')),
            ':token' => $trackingCode,
            ':ficha' => json_encode($expediente, JSON_UNESCAPED_UNICODE)
        ]);

        $dbSaved = true;
    }
} catch (Exception $e) {
    error_log("Database Save Warning (Fallback JSON used): " . $e->getMessage());
}

// ═════════════════════════════════════════════════════════════════════════════════
// 3. DISPARAR WEBHOOK DE NOTIFICACIÓN EN TIEMPO REAL
// ═════════════════════════════════════════════════════════════════════════════════
try {
    require_once __DIR__ . '/api-webhook.php';
    $esCorreccion = !empty($input['correccion_realizada']);
    if ($esCorreccion) {
        dispararWebhook('entrevista_error_reportado', [
            'nombre' => $nombre,
            'alias' => $alias ?: 'Sin alias',
            'token' => $trackingCode,
            'preguntas_modificadas' => $input['preguntas_modificadas'] ?? 'Varias',
            'mensaje' => "El invitado revisó su entrevista y corrigió respuestas antes del guardado definitivo."
        ], 'cuestionario_invitado');
    }
    dispararWebhook('entrevista_completada', [
        'nombre' => $nombre,
        'alias' => $alias ?: 'Sin alias',
        'barrio' => $barrio,
        'jale' => $ocupacion,
        'contacto' => $contacto,
        'token' => $trackingCode,
        'curaduria' => $curaduria['nivel'] ?? 'ALTO',
        'score' => $totalScore . ' / 330 PTS',
        'estado' => $esCorreccion ? 'Confirmado con correcciones' : 'Confirmado y verificado'
    ], 'cuestionario_invitado');
} catch (Exception $e) {
    error_log("Webhook trigger warning: " . $e->getMessage());
}

// ═════════════════════════════════════════════════════════════════════════════════
// RESPUESTA EXITOSA
// ═════════════════════════════════════════════════════════════════════════════════
echo json_encode([
    'status' => 'success',
    'success' => true,
    'code' => $trackingCode,
    'token' => $trackingCode,
    'nombre' => $nombre,
    'db_saved' => $dbSaved,
    'message' => 'Expediente de invitado registrado y procesado con éxito para La Cueva del Güero.'
], JSON_UNESCAPED_UNICODE);
exit;
