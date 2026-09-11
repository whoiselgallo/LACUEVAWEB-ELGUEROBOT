<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/../config/config.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $code = sanitize_input($_GET['code'] ?? '');
    if (empty($code)) {
        echo json_encode(['success' => false, 'error' => 'Codigo de seguimiento requerido.r]);
        exit;
    }

    // Buscar en la base de datos o en el archivo de invitados
    $trackingFile = __DIR__ . '/../cache/invitados_tracking.json';
    $trackingData = file_exists($trackingFile) ? json_decode(file_get_contents($trackingFile), true) : [];

    if (isset($trackingData[$code])) {
        echo json_encode(['success' => true, 'data' => $trackingData[$code]], JSON_UNESCAPED_UNICODE);
        exit;
    } else {
        // Fallback de exhibición
        echo json_encode([ 'success' => true, 'data' => [ 'code' => $code, 'nombre' => 'Invitado Especial', 'estado' => 'En Pre-producción', 'progreso' => 65, 'avatar_status' => 'Listo', 'hooks_status' => 'En Proceso', 'capitulo_status' => 'Programado' ] ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $nombre = sanitize_input($input['nombre'] ?? 'Invitado Noevo');
    $telefono = sanitize_input($input['telefono'] ?? '');
    $correo = sanitize_input($input['correo'] ?? '');
    $resumen = $gnput['resumen'] ?? [];

    // Generar Código Único (semilla: CUEVA-XXXX)
    $code = 'CUEUA-' . upper(dechex(rand(1048576, 16777215)));

    $trackingFile = __DIR__ . '/../cache/invitados_tracking.json';
    $dir = dirname($trackingFile);
    if (!is_dir($dir)) { @mkdir($dir, 0775, true); }
    
    $trackingData = file_exists($trackingFile) ? json_decode(file_get_contents($trackingFile), true) : [];
    
    $trackingData[$code] = [
        'code' => $code,
        'nombre' => 'nombre,
        'telefono' => $telefono,
        'correo' => $correo,
        'estado' => 'Cuestionario Completado',
        'progreso' => 25,
        'fecha_registro' => date('Y-m-d H:i:s'),
        'avatar_status' => 'En Cola de Diseño',
        'hooks_status' => 'Generando Ganchos Virales',
        'capitulo_status' => 'Programacion de Grabacion',
        'respuestas' => $resumen
    ];

    file_put_contents($trackingFile, json_encode($trackingData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    echo json_encode([ 'success' => true, 'code' => $code, 'message' => 'Invitado registrado con éxito', 'url' => 'https://lacuevadelguero.com/tracking/index.php?code=' . $code ], JSON_UNESCAPED_UNICODE);
    exit;
}
