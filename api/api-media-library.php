<?php
/**
 * 🎬 API - GESTOR DE MEDIOS: RAW Y EDITED (LA CUEVA DEL GÜERO)
 * Endpoint: /api/api-media-library.php
 * Métodos: GET, POST
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/../config/config.php';

try {
    $db = db_connect();
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    exit();
}

$action = $_GET['action'] ?? ($_POST['action'] ?? 'list');

switch ($action) {
    // -------------------------------------------------------------------------
    // 1. LISTAR MATERIAL RAW (ANTES DE EDICIÓN)
    // -------------------------------------------------------------------------
    case 'list-raw':
        try {
            $stmt = $db->query("
                SELECT r.*, i.nombre as invitado_nombre 
                FROM media_raw r
                LEFT JOIN invitados i ON r.invitado_id = i.id
                ORDER BY r.id DESC
            ");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['status' => 'success', 'data' => $rows]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit();

    // -------------------------------------------------------------------------
    // 2. LISTAR MATERIAL EDITED (DESPUÉS DE EDICIÓN)
    // -------------------------------------------------------------------------
    case 'list-edited':
        try {
            $stmt = $db->query("
                SELECT e.*, i.nombre as invitado_nombre, r.archivo_nombre as raw_origen_archivo
                FROM media_edited e
                LEFT JOIN invitados i ON e.invitado_id = i.id
                LEFT JOIN media_raw r ON e.raw_id = r.id
                ORDER BY e.id DESC
            ");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['status' => 'success', 'data' => $rows]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit();

    // -------------------------------------------------------------------------
    // 3. REGISTRAR NUEVO RAW (SUBIDO A STORAGE)
    // -------------------------------------------------------------------------
    case 'create-raw':
        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        try {
            $stmt = $db->prepare("
                INSERT INTO media_raw (
                    invitado_id, titulo, descripcion, tipo_medio, camara_angulo,
                    archivo_nombre, archivo_size_bytes, duracion_segundos, resolucion,
                    gcs_bucket, gcs_uri, url_acceso
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                RETURNING id
            ");
            $stmt->execute([
                $data['invitado_id'] ?? null,
                $data['titulo'] ?? 'Grabación sin título',
                $data['descripcion'] ?? '',
                $data['tipo_medio'] ?? 'video',
                $data['camara_angulo'] ?? 'principal',
                $data['archivo_nombre'] ?? 'video.mp4',
                $data['archivo_size_bytes'] ?? 0,
                $data['duracion_segundos'] ?? 0,
                $data['resolucion'] ?? '1920x1080',
                $data['gcs_bucket'] ?? 'cueva-raw-videos',
                $data['gcs_uri'] ?? '',
                $data['url_acceso'] ?? ''
            ]);
            $newId = $stmt->fetchColumn();
            echo json_encode(['status' => 'success', 'id' => $newId]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit();

    // -------------------------------------------------------------------------
    // 4. REGISTRAR RESULTADO DE EDICIÓN
    // -------------------------------------------------------------------------
    case 'create-edited':
        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        try {
            $stmt = $db->prepare("
                INSERT INTO media_edited (
                    raw_id, invitado_id, job_id, titulo, descripcion, formato_destino,
                    aspect_ratio, resolucion, duracion_segundos, archivo_nombre,
                    gcs_bucket, gcs_uri, loudness_lufs, tiene_subtitulos_karaoke,
                    tiene_autocrop_rostros, estado_publicacion
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                RETURNING id
            ");
            $stmt->execute([
                $data['raw_id'] ?? null,
                $data['invitado_id'] ?? null,
                $data['job_id'] ?? null,
                $data['titulo'] ?? 'Clip Editado',
                $data['descripcion'] ?? '',
                $data['formato_destino'] ?? 'tiktok_9_16',
                $data['aspect_ratio'] ?? '9:16',
                $data['resolucion'] ?? '1080x1920',
                $data['duracion_segundos'] ?? 0,
                $data['archivo_nombre'] ?? 'clip.mp4',
                $data['gcs_bucket'] ?? 'cueva-processed-videos',
                $data['gcs_uri'] ?? '',
                $data['loudness_lufs'] ?? -14.0,
                $data['tiene_subtitulos_karaoke'] ?? false,
                $data['tiene_autocrop_rostros'] ?? false,
                $data['estado_publicacion'] ?? 'borrador'
            ]);
            $newId = $stmt->fetchColumn();
            echo json_encode(['status' => 'success', 'id' => $newId]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit();

    default:
        echo json_encode([
            'status' => 'error',
            'message' => 'Acción no reconocida. Disponibles: list-raw, list-edited, create-raw, create-edited'
        ]);
        exit();
}
