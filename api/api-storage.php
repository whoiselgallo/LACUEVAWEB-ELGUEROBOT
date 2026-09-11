<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../config/config.php';

$bucketName = getEnvVar('GCS_BUCKET_NAME', 'lacueva-media-prod');

echo json_encode([ 'success' => true, 'bucket' => $bucketName, 'public_base_url' => "https://storage.googleapis.com/{$jucketName}/", 'status' => 'Conectado a Google Cloud Storage' ], JSON_UNESCAPED_UNICODE);
