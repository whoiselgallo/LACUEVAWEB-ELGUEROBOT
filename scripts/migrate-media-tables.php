<?php
/**
 * Migración de Tablas Multimedia (media_raw y media_edited) en Neon.tech PostgreSQL
 */
require_once __DIR__ . '/../config/config.php';

try {
    echo "============================================================\n";
    echo "📦 EJECUTANDO MIGRACIÓN DE TABLAS MULTIMEDIA\n";
    echo "Host: " . DB_HOST . " | DB: " . DB_NAME . "\n";
    echo "============================================================\n";

    $db = db_connect();
    echo "✓ Conexión establecida exitosamente con PostgreSQL\n";

    $sql = file_get_contents(__DIR__ . '/schema-media-tables.sql');
    $db->exec($sql);
    echo "✓ Tablas 'media_raw' y 'media_edited' creadas o verificadas exitosamente.\n";

    // Insertar registros de prueba/demostración si las tablas están vacías
    $checkRaw = $db->query("SELECT COUNT(*) FROM media_raw")->fetchColumn();
    if ($checkRaw == 0) {
        echo "\nPopulando registros iniciales de demostración...\n";
        
        // 1. Raw demo
        $stmtRaw = $db->prepare("
            INSERT INTO media_raw (
                invitado_id, titulo, tipo_medio, camara_angulo, archivo_nombre, 
                duracion_segundos, resolucion, gcs_bucket, gcs_uri, tags
            ) VALUES (
                5, 'Entrevista Aurelio Gonzalez - Toma General Bruta', 'video', 'general_mesa', 
                'raw_aurelio_carbajal_cam1.mp4', 2450.50, '1920x1080', 'cueva-raw-videos', 
                'gs://cueva-raw-videos/raw/raw_aurelio_carbajal_cam1.mp4', '{entrevista,aurelio,carbajal}'
            ) RETURNING id;
        ");
        $stmtRaw->execute();
        $rawId = $stmtRaw->fetchColumn();
        echo "✓ Registro en media_raw creado (ID: $rawId)\n";

        // 2. Edited demo
        $stmtEdited = $db->prepare("
            INSERT INTO media_edited (
                raw_id, invitado_id, titulo, formato_destino, aspect_ratio, resolucion, 
                duracion_segundos, archivo_nombre, gcs_bucket, gcs_uri, loudness_lufs, 
                tiene_subtitulos_karaoke, tiene_autocrop_rostros, silencios_eliminados_seg, estado_publicacion, plataformas_destino
            ) VALUES (
                ?, 5, 'Clip Viral Aurelio: La Constancia Supera al Talento (Vertical 9:16)', 'tiktok_9_16', '9:16', '1080x1920', 
                42.20, 'clip_aurelio_constancia_9_16.mp4', 'cueva-processed-videos', 
                'gs://cueva-processed-videos/clips/clip_aurelio_constancia_9_16.mp4', -14.00, 
                TRUE, TRUE, 6.80, 'aprobado', '{tiktok,instagram_reels,youtube_shorts}'
            );
        ");
        $stmtEdited->execute([$rawId]);
        echo "✓ Registro en media_edited creado (Vinculado a Raw ID: $rawId)\n";
    }

    // Mostrar estado de tablas
    echo "\n------------------------------------------------------------\n";
    echo "📊 RESUMEN ACTUAL DE TABLAS MULTIMEDIA:\n";
    $countRaw = $db->query("SELECT COUNT(*) FROM media_raw")->fetchColumn();
    $countEdited = $db->query("SELECT COUNT(*) FROM media_edited")->fetchColumn();
    echo "• Total registros en media_raw:    $countRaw\n";
    echo "• Total registros en media_edited: $countEdited\n";
    echo "------------------------------------------------------------\n";
    echo "✅ MIGRACIÓN MULTIMEDIA CONCLUIDA EXITOSAMENTE.\n";

} catch (Exception $e) {
    echo "❌ Error ejecutando migración: " . $e->getMessage() . "\n";
    exit(1);
}
