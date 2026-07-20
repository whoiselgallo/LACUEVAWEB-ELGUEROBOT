<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

// Incluir archivo de configuración desde la raíz
require_once __DIR__ . '/../../config/config.php';

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

// Leer JSON
$input = json_decode(file_get_contents("php://input"), true);

if (!$input) {
    echo json_encode(["error" => "No se recibió información"]);
    exit;
}

// Validar campos obligatorios
$required = ["nombre", "ocupacion", "signo", "fecha", "barrio", "trayectoria", "herida", "incomodo", "gustos", "fecha_propuesta"];

foreach ($required as $field) {
    if (!isset($input[$field]) || empty(trim($input[$field]))) {
        echo json_encode(["error" => "Falta el campo: $field"]);
        exit;
    }
}

// Insertar en BD
try {
    $stmt = $pdo->prepare("
        INSERT INTO invitados 
        (nombre, ocupacion, signo, fecha_nacimiento, barrio, trayectoria, herida, incomodo, gustos, fecha_propuesta)
        VALUES 
        (:nombre, :ocupacion, :signo, :fecha, :barrio, :trayectoria, :herida, :incomodo, :gustos, :fecha_propuesta)
    ");

    $stmt->execute([
        ":nombre" => $input["nombre"],
        ":ocupacion" => $input["ocupacion"],
        ":signo" => $input["signo"],
        ":fecha" => $input["fecha"],
        ":barrio" => $input["barrio"],
        ":trayectoria" => $input["trayectoria"],
        ":herida" => $input["herida"],
        ":incomodo" => $input["incomodo"],
        ":gustos" => $input["gustos"],
        ":fecha_propuesta" => $input["fecha_propuesta"]
    ]);

    echo json_encode(["success" => true, "message" => "Invitado guardado correctamente"]);

echo json_encode([
  "success" => true,
  "uuid" => $uuid,
  "nombre" => $nombre,
  "ficha" => $ficha,
  "message" => "Ficha guardada correctamente"
]);


} catch (Exception $e) {
    echo json_encode(["error" => "Error al guardar: " . $e->getMessage()]);
}
