<?php
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    exit(0);
}

require_once __DIR__ . "/../config/config.php";

$inputData = json_decode(file_get_contents("php://input"), true) ?: $_POST;

$textoOriginal = $inputData["texto_original"] ?? "";
$instruccionEdicion = sanitize_input($inputData["instruccion"] ?? "Pulir y dejar solo lo más viral y dinámico");
$episodio = sanitize_input($inputData["episodio"] ?? "1");

if (empty($textoOriginal)) {
    echo json_encode(["success" => false, "error" => "Falta el texto o transcripción para editar."]);
    exit;
}

$prompt = "Actúa como un Editor de Video y Transcripción experto en Contenido Viral para 'La Cueva del Güero'.\n\n" .
          "TEXTO / TRANSCRIPCIÓN ORIGINAL:\n" .
          $textoOriginal . "\n\n" .
          "INSTRUCCIÓN DE EDICIÓN DEL USUARIO: " . $instruccionEdicion . "\n\n" .
          "GARANTIZA los siguientes pasos:\n" .
          "1. Elimina todas las partes aburridas, repeticiones, pausas y muletillas (eh, este, aah, bueno).\n" .
          "2. Mantén el ritmo viral alto, dinámico y el tono único de El Güero y El Junior.\n" .
          "3. Marca las palabras eliminadas y devuelve la transcripción limpia con subtítulos estilo TikTok dispersos.\n\n" .
          "Responde ÚNICAMENTE EN FORMATO JSON VÁLIDO sin texto extra:\n" .
          '{"texto_editado": "...", "palabras_recortadas": ["muletilla1"], "gancho_inicial": "Frase de impacto", "duracion_estimada_final": "45s"}';

$key = get_gemini_api_key();
if (empty($key)) {
    echo json_encode(["success" => false, "error" => "No hay API Key configurada para Gemini."]);
    exit;
}

$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . $key;
$payload = [
    "contents" => [
        [
            "parts" => [
                ["text" => $prompt]
            ]
        ]
    ],
    "generationConfig" => [
        "temperature" => 0.4,
        "responseMimeType" => "application/json"
    ]
];

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ["Content-Type: application/json"],
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_TIMEOUT => 30,
    CURLOPT_SSL_VERIFYPEER => false
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    $resData = json_decode($response, true);
    $text = $resData["candidates"][0]["content"]["parts"][0]["text"] ?? "{}";
    $jsonResult = json_decode($text, true) ?: ["texto_editado" => $text];
    echo json_encode(["success" => true, "data" => $jsonResult], JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode(["success" => false, "error" => "Error Gemini API (HTTP " . $http_code . "): " . $response]);
}
