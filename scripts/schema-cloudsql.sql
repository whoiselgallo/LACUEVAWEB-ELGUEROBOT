-- ═══════════════════════════════════════════════════════════════════════
-- SCHEMA MAESTRO - LA CUEVA DEL GÜERO
-- Google Cloud SQL PostgreSQL 17.11
-- Instancia: la-cueva-506402:us-central1:la-cueva-del-guero
-- Ejecutar en la base de datos: lacueva_db
-- ═══════════════════════════════════════════════════════════════════════

-- 1. KNOWLEDGE BASE
CREATE TABLE IF NOT EXISTS knowledge_base (
    id          SERIAL PRIMARY KEY,
    nombre      VARCHAR(255) NOT NULL,
    tipo        VARCHAR(100) NOT NULL DEFAULT 'storytelling',
    storytelling TEXT,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_knowledge_base_tipo ON knowledge_base (tipo);
CREATE INDEX IF NOT EXISTS idx_knowledge_base_nombre ON knowledge_base (nombre);
COMMENT ON TABLE knowledge_base IS 'Expedientes: escaletas, guiones, cue cards y curaduria de invitados';

-- 2. INVITADOS
CREATE TABLE IF NOT EXISTS invitados (
    id               SERIAL PRIMARY KEY,
    nombre           VARCHAR(255) NOT NULL,
    ocupacion        TEXT,
    signo            VARCHAR(50),
    fecha_nacimiento DATE,
    barrio           VARCHAR(255),
    trayectoria      TEXT,
    herida           TEXT,
    incomodo         TEXT,
    gustos           TEXT,
    fecha_propuesta  DATE,
    created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_invitados_nombre ON invitados (nombre);
COMMENT ON TABLE invitados IS 'Registro de invitados con datos del cuestionario de storytelling';

-- 3. CONVERSATIONS
CREATE TABLE IF NOT EXISTS conversations (
    id           SERIAL PRIMARY KEY,
    user_id      VARCHAR(255),
    visit_type   VARCHAR(100) DEFAULT 'guest',
    user_message TEXT,
    bot_answer   TEXT,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_conversations_user ON conversations (user_id);
CREATE INDEX IF NOT EXISTS idx_conversations_created ON conversations (created_at DESC);
COMMENT ON TABLE conversations IS 'Log de conversaciones con El Guero Bot';

-- 4. USERS
CREATE TABLE IF NOT EXISTS users (
    id            SERIAL PRIMARY KEY,
    email         VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    nombre        VARCHAR(100),
    role          VARCHAR(50) DEFAULT 'admin',
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login    TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_users_email ON users (email);
COMMENT ON TABLE users IS 'Administradores del Dashboard PRO';

-- 5. AVATARS
CREATE TABLE IF NOT EXISTS avatars (
    id                  SERIAL PRIMARY KEY,
    nombre              VARCHAR(100) UNIQUE NOT NULL,
    episodio            VARCHAR(100),
    foto_frente         TEXT,
    foto_perfil_izq     TEXT,
    foto_perfil_der     TEXT,
    imagen_limpia       TEXT,
    consentimiento_pdf  TEXT,
    rasgos_faciales     TEXT,
    estilo_casual       TEXT,
    estilo_deportivo    TEXT,
    estilo_formal       TEXT,
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_avatars_nombre ON avatars (nombre);
COMMENT ON TABLE avatars IS 'Fotos y datos para generacion de avatares de invitados';

-- 6. GALERIA
CREATE TABLE IF NOT EXISTS galeria (
    id          SERIAL PRIMARY KEY,
    titulo      VARCHAR(255),
    categoria   VARCHAR(100) DEFAULT 'La Cueva',
    imagen_url  TEXT NOT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_galeria_categoria ON galeria (categoria);
COMMENT ON TABLE galeria IS 'Galeria de imagenes del podcast';

-- 7. EPISODES_SYNC
CREATE TABLE IF NOT EXISTS episodes_sync (
    id          SERIAL PRIMARY KEY,
    plataforma  VARCHAR(50) UNIQUE NOT NULL,
    titulo      VARCHAR(255),
    embed_id    VARCHAR(255) NOT NULL,
    portada_url TEXT,
    audio_url   TEXT,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_episodes_plataforma ON episodes_sync (plataforma);
COMMENT ON TABLE episodes_sync IS 'Episodios sincronizados con YouTube, Spotify, etc.';

-- VERIFICACION
SELECT tablename AS tabla FROM pg_tables WHERE schemaname = 'public' ORDER BY tablename;
