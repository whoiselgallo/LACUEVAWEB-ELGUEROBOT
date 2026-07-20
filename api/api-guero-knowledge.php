<?php
/**
 * ═════════════════════════════════════════════════════════════════════════════════
 * API – Guardar Storytelling y Conocimiento del Güero
 * ═════════════════════════════════════════════════════════════════════════════════
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Cargar configuración
require_once __DIR__ . '/../config/config.php';

// Conectar a BD
try {
    $db = db_connect();
} catch (Exception $e) {
    json_response(['error' => 'Error de conexión: ' . $e->getMessage()], 500);
    exit();
}

// ═════════════════════════════════════════════════════════════════════════════════
// LISTAR EPISODIOS CON STORYTELLING
// ═════════════════════════════════════════════════════════════════════════════════

if ($_GET['listar'] ?? false) {
    try {
        $stmt = $db->query("
            SELECT id, nombre, storytelling, created_at 
            FROM knowledge_base 
            WHERE tipo='storytelling' 
            ORDER BY created_at DESC
        ");
        
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        exit();
    } catch (Exception $e) {
        json_response(['error' => $e->getMessage()], 500);
        exit();
    }
}

// ═════════════════════════════════════════════════════════════════════════════════
// OBTENER STORYTELLING POR NOMBRE
// ═════════════════════════════════════════════════════════════════════════════════

if ($_GET['nombre'] ?? false) {
    try {
        $nombre = sanitize_input($_GET['nombre']);
        $stmt = $db->prepare("
            SELECT * FROM knowledge_base 
            WHERE nombre = ? AND tipo='storytelling'
            LIMIT 1
        ");
        $stmt->execute([$nombre]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result && $result['storytelling']) {
            $result['storytelling'] = json_decode($result['storytelling'], true);
        }
        
        json_response($result ?: ['error' => 'No encontrado'], 200);
        exit();
    } catch (Exception $e) {
        json_response(['error' => $e->getMessage()], 500);
        exit();
    }
}

// ═════════════════════════════════════════════════════════════════════════════════
// GUARDAR STORYTELLING COMPLETO
// ═════════════════════════════════════════════════════════════════════════════════

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['nombre'])) {
            json_response(['error' => 'Faltan datos: nombre requerido'], 400);
            exit();
        }

        $nombre = sanitize_input($input['nombre']);
        $tipo = sanitize_input($input['tipo'] ?? 'storytelling');
        $storytelling = isset($input['storytelling']) ? json_encode($input['storytelling']) : null;

        // Verificar si ya existe
        $stmt = $db->prepare("SELECT id FROM knowledge_base WHERE nombre = ? AND tipo = ?");
        $stmt->execute([$nombre, $tipo]);
        $existe = $stmt->fetch();

        if ($existe) {
            // Actualizar
            $stmt = $db->prepare("
                UPDATE knowledge_base 
                SET storytelling = ?, updated_at = NOW()
                WHERE nombre = ? AND tipo = ?
            ");
            $stmt->execute([$storytelling, $nombre, $tipo]);
            $mensaje = 'Storytelling actualizado';
        } else {
            // Insertar
            $stmt = $db->prepare("
                INSERT INTO knowledge_base (nombre, tipo, storytelling, created_at, updated_at)
                VALUES (?, ?, ?, NOW(), NOW())
            ");
            $stmt->execute([$nombre, $tipo, $storytelling]);
            $mensaje = 'Storytelling guardado';
        }

        json_response([
            'success' => true,
            'mensaje' => $mensaje,
            'nombre' => $nombre,
            'tipo' => $tipo
        ], 200);
        exit();

    } catch (PDOException $e) {
        json_response(['error' => 'Error de base de datos: ' . $e->getMessage()], 500);
        exit();
    } catch (Exception $e) {
        json_response(['error' => $e->getMessage()], 500);
        exit();
    }
}

// ═════════════════════════════════════════════════════════════════════════════════
// PREFLIGHT CORS
// ═════════════════════════════════════════════════════════════════════════════════

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Respuesta por defecto
json_response(['error' => 'Método no permitido'], 405);

