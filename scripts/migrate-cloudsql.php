<?php
/**
 * Script de inicialización y migración de tablas para Google Cloud SQL
 */
require_once __DIR__ . '/../config/config.php';

try {
    echo "Conectando a Google Cloud SQL (" . DB_HOST . ":" . DB_PORT . " / " . DB_NAME . ")...\n";
    $db = db_connect();
    echo "Conexión exitosa!\n";

    $sql = file_get_contents(__DIR__ . '/schema-cloudsql.sql');
    
    // Agregar tablas adicionales necesarias para el dashboard
    $extraSql = "
    CREATE TABLE IF NOT EXISTS evaluaciones_cueva (
        id SERIAL PRIMARY KEY,
        invitado_id INT,
        puntuacion INT DEFAULT 0,
        nivel VARCHAR(50) DEFAULT 'Medio',
        observaciones TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

    CREATE TABLE IF NOT EXISTS video_jobs (
        id SERIAL PRIMARY KEY,
        job_id VARCHAR(100) UNIQUE NOT NULL,
        action VARCHAR(100) NOT NULL,
        status VARCHAR(50) DEFAULT 'queued',
        input_uri TEXT,
        output_uri TEXT,
        result_file VARCHAR(255),
        logs TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );
    ";

    $fullSql = $sql . "\n" . $extraSql;

    $db->exec($fullSql);
    echo "Tablas creadas e inicializadas exitosamente en Cloud SQL!\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
