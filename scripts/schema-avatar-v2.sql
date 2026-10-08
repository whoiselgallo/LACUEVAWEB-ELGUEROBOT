-- ═══════════════════════════════════════════════════════════════════════
-- MIGRACIÓN AVATAR-ENGINE v2.0: THE FORGE & THE DARKROOM
-- Base de Datos PostgreSQL (Neon.tech)
-- ═══════════════════════════════════════════════════════════════════════

-- 1. Actualización a la tabla de avatares: Biometría y validación legal
ALTER TABLE avatars 
ADD COLUMN IF NOT EXISTS vector_biometrico JSONB,
ADD COLUMN IF NOT EXISTS status_legal BOOLEAN DEFAULT FALSE;

-- 2. Nueva tabla para el sistema de capas de Fabric.js (The Darkroom)
CREATE TABLE IF NOT EXISTS canvas_sessions (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    avatar_id INT REFERENCES avatars(id) ON DELETE CASCADE,
    nombre_proyecto VARCHAR(255) DEFAULT 'Nuevo Poster',
    canvas_state JSONB NOT NULL,
    versiones_historicas JSONB,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_canvas_sessions_avatar ON canvas_sessions(avatar_id);
COMMENT ON TABLE canvas_sessions IS 'Sesiones y capas completas de diseño interactivo en Fabric.js (The Darkroom)';

-- 3. Nueva tabla para Assets segmentados (Props, Sujetos, Fondos, Sombras)
CREATE TABLE IF NOT EXISTS isolated_assets (
    id SERIAL PRIMARY KEY,
    avatar_id INT REFERENCES avatars(id) ON DELETE SET NULL,
    tipo VARCHAR(50) DEFAULT 'sujeto', -- 'sujeto', 'fondo', 'prop', 'sombra'
    nombre VARCHAR(255) NOT NULL,
    url_asset TEXT NOT NULL,
    es_transparente BOOLEAN DEFAULT TRUE,
    metadata_asset JSONB DEFAULT '{}'::jsonb,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_isolated_assets_avatar ON isolated_assets(avatar_id);
CREATE INDEX IF NOT EXISTS idx_isolated_assets_tipo ON isolated_assets(tipo);
COMMENT ON TABLE isolated_assets IS 'Catálogo de capas segmentadas transparentes listas para ensamblaje en The Darkroom';
