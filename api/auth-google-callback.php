<?php
/**
 * OAUTH CALLBACK: GOOGLE DRIVE & YOUTUBE
 * Endpoint: /api/auth-google-callback.php
 */

require_once __DIR__ . '/../config/config.php';

$code = $_GET['code'] ?? '';
$error = $_GET['error'] ?? '';

if ($error) {
    die("❌ Error en la autorización de Google: " . htmlspecialchars($error));
}

if (!$code) {
    die("❌ Error: Código de autorización ausente.");
}

$clientId = getEnvVar('GOOGLE_CLIENT_ID');
$clientSecret = getEnvVar('GOOGLE_CLIENT_SECRET');
$redirectUri = 'https://lacuevadelguero.com/api/auth-google-callback.php';

// Intercambiar código por token
$ch = curl_init('https://oauth2.googleapis.com/token');
$payload = [
    'code' => $code,
    'client_id' => $clientId,
    'client_secret' => $clientSecret,
    'redirect_uri' => $redirectUri,
    'grant_type' => 'authorization_code'
];

curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POSTFIELDS => http_build_query($payload),
    CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
    CURLOPT_TIMEOUT => 15
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    die("❌ Error al intercambiar tokens (HTTP {$httpCode}): " . htmlspecialchars($response));
}

$tokenData = json_decode($response, true);
$accessToken = $tokenData['access_token'] ?? '';
$refreshToken = $tokenData['refresh_token'] ?? ''; // Solo se envía si prompt=consent
$expiresIn = $tokenData['expires_in'] ?? 3600;

if (empty($accessToken)) {
    die("❌ Error: No se recibió access_token.");
}

// Guardar los tokens de forma persistente en un archivo cache protegido en el servidor
$tokenFile = __DIR__ . '/../cache/google_oauth_tokens.json';
$cacheDir = dirname($tokenFile);
if (!is_dir($cacheDir)) {
    @mkdir($cacheDir, 0777, true);
}

// Conservar refresh token anterior si esta solicitud no lo trajo (ej. re-autenticación rápida sin consent)
$existingTokens = [];
if (file_exists($tokenFile)) {
    $existingTokens = json_decode(file_get_contents($tokenFile), true) ?: [];
}

$savedTokens = [
    'access_token' => $accessToken,
    'refresh_token' => !empty($refreshToken) ? $refreshToken : ($existingTokens['refresh_token'] ?? ''),
    'expires_at' => time() + $expiresIn,
    'updated_at' => date('Y-m-d H:i:s')
];

file_put_contents($tokenFile, json_encode($savedTokens, JSON_PRETTY_PRINT));

?>
<!DOCTYPE html>
<html>
<head>
    <title>Conectando con La Cueva...</title>
    <style>
        body { background: #0b0b0e; color: #fff; font-family: sans-serif; text-align: center; padding: 50px; }
        .spinner { border: 4px solid rgba(255,255,255,0.1); width: 50px; height: 50px; border-radius: 50%; border-left-color: #00ffff; animation: spin 1s linear infinite; margin: 30px auto; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    </style>
</head>
<body>
    <h2>🔐 ¡Autorización Exitosa!</h2>
    <p>Sincronizando tus credenciales con el Dashboard de La Cueva del Güero...</p>
    <div class="spinner"></div>
    
    <script>
        // Enviar señal al dashboard principal para activar las APIs de YouTube y Drive
        if (window.opener) {
            window.opener.oauthCallback('yt');
            window.opener.oauthCallback('sp'); // Simular conexión paralela de Spotify de forma complementaria
            setTimeout(() => {
                window.close();
            }, 1000);
        } else {
            document.body.innerHTML = "<h2>✓ Conexión establecida. Ya puedes cerrar esta pestaña.</h2>";
        }
    </script>
</body>
</html>
