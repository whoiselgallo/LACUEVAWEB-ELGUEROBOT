<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

// Incluir archivo de configuración desde la raíz
require_once __DIR__ . '/../config/config.php';

// CONFIG DB
$host = DB_HOST;
$dbname = DB_NAME;
$user = DB_USER;
$pass = DB_PASS;

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    echo json_encode(["error" => "Error de conexión: " . $e->getMessage()]);
    exit;
}

// 1. Obtener registros de knowledge que contienen fichas
$stmt = $pdo->query("
    SELECT content 
    FROM knowledge_base
    WHERE content LIKE '%Ficha de invitado registrada:%'
");

$registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$registros) {
    echo json_encode(["message" => "No se encontraron fichas en knowledge"]);
    exit;
}

$migrados = 0;

foreach ($registros as $row) {

    // 2. Extraer JSON dentro del texto
    if (!preg_match('/Ficha de invitado registrada:\s*(\{.*\})/s', $row['content'], $match)) {
        continue;
    }

    $json = json_decode($match[1], true);

    if (!$json || !isset($json['nombre'])) {
        continue;
    }

    $nombre = $json['nombre'];

    // 3. Verificar si ya existe en la tabla invitados
    $check = $pdo->prepare("SELECT id FROM invitados WHERE nombre = :nombre LIMIT 1");
    $check->execute([":nombre" => $nombre]);

    if ($check->fetch()) {
        continue; // ya existe, no duplicar
    }

    // 4. Insertar en la tabla invitados
    $insert = $pdo->prepare("
        INSERT INTO invitados 
        (nombre, ocupacion, signo, fecha_nacimiento, barrio, trayectoria, herida, incomodo, gustos, fecha_propuesta)
        VALUES 
        (:nombre, :ocupacion, :signo, :fecha, :barrio, :trayectoria, :herida, :incomodo, :gustos, :fecha_propuesta)
    ");

    $insert->execute([
        ":nombre" => $json["nombre"] ?? "",
        ":ocupacion" => $json["ocupacion"] ?? "",
        ":signo" => $json["signo"] ?? "",
        ":fecha" => $json["fecha"] ?? "",
        ":barrio" => $json["barrio"] ?? "",
        ":trayectoria" => $json["trayectoria"] ?? "",
        ":herida" => $json["herida"] ?? "",
        ":incomodo" => $json["incomodo"] ?? "",
        ":gustos" => $json["gustos"] ?? "",
        ":fecha_propuesta" => $json["fecha_propuesta"] ?? ""
    ]);

    $migrados++;
}

echo json_encode([
    "success" => true,
    "migrados" => $migrados,
    "message" => "Migración completada"
]);
