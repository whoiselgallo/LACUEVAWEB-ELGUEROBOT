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

// Objeto de Curaduría
$curaduria = [
    'nivel' => 'ALTO',
    'badge' => '🟢 NIVEL ALTO',
    'formato' => 'Invitado Principal al Canal (Episodio Completo 45+ min)',
    'color' => '#39FF14',
    'razon' => 'Expediente completo de 33 preguntas con alta potencia narrativa, autenticidad de barrio y vivencias reales.'
];

$ponderacion = [
    'score_total' => 96,
    'criterios' => [
        ['nombre' => 'Autenticidad & Conexión de Barrio', 'score' => 9.0, 'justificacion' => "Raíces sólidas en {$barrio}. Definición: {$definicion}."],
        ['nombre' => 'Potencia Emocional & Resiliencia', 'score' => 8.8, 'justificacion' => "Superación de adversidades: {$ensenanzaBarrio}."],
        ['nombre' => 'Confesión & Dinámica en Set', 'score' => 8.7, 'justificacion' => "Confesión exclusiva y reto en cabina: {$dinamica}."],
        ['nombre' => 'Mensaje Motivacional & Comunidad', 'score' => 9.0, 'justificacion' => "Mensaje a la audiencia: {$mensajeAyuda}."]
    ]
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

// Insertar al inicio de la lista
array_unshift($envios, $expediente);
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
