<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

// URL del canal oficial de YouTube
$channelUrl = 'https://www.youtube.com/@LacuevadelGueroPodcast';

// Ubicación del archivo de caché
$cacheDir = __DIR__ . '/../cache';
$cacheFile = $cacheDir . '/youtube_latest.json';
$cacheTime = 3600; // Carga en caché durante 1 hora (3600 segundos)

// Comprobar si existe la respuesta en caché y si aún es válida
if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < $cacheTime)) {
    $cachedData = json_decode(file_get_contents($cacheFile), true);
    if (json_last_error() === JSON_ERROR_NONE && !empty($cachedData['videoId'])) {
        echo json_encode($cachedData);
        exit();
    }
}

// ID de video por defecto suministrado por el usuario (Último Capítulo Actual)
$defaultVideoId = 'e5AQri7Yz_4';
$videoId = $defaultVideoId;

// Intentar obtener el HTML de la página del canal de YouTube
$ctx = stream_context_create([
    'http' => [
        'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36\r\nAccept-Language: es-ES,es;q=0.9\r\n",
        'timeout' => 8
    ]
]);

$html = @file_get_contents($channelUrl, false, $ctx);

if ($html !== false && !empty($html)) {
    // Buscar la estructura de metadatos "videoId":"..." del reproductor primario
    if (preg_match('/"videoId"\s*:\s*"([^"]+)"/', $html, $matches)) {
        if (strlen($matches[1]) === 11) { // La longitud de los IDs de video de YouTube es siempre de 11 caracteres
            $videoId = $matches[1];
        }
    }
}

$responseData = [
    'status' => 'success',
    'videoId' => $videoId,
    'embedUrl' => 'https://www.youtube.com/embed/' . $videoId . '?si=VvnDYcrJmQgJGLbL',
    'updated_at' => date('Y-m-d H:i:s')
];

// Asegurar que la carpeta de caché exista
if (!is_dir($cacheDir)) {
    @mkdir($cacheDir, 0777, true);
}

// Guardar resultado obtenido en la caché local
@file_put_contents($cacheFile, json_encode($responseData));

echo json_encode($responseData);
exit();
