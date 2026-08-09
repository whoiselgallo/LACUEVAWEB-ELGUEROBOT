<?php
/**
 * Sistema de Carga de Blogs desde PDF
 * Procesa PDFs y los convierte en posts de blog
 */

header('Content-Type: application/json');

// Crear carpeta para PDFs si no existe
$root_dir = dirname(__DIR__);
$pdf_dir = $root_dir . '/uploads/pdfs/';
if (!is_dir($pdf_dir)) {
    mkdir($pdf_dir, 0755, true);
}

// Función para extraer texto de PDF usando pdftotext
function extraerTextoDePDF($ruta_pdf) {
    $ruta_output = sys_get_temp_dir() . '/' . uniqid() . '.txt';
    
    // Intenta usar pdftotext (disponible en Linux/Mac)
    if (shell_exec('which pdftotext')) {
        $comando = "pdftotext -layout " . escapeshellarg($ruta_pdf) . " " . escapeshellarg($ruta_output);
        shell_exec($comando);
        
        if (file_exists($ruta_output)) {
            $contenido = file_get_contents($ruta_output);
            unlink($ruta_output);
            return $contenido;
        }
    }
    
    // Si no funciona pdftotext, retorna false (el cliente usará PDF.js)
    return false;
}

// Validar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(400);
    echo json_encode(['error' => 'Solo se aceptan solicitudes POST']);
    exit;
}

// Obtener acción
$action = $_POST['action'] ?? null;

// ACCIÓN 1: Subir PDF
if ($action === 'upload') {
    
    // Validar que se envió un archivo
    if (!isset($_FILES['pdf']) || $_FILES['pdf']['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(['error' => 'Error al subir el archivo']);
        exit;
    }
    
    $archivo = $_FILES['pdf'];
    
    // Validar que es un PDF
    $tipo_mime = mime_content_type($archivo['tmp_name']);
    $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
    
    if ($extension !== 'pdf' || strpos($tipo_mime, 'pdf') === false) {
        http_response_code(400);
        echo json_encode(['error' => 'Solo se aceptan archivos PDF']);
        exit;
    }
    
    // Validar tamaño (máximo 10MB)
    if ($archivo['size'] > 10 * 1024 * 1024) {
        http_response_code(400);
        echo json_encode(['error' => 'El PDF no debe exceder 10MB']);
        exit;
    }
    
    // Generar nombre único para el archivo
    $nombre_seguro = preg_replace('/[^a-zA-Z0-9._-]/', '_', pathinfo($archivo['name'], PATHINFO_FILENAME));
    $nombre_archivo = date('Y-m-d_H-i-s_') . $nombre_seguro . '.pdf';
    $ruta_destino = $pdf_dir . $nombre_archivo;
    
    // Mover archivo a la carpeta
    if (!move_uploaded_file($archivo['tmp_name'], $ruta_destino)) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al guardar el PDF']);
        exit;
    }
    
    // Intentar extraer texto con pdftotext (lado servidor)
    $contenido = extraerTextoDePDF($ruta_destino);
    
    echo json_encode([
        'success' => true,
        'archivo' => $nombre_archivo,
        'ruta' => 'uploads/pdfs/' . $nombre_archivo,
        'contenido_server' => $contenido, // null si pdftotext no está disponible
        'mensaje' => 'PDF subido exitosamente'
    ]);
    exit;
}

// ACCIÓN 2: Publicar post
if ($action === 'publish') {
    
    // Obtener datos del formulario
    $titulo = trim($_POST['titulo'] ?? '');
    $autor = trim($_POST['autor'] ?? '');
    $fecha = trim($_POST['fecha'] ?? date('Y-m-d'));
    $categoria = trim($_POST['categoria'] ?? 'articulo reflexivo');
    $contenido = trim($_POST['contenido'] ?? '');
    $excerpt = trim($_POST['excerpt'] ?? '');
    
    // Validación básica
    if (!$titulo || !$autor || !$contenido) {
        http_response_code(400);
        echo json_encode(['error' => 'Faltan campos obligatorios']);
        exit;
    }
    
    // Generar excerpt automático si no se proporciona
    if (!$excerpt) {
        $excerpt = substr(strip_tags($contenido), 0, 150) . '...';
    }
    
    // Crear objeto del post
    $post = [
        'id' => time(), // Usar timestamp como ID único
        'titulo' => $titulo,
        'fecha' => $fecha,
        'autor' => $autor,
        'categoria' => $categoria,
        'contenido' => $contenido,
        'excerpt' => $excerpt
    ];
    
    // Guardar en archivo JSON (para persistencia)
    $archivo_posts = $root_dir . '/posts.json';
    
    if (file_exists($archivo_posts)) {
        $posts_existentes = json_decode(file_get_contents($archivo_posts), true) ?? [];
    } else {
        $posts_existentes = [];
    }
    
    $posts_existentes[] = $post;
    
    if (file_put_contents($archivo_posts, json_encode($posts_existentes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
        echo json_encode([
            'success' => true,
            'mensaje' => 'Post publicado exitosamente',
            'post' => $post,
            'instruccion' => 'Agrega este objeto al array blogPosts en blog.js'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Error al guardar el post']);
    }
    exit;
}

// ACCIÓN 3: Listar artículos PDF desde la carpeta articulos blog
if ($action === 'get_articles') {
    $articles_dir = $root_dir . '/articulos blog/';
    $articles = [];

    if (is_dir($articles_dir)) {
        $files = scandir($articles_dir);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if ($extension !== 'pdf') {
                continue;
            }

            $titulo = pathinfo($file, PATHINFO_FILENAME);
            $titulo = str_replace(['_', '-'], ' ', $titulo);
            $titulo = trim(preg_replace('/\s+/', ' ', $titulo));
            $titulo = mb_convert_case($titulo, MB_CASE_TITLE, 'UTF-8');

            $articles[] = [
                'id' => crc32($file),
                'titulo' => $titulo,
                'fecha' => date('Y-m-d', filemtime($articles_dir . $file)),
                'autor' => 'La Cueva del Güero',
                'categoria' => 'articulo pdf',
                'contenido' => 'Este artículo está disponible en PDF. Ábrelo para leerlo completo.',
                'excerpt' => 'Artículo cargado desde la carpeta articulos blog.',
                'url' => 'articulos blog/' . rawurlencode($file),
                'tipo' => 'pdf'
            ];
        }
    }

    echo json_encode(['articles' => $articles]);
    exit;
}

// ACCIÓN 4: Obtener posts guardados
if ($action === 'get_posts') {
    $archivo_posts = $root_dir . '/posts.json';
    
    if (file_exists($archivo_posts)) {
        $posts = json_decode(file_get_contents($archivo_posts), true);
        echo json_encode(['posts' => $posts]);
    } else {
        echo json_encode(['posts' => []]);
    }
    exit;
}

// Si no hay acción válida
http_response_code(400);
echo json_encode(['error' => 'Acción no especificada']);
?>
