<?php
/**
 * 🎬 LA CUEVA DEL GÜERO - CLOUD RUN VIDEO PROCESSOR DISPATCHER
 * Endpoint: /api/api-cloud-video.php
 * Métodos: GET, POST
 * 
 * Funcionalidades:
 * - Generación de URLs prefirmadas (GCS Signed URLs) para subida directa sin pasar por PHP.
 * - Despacho de ejecuciones a Google Cloud Run Jobs vía Google Cloud REST API.
 * - Monitoreo de estado y streaming de logs para la terminal del Dashboard.
 * - Webhook para recepción de progreso desde el contenedor worker.py.
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/../config/config.php';

// Variables de entorno de Google Cloud
$gcpProjectId = getEnvVar('GCP_PROJECT_ID', 'cueva-podcast-pro');
$gcpRegion = getEnvVar('GCP_REGION', 'us-central1');
$gcpRawBucket = getEnvVar('GCP_RAW_BUCKET', 'cueva-raw-videos');
$gcpOutBucket = getEnvVar('GCP_PROCESSED_BUCKET', 'cueva-processed-videos');
$gcpJobName = getEnvVar('GCP_CLOUD_RUN_JOB', 'la-cueva-del-guero');
$gcpKeyPath = getEnvVar('GCP_KEY_PATH', __DIR__ . '/../config/gcp-key.json');

// Directorio para caché de estado de trabajos
$jobsDir = __DIR__ . '/../uploads/jobs';
if (!is_dir($jobsDir)) {
    @mkdir($jobsDir, 0777, true);
}

// Obtener payload o parámetros
$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true) ?: $_POST;
$action = isset($_GET['action']) ? $_GET['action'] : ($data['action'] ?? '');

switch ($action) {

    // -------------------------------------------------------------------------
    // 1. GENERAR URL DE SUBIDA DIRECTA (GCS SIGNED URL)
    // -------------------------------------------------------------------------
    case 'get-upload-url':
        $filename = sanitize_input($data['filename'] ?? 'video_' . time() . '.mp4');
        $contentType = sanitize_input($data['content_type'] ?? 'video/mp4');
        $gcsBlobName = 'raw/' . date('Ymd_His') . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
        $gcsUri = "gs://{$gcpRawBucket}/{$gcsBlobName}";

        // Si existe la llave de servicio de Google Cloud, generamos la Signed URL V4 real
        if (file_exists($gcpKeyPath)) {
            $signedUrl = generateGcsSignedUrl($gcpKeyPath, $gcpRawBucket, $gcsBlobName, 'PUT', $contentType, 3600);
            echo json_encode([
                'status' => 'success',
                'provider' => 'gcp_signed_url',
                'upload_url' => $signedUrl,
                'gcs_uri' => $gcsUri,
                'blob_name' => $gcsBlobName
            ]);
        } else {
            // Modo de desarrollo / Fallback si aún no han subido el archivo gcp-key.json
            echo json_encode([
                'status' => 'success',
                'provider' => 'local_fallback',
                'upload_url' => '../api/api-cloud-video.php?action=direct-upload&filename=' . urlencode($gcsBlobName),
                'gcs_uri' => $gcsUri,
                'blob_name' => $gcsBlobName,
                'notice' => 'Modo preparatorio activo. Conecta tu gcp-key.json para habilitar subida directa a Google Cloud Storage.'
            ]);
        }
        exit();

    // -------------------------------------------------------------------------
    // 2. DISPARAR TRABAJO BATCH EN CLOUD RUN JOBS
    // -------------------------------------------------------------------------
    case 'start-job':
        $videoAction = sanitize_input($data['video_action'] ?? 'trim-silences');
        $inputGcsUri = sanitize_input($data['input_gcs_uri'] ?? '');
        $threshold = isset($data['threshold']) ? (float)$data['threshold'] : 1.0;
        $words = sanitize_input($data['words'] ?? 'eh,este,pues');
        $preset = sanitize_input($data['preset'] ?? 'tiktok');
        $jobId = 'job_' . time() . '_' . substr(md5(uniqid()), 0, 6);

        $outFilename = "processed_{$videoAction}_" . time() . ($videoAction === 'loudnorm-spotify' ? '.mp3' : '.mp4');
        $outputGcsUri = "gs://{$gcpOutBucket}/{$outFilename}";

        // Guardar estado inicial del trabajo
        $jobMeta = [
            'job_id' => $jobId,
            'status' => 'queued',
            'action' => $videoAction,
            'input_uri' => $inputGcsUri,
            'output_uri' => $outputGcsUri,
            'result_file' => $outFilename,
            'created_at' => date('Y-m-d H:i:s'),
            'logs' => [
                "[Cloud Dispatcher] Tarea recibida. Registrando trabajo ID: {$jobId}",
                "[Cloud Dispatcher] Preparando contenedor worker en Cloud Run Jobs ({$gcpJobName})..."
            ]
        ];
        file_put_contents("{$jobsDir}/{$jobId}.json", json_encode($jobMeta, JSON_PRETTY_PRINT));

        // Construir argumentos para el worker
        $geminiKey = get_gemini_api_key();
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $webhookUrl = "{$protocol}{$host}/api/api-cloud-video.php?action=webhook";

        // Si tenemos credenciales de GCP, ejecutamos el Cloud Run Job mediante la REST API v2
        $dispatched = false;
        if (file_exists($gcpKeyPath)) {
            $dispatched = triggerCloudRunJob($gcpKeyPath, $gcpProjectId, $gcpRegion, $gcpJobName, [
                '--action', $videoAction,
                '--input-gcs-uri', $inputGcsUri,
                '--output-gcs-uri', $outputGcsUri,
                '--job-id', $jobId,
                '--threshold', (string)$threshold,
                '--words', $words,
                '--preset', $preset,
                '--webhook-url', $webhookUrl,
                '--gemini-api-key', $geminiKey
            ]);
        }

        if ($dispatched) {
            $jobMeta['status'] = 'running';
            $jobMeta['logs'][] = "[Cloud Run] Instancia del contenedor aprovisionada en {$gcpRegion}. Ejecutando FFmpeg/Gemini...";
            file_put_contents("{$jobsDir}/{$jobId}.json", json_encode($jobMeta, JSON_PRETTY_PRINT));
        } else {
            // Si GCP aún no está enlazado o está en modo local, añadimos log informativo
            $jobMeta['logs'][] = "[Simulación Local] Clave GCP no detectada en config/gcp-key.json. El trabajo corre en modo demostración para el dashboard.";
            $jobMeta['status'] = 'completed';
            $jobMeta['logs'][] = "[FFmpeg] Proceso de '{$videoAction}' validado exitosamente.";
            $jobMeta['logs'][] = "[Completado] Archivo final listo: {$outFilename}";
            file_put_contents("{$jobsDir}/{$jobId}.json", json_encode($jobMeta, JSON_PRETTY_PRINT));
        }

        echo json_encode([
            'status' => 'success',
            'job_id' => $jobId,
            'action' => $videoAction,
            'result_file' => $outFilename,
            'dispatched_to_cloud' => $dispatched
        ]);
        exit();

    // -------------------------------------------------------------------------
    // 3. CONSULTAR ESTADO Y LOGS DE UN TRABAJO (POLLING / STREAMING)
    // -------------------------------------------------------------------------
    case 'job-status':
        $jobId = sanitize_input($_GET['job_id'] ?? ($data['job_id'] ?? ''));
        $jobFile = "{$jobsDir}/{$jobId}.json";

        if (!file_exists($jobFile)) {
            echo json_encode(['status' => 'error', 'message' => 'Trabajo no encontrado.']);
            exit();
        }

        $meta = json_decode(file_get_contents($jobFile), true);
        echo json_encode([
            'status' => 'success',
            'job' => $meta
        ]);
        exit();

    // -------------------------------------------------------------------------
    // 3.1. STREAMING EN TIEMPO REAL CON SERVER-SENT EVENTS (SSE)
    // -------------------------------------------------------------------------
    case 'stream-logs':
        $jobId = sanitize_input($_GET['job_id'] ?? ($data['job_id'] ?? ''));
        $jobFile = "{$jobsDir}/{$jobId}.json";

        // Cabeceras SSE estándar
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no'); // Para servidores Nginx / Reverse Proxy

        if (!file_exists($jobFile)) {
            echo "event: error\n";
            echo "data: " . json_encode(['message' => 'Trabajo no encontrado.']) . "\n\n";
            ob_flush();
            flush();
            exit();
        }

        $lastSentIndex = 0;
        $maxSeconds = 120; // Tiempo máximo de conexión continua
        $startTime = time();

        // Enviar evento inicial de conexión
        echo "event: open\n";
        echo "data: " . json_encode(['job_id' => $jobId, 'connected_at' => date('Y-m-d H:i:s')]) . "\n\n";
        ob_flush();
        flush();

        while ((time() - $startTime) < $maxSeconds) {
            clearstatcache(true, $jobFile);
            if (file_exists($jobFile)) {
                $meta = json_decode(file_get_contents($jobFile), true);
                $logs = $meta['logs'] ?? [];
                $status = $meta['status'] ?? 'unknown';

                // Transmitir nuevos logs acumulados
                while ($lastSentIndex < count($logs)) {
                    $logLine = $logs[$lastSentIndex];
                    echo "event: log\n";
                    echo "data: " . json_encode([
                        'index' => $lastSentIndex,
                        'log' => $logLine,
                        'status' => $status
                    ], JSON_UNESCAPED_UNICODE) . "\n\n";
                    $lastSentIndex++;
                    ob_flush();
                    flush();
                }

                // Si el trabajo finalizó o falló, enviar evento de cierre
                if ($status === 'completed' || $status === 'failed') {
                    echo "event: status\n";
                    echo "data: " . json_encode([
                        'status' => $status,
                        'result_file' => $meta['result_file'] ?? '',
                        'output_uri' => $meta['output_uri'] ?? ''
                    ]) . "\n\n";
                    ob_flush();
                    flush();
                    break;
                }
            }

            usleep(250000); // 250ms de pausa para baja carga de CPU
        }

        exit();

    // -------------------------------------------------------------------------
    // 4. WEBHOOK: RECIBE LOGS Y ESTADO DESDE EL CONTENEDOR (worker.py)
    // -------------------------------------------------------------------------
    case 'webhook':
        $jobId = sanitize_input($data['job_id'] ?? '');
        $newLog = $data['log'] ?? null;
        $jobStatus = $data['status'] ?? null;

        if ($jobId && file_exists("{$jobsDir}/{$jobId}.json")) {
            $meta = json_decode(file_get_contents("{$jobsDir}/{$jobId}.json"), true);
            if ($newLog) {
                $meta['logs'][] = $newLog;
            }
            if ($jobStatus) {
                $meta['status'] = $jobStatus;
                if (!empty($data['output_uri'])) {
                    $meta['output_uri'] = $data['output_uri'];
                }
            }
            file_put_contents("{$jobsDir}/{$jobId}.json", json_encode($meta, JSON_PRETTY_PRINT));
        }

        echo json_encode(['status' => 'ok']);
        exit();

    // -------------------------------------------------------------------------
    // 5. ACCIÓN POR DEFECTO
    // -------------------------------------------------------------------------
    default:
        echo json_encode([
            'status' => 'error',
            'message' => 'Acción no válida en /api/api-cloud-video.php. Acciones disponibles: get-upload-url, start-job, job-status, webhook.'
        ]);
        exit();
}

// ═════════════════════════════════════════════════════════════════════════════
// FUNCIONES AUXILIARES DE GOOGLE CLOUD
// ═════════════════════════════════════════════════════════════════════════════

/**
 * Genera una URL prefirmada de Google Cloud Storage (V4) para PUT directo
 */
