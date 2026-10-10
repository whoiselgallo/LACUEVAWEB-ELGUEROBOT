<?php
/**
 * ═════════════════════════════════════════════════════════════════════════════════
 * API - Webhook & Notificaciones en Tiempo Real (La Cueva del Güero)
 * Endpoint: /api/api-webhook.php
 * ═════════════════════════════════════════════════════════════════════════════════
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    if (file_exists(__DIR__ . '/../config/config.php')) {
        @include_once __DIR__ . '/../config/config.php';
    }
} catch (\Throwable $e) {
    // Si config.php arroja RuntimeException por variables de Dify, no bloquear el servicio de webhooks
    error_log("[api-webhook] Config notice: " . $e->getMessage());
}

$storageDir = __DIR__ . '/../images/formularios';
if (!is_dir($storageDir)) {
    @mkdir($storageDir, 0777, true);
}
$eventosFile = $storageDir . '/eventos_webhook.json';
$configFile = $storageDir . '/webhook_config.json';

// Leer configuración del Webhook
function obtenerWebhookConfig() {
    global $configFile;
    $default = [
        'webhook_url' => getenv('WEBHOOK_NOTIFICACIONES_URL') ?: '',
        'discord_webhook' => getenv('DISCORD_WEBHOOK_URL') ?: '',
        'telegram_bot_token' => getenv('TELEGRAM_BOT_TOKEN') ?: '',
        'telegram_chat_id' => getenv('TELEGRAM_CHAT_ID') ?: '',
        'activo' => true
    ];
    if (file_exists($configFile)) {
        $saved = json_decode(file_get_contents($configFile), true);
        if (is_array($saved)) {
            return array_merge($default, $saved);
        }
    }
    return $default;
}

// Guardar configuración del Webhook
function guardarWebhookConfig($data) {
    global $configFile;
    $current = obtenerWebhookConfig();
    $updated = array_merge($current, $data);
    @file_put_contents($configFile, json_encode($updated, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    return $updated;
}

// Leer historial de eventos
function obtenerEventos($limit = 50) {
    global $eventosFile;
    if (!file_exists($eventosFile)) {
        return [];
    }
    $content = json_decode(file_get_contents($eventosFile), true);
    if (!is_array($content)) {
        return [];
    }
    return array_slice($content, 0, $limit);
}

// Guardar un evento en el log local y disparar webhook
function dispararWebhook($tipo, $datos = [], $origen = 'sistema') {
    global $eventosFile;

    $eventoId = 'EVT-' . date('YmdHis') . '-' . mt_rand(100, 999);
    $timestamp = date('Y-m-d H:i:s');

    // Mapeo amigable de títulos y colores según tipo de evento
    $metaEventos = [
        'entrevista_completada' => [
            'titulo' => '🎉 Entrevista Completada y Confirmada',
            'icono' => 'fa-clipboard-check',
            'color' => '#39FF14',
            'discord_color' => 3800852
        ],
        'entrevista_error_reportado' => [
            'titulo' => '⚠️ Respuesta Modificada / Error Corregido',
            'icono' => 'fa-pen-to-square',
            'color' => '#FFA500',
            'discord_color' => 16753920
        ],
        'tracking_actualizado' => [
            'titulo' => '🎯 Avance de Capítulo Actualizado',
            'icono' => 'fa-satellite-dish',
            'color' => '#00FFFF',
            'discord_color' => 65535
        ],
        'task_kanban' => [
            'titulo' => '📋 Tarea de Producción Actualizada (Kanban)',
            'icono' => 'fa-list-check',
            'color' => '#FF00FF',
            'discord_color' => 16711935
        ],
        'blog_publicado' => [
            'titulo' => '📰 Nuevo Artículo Publicado en Blog',
            'icono' => 'fa-newspaper',
            'color' => '#00E5FF',
            'discord_color' => 58879
        ],
        'analitica_actualizada' => [
            'titulo' => '📊 Métricas / Analítica de Redes Sincronizada',
            'icono' => 'fa-chart-line',
            'color' => '#39FF14',
            'discord_color' => 3800852
        ],
        'decision_votada' => [
            'titulo' => '🗳️ Decisión de Equipo Votada',
            'icono' => 'fa-check-double',
            'color' => '#FFD700',
            'discord_color' => 16766720
        ],
        'solicitud_correccion_enviada' => [
            'titulo' => '⚠️ Solicitud de Corrección Enviada a Invitado',
            'icono' => 'fa-triangle-exclamation',
            'color' => '#FF6600',
            'discord_color' => 16737792
        ],
        'correccion_invitado_enviada' => [
            'titulo' => '✍️ Nuevas Respuestas Enviadas por Invitado',
            'icono' => 'fa-pen-nib',
            'color' => '#00FFFF',
            'discord_color' => 65535
        ],
        'guion_narrativa_actualizada' => [
            'titulo' => '🎬 Guión, Escaleta y Narrativa Actualizados',
            'icono' => 'fa-file-lines',
            'color' => '#FF00FF',
            'discord_color' => 16711935
        ],
        'cuestionario_aprobado_produccion' => [
            'titulo' => '✅ Cuestionario Aprobado por Producción',
            'icono' => 'fa-circle-check',
            'color' => '#39FF14',
            'discord_color' => 3800852
        ]
    ];

    $meta = $metaEventos[$tipo] ?? [
        'titulo' => '📢 Aviso del Sistema',
        'icono' => 'fa-bell',
        'color' => '#00FFFF',
        'discord_color' => 65535
    ];

    $registroEvento = [
        'id' => $eventoId,
        'tipo' => $tipo,
        'titulo' => $meta['titulo'],
        'icono' => $meta['icono'],
        'color' => $meta['color'],
        'timestamp' => $timestamp,
        'origen' => $origen,
        'datos' => $datos,
        'leido' => false
    ];

    // 1. Guardar en JSON persistente
    $eventos = obtenerEventos(150);
    array_unshift($eventos, $registroEvento);
    if (count($eventos) > 100) {
        $eventos = array_slice($eventos, 0, 100);
    }
    @file_put_contents($eventosFile, json_encode($eventos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    // 2. Disparar a webhook externo si está configurado
    $config = obtenerWebhookConfig();
    $webhookUrl = !empty($config['webhook_url']) ? $config['webhook_url'] : (!empty($config['discord_webhook']) ? $config['discord_webhook'] : '');

    $resultadoEnvio = 'no_configurado';
    if (!empty($webhookUrl) && !empty($config['activo'])) {
        $resultadoEnvio = enviarWebhookExterno($webhookUrl, $registroEvento, $meta);
    }

    return [
        'success' => true,
        'evento' => $registroEvento,
        'webhook_status' => $resultadoEnvio
    ];
}

// Envío HTTP del Webhook (Discord / Slack / Generic)
function enviarWebhookExterno($url, $evento, $meta) {
    $isDiscord = (strpos($url, 'discord.com/api/webhooks') !== false);
    $isSlack = (strpos($url, 'hooks.slack.com') !== false);

    $payload = [];

    if ($isDiscord) {
        $fields = [];
        if (!empty($evento['datos'])) {
            foreach ($evento['datos'] as $k => $v) {
                if (is_scalar($v) && !empty($v)) {
                    $fields[] = [
                        'name' => ucfirst(str_replace('_', ' ', $k)),
                        'value' => mb_substr(strval($v), 0, 1000),
                        'inline' => true
                    ];
                }
            }
        }

        $payload = [
            'username' => 'El Güero Bot 🐾 (La Cueva Web)',
            'avatar_url' => 'https://lacuevadelguero.com/images/logotipo.png',
            'embeds' => [
                [
                    'title' => $evento['titulo'],
                    'description' => "Se ha registrado una nueva actividad en vivo en **La Cueva del Güero**.",
                    'color' => $meta['discord_color'] ?? 65535,
                    'fields' => $fields,
                    'footer' => [
                        'text' => "ID: {$evento['id']} • Origen: {$evento['origen']}"
                    ],
                    'timestamp' => gmdate('Y-m-d\TH:i:s\Z')
                ]
            ]
        ];
    } elseif ($isSlack) {
        $textDetails = "";
        if (!empty($evento['datos'])) {
            foreach ($evento['datos'] as $k => $v) {
                if (is_scalar($v) && !empty($v)) {
                    $textDetails .= "\n• *" . ucfirst(str_replace('_', ' ', $k)) . ":* " . mb_substr(strval($v), 0, 300);
                }
            }
        }
        $payload = [
            'text' => "🔔 *{$evento['titulo']}*\n" . $textDetails
        ];
    } else {
        // Genérico JSON (Make, Zapier, n8n, etc.)
        $payload = [
            'evento' => $evento['tipo'],
            'titulo' => $evento['titulo'],
            'id' => $evento['id'],
            'timestamp' => $evento['timestamp'],
            'origen' => $evento['origen'],
            'datos' => $evento['datos']
        ];
    }

    $startTime = microtime(true);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, JSON_UNESCAPED_UNICODE));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'User-Agent: LaCuevaDelGuero-Webhook/2.0'
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 6);
    $res = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $latencyMs = round((microtime(true) - $startTime) * 1000);
    $curlErr = curl_error($ch);
    curl_close($ch);

    $isOk = ($httpCode >= 200 && $httpCode < 300);
    return [
        'success' => $isOk,
        'code' => $httpCode,
        'latency_ms' => $latencyMs,
        'status' => $isOk ? 'enviado_ok' : ($curlErr ? "error_curl_{$curlErr}" : "error_http_{$httpCode}"),
        'response' => mb_substr($res ?: $curlErr, 0, 500),
        'payload' => $payload
    ];
}

// ═════════════════════════════════════════════════════════════════════════════════
// MANEJO DE PETICIONES HTTP DIRECTAS
// ═════════════════════════════════════════════════════════════════════════════════
$isDirectWebhookCall = (isset($_SERVER['SCRIPT_FILENAME']) && realpath(__FILE__) === realpath($_SERVER['SCRIPT_FILENAME']))
    || (isset($_SERVER['REQUEST_URI']) && stripos($_SERVER['REQUEST_URI'], 'api-webhook.php') !== false);

if ($isDirectWebhookCall) {
    try {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $action = $_GET['action'] ?? '';

        $rawInput = file_get_contents('php://input');
        $input = json_decode($rawInput, true) ?: $_POST;
        if (empty($action) && isset($input['action'])) {
            $action = $input['action'];
        }

        // 1. LISTAR EVENTOS
        if ($action === 'listar' || ($method === 'GET' && empty($action))) {
            $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 50;
            $eventos = obtenerEventos($limit);
            echo json_encode([
                'status' => 'success',
                'total' => count($eventos),
                'eventos' => $eventos
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // 2. DISPARAR EVENTO (POST)
        if ($action === 'disparar' || $action === 'trigger') {
            $tipo = $input['tipo'] ?? ($input['evento'] ?? 'aviso_general');
            $datos = $input['datos'] ?? [];
            $origen = $input['origen'] ?? 'cliente_web';

            $resultado = dispararWebhook($tipo, $datos, $origen);
            echo json_encode($resultado, JSON_UNESCAPED_UNICODE);
            exit;
        }

        // 3. OBTENER CONFIGURACIÓN DEL WEBHOOK
        if ($action === 'obtener_config') {
            $config = obtenerWebhookConfig();
            // Ocultar caracteres sensibles si no es admin estricto
            echo json_encode([
                'status' => 'success',
                'config' => $config
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // 4. GUARDAR CONFIGURACIÓN DEL WEBHOOK
        if ($action === 'guardar_config') {
            $webhookUrl = trim($input['webhook_url'] ?? '');
            $activo = isset($input['activo']) ? boolval($input['activo']) : true;

            $updated = guardarWebhookConfig([
                'webhook_url' => $webhookUrl,
                'activo' => $activo
            ]);

            // Probar envío de test si se ingresó una URL
            $testResult = 'no_probado';
            if (!empty($webhookUrl)) {
                $meta = [
                    'titulo' => '🧪 Webhook de Prueba - La Cueva del Güero',
                    'discord_color' => 65535
                ];
                $testResult = enviarWebhookExterno($webhookUrl, [
                    'id' => 'TEST-' . time(),
                    'tipo' => 'test_webhook',
                    'titulo' => '🧪 Webhook de Prueba - La Cueva del Güero',
                    'origen' => 'dashboard_admin',
                    'datos' => [
                        'mensaje' => 'Conexión verificada exitosamente entre La Cueva del Güero y tu canal de avisos en tiempo real.',
                        'fecha' => date('Y-m-d H:i:s')
                    ]
                ], $meta);
            }

            echo json_encode([
                'status' => 'success',
                'message' => 'Configuración de webhook actualizada correctamente.',
                'config' => $updated,
                'test_result' => $testResult
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // 5. MARCAR EVENTOS COMO LEÍDOS
        if ($action === 'marcar_leidos') {
            global $eventosFile;
            if (file_exists($eventosFile)) {
                $eventos = json_decode(file_get_contents($eventosFile), true);
                if (is_array($eventos)) {
                    foreach ($eventos as &$ev) {
                        $ev['leido'] = true;
                    }
                    @file_put_contents($eventosFile, json_encode($eventos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                }
            }
            echo json_encode(['status' => 'success', 'message' => 'Eventos marcados como leídos'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // 6. REENVIAR EVENTO ESPECÍFICO (1-CLICK REPLAY)
        if ($action === 'reenviar') {
            $eventoId = $input['evento_id'] ?? '';
            $eventos = obtenerEventos(150);
            $encontrado = null;
            foreach ($eventos as $ev) {
                if ($ev['id'] === $eventoId) {
                    $encontrado = $ev;
                    break;
                }
            }
            if (!$encontrado) {
                http_response_code(404);
                echo json_encode(['status' => 'error', 'error' => 'Evento no encontrado'], JSON_UNESCAPED_UNICODE);
                exit;
            }
            $config = obtenerWebhookConfig();
            $webhookUrl = !empty($config['webhook_url']) ? $config['webhook_url'] : (!empty($config['discord_webhook']) ? $config['discord_webhook'] : '');
            if (empty($webhookUrl)) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'error' => 'No hay webhook configurado actualmente'], JSON_UNESCAPED_UNICODE);
                exit;
            }
            $meta = [
                'titulo' => $encontrado['titulo'] . ' [Reenviado]',
                'discord_color' => 65535
            ];
            $envio = enviarWebhookExterno($webhookUrl, $encontrado, $meta);
            echo json_encode([
                'status' => 'success',
                'message' => 'Evento reenviado al webhook.',
                'telemetry' => $envio
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        echo json_encode(['status' => 'error', 'error' => 'Acción no válida'], JSON_UNESCAPED_UNICODE);
        exit;
    } catch (\Throwable $e) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'error' => $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}
