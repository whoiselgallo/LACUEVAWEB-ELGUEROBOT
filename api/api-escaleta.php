<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(); }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status'=>'error','message'=>'Método no permitido']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    http_response_code(400);
    echo json_encode(['status'=>'error','message'=>'JSON inválido']);
    exit();
}

$campos = [
    'nombre','ocupacion','signo','fecha','barrio',
    'trayectoria','herida','incomodo','gustos'
];

$errores = [];
foreach ($campos as $c) {
    if (empty(trim($input[$c] ?? ''))) $errores[] = $c;
}

if ($errores) {
    http_response_code(400);
    echo json_encode(['status'=>'error','message'=>'Faltan campos: '.implode(', ',$errores)]);
    exit();
}

$datos = [];
foreach ($campos as $c) {
    $datos[$c] = htmlspecialchars(trim($input[$c]), ENT_QUOTES, 'UTF-8');
}

require_once __DIR__ . '/../config/config.php';

$prompt = "
Eres Güero Bot. Genera ESCALETA, GUION y CUE CARDS.

Datos del invitado:
Nombre: {$datos['nombre']}
Ocupación: {$datos['ocupacion']}
Signo: {$datos['signo']}
Fecha: {$datos['fecha']}
Barrio: {$datos['barrio']}
Trayectoria: {$datos['trayectoria']}
Herida: {$datos['herida']}
Qué le incomoda: {$datos['incomodo']}
Gustos: {$datos['gustos']}

===ESCALETA===
Genera la escaleta completa.

===GUION===
Genera diálogos entre JUNIOR y el invitado.

===CUE_CARDS===
Genera lista de frases clave.
";

$payload = [
    'inputs'=>new stdClass(),
    'query'=>$prompt,
    'response_mode'=>'blocking',
    'user'=>'la-cueva-web'
];

$ch = curl_init(DIFY_URL);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer '.DIFY_API_KEY,
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_POST,1);
curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode($payload));
curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
curl_setopt($ch, CURLOPT_TIMEOUT,60);

$response = curl_exec($ch);
$http = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if (curl_errno($ch)) {
    http_response_code(500);
    echo json_encode(['status'=>'error','message'=>curl_error($ch)]);
    curl_close($ch);
    exit();
}
curl_close($ch);

if ($http !== 200) {
    http_response_code(500);
    echo json_encode(['status'=>'error','message'=>$response]);
    exit();
}

$dify = json_decode($response,true);
$texto = $dify['answer'] ?? '';

preg_match('/===ESCALETA===(.*?)===GUION===/s',$texto,$m1);
preg_match('/===GUION===(.*?)===CUE_CARDS===/s',$texto,$m2);
preg_match('/===CUE_CARDS===(.*)/s',$texto,$m3);

echo json_encode([
    'status'=>'success',
    'escaleta'=>trim($m1[1] ?? ''),
    'guion'=>trim($m2[1] ?? ''),
    'cue_cards'=>trim($m3[1] ?? '')
]);
exit();
