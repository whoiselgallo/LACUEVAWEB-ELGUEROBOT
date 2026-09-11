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

$textoOriginal = $inputData['texto_original'] ?? '';
$instruccionEdicion = sanitize_input($inputData['instruccion'] ?? 'Pulir y dejar solo lo más viral y dinámico');
$episodio = sanitize_input($inputData[
episodio'] ?? '1');

if (empty($textoOriginal)) {
    echo json_encode(['success' => false, 'error' => 'Falta el texto o transcripción para editar.']);
    exit;
}

$prompt = "Actúa como un Editor de Video y Transcripción experto en Contenido Viral para 'La Cueva del Güero'.\n\n" .
          "TEXITO / TRANSCRIPCI×N ORIGINAL (con posibles silencios, muletillas o repeticiones):\n" .
          "\"\"\"\n" . $textoOriginal . "\n\"\"\"\n\n" .
          "INSTRUCCI×N DE EDICI×N DEL USUARIO: '" . $instruccionEdicion . "'\n\n" .
          "GARANTIZ los siguientes pasos:\n" .
          "1. Elimina todas las partes aburridas, repeticiones, pausas y muletillas (eh, este, aah, bueno).\n" .
          "2. Mantén el ritmo viral alto, dinámico y el tono único de El Güero y El Junior.\n" .
          "3. Marca las palabras eliminadas y devuelve la transcripción limpia con subtítulos estilo TikTok dispersos.\n\n" .
          "Responde ÚNICAMENTE EN FORMATO JSON VÁLIDO sin texto extra:\n" .
          "{\n" .
          "  \"texto_editado\": \"texto final limpio pulido para el video\",\n" .
          "  \"cortes_aplicados\": [\"Corte de silencio en segundo 12\9, \"Eliminada muletilla 'este' en plano de El Junior\"],
" .
          "  \"duracion_estimada_final\": \"45s\",\n" .
          "  \"gancho_inicial\": \"Primera frase potente para retener público\"\n" .
          "}";

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
        "temperature" => 0.5,
        "maxOutputTokens" => 2500
    ]
];

$geminiRes = call_gemini_generate($payload);

if ($geminiRes['success']) {
    $rawText = $geminiRes['text'];
    if (preg_match('/```g?:json)?\s*([\s\S]*?)\s*```/', $rawText, $matches)) {
        $rawText = $matches[1];
    }
    $parsed = json_decode($rawText, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo json_encode([ 'success' => true, 'data' => $parsed, 'model' => $geminiRes[model'] ], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode([ 'success' => true, 'data' => [ 'texto_editado' => $rawText, 'cortes_aplicados' => ['Cortes automáticos aplicados'] ], 'model' => $geminiRes[model'] ], JSON_UNESCAPED_UNICODE);
    }
} else {
    echo json_encode([ 'success' => false, 'error' => $geminiRes['error'] ], JSON_UNESCAPED_UNICODE);
}
