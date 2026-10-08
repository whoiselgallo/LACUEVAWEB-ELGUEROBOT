<?php
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json; charset=utf-8');

try {
    assert_runtime_config();
    $db = db_connect();
    $tables = ['knowledge_base', 'invitados', 'conversations', 'users', 'avatars', 'galeria', 'episodes_sync'];
    $status = check_required_db_tables($tables);

    echo json_encode([
        'success' => true,
        'app_env' => APP_ENV,
        'db_connected' => true,
        'missing_tables' => $status['missing'],
        'present_tables' => $status['present']
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}
