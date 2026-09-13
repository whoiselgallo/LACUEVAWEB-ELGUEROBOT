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
$episodio = sanitize_input($inputData["episodio"] ?? "1");
$tema = sanitize_input($inputData["tema"] ?? "Episodio General");
$guion = $inputData["guion"] ?? "";

if (empty($guion)) {
    $guion = "Episodio de charla urbana y comedia en La Cueva del Güero sobre " . $tema . ". Participan El Güero, El Junior y el invitado especial.";
}

$prompt = "Actúa como el Director de Contenido Viral y Productor de Clips (Shorts/TikTok/Reels) de 'La Cueva del Güero Podcast'.\n\n" .
          "A partir del siguiente contexto o guion del Episodio #" . $episodio . " (" . $tema . "):\n\n" .
          $guion . "\n\n" .
          "Identifica exactamente de 3 a 5 de los MOMENTOS MÁS VIRALES, polémicos, cómicos o sorprendentes para recortar en formato vertical.\n\n" .
          "Debes responder ÚNICAMENTE en formato JSON válido con la siguiente estructura (sin texto adicional):\n" .
          '{"info": "Clips detectados", "clips": [{"id": 1, "timestamp_inicio": "00:03:15", "timestamp_fin": "00:04:10", "duracion_seg": 55, "categoria": "Polémica", "titulo_gancho": "Gancho", "sugerencia_corte": "Corte dinámico"}]}';

$payload = [
    "contents" => [
        [
            "role" => "user",
            "parts" => [
                ["text" => $prompt]
            ]
        ]
    ],
    "generationConfig" => [
        "temperature" => 0.7,
        "maxOutputTokens" => 3000
    ]
];

$geminiRes = call_gemini_generate($payload);

if ($geminiRes["success"]) {
    $rawText = $geminiRes["text"];
    if (preg_match('/```(?:json)?\s*([\s\S]*?)\s*```/', $rawText, $matches)) {
        $rawText = $matches[1];
    }
    $parsed = json_decode($rawText, true);
    if (json_last_error() === JSON_ERROR_NONE && !empty($parsed["clips"])) {
        echo json_encode(["success" => true, "episodio" => $episodio, "tema" => $tema, "clips" => $parsed["clips"], "model" => $geminiRes["model"]], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(["success" => true, "episodio" => $episodio, "tema" => $tema, "clips" => [["id" => 1, "timestamp_inicio" => "00:04:20", "timestamp_fin" => "00:05:15", "duracion_seg" => 55, "categoria" => "Momento Cumbre", "titulo_gancho" => "Lo más fuerte de " . $tema, "sugerencia_corte" => "Corte dinámico a 3 cámaras y subtítulos en amarillo neón."]], "model" => $geminiRes["model"]], JSON_UNESCAPED_UNICODE);
    }
} else {
    echo json_encode(["success" => false, "error" => $geminiRes["error"]], JSON_UNESCAPED_UNICODE);
}