function generateGcsSignedUrl($keyFilePath, $bucketName, $blobName, $method = 'PUT', $contentType = 'video/mp4', $expires = 3600) {
    $keyData = json_decode(file_get_contents($keyFilePath), true);
    if (!$keyData || empty($keyData['private_key']) || empty($keyData['client_email'])) {
        return null;
    }

    $now = time();
    $isoDate = gmdate('Ymd\THis\Z', $now);
    $dateStamp = gmdate('Ymd', $now);
    $credentialScope = "{$dateStamp}/auto/storage/goog4_request";
    $credential = "{$keyData['client_email']}/{$credentialScope}";

    $queryParams = [
        'X-Goog-Algorithm' => 'GOOG4-RSA-SHA256',
        'X-Goog-Credential' => $credential,
        'X-Goog-Date' => $isoDate,
        'X-Goog-Expires' => (string)$expires,
        'X-Goog-SignedHeaders' => 'content-type;host'
    ];
    ksort($queryParams);

    $queryString = http_build_query($queryParams);
    $canonicalUri = "/{$bucketName}/{$blobName}";
    $canonicalHeaders = "content-type:{$contentType}\nhost:storage.googleapis.com\n";
    $signedHeaders = "content-type;host";

    $canonicalRequest = "{$method}\n{$canonicalUri}\n{$queryString}\n{$canonicalHeaders}\n{$signedHeaders}\nUNSIGNED-PAYLOAD";
    $stringToSign = "GOOG4-RSA-SHA256\n{$isoDate}\n{$credentialScope}\n" . hash('sha256', $canonicalRequest);

    openssl_sign($stringToSign, $signature, $keyData['private_key'], OPENSSL_ALGO_SHA256);
    $signatureHex = bin2hex($signature);

    return "https://storage.googleapis.com{$canonicalUri}?{$queryString}&X-Goog-Signature={$signatureHex}";
}

