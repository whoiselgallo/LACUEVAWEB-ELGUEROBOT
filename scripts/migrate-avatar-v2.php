<?php
/**
 * Migración en Neon.tech para Avatar Engine v2.0 (The Forge & The Darkroom)
 */
require_once __DIR__ . '/../config/config.php';

try {
    echo "============================================================\n";
    echo "🚀 EJECUTANDO MIGRACIÓN: AVATAR ENGINE v2.0 (THE FORGE & DARKROOM)\n";
    echo "Host: " . DB_HOST . " | DB: " . DB_NAME . "\n";
    echo "============================================================\n";

    $db = db_connect();
    echo "✓ Conectado a Neon PostgreSQL exitosamente.\n";

    $sql = file_get_contents(__DIR__ . '/schema-avatar-v2.sql');
    $db->exec($sql);
    echo "✓ Tablas 'canvas_sessions' e 'isolated_assets' creadas/verificadas.\n";
    echo "✓ Columnas 'vector_biometrico' y 'status_legal' integradas en 'avatars'.\n";

    // Insertar registros demo en isolated_assets si está vacía
    $count = $db->query("SELECT COUNT(*) FROM isolated_assets")->fetchColumn();
    if ($count == 0) {
        echo "\nInsertando assets iniciales de demostración...\n";
        $db->exec("
            INSERT INTO isolated_assets (tipo, nombre, url_asset, es_transparente) VALUES
            ('fondo', 'Muro de Ladrillo Neón Mexicali', '../images/galeria/fondo_cueva_neon.jpg', FALSE),
            ('prop', 'Sofá Neón La Cueva', '../images/galeria/sofa_neon_cueva.png', TRUE),
            ('prop', 'Micrófono Vintage Podcast', '../images/galeria/microfono_vintage.png', TRUE),
            ('sujeto', 'El Güero (Avatar Oficial Base)', '../images/avatar-alan-barraza.png', TRUE);
        ");
        echo "✓ Assets demo inicializados en la biblioteca.\n";
    }

    $c1 = $db->query("SELECT COUNT(*) FROM avatars")->fetchColumn();
    $c2 = $db->query("SELECT COUNT(*) FROM isolated_assets")->fetchColumn();
    $c3 = $db->query("SELECT COUNT(*) FROM canvas_sessions")->fetchColumn();

    echo "\n------------------------------------------------------------\n";
    echo "📊 ESTADO ACTUAL DEL SISTEMA:\n";
    echo "• Avatares registrados:        $c1\n";
    echo "• Assets segmentados aislados: $c2\n";
    echo "• Sesiones de lienzo activas:  $c3\n";
    echo "------------------------------------------------------------\n";
    echo "✅ FASE 1: BASE DE DATOS MIGRADA EXITOSAMENTE.\n";

} catch (Exception $e) {
    echo "❌ Error en migración: " . $e->getMessage() . "\n";
    exit(1);
}
