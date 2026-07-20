<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD']==='OPTIONS'){ http_response_code(200); exit(); }
if ($_SERVER['REQUEST_METHOD']!=='POST'){
    http_response_code(405);
    echo json_encode(['status'=>'error','message'=>'Método no permitido']);
    exit();
}

$input=json_decode(file_get_contents('php://input'),true);
$invitado=trim($input['invitado']??'');
$tarjetas=$input['tarjetas']??[];

if($invitado==='' || !is_array($tarjetas) || empty($tarjetas)){
    http_response_code(400);
    echo json_encode(['status'=>'error','message'=>'Faltan datos']);
    exit();
}

require_once __DIR__ . '/../config/config.php';

$prompt="
Genera TARJETAS DE CONDUCCIÓN HTML.

Invitado: $invitado
Tarjetas:
".json_encode($tarjetas,JSON_PRETTY_PRINT)."

Output:
- HTML completo con <style>
- Botón imprimir
";

$payload=[
    'inputs'=>new stdClass(),
    'query'=>$prompt,
    'response_mode'=>'blocking',
    'user'=>'cuecards-web'
];

$ch=curl_init(DIFY_URL);
curl_setopt($ch,CURLOPT_HTTPHEADER,[
    'Authorization: Bearer '.DIFY_API_KEY,
    'Content-Type: application/json'
]);
curl_setopt($ch,CURLOPT_POST,1);
curl_setopt($ch,CURLOPT_POSTFIELDS,json_encode($payload));
curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);

$response=curl_exec($ch);
$http=curl_getinfo($ch,CURLINFO_HTTP_CODE);

if(curl_errno($ch)){
    http_response_code(500);
    echo json_encode(['status'=>'error','message'=>curl_error($ch)]);
    curl_close($ch);
    exit();
}
curl_close($ch);

if($http!==200){
    http_response_code(500);
    echo json_encode(['status'=>'error','message'=>$response]);
    exit();
}

$dify=json_decode($response,true);
$html=$dify['answer']??'';

echo json_encode(['status'=>'success','html'=>$html]);
exit();
