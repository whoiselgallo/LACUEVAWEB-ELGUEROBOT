<?php
/**
 * API: OPTIMIZACIÓN Y PLANIFICADOR DE ACCIONES YOUTUBE CON GEMINI IA
 * Endpoint: /api/api-youtube-actions.php
 * Métodos: POST
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método no permitido.']);
    exit;
}

$inputData = json_decode(file_get_contents('php://input'), true);
$views = (int)($inputData['views'] ?? 0);
$ctr = (float)($inputData['ctr'] ?? 0.0);
$retention = (int)($inputData['retention'] ?? 0);
$impressions = (int)($inputData['impressions'] ?? 0);

$geminiApiKey = get_gemini_api_key();
if (empty($geminiApiKey)) {
    echo json_encode(['success' => false, 'error' => 'No hay claves de API de Gemini configuradas.']);
    exit;
}

$prompt = "Actúa como el Consultor Experto en YouTube Studio de 'La Cueva del Güero'. " .
          "Analiza los siguientes métricas de rendimiento reales del canal:\n" .
          "- Impresiones de miniaturas: " . number_format($impressions) . "\n" .
          "- Vistas del capítulo: " . number_format($views) . "\n" .
          "- Tasa de Clics (CTR): {$ctr}%\n" .
          "- Retención de audiencia promedio: {$retention}%\n\n" .
          "Traduce estas métricas en exactamente 3 acciones correctivas específicas que el usuario debe ejecutar dentro del panel de La Cueva:\n" .
          "1. Si el CTR es bajo (< 6%), sugiere crear un diseño neón llamativo en el 'Editor Canva PRO'.\n" .
          "2. Si la retención es baja (< 50%), sugiere acortar silencios a 0.5s en el 'Editor de Video'.\n" .
          "3. Si las impresiones son bajas, sugiere regenerar títulos llamativos con el 'Generador de Hooks'.\n\n" .
          "Escribe las sugerencias con jerga mexicana del norte, de forma muy concisa y directa. Formatea cada acción en una línea independiente.";

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
        "maxOutputTokens" => 1500
    ]
];

$geminiRes = call_gemini_generate($payload);

if ($geminiRes['success']) {
    $text = $geminiRes['text'];
    
    // Formatear a HTML básico para desplegar en la terminal del dashboard
    $formattedHtml = "";
    $lines = explode("\n", trim($text));
    foreach ($lines as $line) {
        $cleanLine = trim(preg_replace('/^[-*\d.]+\s*/', '', $line));
        if (!empty($cleanLine)) {
            $formattedHtml .= "<div style='margin-bottom:8px;'>> ⚠️ <strong>" . htmlspecialchars($cleanLine) . "</strong></div>";
        }
    }
    
    echo json_encode([
        'success' => true,
        'actions_html' => $formattedHtml,
        'model' => $geminiRes['model']
    ], JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode([
        'success' => false,
        'error' => $geminiRes['error']
    ]);
}
?>
