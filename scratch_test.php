<?php
require_once __DIR__ . '/config/config.php';

echo "Testing DB connection...\n";
try {
    $db = db_connect();
    echo "✓ DB connection successful!\n";
    
    // Check tables
    $tables = $db->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'")->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables in DB: " . implode(', ', $tables) . "\n";
} catch (Exception $e) {
    echo "✗ DB Connection failed: " . $e->getMessage() . "\n";
}

echo "\nTesting Dify API...\n";
try {
    $res = call_dify_api("Hola, eres Güero Bot?", "test_user", "guest");
    echo "Dify response:\n";
    print_r($res);
} catch (Exception $e) {
    echo "✗ Dify call failed: " . $e->getMessage() . "\n";
}
