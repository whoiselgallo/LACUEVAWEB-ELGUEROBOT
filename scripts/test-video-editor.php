<?php
/**
 * Test Suite Integral para el Módulo de Video Editor (La Cueva del Güero)
 * Evalúa:
 * 1. Endpoint api-cloud-video.php (despacho de job, estado y SSE)
 * 2. Estructura y sintaxis de video-editor.js y ffmpeg-wasm-helper.js
 * 3. Elementos del DOM en dashboard/index.php (Timeline multicapa, waveform, marcadores)
 * 4. Sintaxis y rutinas en worker.py (Karaoke Whisper y MediaPipe Face Tracking)
 */

$results = [
    'total' => 0,
    'passed' => 0,
    'failed' => 0,
    'tests' => []
];

function assert_test($name, $condition, $details = '') {
    global $results;
    $results['total']++;
    if ($condition) {
        $results['passed']++;
        $results['tests'][] = ['name' => $name, 'status' => 'PASS', 'details' => $details];
        echo "✅ PASS: $name\n";
    } else {
        $results['failed']++;
        $results['tests'][] = ['name' => $name, 'status' => 'FAIL', 'details' => $details];
        echo "❌ FAIL: $name - $details\n";
    }
}

echo "============================================================\n";
echo "🎬 INICIANDO SUITE DE PRUEBAS DEL EDITOR DE VIDEO\n";
echo "============================================================\n\n";

// TEST 1: Sintaxis de scripts backend
$syntaxCloudVideo = shell_exec('php -l api/api-cloud-video.php 2>&1');
assert_test("Sintaxis PHP de api/api-cloud-video.php", strpos($syntaxCloudVideo, 'No syntax errors') !== false, $syntaxCloudVideo);

// TEST 2: Presencia de archivos JS del editor
assert_test("Existencia de js/video-editor.js", file_exists('js/video-editor.js'));
assert_test("Existencia de js/ffmpeg-wasm-helper.js", file_exists('js/ffmpeg-wasm-helper.js'));

// TEST 3: Funciones clave en video-editor.js
$editorJs = file_get_contents('js/video-editor.js');
assert_test("SSE EventSource implementado en video-editor.js", strpos($editorJs, 'new EventSource') !== false);
assert_test("Integración de WaveSurfer en video-editor.js", strpos($editorJs, 'WaveSurfer.create') !== false);
assert_test("Salto a marcadores virales en video-editor.js", strpos($editorJs, 'saltarAMarcador') !== false);
assert_test("Recorte local con FFmpeg WASM en video-editor.js", strpos($editorJs, 'recortarClipLocalmente') !== false);
assert_test("Drag and Drop en timeline implementado", strpos($editorJs, 'initTimelineDragAndDrop') !== false);

// TEST 4: Verificación del DOM en dashboard/index.php
$dashboardHtml = file_get_contents('dashboard/index.php');
assert_test("Contenedor de Waveform en timeline (#waveform)", strpos($dashboardHtml, 'id="waveform"') !== false);
assert_test("Pista de marcadores virales en timeline (#viral-markers-track)", strpos($dashboardHtml, 'id="viral-markers-track"') !== false);
assert_test("Pista de video draggable (#track-video)", strpos($dashboardHtml, 'id="track-video"') !== false);
assert_test("Pista de efectos y música (#track-fx)", strpos($dashboardHtml, 'id="track-fx"') !== false);
assert_test("Inclusión de WaveSurfer.js vía CDN", strpos($dashboardHtml, 'wavesurfer.min.js') !== false);
assert_test("Inclusión de ffmpeg-wasm-helper.js", strpos($dashboardHtml, 'ffmpeg-wasm-helper.js') !== false);

// TEST 5: Verificación de worker.py (Pipeline IA)
$workerPy = file_get_contents('docker/video-worker/worker.py');
assert_test("Rutina Whisper Karaoke en worker.py", strpos($workerPy, 'action_auto_subtitles_karaoke_whisper') !== false);
assert_test("Rutina MediaPipe AutoCrop en worker.py", strpos($workerPy, 'action_smart_autocrop_mediapipe') !== false);
assert_test("Alineación forzada por palabra (word_timestamps)", strpos($workerPy, 'word_timestamps=True') !== false);

// TEST 6: Simulación de despacho de Job y SSE en api-cloud-video.php
$testJobId = 'test_job_' . time();
$jobsDir = __DIR__ . '/../uploads/jobs';
if (!is_dir($jobsDir)) {
    mkdir($jobsDir, 0777, true);
}
$mockJobData = [
    'job_id' => $testJobId,
    'status' => 'completed',
    'action' => 'test-render',
    'result_file' => 'test_output.mp4',
    'logs' => [
        '[Cloud Run] Iniciando contenedor...',
        '[FFmpeg] Normalización completada.',
        '[Completado] Archivo final listo'
    ]
];
file_put_contents("$jobsDir/$testJobId.json", json_encode($mockJobData));

assert_test("Persistencia de Job Meta en uploads/jobs", file_exists("$jobsDir/$testJobId.json"));

// Ejecución local de action=job-status
$_GET['action'] = 'job-status';
$_GET['job_id'] = $testJobId;
ob_start();
include __DIR__ . '/../api/api-cloud-video.php';
$outputStatus = ob_get_clean();
$statusJson = json_decode($outputStatus, true);
assert_test("Endpoint action=job-status responde correctamente", isset($statusJson['status']) && $statusJson['status'] === 'success');

// Limpieza del archivo de test
@unlink("$jobsDir/$testJobId.json");

echo "\n============================================================\n";
echo "📊 RESULTADOS DE LA SUITE:\n";
echo "Total de pruebas ejecutadas: {$results['total']}\n";
echo "Pruebas superadas (PASS): {$results['passed']}\n";
echo "Pruebas fallidas (FAIL): {$results['failed']}\n";
echo "Tasa de éxito: " . round(($results['passed'] / $results['total']) * 100, 2) . "%\n";
echo "============================================================\n";
