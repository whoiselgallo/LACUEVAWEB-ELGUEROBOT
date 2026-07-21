<?php
/**
 * Script de inicialización de Base de Datos para Neon.tech (PostgreSQL)
 * Endpoint: /db_init.php
 */

header('Content-Type: application/json; charset=utf-8');

// Cargar configuración central
require_once __DIR__ . '/config/config.php';

try {
    $db = db_connect();

    // 1. Crear tabla knowledge_base
    $db->exec("
        CREATE TABLE IF NOT EXISTS knowledge_base (
            id SERIAL PRIMARY KEY,
            nombre VARCHAR(255),
            tipo VARCHAR(50),
            storytelling TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT unique_name_type UNIQUE (nombre, tipo)
        )
    ");

    // 2. Crear tabla invitados
    $db->exec("
        CREATE TABLE IF NOT EXISTS invitados (
            id SERIAL PRIMARY KEY,
            nombre VARCHAR(255) UNIQUE,
            ocupacion TEXT,
            signo VARCHAR(100),
            fecha_nacimiento VARCHAR(100),
            barrio VARCHAR(255),
            trayectoria TEXT,
            herida TEXT,
            incomodo TEXT,
            gustos TEXT,
            fecha_propuesta VARCHAR(100),
            ficha TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    // 3. Crear tabla conversations
    $db->exec("
        CREATE TABLE IF NOT EXISTS conversations (
            id SERIAL PRIMARY KEY,
            user_id VARCHAR(255),
            visit_type VARCHAR(100),
            user_message TEXT,
            bot_answer TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    // 4. Crear tabla evaluaciones_cueva
    $db->exec("
        CREATE TABLE IF NOT EXISTS evaluaciones_cueva (
            id SERIAL PRIMARY KEY,
            nombre VARCHAR(255),
            calificacion INT,
            comentario TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    // 5. Crear tabla users (Autenticación Corporativa)
    $db->exec("
        CREATE TABLE IF NOT EXISTS users (
            id SERIAL PRIMARY KEY,
            email VARCHAR(255) UNIQUE NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            nombre VARCHAR(100),
            role VARCHAR(50) DEFAULT 'admin',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            last_login TIMESTAMP
        )
    ");

    // 6. Crear tabla avatars (Avatar-Engine & Personajes)
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

    // 7. Crear tabla galeria
    $db->exec("
        CREATE TABLE IF NOT EXISTS galeria (
            id SERIAL PRIMARY KEY,
            titulo VARCHAR(255),
            categoria VARCHAR(100) DEFAULT 'La Cueva',
            imagen_url TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    // 8. Crear tabla episodes_sync (Auto-Sync YouTube & Spotify)
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

    echo json_encode([
        "success" => true,
        "message" => "Tablas creadas e inicializadas correctamente en Neon.tech (PostgreSQL)"
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => "Error al inicializar la base de datos: " . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}
