<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/../config/config.php';

$inputData = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$prompt = sanitize_input($inputData['prompt'] ?? 'Dark urban podcast studio neon purple cyan lighting microphone');
$aspectRatio = sanitize_input($inputData[
essect_ratio'] ?? '16:9');

$key = get_gemini_api_key();
if (empty($key)) {
    echo json_encode(['success' => false, 'error' => 'No hay API Key configurada.']);
    exit;
}

$enhancedPrompt = "High quality professional photography, " . $prompt . ", dark aesthetic, neon glow accents, cinematic lighting, photorealistic, 4k resolution, no text, empty central space for podcast thumbnail.";
$ch = curl_init("https://generativelanguage.googleapis.com/v1beta/models/imagen-3.0-generate-002:generateImages?key=" . $key);
$payload = [
    "prompt" => $enhancedPrompt,
    "numberOfImages" => 1,
    "aspectRatio" => $aspectRatio,
    "outputMimeType" => "image/jpeg",
    "compressionQuality" => 85
];

curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPW_TIMEOUT => 40,
    CURLOPT_SSL_VERIFYPEER => false
];

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    $resData = json_decode($response, true);
    $b64 = $resData['generatedImages'][0]['image']['imageBytes'] ?? '';
    if (!empty($b64)) {
        $dir = __DIR__ . '/../images/generated/';
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        $filename = 'neon_bg_' . time() . '.jpg';
        file_put_contents($dir . $filename, base64_decode($b64));
        echo json_encode([ 'success' => true, 'image_url' => '/images/generated/' . $filename, 'base64' => 'data:image/jpeg;base64,' . $b64 ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'No se devolvieron bytes de imagen.']);
    }
} else {
    echo json_encode(['success' => false, 'error' => "Error Imagen API (HTTP {$http_code}): " . $response]);
}