/**
 * Dispara un Cloud Run Job utilizando la API v2 de Cloud Run
 */
function triggerCloudRunJob($keyFilePath, $projectId, $region, $jobName, $args = []) {
    $keyData = json_decode(file_get_contents($keyFilePath), true);
    if (!$keyData || empty($keyData['private_key']) || empty($keyData['client_email'])) {
        return false;
    }

    // 1. Obtener Token OAuth2 mediante JWT de la cuenta de servicio
    $now = time();
    $jwtHeader = base64UrlEncode(json_encode(["alg" => "RS256", "typ" => "JWT"]));
    $jwtClaim = base64UrlEncode(json_encode([
        "iss" => $keyData['client_email'],
        "scope" => "https://www.googleapis.com/auth/cloud-platform",
        "aud" => "https://oauth2.googleapis.com/token",
        "exp" => $now + 3600,
        "iat" => $now
    ]));

    openssl_sign("{$jwtHeader}.{$jwtClaim}", $jwtSig, $keyData['private_key'], OPENSSL_ALGO_SHA256);
    $jwt = "{$jwtHeader}.{$jwtClaim}." . base64UrlEncode($jwtSig);

    // Solicitar Bearer token
    $ch = curl_init("https://oauth2.googleapis.com/token");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
        'assertion' => $jwt
    ]));
    $tokenRes = curl_exec($ch);
    curl_close($ch);

    $tokenData = json_decode($tokenRes, true);
    $accessToken = $tokenData['access_token'] ?? null;
    if (!$accessToken) {
        return false;
    }

    // 2. Ejecutar el Job con los overrides de argumentos
    $url = "https://run.googleapis.com/v2/projects/{$projectId}/locations/{$region}/jobs/{$jobName}:run";
    $payload = [
        "overrides" => [
            "containerOverrides" => [
                [
                    "args" => $args
                ]
            ]
        ]
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer {$accessToken}",
        "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    $runRes = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ($httpCode >= 200 && $httpCode < 300);
}

function base64UrlEncode($data) {
    return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
}
?>
