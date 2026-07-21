<?php
/**
 * API - Guardar Storytelling y Conocimiento del Güero (PostgreSQL / Neon)
 * Endpoint: /api/api-guero-knowledge.php
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

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
// LISTAR EPISODIOS CON STORYTELLING (GET)
// ═════════════════════════════════════════════════════════════════════════════════

if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($_GET['listar'] ?? false)) {
    try {
        $stmt = $db->query("
            SELECT id, nombre, created_at 
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
// OBTENER STORYTELLING POR NOMBRE (GET)
// ═════════════════════════════════════════════════════════════════════════════════

if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($_GET['nombre'] ?? false)) {
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
// ACCIONES DE ESCRITURA Y OBTENCIÓN DETALLE (POST)
// ═════════════════════════════════════════════════════════════════════════════════

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            json_response(['error' => 'No se recibieron datos JSON'], 400);
            exit();
        }

        // --- ACCIÓN: OBTENER DETALLE (GET DESDE POST) ---
        if (isset($input['action']) && $input['action'] === 'get') {
            $id = intval($input['id'] ?? 0);
            $stmt = $db->prepare("SELECT * FROM knowledge_base WHERE id = ?");
            $stmt->execute([$id]);
            $reg = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($reg) {
                // Decodificar el JSON de storytelling para el frontend
                $story = json_decode($reg['storytelling'] ?? '{}', true);
                $reg['escaleta'] = $story['escaleta'] ?? '';
                $reg['guion'] = $story['guion'] ?? '';
                $reg['cue_cards'] = $story['cue_cards'] ?? '';
                
                json_response(['registro' => $reg], 200);
            } else {
                json_response(['error' => 'Registro no encontrado'], 404);
            }
            exit();
        }

        // --- ACCIÓN: ACTUALIZAR MANUALMENTE DESDE EL DASHBOARD ---
        if (isset($input['action']) && $input['action'] === 'update') {
            $id = intval($input['id'] ?? 0);
            $escaleta = $input['escaleta'] ?? null;
            $guion = $input['guion'] ?? null;
            $cue_cards = $input['cuecards'] ?? $input['cue_cards'] ?? null;
            
            // Obtener el storytelling actual
            $stmt = $db->prepare("SELECT storytelling FROM knowledge_base WHERE id = ?");
            $stmt->execute([$id]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$existing) {
                json_response(['error' => 'Registro no encontrado'], 404);
                exit();
            }
            
            $story = json_decode($existing['storytelling'] ?? '{}', true);
            if ($escaleta !== null) $story['escaleta'] = $escaleta;
            if ($guion !== null) $story['guion'] = $guion;
            if ($cue_cards !== null) $story['cue_cards'] = $cue_cards;
            
            $stmt = $db->prepare("
                UPDATE knowledge_base 
                SET storytelling = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([json_encode($story, JSON_UNESCAPED_UNICODE), $id]);
            
            json_response(['success' => true, 'message' => 'Registro actualizado correctamente'], 200);
            exit();
        }

        // --- ACCIÓN POR DEFECTO: GUARDAR / CREAR STORYTELLING ---
        if (!isset($input['nombre'])) {
            json_response(['error' => 'Faltan datos: nombre requerido'], 400);
            exit();
        }

        $nombre = sanitize_input($input['nombre']);
        $tipo = sanitize_input($input['tipo'] ?? 'storytelling');

        // Construir o mezclar el storytelling
        // Esto evita que llamados a guardarConocimiento() con parámetros nulos sobreescriban los datos existentes
        $stmt = $db->prepare("SELECT storytelling FROM knowledge_base WHERE nombre = ? AND tipo = ?");
        $stmt->execute([$nombre, $tipo]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $story_data = [];
        if ($existing && $existing['storytelling']) {
            $story_data = json_decode($existing['storytelling'], true);
        }

        // Actualizar solo los parámetros que vengan en la petición
        if (isset($input['escaleta'])) $story_data['escaleta'] = $input['escaleta'];
        if (isset($input['guion'])) $story_data['guion'] = $input['guion'];
        if (isset($input['cuecards'])) $story_data['cue_cards'] = $input['cuecards'];
        
        // Si viene directamente envuelto en 'storytelling' (formato alternativo)
        if (isset($input['storytelling'])) {
            $story_data = is_array($input['storytelling']) ? $input['storytelling'] : json_decode($input['storytelling'], true);
        }

        $storytelling_json = json_encode($story_data, JSON_UNESCAPED_UNICODE);

        if ($existing) {
            // Actualizar
            $stmt = $db->prepare("
                UPDATE knowledge_base 
                SET storytelling = ?, updated_at = NOW()
                WHERE nombre = ? AND tipo = ?
            ");
            $stmt->execute([$storytelling_json, $nombre, $tipo]);
            $mensaje = 'Storytelling actualizado';
        } else {
            // Insertar
            $stmt = $db->prepare("
                INSERT INTO knowledge_base (nombre, tipo, storytelling, created_at, updated_at)
                VALUES (?, ?, ?, NOW(), NOW())
            ");
            $stmt->execute([$nombre, $tipo, $storytelling_json]);
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

// Respuesta por defecto
json_response(['error' => 'Método no permitido'], 405);
