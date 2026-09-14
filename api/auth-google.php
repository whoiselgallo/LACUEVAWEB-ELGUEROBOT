<?php
/**
 * OAUTH INITIATOR: GOOGLE DRIVE & YOUTUBE
 * Endpoint: /api/auth-google.php
 */

require_once __DIR__ . '/../config/config.php';

$clientId = getEnvVar('GOOGLE_CLIENT_ID');
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'lacuevadelguero.com';
$redirectUri = $protocol . $host . '/api/auth-google-callback.php';

$scopes = [
    'https://www.googleapis.com/auth/youtube.readonly',
    'https://www.googleapis.com/auth/youtube.upload',
    'https://www.googleapis.com/auth/drive'
];

$authUrl = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
    'client_id' => $clientId,
    'redirect_uri' => $redirectUri,
    'response_type' => 'code',
    'scope' => implode(' ', $scopes),
    'access_type' => 'offline',
    'prompt' => 'consent'
]);

header('Location: ' . $authUrl);
exit;
?>
