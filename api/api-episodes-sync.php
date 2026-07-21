<?php
/**
 * 📻 LA CUEVA DEL GÜERO - AUTO-SYNC API (YOUTUBE & SPOTIFY)
 * Endpoint: /api/api-episodes-sync.php
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

    // Auto-crear tabla episodes_sync si no existe (Self-healing)
    $db->exec("
        CREATE TABLE IF NOT EXISTS episodes_sync (
            id SERIAL PRIMARY KEY,
            plataforma VARCHAR(50) UNIQUE NOT NULL,
            titulo VARCHAR(255),
            embed_id VARCHAR(255) NOT NULL,
            portada_url TEXT,
            audio_url TEXT,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    // METODO GET: Devuelve los últimos episodios sincronizados para YouTube y Spotify
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // 1. Intentar auto-sincronizar desde el feed de YouTube en tiempo real
        try {
            $feedUrl = "https://www.youtube.com/feeds/videos.xml?channel_id=UCpG7A8rZ1pCd2L5xT244y2g";
            $ctx = stream_context_create(['http' => ['timeout' => 3]]);
            $xmlStr = @file_get_contents($feedUrl, false, $ctx);
            if ($xmlStr) {
                $xml = @simplexml_load_string($xmlStr);
                if ($xml && isset($xml->entry[0])) {
                    $entry = $xml->entry[0];
                    $ytNamespace = $entry->children('yt', true);
                    $ytId = (string)$ytNamespace->videoId;
                    $ytTitle = (string)$entry->title;

                    if ($ytId) {
                        $stmt = $db->prepare("
                            INSERT INTO episodes_sync (plataforma, titulo, embed_id, updated_at)
                            VALUES ('youtube', ?, ?, NOW())
                            ON CONFLICT (plataforma) DO UPDATE SET
                                titulo = EXCLUDED.titulo,
                                embed_id = EXCLUDED.embed_id,
                                updated_at = NOW()
                        ");
                        $stmt->execute([$ytTitle, $ytId]);
                    }
                }
            }
        } catch (Exception $e) {
            // Ignorar errores de conexión y usar caché o fallback
        }

        $stmt = $db->query("SELECT * FROM episodes_sync");
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $yt = null;
        $sp = null;

        foreach ($records as $r) {
            if ($r['plataforma'] === 'youtube') $yt = $r;
            if ($r['plataforma'] === 'spotify') $sp = $r;
        }

        // Si aún no existen registros iniciales, devolver valores de producción por defecto
        if (!$yt) {
            $yt = [
                'plataforma' => 'youtube',
                'titulo'     => 'Alan Barraza \"El Perro\" - Lealtad y calle',
                'embed_id'   => 'dxEoV_H8flA', // Video de respaldo
            ];
        }

        if (!$sp) {
            $sp = [
                'plataforma'  => 'spotify',
                'titulo'      => 'Audio & Portada Oficial - La Cueva del Güero',
                'embed_id'    => '4kIy0j2',
                'portada_url' => 'https://img.youtube.com/vi/dxEoV_H8flA/maxresdefault.jpg',
                'audio_url'   => 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-1.mp3'
            ];
        }

        json_response([
            'success' => true,
            'youtube' => $yt,
            'spotify' => $sp,
            'auto_sync' => true
        ], 200);
        exit();
    }

    // METODO POST: Actualización manual o por webhook del canal
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $plataforma = sanitize_input($input['plataforma'] ?? '');
    $embedId = sanitize_input($input['embed_id'] ?? '');
    $titulo = sanitize_input($input['titulo'] ?? 'Nuevo Lanzamiento');
    $portadaUrl = $input['portada_url'] ?? '';
    $audioUrl = $input['audio_url'] ?? '';

    if (empty($plataforma) || empty($embedId)) {
        json_response(['error' => 'Plataforma y Embed ID son obligatorios'], 400);
        exit();
    }

    $stmt = $db->prepare("
        INSERT INTO episodes_sync (plataforma, titulo, embed_id, portada_url, audio_url, updated_at)
        VALUES (?, ?, ?, ?, ?, NOW())
        ON CONFLICT (plataforma) DO UPDATE SET
            titulo = EXCLUDED.titulo,
            embed_id = EXCLUDED.embed_id,
            portada_url = EXCLUDED.portada_url,
            audio_url = EXCLUDED.audio_url,
            updated_at = NOW()
    ");
    $stmt->execute([$plataforma, $titulo, $embedId, $portadaUrl, $audioUrl]);

    json_response([
        'success' => true,
        'message' => "Contenedor de $plataforma sincronizado automáticamente.",
        'record'  => ['plataforma' => $plataforma, 'embed_id' => $embedId]
    ], 200);
    exit();

} catch (PDOException $e) {
    error_log('Sync API Error: ' . $e->getMessage());
    json_response(['error' => 'Error de base de datos en Auto-Sync: ' . $e->getMessage()], 500);
}
