<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../config/config.php';

$action = $_GET['action'] ?? $_POST['action'] ?? 'get_status';

$pdo = null;
try {
    $pdo = db_connect();
} catch (Exception $e) {
    $pdo = null;
}

function getEnviosLocales() {
    $file = __DIR__ . '/../images/formularios/cuestionarios_envios.json';
    if (file_exists($file)) {
        $data = json_decode(file_get_contents($file), true);
        if (is_array($data)) return $data;
    }
    return [];
}

if ($action === 'get_status') {
    $code = trim($_GET['code'] ?? $_POST['code'] ?? '');
    
    if (empty($code)) {
        echo json_encode(['status' => 'error', 'message' => 'Código no proporcionado']);
        exit;
    }

    // 1. Buscar en BD
    if ($pdo) {
        try {
            $stmt = $pdo->prepare("SELECT id, nombre, token, estado, fase_index, fecha_propuesta, ficha FROM invitados WHERE id::text = :code OR token = :code OR nombre ILIKE :code LIMIT 1");
            $stmt->execute([':code' => $code]);
            $invitado = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($invitado) {
                $ficha = json_decode($invitado['ficha'] ?? '{}', true) ?: [];
                echo json_encode([
                    'status' => 'success',
                    'invitado' => [
                        'nombre' => $invitado['nombre'],
                        'token' => $invitado['token'] ?? $code,
                        'estado' => $invitado['estado'] ?? 'Ficha en Revisión',
                        'fase_index' => (int)($invitado['fase_index'] ?? 1),
                        'fecha_grabacion' => $invitado['fecha_propuesta'] ?? null,
                        'solicitud_correccion' => $ficha['solicitud_correccion'] ?? null
                    ]
                ]);
                exit;
            }
        } catch (Exception $ex) {
            // Seguir a fallback
        }
    }

    // 2. Buscar en envíos recientes JSON
    $envios = getEnviosLocales();
    foreach ($envios as $env) {
        if ((isset($env['token']) && strcasecmp($env['token'], $code) === 0) || (isset($env['id']) && strval($env['id']) === strval($code)) || (isset($env['nombre']) && stripos($env['nombre'], $code) !== false)) {
            echo json_encode([
                'status' => 'success',
                'invitado' => [
                    'nombre' => $env['nombre'],
                    'token' => $env['token'] ?? $code,
                    'estado' => $env['estado'] ?? 'Cuestionario Recibido',
                    'fase_index' => (int)($env['fase_index'] ?? 1),
                    'fecha_grabacion' => date('d/m/Y', strtotime('+7 days')),
                    'solicitud_correccion' => $env['solicitud_correccion'] ?? null
                ]
            ]);
            exit;
        }
    }

    // 3. Fallback de demostración amigable
    echo json_encode([
        'status' => 'success',
        'invitado' => [
            'nombre' => 'Invitado de La Cueva',
            'estado' => 'Cuestionario en Revisión de Producción',
            'fase_index' => 1,
            'fecha_grabacion' => date('d/m/Y', strtotime('+7 days'))
        ]
    ]);
    exit;
}

if ($action === 'recover_code') {
    $query = trim($_GET['query'] ?? $_POST['query'] ?? '');
    
    if (empty($query)) {
        echo json_encode(['status' => 'error', 'message' => 'Debes ingresar tu nombre']);
        exit;
    }

    if ($pdo) {
        try {
            $stmt = $pdo->prepare("SELECT id, token, nombre FROM invitados WHERE nombre ILIKE :q LIMIT 1");
            $stmt->execute([':q' => "%$query%"]);
            $invitado = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($invitado) {
                $code = !empty($invitado['token']) ? $invitado['token'] : 'GUEST-' . $invitado['id'];
                echo json_encode(['status' => 'success', 'code' => $code, 'nombre' => $invitado['nombre']]);
                exit;
            }
        } catch (Exception $ex) {
            // Seguir al fallback
        }
    }

    $envios = getEnviosLocales();
    foreach ($envios as $env) {
        if (isset($env['nombre']) && stripos($env['nombre'], $query) !== false) {
            $code = !empty($env['token']) ? $env['token'] : ('GUEST-' . ($env['id'] ?? '2026'));
            echo json_encode(['status' => 'success', 'code' => $code, 'nombre' => $env['nombre']]);
            exit;
        }
    }

    // Si no existe, generamos un código asignado dinámico
    $newCode = 'GUEST-' . strtoupper(substr(md5($query . time()), 0, 4));
    echo json_encode([
        'status' => 'success',
        'code' => $newCode,
        'nombre' => $query,
        'message' => 'Nuevo código generado'
    ]);
    exit;
}

if ($action === 'update_phase' || $action === 'update_status') {
    $code = trim($_GET['code'] ?? ($_POST['code'] ?? ''));
    $fase_index = intval($_GET['fase_index'] ?? ($_POST['fase_index'] ?? 1));
    $estado = trim($_GET['estado'] ?? ($_POST['estado'] ?? 'En Proceso'));
    
    $envios = getEnviosLocales();
    $nombreInv = 'Invitado';
    foreach ($envios as &$env) {
        if ((isset($env['token']) && strcasecmp($env['token'], $code) === 0) || (isset($env['id']) && strval($env['id']) === strval($code))) {
            $env['fase_index'] = $fase_index;
            $env['estado'] = $estado;
            $nombreInv = $env['nombre'] ?? $nombreInv;
            break;
        }
    }
    @file_put_contents(__DIR__ . '/../images/formularios/cuestionarios_envios.json', json_encode($envios, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    if ($pdo) {
        try {
            $stmt = $pdo->prepare("UPDATE invitados SET fase_index = :fase, estado = :estado WHERE token = :c OR id::text = :c");
            $stmt->execute([':fase' => $fase_index, ':estado' => $estado, ':c' => $code]);
        } catch (Exception $e) {}
    }

    try {
        require_once __DIR__ . '/api-webhook.php';
        dispararWebhook('tracking_actualizado', [
            'invitado' => $nombreInv,
            'token' => $code,
            'fase' => "Fase {$fase_index}: {$estado}",
            'fecha' => date('Y-m-d H:i:s')
        ], 'panel_tracking');
    } catch (Exception $e) {}
    
    echo json_encode(['status' => 'success', 'message' => 'Fase de tracking actualizada y notificada con webhook']);
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Acción no válida']);
