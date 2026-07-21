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
    // ACCIÓN: GENERAR AVATAR HUMANOIDE AISLADO (COMIC NEÓN)
    // -----------------------------------------------------------------------------
    if ($action === 'generate') {
        $nombre = sanitize_input($input['nombre'] ?? 'El Güero');
        $actividad = sanitize_input($input['actividad'] ?? 'sentado');
        $ropa = sanitize_input($input['ropa'] ?? 'casual');

        // Buscar rasgos base en la BD
        $stmt = $db->prepare("SELECT * FROM avatars WHERE LOWER(nombre) = LOWER(?)");
        $stmt->execute([$nombre]);
        $profile = $stmt->fetch(PDO::FETCH_ASSOC);

        $rasgos = $profile['rasgos_faciales'] ?? "Personaje icónico de La Cueva del Güero";

        $promptConsolidado = "Comic Neón Art Style, La Cueva del Güero signature aesthetic. Isolated humanoid character of '$nombre', $actividad pose, wearing $ropa outfit. Facial features: $rasgos. STRICT RULE: Transparent PNG background, ZERO background furniture, no chair, no sofa, no microphone, strictly isolated full-body character.";

        json_response([
            'success'   => true,
            'character' => $nombre,
            'actividad' => $actividad,
            'ropa'      => $ropa,
            'style'     => 'Comic Neón (La Cueva del Güero)',
            'background' => 'Transparent PNG (Isolated Humanoid)',
            'prompt'    => $promptConsolidado
        ], 200);
        exit();
    }

    json_response(['error' => 'Acción no válida'], 400);

} catch (PDOException $e) {
    error_log('Avatar API Error: ' . $e->getMessage());
    json_response(['error' => 'Error de base de datos en Avatar Engine: ' . $e->getMessage()], 500);
}
