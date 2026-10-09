<?php
/**
 * ═════════════════════════════════════════════════════════════════════════════════
 * API - Flujo de Correcciones de Cuestionario, Solicitud y Aprobación de Producción
 * Endpoint: /api/api-guest-corrections.php
 * Conecta: Dashboard <-> Webhooks <-> Tracking de Invitado <-> Guión/Storytelling
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
require_once __DIR__ . '/api-webhook.php';

$rawInput = file_get_contents("php://input");
$input = json_decode($rawInput, true) ?: [];
if (empty($input) && !empty($_POST)) {
    $input = $_POST;
}

$action = $_GET['action'] ?? ($input['action'] ?? '');

$pdo = null;
try {
    $pdo = db_connect();
} catch (Exception $e) {
    $pdo = null;
}

function getEnvios() {
    $file = __DIR__ . '/../images/formularios/cuestionarios_envios.json';
    if (file_exists($file)) {
        $data = json_decode(file_get_contents($file), true);
        if (is_array($data)) return $data;
    }
    return [];
}

function guardarEnvios($envios) {
    $file = __DIR__ . '/../images/formularios/cuestionarios_envios.json';
    $dir = dirname($file);
    if (!is_dir($dir)) @mkdir($dir, 0777, true);
    return @file_put_contents($file, json_encode($envios, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function buscarInvitado(&$envios, $identificador) {
    $norm = mb_strtolower(trim($identificador));
    foreach ($envios as $idx => &$item) {
        $token = mb_strtolower(trim($item['token'] ?? ''));
        $id = strval($item['id'] ?? '');
        $nombre = mb_strtolower(trim($item['nombre'] ?? ''));
        if ($token === $norm || $id === strval($identificador) || $nombre === $norm || (!empty($norm) && stripos($nombre, $norm) !== false)) {
            return $idx;
        }
    }
    return -1;
}

// ═════════════════════════════════════════════════════════════════════════════════
// 1. SOLICITAR CORRECCIÓN (Llamado desde el Dashboard por Producción)
// ═════════════════════════════════════════════════════════════════════════════════
if ($action === 'solicitar_correccion') {
    $identificador = trim($input['token'] ?? ($input['id'] ?? ($input['nombre'] ?? '')));
    $observaciones = $input['observaciones'] ?? []; // Array de { id_pregunta, pregunta, respuesta_original, causa, nota }
    $productor = trim($input['productor'] ?? 'Equipo de Producción');

    if (empty($identificador) || empty($observaciones)) {
        echo json_encode(['status' => 'error', 'message' => 'Faltan parámetros: token/id u observaciones de preguntas.']);
        exit;
    }

    $envios = getEnvios();
    $idx = buscarInvitado($envios, $identificador);

    if ($idx === -1) {
        echo json_encode(['status' => 'error', 'message' => 'No se encontró el invitado especificado.']);
        exit;
    }

    $invitado = &$envios[$idx];
    $token = $invitado['token'] ?? ('GUEST-' . ($invitado['id'] ?? '2026'));
    $nombre = $invitado['nombre'] ?? 'Invitado';

    $solicitudData = [
        'activa' => true,
        'fecha_solicitud' => date('Y-m-d H:i:s'),
        'productor' => $productor,
        'estado' => 'Corrección Solicitada',
        'cantidad_preguntas' => count($observaciones),
        'observaciones' => $observaciones
    ];

    $invitado['solicitud_correccion'] = $solicitudData;
    $invitado['estado'] = 'Corrección Solicitada';
    $invitado['fase_index'] = 1;

    guardarEnvios($envios);

    // Actualizar en Neon PostgreSQL
    if ($pdo) {
        try {
            $stmt = $pdo->prepare("UPDATE invitados SET estado = :estado, ficha = :ficha WHERE token = :token OR id::text = :token");
            $stmt->execute([
                ':estado' => 'Corrección Solicitada',
                ':ficha' => json_encode($invitado, JSON_UNESCAPED_UNICODE),
                ':token' => $token
            ]);
        } catch (Exception $e) {}
    }

    // Generar enlaces para compartir
    $host = $_SERVER['HTTP_HOST'] ?? 's.lacuevadelguero.com';
    $proto = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $trackingLink = "{$proto}://{$host}/tracking/?code=" . urlencode($token);

    $listaPreguntasTexto = "";
    foreach ($observaciones as $obs) {
        $pNum = $obs['id_pregunta'] ?? '?';
        $causa = ucfirst($obs['causa'] ?? 'Observación');
        $nota = !empty($obs['nota']) ? " - \"{$obs['nota']}\"" : "";
        $listaPreguntasTexto .= "\n• Pregunta {$pNum} [{$causa}]{$nota}";
    }

    $mensajeWhatsApp = "¡Qué onda {$nombre}! 🎙️ En cabina de La Cueva del Güero revisamos tu cuestionario y tenemos " . count($observaciones) . " detalle(s) para afilar tu escaleta antes de grabar:\n{$listaPreguntasTexto}\n\nEntra con tu código directo aquí para corregirlas en 1 minuto:\n{$trackingLink}";
    $whatsappUrl = "https://wa.me/?text=" . urlencode($mensajeWhatsApp);

    // Disparar Webhook
    try {
        dispararWebhook('solicitud_correccion_enviada', [
            'invitado' => $nombre,
            'token' => $token,
            'preguntas_observadas' => count($observaciones),
            'productor' => $productor,
            'detalles' => mb_substr($listaPreguntasTexto, 0, 500),
            'enlace_tracking' => $trackingLink
        ], 'dashboard_produccion');
    } catch (Exception $e) {}

    echo json_encode([
        'status' => 'success',
        'success' => true,
        'message' => 'Solicitud de corrección enviada con éxito.',
        'token' => $token,
        'invitado' => $nombre,
        'tracking_link' => $trackingLink,
        'whatsapp_url' => $whatsappUrl,
        'whatsapp_message' => $mensajeWhatsApp,
        'preguntas_observadas' => count($observaciones)
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// ═════════════════════════════════════════════════════════════════════════════════
// 2. OBTENER SOLICITUD DE CORRECCIÓN (Llamado desde el Portal de Tracking)
// ═════════════════════════════════════════════════════════════════════════════════
if ($action === 'obtener_solicitud') {
    $code = trim($_GET['code'] ?? ($input['code'] ?? ($_GET['token'] ?? ($input['token'] ?? ''))));

    if (empty($code)) {
        echo json_encode(['status' => 'error', 'message' => 'Código no proporcionado']);
        exit;
    }

    $envios = getEnvios();
    $idx = buscarInvitado($envios, $code);

    if ($idx === -1) {
        echo json_encode(['status' => 'error', 'message' => 'Invitado no encontrado']);
        exit;
    }

    $invitado = $envios[$idx];
    $solicitud = $invitado['solicitud_correccion'] ?? null;
    $activa = !empty($solicitud['activa']);

    echo json_encode([
        'status' => 'success',
        'activa' => $activa,
        'invitado' => [
            'nombre' => $invitado['nombre'] ?? 'Invitado',
            'alias' => $invitado['alias'] ?? '',
            'token' => $invitado['token'] ?? $code,
            'estado' => $invitado['estado'] ?? 'En Proceso'
        ],
        'solicitud' => $solicitud,
        'preguntas_a_corregir' => $activa ? ($solicitud['observaciones'] ?? []) : []
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// ═════════════════════════════════════════════════════════════════════════════════
// 3. GUARDAR CORRECCIÓN DEL INVITADO (Llamado cuando el invitado envía cambios)
// ═════════════════════════════════════════════════════════════════════════════════
if ($action === 'guardar_correccion_invitado') {
    $code = trim($input['code'] ?? ($input['token'] ?? ''));
    $respuestasCorregidas = $input['respuestas_corregidas'] ?? []; // Map de { id_pregunta => nueva_respuesta }

    if (empty($code) || empty($respuestasCorregidas)) {
        echo json_encode(['status' => 'error', 'message' => 'Faltan parámetros: código o respuestas corregidas.']);
        exit;
    }

    $envios = getEnvios();
    $idx = buscarInvitado($envios, $code);

    if ($idx === -1) {
        echo json_encode(['status' => 'error', 'message' => 'Invitado no encontrado.']);
        exit;
    }

    $invitado = &$envios[$idx];
    $nombre = $invitado['nombre'] ?? 'Invitado';
    $token = $invitado['token'] ?? $code;

    // 3.1 Actualizar respuestas in-place en el array de respuestas
    if (!isset($invitado['respuestas']) || !is_array($invitado['respuestas'])) {
        $invitado['respuestas'] = [];
    }

    $preguntasNombres = [];
    foreach ($respuestasCorregidas as $pId => $nuevaResp) {
        $pIdInt = intval($pId);
        $nuevaRespTrim = trim(strval($nuevaResp));
        $invitado['respuestas'][$pIdInt] = $nuevaRespTrim;
        $preguntasNombres[] = "P{$pIdInt}";

        // Mapear campos clave si aplican
        switch ($pIdInt) {
            case 1: $invitado['nombre'] = $nuevaRespTrim; $nombre = $nuevaRespTrim; break;
            case 2: $invitado['alias'] = $nuevaRespTrim; break;
            case 3: $invitado['contacto'] = $nuevaRespTrim; break;
            case 4: $invitado['ocupacion'] = $nuevaRespTrim; break;
            case 6: $invitado['barrio'] = $nuevaRespTrim; break;
            case 8: $invitado['ensenanza'] = $nuevaRespTrim; break;
            case 12: $invitado['herida'] = $nuevaRespTrim; break;
            case 13: $invitado['peorError'] = $nuevaRespTrim; break;
            case 20: $invitado['frase'] = "\"{$nuevaRespTrim}\""; break;
            case 24: $invitado['cancionBelica'] = $nuevaRespTrim; break;
            case 25: $invitado['incomodo'] = $nuevaRespTrim; break;
            case 26: $invitado['molestia'] = $nuevaRespTrim; break;
            case 33: $invitado['mensajeAyuda'] = $nuevaRespTrim; break;
        }
    }

    // 3.2 Actualizar estado de la solicitud
    if (isset($invitado['solicitud_correccion'])) {
        $invitado['solicitud_correccion']['activa'] = false;
        $invitado['solicitud_correccion']['estado'] = 'Corregido por Invitado (Pendiente de Aceptación)';
        $invitado['solicitud_correccion']['fecha_respuesta'] = date('Y-m-d H:i:s');
        $invitado['solicitud_correccion']['respuestas_enviadas'] = $respuestasCorregidas;
    }
    $invitado['estado'] = 'Correcciones Enviadas (En Revisión Final)';

    // 3.3 Regenerar / Adaptar Storytelling, Escaleta, Guión y Cue Cards con las respuestas actualizadas
    $alias = $invitado['alias'] ?? '';
    $barrio = $invitado['barrio'] ?? 'Mexicali, B.C.';
    $jale = $invitado['ocupacion'] ?? 'Invitado Especial';
    $herida = $invitado['herida'] ?? 'Los madrazos del camino';
    $confesion = $invitado['incomodo'] ?? 'Confesión en exclusiva';
    $musica = $invitado['cancionBelica'] ?? 'Música norteña y bélica';
    $frase = $invitado['frase'] ?? '"El barrio no se platica, se demuestra."';

    $invitado['storytelling_enfoque'] = "De {$barrio} para el mundo: Cómo {$nombre} " . ($alias ? "('{$alias}')" : "") . " forjó su visión en {$jale}, superando: {$herida}.";
    $invitado['reto'] = "Superar la adversidad: {$herida}.";

    $invitado['escaleta'] = "ESCALETA DE PRODUCCIÓN (ACTUALIZADA) - LA CUEVA DEL GÜERO\n"
        . "Invitado: {$nombre} " . ($alias ? "({$alias})" : "") . "\n"
        . "Tema: Historias de {$barrio}, jale diario en {$jale} y códigos de lealtad\n\n"
        . "[00:00 - 05:00] Hook & Intro: Quién es {$nombre} y por qué Mexicali lo ubica en {$barrio}\n"
        . "[05:00 - 18:00] Bloque 1: Primeros jales, la infancia en {$barrio} y el aprendizaje de calle\n"
        . "[18:00 - 32:00] Bloque 2: Los madrazos de la vida: {$herida}\n"
        . "[32:00 - 44:00] Bloque 3: Códigos de respeto, el secreto del éxito y tema musical favorito ({$musica})\n"
        . "[44:00 - 49:00] Bloque 4: Confesión incómoda sin filtro: {$confesion}\n"
        . "[49:00 - 52:00] Cierre & Legado: Reflexión para la manada: {$frase}";

    $invitado['guion'] = "GUIÓN BROADCAST (ACTUALIZADO) - LA CUEVA DEL GÜERO\n"
        . "Invitado: {$nombre} | Conducción: El Güero & Junior\n\n"
        . "El Güero: ¡Qué onda manada! Hoy tenemos sentado en la mesa a un compa derecho y de palabra: {$nombre}" . ($alias ? " ('{$alias}')" : "") . " desde {$barrio}. ¡Bienvenido a La Cueva!\n\n"
        . "Junior: ¡Qué onda carnal! Mexicali entero sabe de tu jale en {$jale}. Pero cuéntanos la neta de cómo empezó todo cuando nadie creía...\n\n"
        . "{$nombre}: ¡Qué onda Güero, qué onda Junior! Pues la neta nos tocó picar piedra desde abajo. Como siempre digo: " . trim($frase, '"') . ".\n\n"
        . "El Güero: En tus respuestas nos platicaste del madrazo más duro: '{$herida}'. Cuéntale a la raza qué se siente estar en esa lumbre y cómo saliste adelante...\n\n"
        . "Junior: Y para cerrar el bloque, nos dejaste una confesión que no cualquiera se atreve a soltar: '{$confesion}'...";

    $invitado['cue_cards'] = "CUE CARDS DE CABINA (ACTUALIZADAS)\n"
        . "• TARJETA 1 (HOOK): Origen en {$barrio} y primeros pasos en {$jale}.\n"
        . "• TARJETA 2 (VULNERABILIDAD): Profundizar en el momento clave: {$herida}.\n"
        . "• TARJETA 3 (DINÁMICA & EXCLUSIVA): Detonar confesión sin censura: {$confesion}.\n"
        . "• TARJETA 4 (CIERRE): Mensaje de fuerza: {$frase}.";

    guardarEnvios($envios);

    // Actualizar en Neon PostgreSQL
    if ($pdo) {
        try {
            $stmt = $pdo->prepare("UPDATE invitados SET estado = :estado, trayectoria = :trayectoria, herida = :herida, ficha = :ficha WHERE token = :token OR id::text = :token");
            $stmt->execute([
                ':estado' => 'Correcciones Enviadas (En Revisión Final)',
                ':trayectoria' => $invitado['storytelling_enfoque'],
                ':herida' => $herida,
                ':ficha' => json_encode($invitado, JSON_UNESCAPED_UNICODE),
                ':token' => $token
            ]);
        } catch (Exception $e) {}
    }

    // 3.4 Disparar Webhook 1: Correcciones enviadas por el invitado
    try {
        dispararWebhook('correccion_invitado_enviada', [
            'invitado' => $nombre,
            'token' => $token,
            'preguntas_modificadas' => implode(', ', $preguntasNombres),
            'total_modificadas' => count($preguntasNombres),
            'estado' => 'Respuestas recibidas con éxito. Listo para revisión de producción.'
        ], 'tracking_invitado');
    } catch (Exception $e) {}

    // 3.5 Disparar Webhook 2: Guión y Narrativa de Episodio Actualizados
    try {
        dispararWebhook('guion_narrativa_actualizada', [
            'invitado' => $nombre,
            'token' => $token,
            'elementos_actualizados' => 'Escaleta de 5 bloques, Guión broadcast, Cue Cards de cabina y Storytelling sintetizado',
            'estado' => 'Sincronizado automáticamente en base de datos y dashboard.'
        ], 'motor_storytelling_ia');
    } catch (Exception $e) {}

    echo json_encode([
        'status' => 'success',
        'success' => true,
        'message' => 'Tus respuestas han sido corregidas y el guión del episodio se actualizó en tiempo real.',
        'invitado' => $nombre,
        'token' => $token,
        'preguntas_modificadas' => count($preguntasNombres)
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// ═════════════════════════════════════════════════════════════════════════════════
// 4. APROBAR CUESTIONARIO (Llamado desde Producción cuando todo está en orden)
// ═════════════════════════════════════════════════════════════════════════════════
if ($action === 'aprobar_cuestionario') {
    $identificador = trim($input['token'] ?? ($input['id'] ?? ($input['nombre'] ?? '')));

    if (empty($identificador)) {
        echo json_encode(['status' => 'error', 'message' => 'Token o ID no especificado.']);
        exit;
    }

    $envios = getEnvios();
    $idx = buscarInvitado($envios, $identificador);

    if ($idx === -1) {
        echo json_encode(['status' => 'error', 'message' => 'Invitado no encontrado.']);
        exit;
    }

    $invitado = &$envios[$idx];
    $nombre = $invitado['nombre'] ?? 'Invitado';
    $token = $invitado['token'] ?? ('GUEST-' . ($invitado['id'] ?? '2026'));

    if (isset($invitado['solicitud_correccion'])) {
        $invitado['solicitud_correccion']['activa'] = false;
        $invitado['solicitud_correccion']['estado'] = 'Aprobada Definitiva';
        $invitado['solicitud_correccion']['fecha_aprobacion'] = date('Y-m-d H:i:s');
    }

    $invitado['estado'] = 'Cuestionario Aprobado (Listo para Escaleta)';
    $invitado['fase_index'] = 2; // Avanza a Escaleta & Curaduría

    guardarEnvios($envios);

    // Actualizar en PostgreSQL
    if ($pdo) {
        try {
            $stmt = $pdo->prepare("UPDATE invitados SET estado = :estado, fase_index = 2, ficha = :ficha WHERE token = :token OR id::text = :token");
            $stmt->execute([
                ':estado' => 'Cuestionario Aprobado (Listo para Escaleta)',
                ':ficha' => json_encode($invitado, JSON_UNESCAPED_UNICODE),
                ':token' => $token
            ]);
        } catch (Exception $e) {}
    }

    // Disparar Webhook
    try {
        $curaduria = $invitado['curaduria'] ?? [];
        dispararWebhook('cuestionario_aprobado_produccion', [
            'invitado' => $nombre,
            'token' => $token,
            'estado' => 'Aprobado 100% por Producción',
            'fase' => 'Fase 2: Escaleta & Curaduría en marcha',
            'curaduria_nivel' => $curaduria['nivel'] ?? 'ALTO',
            'fecha_aprobacion' => date('Y-m-d H:i:s')
        ], 'dashboard_produccion');
    } catch (Exception $e) {}

    echo json_encode([
        'status' => 'success',
        'success' => true,
        'message' => 'Cuestionario aprobado con éxito. El episodio avanzó a Fase 2 (Escaleta & Curaduría).',
        'invitado' => $nombre,
        'token' => $token,
        'fase_index' => 2
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Acción no válida']);
