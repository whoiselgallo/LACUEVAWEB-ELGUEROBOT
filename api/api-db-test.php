<?php
/**
 * API: TEST DE CONEXIÓN EN VIVO A POSTGRESQL / NEON
 * Endpoint: /api/api-db-test.php
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/config.php';

try {
    $start = microtime(true);
    $db = db_connect();
    $elapsed = round((microtime(true) - $start) * 1000, 2);

    // Muestrear tablas existentes y conteo de filas
    $tables = ['knowledge_base', 'invitados', 'conversations', 'evaluaciones_cueva', 'users', 'avatars', 'galeria', 'episodes_sync'];
    $details = [];

    foreach ($tables as $table) {
        try {
            $stmt = $db->query("SELECT COUNT(*) as cnt FROM $table");
            $row = $stmt->fetch();
            $details[$table] = [
                'status' => 'OK',
                'rows' => (int)$row['cnt']
            ];
        } catch (Exception $ex) {
            $details[$table] = [
                'status' => 'Error: ' . $ex->getMessage(),
                'rows' => 0
            ];
        }
    }

    echo json_encode([
        'success' => true,
        'driver' => $db->getAttribute(PDO::ATTR_DRIVER_NAME),
        'latency_ms' => $elapsed,
        'details' => $details
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
