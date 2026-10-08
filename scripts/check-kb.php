<?php
require_once __DIR__ . '/../config/config.php';
$db = db_connect();
echo "=== knowledge_base records ===\n";
$stmt = $db->query('SELECT id, nombre, tipo FROM knowledge_base ORDER BY id');
foreach ($stmt as $r) {
    echo $r['id'] . ' | ' . $r['nombre'] . ' | ' . $r['tipo'] . "\n";
}
echo "\n=== invitados records ===\n";
$stmt2 = $db->query('SELECT id, nombre FROM invitados ORDER BY id');
foreach ($stmt2 as $r) {
    echo $r['id'] . ' | ' . $r['nombre'] . "\n";
}
echo "\n=== Test detail fetch for ID 2 ===\n";
$stmt3 = $db->prepare("SELECT id, nombre, storytelling FROM knowledge_base WHERE id = ?");
$stmt3->execute([2]);
$row = $stmt3->fetch(PDO::FETCH_ASSOC);
if ($row) {
    $story = json_decode($row['storytelling'], true);
    echo "Name: " . $row['nombre'] . "\n";
    echo "Has escaleta: " . (isset($story['escaleta']) ? 'YES' : 'NO') . "\n";
    echo "Has guion: " . (isset($story['guion']) ? 'YES' : 'NO') . "\n";
    echo "Has cue_cards: " . (isset($story['cue_cards']) ? 'YES' : 'NO') . "\n";
    echo "Has curaduria: " . (isset($story['curaduria']) ? 'YES' : 'NO') . "\n";
} else {
    echo "ID 2 NOT FOUND!\n";
}
