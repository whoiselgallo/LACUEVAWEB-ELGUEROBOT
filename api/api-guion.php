<?php
/**
 * API - Generador de Guion (El Güero Bot)
 * Endpoint: /api/api-guion.php
 * Método: POST
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
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido. Usa POST.']);
    exit();
}

// Cargar configuración central
require_once __DIR__ . '/../config/config.php';

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

// Mapear campos a las variables del nodo INICIO de Dify
$workflowInputs = [
    'NOMBRE'              => $nombre,
    'Ocupacion'           => $ocupacion,
    'Barrio'              => $barrio,
    'herida'              => $herida,
    'momento'             => $momento,
    'trayectoria'         => $trayectoria,
    'incomodo'            => $incomodo,
    'gustos'              => $gustos, // Mapeado a 'gustos'
    'logros'              => $logros,
    'fecha_de_nacimiento' => $inv["fecha"] ?? ""
];

// Ejecutar workflow de Dify
$workflowResult = call_dify_workflow($workflowInputs, 'la-cueva-web');

if (!$workflowResult['success']) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Error al llamar a Dify Workflow: ' . $workflowResult['error']
    ], JSON_UNESCAPED_UNICODE);
    exit();
}

// Extraer la salida específica del guion generada por el nodo correspondiente
$outputs = $workflowResult['outputs']['result'] ?? [];
$guion = $outputs['guion'] ?? 'No se pudo generar el guion.';

// Respuesta final
echo json_encode([
    'status' => 'success',
    'guion' => trim($guion)
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
exit();
