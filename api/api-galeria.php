<?php
/**
 * 🖼️ LA CUEVA DEL GÜERO - API GESTOR DE GALERÍA
 * Endpoint: /api/api-galeria.php
 * Métodos: GET, POST
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../config/config.php';

try {
    $db = db_connect();

    // Auto-crear tabla galeria si no existe (Self-healing)
    $db->exec("
        CREATE TABLE IF NOT EXISTS galeria (
            id SERIAL PRIMARY KEY,
            titulo VARCHAR(255),
            categoria VARCHAR(100) DEFAULT 'La Cueva',
            imagen_url TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    // METODO GET: Obtener fotos de la galería pública
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $stmt = $db->query("SELECT * FROM galeria ORDER BY id DESC LIMIT 50");
        $fotos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        json_response(['success' => true, 'fotos' => $fotos], 200);
        exit();
    }

    // METODO POST: Subir nueva foto desde el Dashboard
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $action = sanitize_input($input['action'] ?? 'upload');

    if ($action === 'upload') {
        $titulo = sanitize_input($input['titulo'] ?? 'Fotografía de La Cueva');
        $categoria = sanitize_input($input['categoria'] ?? 'La Cueva');
        $imagenUrl = $input['imagen_url'] ?? '';

        if (empty($imagenUrl)) {
            json_response(['error' => 'La imagen es requerida'], 400);
            exit();
        }

        $stmt = $db->prepare("INSERT INTO galeria (titulo, categoria, imagen_url) VALUES (?, ?, ?)");
        $stmt->execute([$titulo, $categoria, $imagenUrl]);

        json_response([
            'success' => true,
            'message' => 'Fotografía subida a la galería exitosamente',
            'foto'    => ['titulo' => $titulo, 'categoria' => $categoria]
        ], 200);
        exit();
    }

    json_response(['error' => 'Acción no válida'], 400);

} catch (PDOException $e) {
    error_log('Galeria API Error: ' . $e->getMessage());
    json_response(['error' => 'Error de base de datos en Galería: ' . $e->getMessage()], 500);
}
