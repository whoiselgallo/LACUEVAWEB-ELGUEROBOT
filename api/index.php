<?php
/**
 * Unified Serverless Gateway Router for Vercel
 * Handles all API endpoints and Dashboard execution within a single Serverless Function
 */

// Global CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$host = $_SERVER['HTTP_HOST'] ?? '';

// 1. Host-based routing for admin.lacuevadelguero.com
if (strpos($host, 'admin.lacuevadelguero') !== false) {
    if ($uri === '/login' || $uri === '/dashboard/login' || $uri === '/dashboard/login.php') {
        chdir(__DIR__ . '/../dashboard');
        require __DIR__ . '/../dashboard/login.php';
        exit;
    }
    if ($uri === '/logout' || $uri === '/dashboard/logout' || $uri === '/dashboard/logout.php') {
        chdir(__DIR__ . '/../dashboard');
        require __DIR__ . '/../dashboard/logout.php';
        exit;
    }
    // Default to main dashboard
    chdir(__DIR__ . '/../dashboard');
    require __DIR__ . '/../dashboard/index.php';
    exit;
}

// 2. Direct Dashboard routes
if (strpos($uri, '/dashboard') === 0) {
    if ($uri === '/dashboard/login' || $uri === '/dashboard/login.php') {
        chdir(__DIR__ . '/../dashboard');
        require __DIR__ . '/../dashboard/login.php';
        exit;
    }
    if ($uri === '/dashboard/logout' || $uri === '/dashboard/logout.php') {
        chdir(__DIR__ . '/../dashboard');
        require __DIR__ . '/../dashboard/logout.php';
        exit;
    }
    chdir(__DIR__ . '/../dashboard');
    require __DIR__ . '/../dashboard/index.php';
    exit;
}

// 3. API endpoint routing (/api/...)
if (strpos($uri, '/api/') === 0) {
    $script = basename($uri);
    // If no .php extension provided, append .php
    if (pathinfo($script, PATHINFO_EXTENSION) === '') {
        $script .= '.php';
    }
    
    $targetFile = __DIR__ . '/' . $script;
    if (file_exists($targetFile) && $script !== 'index.php') {
        chdir(__DIR__);
        require $targetFile;
        exit;
    }
}

// Default response if endpoint not found
http_response_code(404);
header('Content-Type: application/json');
echo json_encode([
    'status' => 'error',
    'message' => 'Endpoint no encontrado en el servidor serverless.',
    'uri' => $uri
]);
