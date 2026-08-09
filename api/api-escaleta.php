<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(); }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status'=>'error','message'=>'Método no permitido']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    http_response_code(400);
    echo json_encode(['status'=>'error','message'=>'JSON inválido']);
    exit();
}

$campos = [
    'nombre','ocupacion','signo','fecha','barrio',
    'trayectoria','herida','incomodo','gustos'
];

$errores = [];
foreach ($campos as $c) {
    if (empty(trim($input[$c] ?? ''))) $errores[] = $c;
}

if ($errores) {
    http_response_code(400);
    echo json_encode(['status'=>'error','message'=>'Faltan campos: '.implode(', ',$errores)]);
    exit();
}

$datos = [];
foreach ($campos as $c) {
    $datos[$c] = htmlspecialchars(trim($input[$c]), ENT_QUOTES, 'UTF-8');
}

require_once __DIR__ . '/../config/config.php';

// Mapear campos a las variables del nodo INICIO de Dify
$workflowInputs = [
    'NOMBRE'              => $datos['nombre'],
    'Ocupacion'           => $datos['ocupacion'],
    'Barrio'              => $datos['barrio'],
    'herida'              => $datos['herida'],
    'momento'             => htmlspecialchars(trim($input['momento'] ?? 'Superación de adversidades'), ENT_QUOTES, 'UTF-8'),
    'trayectoria'         => $datos['trayectoria'],
    'incomodo'            => $datos['incomodo'],
    'gustos'              => $datos['gustos'], // Actualizado a 'gustos' tras la corrección del usuario en Dify
    'logros'              => htmlspecialchars(trim($input['logros'] ?? 'Éxito y superación personal'), ENT_QUOTES, 'UTF-8'),
    'fecha_de_nacimiento' => $datos['fecha']
];

// Ejecutar workflow de Dify
$workflowResult = call_dify_workflow($workflowInputs, 'la-cueva-web');

if (!$workflowResult['success']) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $workflowResult['error']]);
    exit();
}

// Extraer salidas consolidadas por el nodo Code de Dify
$outputs = $workflowResult['outputs']['result'] ?? [];
$escaleta = $outputs['escaleta'] ?? 'No se pudo generar la escaleta.';
$guion = $outputs['guion'] ?? 'No se pudo generar el guion.';
$cueCards = $outputs['cue_cards'] ?? 'No se pudieron generar las cue cards.';

// Devolver la respuesta exitosa
echo json_encode([
    'status'    => 'success',
    'escaleta'  => $escaleta,
    'guion'     => $guion,
    'cue_cards' => $cueCards
], JSON_UNESCAPED_UNICODE);
exit();
