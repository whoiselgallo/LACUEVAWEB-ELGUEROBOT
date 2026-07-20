<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

// Configuración
require_once __DIR__ . '/../config/config.php';

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (Exception $e) {
    echo json_encode(["error" => "Error de conexión: " . $e->getMessage()]);
    exit;
}

// Leer JSON
$input = json_decode(file_get_contents("php://input"), true);

if (!$input || !isset($input["nombre"]) || !isset($input["ficha"])) {
    echo json_encode(["error" => "Formato inválido. Se requiere nombre y ficha."]);
    exit;
}

$nombre = trim($input["nombre"]);
$ficha  = $input["ficha"];

// Validar nombre
if ($nombre === "") {
    echo json_encode(["error" => "El nombre no puede estar vacío."]);
    exit;
}

// Guardar en BD
try {
    $stmt = $pdo->prepare("
        INSERT INTO invitados (nombre, ficha, created_at)
        VALUES (:nombre, :ficha, NOW())
        ON DUPLICATE KEY UPDATE ficha = :ficha2
    ");

    $stmt->execute([
        ":nombre" => $nombre,
        ":ficha"  => json_encode($ficha, JSON_UNESCAPED_UNICODE),
        ":ficha2" => json_encode($ficha, JSON_UNESCAPED_UNICODE)
    ]);

    echo json_encode(["success" => true, "message" => "Ficha guardada correctamente"]);

} catch (Exception $e) {
    echo json_encode(["error" => "Error al guardar: " . $e->getMessage()]);
}
