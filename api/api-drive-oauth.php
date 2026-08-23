<?php
session_start();

// Cargar variables de entorno manualmente (ya que no usamos Composer/phpdotenv)
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        $_ENV[trim($name)] = trim($value);
    }
}

// Configuración de Google OAuth
$clientId = $_ENV['GOOGLE_CLIENT_ID'] ?? '';
$clientSecret = $_ENV['GOOGLE_CLIENT_SECRET'] ?? '';
// Redirige a este mismo archivo
$redirectUri = rtrim($_ENV['APP_URL'] ?? 'http://localhost', '/') . '/api/api-drive-oauth.php'; 

// Sincronizar token de sesión con el archivo cache persistente centralizado
$tokenFile = __DIR__ . '/../cache/google_oauth_tokens.json';
if (!isset($_SESSION['google_access_token']) && file_exists($tokenFile)) {
    $tokens = json_decode(file_get_contents($tokenFile), true);
    if (!empty($tokens['access_token'])) {
        if ($tokens['expires_at'] > time()) {
            $_SESSION['google_access_token'] = $tokens['access_token'];
            $_SESSION['google_refresh_token'] = $tokens['refresh_token'] ?? '';
        } else {
            // El token expiró. Intentar refrescar usando el refresh_token
            if (!empty($tokens['refresh_token']) && !empty($clientId) && !empty($clientSecret)) {
                $ch = curl_init('https://oauth2.googleapis.com/token');
                curl_setopt_array($ch, [
                    CURLOPT_POST => true,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_POSTFIELDS => http_build_query([
                        'client_id' => $clientId,
                        'client_secret' => $clientSecret,
                        'refresh_token' => $tokens['refresh_token'],
                        'grant_type' => 'refresh_token'
                    ]),
                    CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
                    CURLOPT_TIMEOUT => 10
                ]);
                $res = curl_exec($ch);
                curl_close($ch);
                $resData = json_decode($res, true);
                if (!empty($resData['access_token'])) {
                    $_SESSION['google_access_token'] = $resData['access_token'];
                    $tokens['access_token'] = $resData['access_token'];
                    $tokens['expires_at'] = time() + ($resData['expires_in'] ?? 3600);
                    $tokens['updated_at'] = date('Y-m-d H:i:s');
                    file_put_contents($tokenFile, json_encode($tokens, JSON_PRETTY_PRINT));
                }
            }
        }
    }
}

$action = $_GET['action'] ?? '';

// 1. Redirigir al usuario a Google
if ($action === 'login') {
    $authUrl = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
        'client_id' => $clientId,
        'redirect_uri' => $redirectUri,
        'response_type' => 'code',
        'scope' => 'https://www.googleapis.com/auth/drive.readonly',
        'access_type' => 'offline',
        'prompt' => 'consent' // Fuerza a pedir permisos siempre (ideal para pruebas)
    ]);
    header('Location: ' . $authUrl);
    exit;
}

// 2. Manejar el callback de Google (cuando google redirige de vuelta con el 'code')
if (isset($_GET['code'])) {
    $code = $_GET['code'];
    
    // Intercambiar código por token de acceso
    $ch = curl_init('https://oauth2.googleapis.com/token');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'client_id' => $clientId,
        'client_secret' => $clientSecret,
        'redirect_uri' => $redirectUri,
        'grant_type' => 'authorization_code',
        'code' => $code
    ]));
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    $data = json_decode($response, true);
    
    if (isset($data['access_token'])) {
        // Guardar token en sesión (en producción podrías guardarlo en DB asociado al admin)
        $_SESSION['google_access_token'] = $data['access_token'];
        if (isset($data['refresh_token'])) {
            $_SESSION['google_refresh_token'] = $data['refresh_token'];
        }
        // Redirigir de vuelta al dashboard
        header('Location: ../dashboard/index.php?sync=google-success');
        exit;
    } else {
        die("Error obteniendo token de Google: " . print_r($data, true));
    }
}

// 3. Listar archivos desde Google Drive
if ($action === 'list') {
    if (!isset($_SESSION['google_access_token'])) {
        header('HTTP/1.1 401 Unauthorized');
        echo json_encode(['error' => 'No autorizado. Conecta tu cuenta de Google primero.']);
        exit;
    }
    
    $accessToken = $_SESSION['google_access_token'];
    
    // Buscar solo imágenes y videos
    $query = urlencode("mimeType contains 'video/' or mimeType contains 'image/'");
    $url = "https://www.googleapis.com/drive/v3/files?q={$query}&fields=files(id,name,mimeType,thumbnailLink,webViewLink)&pageSize=10";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $accessToken
    ]);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    header('Content-Type: application/json');
    echo $response;
    exit;
}

// 4. Comprobar estado de conexión
if ($action === 'status') {
    header('Content-Type: application/json');
    echo json_encode([
        'connected' => isset($_SESSION['google_access_token'])
    ]);
    exit;
}

// 5. Desconectar
if ($action === 'logout') {
    unset($_SESSION['google_access_token']);
    unset($_SESSION['google_refresh_token']);
    header('Location: ../dashboard/index.php');
    exit;
}
