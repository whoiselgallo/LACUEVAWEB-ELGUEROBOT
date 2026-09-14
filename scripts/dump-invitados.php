<?php
require_once __DIR__ . '/../config/config.php';
$db = db_connect();
$stmt = $db->query('SELECT * FROM invitados ORDER BY id');
foreach ($stmt as $r) {
    echo "=== ID {$r['id']}: {$r['nombre']} ===\n";
    echo "Ocupacion: " . ($r['ocupacion'] ?? 'N/A') . "\n";
    echo "Barrio: " . ($r['barrio'] ?? 'N/A') . "\n";
    echo "Trayectoria: " . substr($r['trayectoria'] ?? 'N/A', 0, 200) . "\n";
    echo "Herida: " . substr($r['herida'] ?? 'N/A', 0, 200) . "\n";
    echo "Incomodo: " . substr($r['incomodo'] ?? 'N/A', 0, 200) . "\n";
    echo "Gustos: " . substr($r['gustos'] ?? 'N/A', 0, 200) . "\n";
    echo "Molestia: " . substr($r['molestia'] ?? 'N/A', 0, 200) . "\n";
    echo "\n";
}
