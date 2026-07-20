<?php

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
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'No se recibió información del invitado']);
    exit();
}

// Normalizar estructura (por si viene como array)
$inv = isset($input["nombre"]) ? $input : (isset($input[0]) ? $input[0] : null);

if (!$inv) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Formato inválido de invitado']);
    exit();
}

// Extraer ficha
$ficha = isset($inv["ficha"]) ? $inv["ficha"] : $inv;

// Variables seguras
$nombre        = $inv["nombre"]        ?? "Invitado";
$ocupacion     = $ficha["ocupacion"]   ?? "Ocupación no registrada";
$barrio        = $ficha["barrio"]      ?? "Barrio no registrado";
$herida        = $ficha["herida"]      ?? "Herida no registrada";
$momento       = $ficha["momento"]     ?? "Momento decisivo no registrado";
$trayectoria   = $ficha["trayectoria"] ?? "Trayectoria no registrada";
$incomodo      = $ficha["incomodo"]    ?? "Temas incómodos no registrados";
$gustos        = $ficha["gustos"]      ?? "Gustos no registrados";
$logros        = $ficha["logros"]      ?? "Logros no registrados";

// ===============================
// GENERADOR DEL GUION
// ===============================

$guion = "
GUION DEL EPISODIO – $nombre

🎬 INTRO (0–5 min)
- Presentación del invitado: $nombre, $ocupacion.
- Contexto del barrio: $barrio.
- Frase o vibra inicial.

🔥 BLOQUE 1 – INFANCIA Y ORIGEN (5–15 min)
- ¿Cómo era crecer en $barrio?
- Primeras influencias.
- Momentos clave de su niñez.

💥 BLOQUE 2 – HERIDA / MOMENTO DURO (15–30 min)
- La herida: $herida.
- ¿Cómo lo marcó?
- ¿Qué cambió en su vida?

⚡ BLOQUE 3 – MOMENTO DECISIVO (30–45 min)
- Punto de quiebre: $momento.
- Decisiones que lo llevaron a donde está.

🎯 BLOQUE 4 – TRAYECTORIA (45–60 min)
- Historia: $trayectoria.
- Logros: $logros.
- Obstáculos superados.

🔥 BLOQUE 5 – TEMAS INCÓMODOS (60–70 min)
- $incomodo

❤️ BLOQUE 6 – PASIONES Y FUTURO (70–80 min)
- Gustos e intereses: $gustos
- Qué sigue para él.

🎤 CIERRE (80–90 min)
- Mensaje final del invitado.
- Reflexión.
";

// Respuesta final
echo json_encode([
    'status' => 'success',
    'guion' => trim($guion)
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

exit();
