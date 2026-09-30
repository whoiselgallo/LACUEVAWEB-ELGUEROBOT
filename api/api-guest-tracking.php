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
            $stmt = $pdo->prepare("SELECT id, nombre, token, estado, fase_index, fecha_propuesta FROM invitados WHERE id::text = :code OR token = :code OR nombre ILIKE :code LIMIT 1");
            $stmt->execute([':code' => $code]);
            $invitado = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($invitado) {
                echo json_encode([
                    'status' => 'success',
                    'invitado' => [
                        'nombre' => $invitado['nombre'],
                        'estado' => $invitado['estado'] ?? 'Ficha en Revisión',
                        'fase_index' => (int)($invitado['fase_index'] ?? 1),
                        'fecha_grabacion' => $invitado['fecha_propuesta'] ?? null
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
                    'estado' => $env['estado'] ?? 'Cuestionario Recibido',
                    'fase_index' => (int)($env['fase_index'] ?? 1),
                    'fecha_grabacion' => date('d/m/Y', strtotime('+7 days'))
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

echo json_encode(['status' => 'error', 'message' => 'Acción no válida']);
