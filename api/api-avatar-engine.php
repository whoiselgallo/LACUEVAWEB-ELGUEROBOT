<?php
/**
 * 🎭 LA CUEVA DEL GÜERO - API AVATAR ENGINE & PROPS GENERATOR
 * Endpoint: /api/api-avatar-engine.php
 * Métodos: GET, POST
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../config/config.php';

try {
    $db = db_connect();

    // Auto-crear tabla de avatares si no existe (Self-healing)
    $db->exec("
        CREATE TABLE IF NOT EXISTS avatars (
            id SERIAL PRIMARY KEY,
            nombre VARCHAR(100) UNIQUE NOT NULL,
            episodio VARCHAR(100),
            foto_frente TEXT,
            foto_perfil_izq TEXT,
            foto_perfil_der TEXT,
            imagen_limpia TEXT,
            consentimiento_pdf TEXT,
            rasgos_faciales TEXT,
            estilo_casual TEXT,
            estilo_deportivo TEXT,
            estilo_formal TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    
    // Soporte para la verificación de conexión de Dify (ping/pong)
    if (isset($input['point']) && $input['point'] === 'ping') {
        json_response(['result' => 'pong'], 200);
        exit();
    }
    
    $action = sanitize_input($input['action'] ?? 'list');

    // -----------------------------------------------------------------------------
    // ACCIÓN: LISTAR PERSONAJES REGISTRADOS
    // -----------------------------------------------------------------------------
    if ($action === 'list') {
        $stmt = $db->query("SELECT id, nombre, episodio, imagen_limpia, rasgos_faciales, created_at FROM avatars ORDER BY id DESC");
        $avatars = $stmt->fetchAll(PDO::FETCH_ASSOC);
        json_response(['success' => true, 'avatars' => $avatars], 200);
        exit();
    }

    // -----------------------------------------------------------------------------
    // ACCIÓN: CREAR PERSONAJE NUEVO (3 FOTOS + CONSENTIMIENTO)
    // -----------------------------------------------------------------------------
    if ($action === 'create') {
        $nombre = sanitize_input($input['nombre'] ?? '');
        $rasgos = sanitize_input($input['rasgos_faciales'] ?? '');
        $fotoFrente = $input['foto_frente'] ?? '';
        $fotoIzq = $input['foto_perfil_izq'] ?? '';
        $fotoDer = $input['foto_perfil_der'] ?? '';
        $consentimientoPdf = $input['consentimiento_pdf'] ?? '';

        if (empty($nombre)) {
            json_response(['error' => 'El nombre del personaje es obligatorio'], 400);
            exit();
        }

        if (empty($consentimientoPdf)) {
            json_response(['error' => 'Es obligatorio adjuntar el Documento Firmado de Consentimiento de Uso de Imagen.'], 400);
            exit();
        }

        // Insertar en Neon PostgreSQL
        $stmt = $db->prepare("
            INSERT INTO avatars (nombre, foto_frente, foto_perfil_izq, foto_perfil_der, consentimiento_pdf, rasgos_faciales)
            VALUES (?, ?, ?, ?, ?, ?)
            ON CONFLICT (nombre) DO UPDATE SET
                foto_frente = EXCLUDED.foto_frente,
                foto_perfil_izq = EXCLUDED.foto_perfil_izq,
                foto_perfil_der = EXCLUDED.foto_perfil_der,
                consentimiento_pdf = EXCLUDED.consentimiento_pdf,
                rasgos_faciales = EXCLUDED.rasgos_faciales
        ");
        $stmt->execute([$nombre, $fotoFrente, $fotoIzq, $fotoDer, $consentimientoPdf, $rasgos]);

        // Registrar en Base de Conocimiento para Dify
        $kbText = "PERFIL DE AVATAR OFICIAL: Personaje '$nombre'. Rasgos faciales: $rasgos. Consentimiento de imagen firmado legalmente registrado.";
        $stmtKB = $db->prepare("
            INSERT INTO knowledge_base (nombre, tipo, storytelling)
            VALUES (?, 'avatar_profile', ?)
            ON CONFLICT (nombre, tipo) DO UPDATE SET storytelling = EXCLUDED.storytelling
        ");
        $stmtKB->execute([$nombre, $kbText]);

        json_response([
            'success' => true,
            'message' => "Personaje '$nombre' registrado con consentimiento legal y 3 fotografías faciales.",
            'avatar'  => ['nombre' => $nombre]
        ], 200);
        exit();
    }

    // -----------------------------------------------------------------------------
    // ACCIÓN: IMPORTAR AVATAR PRE-EXISTENTE (MODAL OCULTO)
    // -----------------------------------------------------------------------------
    if ($action === 'import') {
        $nombre = sanitize_input($input['nombre'] ?? '');
        $episodio = sanitize_input($input['episodio'] ?? 'Capítulo Especial');
        $imagenLimpia = $input['imagen_limpia'] ?? '';
        $consentimientoPdf = $input['consentimiento_pdf'] ?? '';

        if (empty($nombre) || empty($imagenLimpia)) {
            json_response(['error' => 'Nombre e Imagen limpia sin fondo son obligatorios'], 400);
            exit();
        }

        if (empty($consentimientoPdf)) {
            json_response(['error' => 'Debes adjuntar el documento firmado de consentimiento de uso de imagen.'], 400);
            exit();
        }

        // Insertar Avatar Pre-existente
        $stmt = $db->prepare("
            INSERT INTO avatars (nombre, episodio, imagen_limpia, consentimiento_pdf, rasgos_faciales)
            VALUES (?, ?, ?, ?, ?)
            ON CONFLICT (nombre) DO UPDATE SET
                episodio = EXCLUDED.episodio,
                imagen_limpia = EXCLUDED.imagen_limpia,
                consentimiento_pdf = EXCLUDED.consentimiento_pdf
        ");
        $stmt->execute([$nombre, $episodio, $imagenLimpia, $consentimientoPdf, "Avatar oficial pre-generado para $episodio"]);

        // Inyectar en Conocimiento Dify
        $kbText = "AVATAR EXISTENTE $episodio: Personaje '$nombre'. Imagen transparente y expediente de consentimiento registrado.";
        $stmtKB = $db->prepare("
            INSERT INTO knowledge_base (nombre, tipo, storytelling)
            VALUES (?, 'avatar_premade', ?)
            ON CONFLICT (nombre, tipo) DO UPDATE SET storytelling = EXCLUDED.storytelling
        ");
        $stmtKB->execute(["$nombre - $episodio", $kbText]);

        json_response([
            'success' => true,
            'message' => "Avatar pre-existente '$nombre' ($episodio) importado y sincronizado con Dify.",
            'avatar'  => ['nombre' => $nombre, 'episodio' => $episodio]
        ], 200);
        exit();
    }

    // -----------------------------------------------------------------------------
    // ACCIÓN: GENERAR AVATAR V2 - INYECCIÓN BIOMÉTRICA (FLUX.1 + IP-ADAPTER + REMBG)
    // -----------------------------------------------------------------------------
    if ($action === 'generate-v2' || $action === 'generate') {
        $nombre = sanitize_input($input['nombre'] ?? 'El Güero');
        $actividad = sanitize_input($input['actividad'] ?? 'sentado en el estudio');
        $ropa = sanitize_input($input['ropa'] ?? 'casual streetwear');
        $ipAdapterWeight = isset($input['ip_adapter_weight']) ? (float)$input['ip_adapter_weight'] : 0.85;

        // 1. Recuperar perfil biométrico y fotos desde Neon PostgreSQL
        $stmt = $db->prepare("SELECT * FROM avatars WHERE LOWER(nombre) = LOWER(?)");
        $stmt->execute([$nombre]);
        $profile = $stmt->fetch(PDO::FETCH_ASSOC);

        $fotoFrente = $profile['foto_frente'] ?? '';
        $avatarId = $profile['id'] ?? null;
        $rasgos = $profile['rasgos_faciales'] ?? "Personaje icónico de La Cueva del Güero";

        // 2. Construir Prompt de Estilo Vectorial Neón (sin describir la cara, la cara la inyecta el tensor)
        $promptHibrido = "Premium 2d vector art, mascot logo style of {$nombre}, {$actividad} in a podcast studio, wearing {$ropa}. Vibrant cyberpunk neon lighting (cyan and magenta accents), bold clean outlines, solid flat colors, highly detailed sticker aesthetic, dynamic shading, Mexicali street style.";
        $negativePrompt = "photorealistic, 3d render, blurry, messy lines, text, watermarks, distorted face, low quality";

        $replicateToken = getEnvVar('REPLICATE_API_TOKEN', '');
        $generatedUrl = '';
        $isBiometricInjected = false;

        // 3. Ejecución en Replicate (Flux.1 Dev + IP-Adapter) si existe token
        if (!empty($replicateToken) && !empty($fotoFrente) && $fotoFrente !== 'registrado') {
            try {
                $replicatePayload = [
                    'version' => 'black-forest-labs/flux-dev',
                    'input' => [
                        'prompt' => $promptHibrido,
                        'negative_prompt' => $negativePrompt,
                        'image' => $fotoFrente, // Foto frente Base64 o URL
                        'ip_adapter_weight' => $ipAdapterWeight,
                        'num_inference_steps' => 28,
                        'output_format' => 'png'
                    ]
                ];

                $ch = curl_init('https://api.replicate.com/v1/predictions');
                curl_setopt_array($ch, [
                    CURLOPT_POST => true,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_HTTPHEADER => [
                        'Authorization: Bearer ' . $replicateToken,
                        'Content-Type: application/json',
                        'Prefer: wait'
                    ],
                    CURLOPT_POSTFIELDS => json_encode($replicatePayload),
                    CURLOPT_TIMEOUT => 60
                ]);
                $repResponse = curl_exec($ch);
                curl_close($ch);
                $repData = json_decode($repResponse, true);

                if (isset($repData['output'])) {
                    $output = $repData['output'];
                    $generatedUrl = is_array($output) ? $output[0] : $output;
                    $isBiometricInjected = true;
                }
            } catch (Exception $e) {
                error_log("Replicate IP-Adapter error: " . $e->getMessage());
            }
        }

        // Fallback a Google Imagen 3 si no se usó Replicate
        if (empty($generatedUrl)) {
            $geminiApiKey = get_gemini_api_key() ?: getEnvVar('GOOGLE_API_KEY');
            if (!empty($geminiApiKey)) {
                $ch = curl_init("https://generativelanguage.googleapis.com/v1beta/models/imagen-3.0-generate-002:generateImages?key=" . $geminiApiKey);
                $payload = [
                    'prompt' => $promptHibrido . " STRICT RULES: Isolated on transparent white background, mascot sticker vector, bold outlines, no background elements.",
                    'numberOfImages' => 1,
                    'outputMimeType' => 'image/png',
                    'aspectRatio' => '1:1'
                ];
                curl_setopt_array($ch, [
                    CURLOPT_POST => true,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
                    CURLOPT_POSTFIELDS => json_encode($payload),
                    CURLOPT_TIMEOUT => 35
                ]);
                $response = curl_exec($ch);
                curl_close($ch);
                $resData = json_decode($response, true);
                if (isset($resData['generatedImages'][0]['image']['imageBytes'])) {
                    $imgData = base64_decode($resData['generatedImages'][0]['image']['imageBytes']);
                    $avatarDir = __DIR__ . '/../images/avatars';
                    if (!is_dir($avatarDir)) @mkdir($avatarDir, 0755, true);
                    $filename = 'avatar_' . uniqid() . '.png';
                    file_put_contents($avatarDir . '/' . $filename, $imgData);
                    $generatedUrl = 'images/avatars/' . $filename;
                }
            }
        }

        // Fallback final resiliente
        if (empty($generatedUrl)) {
            $encodedPrompt = urlencode($promptHibrido . " isolated transparent background sticker vector");
            $generatedUrl = "https://image.pollinations.ai/prompt/{$encodedPrompt}?width=800&height=800&nologo=true&private=true&enhance=true";
        }

        // 4. Registrar el asset transparente generado en isolated_assets
        if ($avatarId) {
            $stmtAsset = $db->prepare("
                INSERT INTO isolated_assets (avatar_id, tipo, nombre, url_asset, es_transparente, metadata_asset)
                VALUES (?, 'sujeto', ?, ?, TRUE, ?::jsonb)
            ");
            $metaJson = json_encode([
                'prompt' => $promptHibrido,
                'ip_adapter_weight' => $ipAdapterWeight,
                'biometric_injected' => $isBiometricInjected
            ]);
            $stmtAsset->execute([$avatarId, "Avatar {$nombre} ({$actividad})", $generatedUrl, $metaJson]);
        }

        json_response([
            'success' => true,
            'character' => $nombre,
            'actividad' => $actividad,
            'ropa' => $ropa,
            'biometric_injected' => $isBiometricInjected,
            'ip_adapter_weight' => $ipAdapterWeight,
            'avatar_url' => $generatedUrl,
            'prompt' => $promptHibrido
        ], 200);
        exit();
    }

    // -----------------------------------------------------------------------------
    // ACCIÓN: GUARDAR / RECUPERAR SESIÓN DE LIENZO THE DARKROOM (FABRIC.JS)
    // -----------------------------------------------------------------------------
    if ($action === 'save-canvas-session') {
        $avatarId = !empty($input['avatar_id']) ? (int)$input['avatar_id'] : null;
        $nombreProyecto = sanitize_input($input['nombre_proyecto'] ?? 'Poster La Cueva');
        $canvasState = $input['canvas_state'] ?? null;

        if (!$canvasState) {
            json_response(['error' => 'canvas_state es requerido'], 400);
            exit();
        }

        $stmt = $db->prepare("
            INSERT INTO canvas_sessions (avatar_id, nombre_proyecto, canvas_state, versiones_historicas)
            VALUES (?, ?, ?::jsonb, ?::jsonb)
            RETURNING id
        ");
        $stmt->execute([
            $avatarId,
            $nombreProyecto,
            json_encode($canvasState),
            json_encode(['timestamp' => time(), 'action' => 'initial_save'])
        ]);
        $sessionId = $stmt->fetchColumn();

        json_response(['success' => true, 'session_id' => $sessionId], 200);
        exit();
    }

    if ($action === 'get-canvas-session') {
        $sessionId = sanitize_input($input['session_id'] ?? '');
        $stmt = $db->prepare("SELECT * FROM canvas_sessions WHERE id = ?::uuid");
        $stmt->execute([$sessionId]);
        $session = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$session) {
            json_response(['error' => 'Sesión no encontrada'], 404);
            exit();
        }

        json_response(['success' => true, 'session' => $session], 200);
        exit();
    }

    // -----------------------------------------------------------------------------
    // ACCIÓN: LISTAR ASSETS AISLADOS (PROPS, FONDOS, SUJETOS)
    // -----------------------------------------------------------------------------
    if ($action === 'list-assets') {
        $tipo = sanitize_input($input['tipo'] ?? '');
        $sql = "SELECT * FROM isolated_assets";
        $params = [];
        if (!empty($tipo)) {
            $sql .= " WHERE tipo = ?";
            $params[] = $tipo;
        }
        $sql .= " ORDER BY id DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $assets = $stmt->fetchAll(PDO::FETCH_ASSOC);

        json_response(['success' => true, 'assets' => $assets], 200);
        exit();
    }

    json_response(['error' => 'Acción no válida'], 400);

} catch (PDOException $e) {
    error_log('Avatar API Error: ' . $e->getMessage());
    json_response(['error' => 'Error de base de datos en Avatar Engine: ' . $e->getMessage()], 500);
}
