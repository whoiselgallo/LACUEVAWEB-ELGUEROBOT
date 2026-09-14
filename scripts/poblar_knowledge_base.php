<?php
/**
 * POBLADOR DE KNOWLEDGE_BASE - LA CUEVA DEL GÜERO
 * Genera y almacena en PostgreSQL (Neon) los 8 expedientes completos de producción:
 * Escaleta Técnica, Guion para Set (El Güero & El Junior), Cue Cards y Curaduría.
 */

header('Content-Type: text/plain; charset=utf-8');
require_once __DIR__ . '/../config/config.php';

try {
    $db = db_connect();
    echo "✓ Conexión exitosa a PostgreSQL (Google Cloud SQL)\n\n";

    // Crear tabla knowledge_base si no existe
    $db->exec("
        CREATE TABLE IF NOT EXISTS knowledge_base (
            id          SERIAL PRIMARY KEY,
            nombre      VARCHAR(255) NOT NULL,
            tipo        VARCHAR(100) NOT NULL DEFAULT 'storytelling',
            storytelling TEXT,
            created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
        CREATE INDEX IF NOT EXISTS idx_knowledge_base_tipo ON knowledge_base (tipo);
        CREATE INDEX IF NOT EXISTS idx_knowledge_base_nombre ON knowledge_base (nombre);
    ");
} catch (Exception $e) {
    die("❌ Error conectando a DB: " . $e->getMessage() . "\n");
}

$cuestionariosFile = __DIR__ . '/../images/formularios/cuestionarios.json';
if (!file_exists($cuestionariosFile)) {
    die("❌ Archivo de cuestionarios no encontrado: $cuestionariosFile\n");
}

$rawJson = json_decode(file_get_contents($cuestionariosFile), true);
if (!$rawJson) {
    die("❌ No se pudo decodificar el JSON de cuestionarios.\n");
}

echo "Procesando cuestionarios para siembra en knowledge_base...\n\n";

foreach ($rawJson as $index => $q) {
    $rawName = trim($q['No bre'] ?? $q['Nombre'] ?? '');
    if (empty($rawName) || $rawName === 'Nombre') continue;

    $nombre = ucwords(strtolower($rawName));
    $ocupacion = trim($q['¿A qué te dedicas actualmente?'] ?? 'Invitado Especial');
    $barrio = trim($q['¿De qué colonia eres?'] ?? 'Mexicali');
    $barrioDef = trim($q['Que significa para ti el Barrio?'] ?? 'Familia y lealtad');
    $ensenanza = trim($q['¿Cuál es la mayor enseñanza que te ha regalado el barrio?'] ?? 'Valores de la calle');
    $infancia = trim($q['Que quería ser de niño, el tu de 10 años?'] ?? 'Triunfar');
    $humillacion = trim($q['El Camino y los madrazos: ¿Qué es lo más humillante que te han pedido hacer en un jale (trabajo)? ¿cuál fue tu reacción?'] ?? 'No permitir humillaciones');
    $error = trim($q['En el camino a veces perdemos el rumbo y nos desviamos del objetivo, ¿Cual ha sido el peor error que cometiste en tu carrera, ese que casi hace que renuncies a tus sueños?'] ?? 'Desvíos del camino');
    $chusco = trim($q['Algo chusco o chistoso que te haya pasado, que cada que lo recuerdas te hace reir.'] ?? 'Anécdotas del barrio');
    $belico = trim($q['¿Esa canción que escuchas y te pones "bélico"?'] ?? 'Música urbana');
    $confesion = trim($q['Confesión incomoda: Cuentanos eso que siempre has querido decir, pero no te atreves a sacarlo de tu ronco pecho. Danos la exclusiva.'] ?? 'Sin confesión');
    $molestia = trim($q['Lo que más te molesta:'] ?? 'Las mentiras y la deslealtad');
    $defecto = trim($q['¿Cuál consideras que es tu mayor defecto?'] ?? 'Desesperado');
    $recuerdo = trim($q['¿Como te gustaría que la gente te recuerde cuando ya no estes en este mundo?'] ?? 'Por la persona que fui con ellos');
    $ayuda = trim($q['Es momento de la ayuda, que le dirias a esa persona que esta pasando por un mal momento, para que no tire la toalla y se motive a seguir adelante.'] ?? 'Que el sol siempre va a buscar brillar.');
    $tresPalabras = trim($q['¿Como te defines en 3 palabras?'] ?? 'Leal, auténtico, firme');

    // 1. ESCALETA TÉCNICA
    $escaleta = "==============================================================\n" .
                "ESCALETA DE PRODUCCIÓN - LA CUEVA DEL GÜERO\n" .
                "INVITADO: " . mb_strtoupper($nombre) . " | OFICIO: $ocupacion\n" .
                "BARRIO: $barrio | LEMA: \"$tresPalabras\"\n" .
                "==============================================================\n\n" .
                "BLOQUE 1: APERTURA Y BIENVENIDA CALLEJERA (00:00 - 08:30)\n" .
                "- Entrada con música estilo $belico y saludo de El Güero y El Junior.\n" .
                "- Presentación de $nombre y cómo se define en 3 palabras: \"$tresPalabras\".\n" .
                "- Primer brindis y rompimiento de hielo sobre el barrio $barrio.\n\n" .
                "BLOQUE 2: RAÍCES DEL BARRIO Y EL SUEÑO DE NIÑO (08:30 - 20:00)\n" .
                "- ¿Qué significa el barrio para ti? (\"$barrioDef\").\n" .
                "- Lo que enseñan las esquinas de $barrio: \"$ensenanza\".\n" .
                "- El morro de 10 años: ¿Qué soñaba ser? (\"$infancia\").\n\n" .
                "BLOQUE 3: LOS MADRAZOS DEL CAMINO Y EL PUNTO DE QUIEBRE (20:00 - 35:00)\n" .
                "- Los momentos duros: \"$humillacion\".\n" .
                "- El error que casi lo cambia todo: \"$error\".\n" .
                "- Mayor molestia en la vida: \"$molestia\" y lidiar con los defectos (\"$defecto\").\n\n" .
                "BLOQUE 4: ANÉCDOTA INÉDITA Y DESMADRE EN LA CUEVA (35:00 - 48:00)\n" .
                "- Momento chusco que no se olvida: \"$chusco\".\n" .
                "- La canción que lo prende en corto: \"$belico\".\n" .
                "- Confesión sin censura: \"$confesion\".\n\n" .
                "BLOQUE 5: EL LEGADO Y MENSAJE PARA EL BARRIO (48:00 - 60:00)\n" .
                "- ¿Cómo quieres que te recuerden?: \"$recuerdo\".\n" .
                "- Mensaje directo a la cámara para la banda que no se debe rendir: \"$ayuda\".\n" .
                "- Despedida oficial, firma del muro de La Cueva y cierre.";

    // 2. GUIÓN DE SET PARA EL GÜERO Y EL JUNIOR
    $guion = "==============================================================\n" .
             "GUIÓN DE CONVERSACIÓN - LA CUEVA DEL GÜERO PODCAST\n" .
             "HOSTS: EL GÜERO (Perro Callejero Sabio) & EL JUNIOR (Curioso Leal)\n" .
             "INVITADO: $nombre\n" .
             "==============================================================\n\n" .
             "[INTRODUCCIÓN - SET ILUMINADO EN NEÓN CIAN Y MORADO]\n\n" .
             "EL GÜERO:\n" .
             "¡Qué onda mi gente de La Cueva! Ya estamos transmitiendo en vivo desde el mero corazón del underground. " .
             "Hoy no tenemos a cualquier personaje... hoy nos acompaña un carnal que conoce el pavimento, que viene desde $barrio " .
             "y que se define nada más y nada menos que como: \"$tresPalabras\". ¡Démosle un aplauso machín a $nombre!\n\n" .
             "EL JUNIOR:\n" .
             "¡Bienvenido a La Cueva, $nombre! Oye carnal, yo quiero arrancar preguntándote directo: " .
             "para ti el barrio significa \"$barrioDef\"... ¿en qué momento te diste cuenta que la calle era tu verdadera escuela?\n\n" .
             "$nombre:\n" .
             "[Responde compartiendo la anécdota de cómo las vivencias en $barrio forjaron su carácter: \"$ensenanza\"]\n\n" .
             "EL GÜERO:\n" .
             "¡A huevo! Es que en la calle nadie te regala nada carnal. Pero platícanos de morro: a los 10 años me decías que querías ser " .
             "\"$infancia\". ¿Qué te decían en tu cantón cuando les contabas ese sueño?\n\n" .
             "$nombre:\n" .
             "[Relata sus inicios, los que dudaron y los que apoyaron su camino hacia $ocupacion]\n\n" .
             "EL JUNIOR:\n" .
             "Oye Güero, pero en este camino también hay madrazos duros. $nombre, tú pusiste en tu ficha que lo más humillante o difícil que pasaste fue: " .
             "\"$humillacion\"... ¿cómo le hiciste para no rajarte y mantener la frente en alto?\n\n" .
             "$nombre:\n" .
             "[Comparte el momento de quiebre y cómo la lealtad a sus principios lo sacó adelante]\n\n" .
             "EL GÜERO:\n" .
             "Eso es tener huevos carnal, con perdón de la palabra pero las cosas al chile. Ahora, relajando la rajada... ¡cuéntate esa anécdota " .
             "que cada que te acuerdas te da un ataque de risa! La de: \"$chusco\". ¡Suéltala aquí en La Cueva!\n\n" .
             "$nombre:\n" .
             "[Cuenta la historia chusca mientras El Güero y El Junior botan la risa en el set]\n\n" .
             "EL JUNIOR:\n" .
             "¡Jajajaja no manches! ¡Esa está de antología! Oye carnal, y para cerrar con broche de oro... esa gente que hoy la está pasando gacha, " .
             "¿qué mensaje les dejas directo a los ojos?\n\n" .
             "$nombre:\n" .
             "\"$ayuda\"\n\n" .
             "EL GÜERO:\n" .
             "¡Puro fuego mi gente! El sol siempre va a buscar brillar. ¡Un aplauso para $nombre! Esto es La Cueva del Güero, ¡nos vemos en la próxima!";

    // 3. CUE CARDS DE MANO
    $cue_cards = "[CUE CARD 1: APERTURA]\n" .
                 "• Invitado: $nombre | Ocupación: $ocupacion\n" .
                 "• Barrio de origen: $barrio\n" .
                 "• 3 palabras clave: $tresPalabras\n" .
                 "• Canción favorita: $belico\n\n" .
                 "[CUE CARD 2: CONFLICTO Y MADRAZOS]\n" .
                 "• Sueño de niño: $infancia\n" .
                 "• Reto / Madrazo: $humillacion\n" .
                 "• Lo que más le molesta: $molestia\n\n" .
                 "[CUE CARD 3: ANÉCDOTA Y REMATE]\n" .
                 "• Historia chusca: $chusco\n" .
                 "• Confesión: $confesion\n\n" .
                 "[CUE CARD 4: CIERRE Y MENSAJE]\n" .
                 "• Legado: $recuerdo\n" .
                 "• Frase motivacional: \"$ayuda\"";

    // 4. EVALUACIÓN DE CURADURÍA
    $longitudTotal = mb_strlen($escaleta . $guion);
    if ($longitudTotal > 2000 || mb_stripos($escaleta, 'traición') !== false || mb_stripos($escaleta, 'enseñanza') !== false) {
        $curaduria = [
            'nivel' => 'ALTO',
            'badge' => '🟢 NIVEL ALTO',
            'formato' => 'Invitado Principal al Canal',
            'color' => '#39FF14',
            'razon' => 'Expediente completo de alta potencia narrativa y lealtad de barrio. Aprobado para emisión completa de 45+ minutos.'
        ];
    } else {
        $curaduria = [
            'nivel' => 'MEDIO',
            'badge' => '🟡 NIVEL MEDIO',
            'formato' => 'Entrevista Corta / Segmento (10 min)',
            'color' => '#00FFFF',
            'razon' => 'Historia atractiva con anécdotas puntuales. Ideal para cápsula semanal o segmento temático de 10 min.'
        ];
    }

    $story_payload = [
        'escaleta' => $escaleta,
        'guion' => $guion,
        'cue_cards' => $cue_cards,
        'curaduria' => $curaduria,
        'ocupacion' => $ocupacion,
        'barrio' => $barrio
    ];

    $storyJson = json_encode($story_payload, JSON_UNESCAPED_UNICODE);

    // 5. INSERTAR O ACTUALIZAR EN KNOWLEDGE_BASE
    $check = $db->prepare("SELECT id FROM knowledge_base WHERE nombre = ? AND tipo = 'storytelling'");
    $check->execute([$nombre]);
    $exist = $check->fetch(PDO::FETCH_ASSOC);

    if ($exist) {
        $upd = $db->prepare("UPDATE knowledge_base SET storytelling = ?, updated_at = NOW() WHERE id = ?");
        $upd->execute([$storyJson, $exist['id']]);
        echo "✓ Actualizado expediente #{$exist['id']}: $nombre\n";
    } else {
        $ins = $db->prepare("INSERT INTO knowledge_base (nombre, tipo, storytelling, created_at, updated_at) VALUES (?, 'storytelling', ?, NOW(), NOW())");
        $ins->execute([$nombre, $storyJson]);
        $newId = $db->lastInsertId();
        echo "✓ Creado nuevo expediente #{$newId}: $nombre\n";
    }
}

$count = $db->query("SELECT count(*) FROM knowledge_base WHERE tipo = 'storytelling'")->fetchColumn();
echo "\n======================================================\n";
echo "✓ Proceso terminado con éxito. Total registros en knowledge_base: $count\n";
echo "======================================================\n";
?>
