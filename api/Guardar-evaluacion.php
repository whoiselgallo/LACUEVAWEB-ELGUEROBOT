<?php
require_once __DIR__ . '/../config/config.php';

$host = DB_HOST;
$db   = "u115767692_LCDGSUSCRIPTOR";
$user = DB_USER;
$pass = DB_PASS;

$conn = new mysqli($host, $user, $pass, $db);
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
  http_response_code(500);
  exit("Error de conexión.");
}

$nombre = trim($_POST['nombre'] ?? '');
$correo = trim($_POST['correo'] ?? '');
$calificacion = intval($_POST['calificacion'] ?? 0);
$comentario = trim($_POST['comentario'] ?? '');

if ($nombre === '' || $correo === '' || $calificacion < 1 || $calificacion > 5 || $comentario === '') {
  http_response_code(400);
  exit("Completa todos los campos.");
}

$stmt = $conn->prepare("INSERT INTO evaluaciones_cueva (nombre, correo, calificacion, comentario) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssis", $nombre, $correo, $calificacion, $comentario);

if ($stmt->execute()) {
  echo "Evaluación enviada correctamente.";
} else {
  http_response_code(500);
  echo "No se pudo guardar la evaluación.";
}

$stmt->close();
$conn->close();
?>