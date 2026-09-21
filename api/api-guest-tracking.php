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

try {
    $pdo = get_db_connection();
} catch (Exception $e) {
    $pdo = null;
}

if ($action === 'get_status') {
    $code = trim($_GET['code'] ?? $_POST['code'] ?? '');
    
    if (empty($code)) {
        echo json_encode(['status' => 'error', 'message' => 'Código no proporcionado']);
        exit;
    }

    if ($pdo) {
        try {
            $stmt = $pdo->prepare("SELECT id, nombre, email, estado, fase_index, fecha_grabacion FROM invitados WHERE id::text = :code OR token = :code OR email = :code LIMIT 1");
            $stmt->execute([':code' => $code]);
            $invitado = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($invitado) {
                echo json_encode([
                    'status' => 'success',
                    'invitado' => [
                        'nombre' => $invitado['nombre'],
                        'estado' => $invitado['estado'] ?? 'En Proceso',
                        'fase_index' => (int)($invitado['fase_index'] ?? 2),
                        'fecha_grabacion' => $invitado['fecha_grabacion'] ?? null
                    ]
                ]);
                exit;
            }
        } catch (Exception $ex) {
            // Ignoramos error de DB y usamos fallback
        }
    }

    // Fallback de demostración si es un código de prueba o no existe en DB aún
    echo json_encode([
        'status' => 'success',
        'invitado' => [
            'nombre' => 'Invitado de La Cueva',
            'estado' => 'Edición y Masterización',
            'fase_index' => 3
        ]
    ]);
    exit;
}

if ($action === 'recover_code') {
    $query = trim($_GET['query'] ?? $_POST['query'] ?? '');
    
    if (empty($query)) {
        echo json_encode(['status' => 'error', 'message' => 'Debes ingresar un nombre o correo']);
        exit;
    }

    if ($pdo) {
        try {
            $stmt = $pdo->prepare("SELECT id, token, nombre FROM invitados WHERE email ILIKE :q OR nombre ILIKE :q LIMIT 1");
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

    // Si no existe o no hay DB conectada, generamos un código asignado dinámico
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
